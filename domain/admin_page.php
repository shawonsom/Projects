<?php
// --- 1. CONFIGURATION ---
include 'config.php'; 
// Make sure config.php defines: $host, $username, $password, $dbname, $TABLE_NAME
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Static Authentication Credentials (CHANGE IN PRODUCTION!)
$STATIC_USER = "admin";
$STATIC_PASS = "admin123"; // CHANGE THIS!!

// --- 2. AUTHENTICATION LOGIC ---
session_start();

// Logout handling
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// Check if already authenticated
if (!isset($_SESSION['authenticated']) || $_SESSION['authenticated'] !== true) {
    // Process login attempt
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['username']) && isset($_POST['password'])) {
        if ($_POST['username'] === $STATIC_USER && $_POST['password'] === $STATIC_PASS) {
            $_SESSION['authenticated'] = true;
            header("Location: " . $_SERVER['PHP_SELF']);
            exit;
        } else {
            $login_error = "Invalid username or password.";
        }
    }

    // Show login form
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Login - SSL List Manager</title>
        <style>
            body { font-family: Arial, sans-serif; background: #f4f4f4; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
            .login-box { background: white; padding: 30px; border-radius: 10px; box-shadow: 0 0 15px rgba(0,0,0,0.2); width: 350px; }
            h2 { text-align: center; color: #333; }
            input[type="text"], input[type="password"] { width: 100%; padding: 12px; margin: 10px 0; border: 1px solid #ccc; border-radius: 5px; box-sizing: border-box; }
            button { width: 100%; padding: 12px; background: #5cb85c; color: white; border: none; border-radius: 5px; font-size: 16px; cursor: pointer; }
            button:hover { background: #4cae4c; }
            .error { color: #d9534f; text-align: center; margin: 10px 0; }
        </style>
    </head>
    <body>
        <div class="login-box">
            <h2>SSL List Manager</h2>
            <?php if (isset($login_error)) echo "<p class='error'>$login_error</p>"; ?>
            <form method="POST">
                <input type="text" name="username" placeholder="Username" required autofocus>
                <input type="password" name="password" placeholder="Password" required>
                <button type="submit">Login</button>
            </form>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// --- 3. USER IS AUTHENTICATED → MAIN APP ---

// Database connection with error handling
$mysqli = new mysqli($host, $username, $password, $dbname);

if ($mysqli->connect_error) {
    error_log("Connection failed: " . $mysqli->connect_error);
    die("Database connection failed. Please check config.");
}

// Optional: Set charset
$mysqli->set_charset("utf8mb4");

$message = "";
// === NEW: AJAX FIND DOMAIN ===
if (isset($_POST['action']) && $_POST['action'] === 'find') {
    $domain = trim($_POST['domain']);
    $stmt = $mysqli->prepare("SELECT domain_name, expire_date, mail_to FROM `$TABLE_NAME` WHERE domain_name = ? LIMIT 1");
    $stmt->bind_param("s", $domain);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        header('Content-Type: application/json');
        echo json_encode($row);
    } else {
        echo "NOT_FOUND";
    }
    $stmt->close();
    exit;
}
// --- 4. HANDLE ACTIONS ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    
    // ADD NEW RECORD
    if ($_POST['action'] === 'add') {
        $domain_name = trim($_POST['new_domain_name']);
        $expire_date = $_POST['new_expire_date'];
        $mail_to     = trim($_POST['new_mail_to'] ?? '');

        if (empty($domain_name) || empty($expire_date)) {
            $message = "Domain Name and Expire Date are required.";
        } else {
            $stmt = $mysqli->prepare("INSERT INTO `$TABLE_NAME` (`domain_name`, `expire_date`, `mail_to`) VALUES (?, ?, ?)");
            if ($stmt === false) {
                $message = "Prepare failed: " . $mysqli->error;
            } else {
                $stmt->bind_param("sss", $domain_name, $expire_date, $mail_to);
                if ($stmt->execute()) {
                    $message = "New record added successfully!";
                } else {
                    $message = "Error adding record: " . $stmt->error;
                }
                $stmt->close();
            }
        }
    }

    // MODIFY RECORD
    if ($_POST['action'] === 'modify') {
        $search_domain   = trim($_POST['search_domain']);
        $new_expire_date = $_POST['mod_expire_date'];
        $new_mail_to     = trim($_POST['mod_mail_to'] ?? '');

        if (empty($search_domain)) {
            $message = "Domain name is required for modification.";
        } else {
            $stmt = $mysqli->prepare("UPDATE `$TABLE_NAME` SET `expire_date` = ?, `mail_to` = ? WHERE `domain_name` = ?");
            if ($stmt === false) {
                $message = "Prepare failed: " . $mysqli->error;
            } else {
                $stmt->bind_param("sss", $new_expire_date, $new_mail_to, $search_domain);
                if ($stmt->execute()) {
                    $message = $stmt->affected_rows > 0
                        ? "Record for **$search_domain** updated successfully!"
                        : "No record found for **$search_domain** or no changes made.";
                } else {
                    $message = "Error updating record: " . $stmt->error;
                }
                $stmt->close();
            }
        }
    }
}

// --- 5. FETCH ALL RECORDS ---
$data = [];
$result = $mysqli->query("SELECT id, domain_name, expire_date, mail_to FROM `$TABLE_NAME` ORDER BY expire_date ASC");

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    $result->free();
} else {
    $message .= " <br>Database query error: " . $mysqli->error;
}

$mysqli->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>SSL List Manager</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        body { font-family: Arial, sans-serif; background: #f4f4f4; margin: 0; padding: 20px; }
        .container { max-width: 1200px; margin: auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
        h1 { color: #333; padding-bottom: 10px; border-bottom: 2px solid #eee; }
        .logout { float: right; background: #d9534f; color: white; padding: 8px 16px; text-decoration: none; border-radius: 8px; }
        .logout:hover { background: #c9302c; }
        .message { padding: 12px; margin: 15px 0; border-radius: 4px; font-weight: bold; }
        .success { background: #dff0d8; color: #3c763d; border: 1px solid #d6e9c6; }
        .error   { background: #f2dede; color: #a94442; border: 1px solid #ebccd1; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { padding: 12px; text-align: left; border: 1px solid #ddd; }
        th { background: #5cb85c; color: white; }
        input[type="text"], input[type="date"] { width: 100%; padding: 8px; margin: 5px 0; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        button { background: #4CAF50; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; }
        button:hover { background: #45a049; }
        .form-box { background: #f9f9f9; padding: 20px; margin-bottom: 20px; border: 1px solid #eee; border-radius: 6px; }
        .col { float: left; width: 48%; margin-right: 4%; }
        .col:last-child { margin-right: 0; }
        @media (max-width: 900px) { .col { width: 100%; margin: 10px 0; } }
        .clear { clear: both; }
    </style>
</head>
<body>
<div class="container">
    <h1>SSL List Manager
        <a href="?refresh=1" class="logout" style="background:#337ab7; margin-right:8px;">  Refresh  </a>
        <!-- <a href="?logout=true" class="logout">  Logout</a> -->
        <a href="logout.php" class="logout" onclick="closeTab()">Logout</a>

    </h1>

    <?php if ($message): ?>
        <div class="message <?= strpos($message, 'success') !== false || strpos($message, 'added') !== false ? 'success' : 'error' ?>">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>
<div style="display: flex; gap: 30px; flex-wrap: wrap;">
    <!-- ========== ADD NEW RECORD ========== -->
    <div style="flex: 1; min-width: 300px;">
        <div class="form-box">
            <h2>Add New SSL Record</h2>
            <form method="POST">
                <input type="hidden" name="action" value="add">
                <p><input type="text" name="new_domain_name" placeholder="example.com" required></p>
                <p><input type="date" name="new_expire_date" required></p>
                <p><input type="text" name="new_mail_to" placeholder="alert@example.com (optional)"></p>
                <p><button type="submit">Add Record</button></p>
            </form>
        </div>
    </div>

    <!-- ========== MODIFY EXISTING RECORD (2-STEP) ========== -->
    <div style="flex: 1; min-width: 300px;">
        <div class="form-box">
            <h2>Modify Existing Record</h2>

            <!-- Step 1: Search Domain -->
            <form id="searchForm" style="margin-bottom: 15px;">
                <p>
                    <input type="text" id="search_domain" placeholder="Enter domain to modify" required style="width: 70%;">
                    <button type="button" onclick="findDomain()" style="width: 28%;">Find Domain</button>
                </p>
            </form>

            <!-- Step 2: Edit Form (initially hidden) -->
            <form method="POST" id="modifyForm" style="display: none;">
                <input type="hidden" name="action" value="modify">
                <input type="hidden" name="search_domain" id="modify_domain">

                <p><strong>Domain:</strong> <span id="display_domain"></span></p>

                <p><input type="date" name="mod_expire_date" id="mod_expire_date" required></p>
                <p><input type="text" name="mod_mail_to" id="mod_mail_to" placeholder="Notification email (optional)"></p>

                <p>
                    <button type="submit">Update Record</button>
                    <button type="button" onclick="cancelModify()" style="background:#999;">Cancel</button>
                </p>
            </form>

            <div id="searchResult"></div>
        </div>
    </div>
</div>
<div style="clear:both;"></div>

<script>
async function findDomain() {
    const domain = document.getElementById('search_domain').value.trim();
    if (!domain) return alert('Please enter a domain name');

    const resultDiv = document.getElementById('searchResult');
    resultDiv.innerHTML = 'Searching...';

    try {
        const response = await fetch('', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'action=find&domain=' + encodeURIComponent(domain)
        });
        const text = await response.text();

        if (text.includes('NOT_FOUND')) {
            resultDiv.innerHTML = '<p style="color:red;">Domain not found!</p>';
        } else {
            const data = JSON.parse(text);
            // Fill and show the modify form
            document.getElementById('modify_domain').value = data.domain_name;
            document.getElementById('display_domain').textContent = data.domain_name;
            document.getElementById('mod_expire_date').value = data.expire_date;
            document.getElementById('mod_mail_to').value = data.mail_to || '';

            document.getElementById('modifyForm').style.display = 'block';
            document.getElementById('searchForm').style.display = 'none';
            resultDiv.innerHTML = '';
        }
    } catch (e) {
        resultDiv.innerHTML = '<p style="color:red;">Error: ' + e.message + '</p>';
    }
}

function cancelModify() {
    document.getElementById('modifyForm').style.display = 'none';
    document.getElementById('searchForm').style.display = 'block';
    document.getElementById('search_domain').value = '';
    document.getElementById('searchResult').innerHTML = '';
}
</script>


    <h2>Current SSL Certificates</h2>
    <?php if ($data): ?>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Domain Name</th>
                <th>Expire Date</th>
                <th>Notification Email</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($data as $row): ?>
            <tr>
                <td><?= htmlspecialchars($row['id']) ?></td>
                <td><strong><?= htmlspecialchars($row['domain_name']) ?></strong></td>
                <td><?= htmlspecialchars($row['expire_date']) ?></td>
                <td><?= htmlspecialchars($row['mail_to'] ?: '-') ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php else: ?>
        <p>No records found.</p>
    <?php endif; ?>
</div>
</body>
</html>