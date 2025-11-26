<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SSL Certificate Monitor</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        .status-critical { @apply bg-red-100 text-red-800 border-red-300; }
        .status-warning { @apply bg-yellow-100 text-yellow-800 border-yellow-300; }
        .status-safe { @apply bg-green-100 text-green-800 border-green-300; }
        .days-critical { @apply text-red-600 font-bold; }
        .days-warning { @apply text-yellow-600 font-bold; }
        .days-safe { @apply text-green-600 font-bold; }
        .days-expired { @apply text-red-700 font-bold; }
    </style>
</head>
<body class="bg-gray-50 text-gray-800 font-sans transition-colors duration-300">
    <div class="max-w-7xl mx-auto p-6">

        <!-- Header -->
        <div class="flex justify-between items-center mb-8">
            <div>
                <h1 class="text-4xl font-bold text-purple-700 flex items-center gap-3">
                    <i class="fas fa-lock"></i> SSL Certificate Monitor
                </h1>
                <p class="text-gray-600 mt-2">Real-time monitoring of SSL certificate expiration dates</p>
            </div>
            <a href="admin_page.php" target="_blank" class="flex items-center gap-3 cursor-pointer select-none bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded"> <span>administrator</span> </a>
          </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-6 mb-8">
            <?php
            include 'config.php'; 
            $total = $critical = $warning = $safe = 0;

            try {
                $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

                $total = $pdo->query("SELECT COUNT(*) FROM ssl_list")->fetchColumn();

                // Critical: 0–6 days remaining (not expired)
                $critical = $pdo->query("SELECT COUNT(*) FROM ssl_list 
                    WHERE expire_date >= CURDATE() 
                    AND DATEDIFF(expire_date, CURDATE()) <= 6")->fetchColumn();

                // Warning: 7–15 days
                $warning = $pdo->query("SELECT COUNT(*) FROM ssl_list 
                    WHERE DATEDIFF(expire_date, CURDATE()) BETWEEN 7 AND 15")->fetchColumn();

                // Safe: 16+ days
                $safe = $pdo->query("SELECT COUNT(*) FROM ssl_list 
                    WHERE expire_date > DATE_ADD(CURDATE(), INTERVAL 15 DAY)")->fetchColumn();

            } catch(PDOException $e) {
                $total = $critical = $warning = $safe = 0;
                $db_error = $e->getMessage();
            }
            ?>

            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm p-6 text-center border border-gray-200 dark:border-gray-700 transition">
                <div class="text-2xl mb-2">Total 🌐</div>
                <div class="text-4xl font-bold text-gray-800 dark:text-gray-200"><?php echo $total; ?></div>
                </div>
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm p-6 text-center border border-gray-200 dark:border-gray-700">
                <div class="text-2xl mb-2">Critical ☠</div>
                <div class="text-4xl font-bold text-red-600"><?php echo $critical; ?></div>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm p-6 text-center border border-gray-200 dark:border-gray-700">
                <div class="text-2xl mb-2">Warning ⚠️</div>
                <div class="text-4xl font-bold text-yellow-600"><?php echo $warning; ?></div>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm p-6 text-center border border-gray-200 dark:border-gray-700">
                <div class="text-2xl mb-2">Safe 👌</div>
                <div class="text-4xl font-bold text-green-600"><?php echo $safe; ?></div>
            </div>
        </div>

        <!-- Search & Refresh -->
 

        <!-- Table -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="bg-gradient-to-r from-purple-600 to-blue-600 text-white px-6 py-4">
                <div class="grid grid-cols-12 font-bold text-sm uppercase tracking-wider">
                    <div class="col-span-3">Domain Name</div>
                    <div class="col-span-2">Expiration Date</div>
                    <div class="col-span-2 text-center">Days Remaining</div>
                    <div class="col-span-2 text-center">Status</div>
                    <div class="col-span-3">Notification Email</div>
                </div>
            </div>

            <div class="divide-y divide-gray-200 dark:divide-gray-700">
                <?php
                if (isset($pdo)) {
                    try {
                        $stmt = $pdo->query("
                            SELECT domain_name, expire_date, mail_to,
                                   DATEDIFF(expire_date, CURDATE()) AS days_left
                            FROM ssl_list
                            WHERE expire_date IS NOT NULL
                            ORDER BY expire_date ASC
                            LIMIT 10
                        ");

                        if ($stmt->rowCount() == 0) {
                            echo '<div class="text-center py-16 text-gray-500 dark:text-gray-400">
                                    <i class="fas fa-info-circle text-5xl mb-4"></i><br>
                                    No SSL certificates found in the database.
                                  </div>';
                        }

                        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                            $days = $row['days_left'];
                            $expireDisplay = $row['expire_date'] ? date('M j, Y', strtotime($row['expire_date'])) : '—';

                            if ($days < 0) {
                                $status = 'Expired';
                                $statusClass = 'bg-red-100 text-red-800 border border-red-300';
                                $daysClass = 'text-red-700';
                                $daysText = abs($days) . ' days ago';
                                                        
                            } elseif ($days <= 6) {
                                $status = 'Critical';
                                $statusClass = 'bg-orange-100 text-orange-800 border border-orange-300';
                                $daysClass = 'text-orange-700';
                                $daysText = $days;
                            
                            } elseif ($days <= 15) {
                                $status = 'Warning';
                                $statusClass = 'bg-yellow-100 text-yellow-900 border border-yellow-300';
                                $daysClass = 'text-yellow-800';
                                $daysText = $days;
                            
                            } else {
                                $status = 'Safe';
                                $statusClass = 'bg-green-100 text-green-800 border border-green-300';
                                $daysClass = 'text-green-700';
                                $daysText = $days;
                            }
                            
                            ?>
                            <div class="grid grid-cols-12 px-6 py-5 hover:bg-gray-50 dark:hover:bg-gray-700 transition text-sm">
                                <div class="col-span-3 font-medium text-gray-900 dark:text-gray-100">
                                    <?php echo htmlspecialchars($row['domain_name']); ?>
                                </div>
                                <div class="col-span-2 text-gray-600 dark:text-gray-400">
                                    <?php echo $expireDisplay; ?>
                                </div>
                                <div class="col-span-2 text-center font-bold <?php echo $daysClass; ?>">
                                    <?php echo $daysText; ?>
                                </div>
                                <div class="col-span-2 text-center">
                                    <span class="px-3 py-1 rounded-full text-xs font-medium border <?php echo $statusClass; ?>">
                                        <?php echo $status; ?>
                                    </span>
                                </div>
                                <div class="col-span-3 text-gray-600 dark:text-gray-400 whitespace-normal break-words">
                                    <?php echo htmlspecialchars($row['mail_to'] ?: '—'); ?>
                                </div>
                            </div>
                            <?php
                        }
                    } catch(Exception $e) {
                        echo '<div class="text-center py-16 text-red-600 dark:text-red-400">
                                <i class="fas fa-exclamation-triangle text-5xl mb-4"></i><br>
                                Failed to load data: ' . htmlspecialchars($e->getMessage()) . '
                              </div>';
                    }
                } else {
                    echo '<div class="text-center py-16 text-red-600 dark:text-red-400">
                            <i class="fas fa-exclamation-triangle text-5xl mb-4"></i><br>
                            Database connection failed.
                          </div>';
                }
                ?>
            </div>

            <div class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400 border-t border-gray-200 dark:border-gray-700">
                Last updated: <?php echo date('M j, Y g:i A'); ?> 
                • Showing 10 soonest-to-expire certificates (including expired)
            </div>
        </div>
    </div>

    <script>
        const toggle = document.getElementById('darkMode');
        const dot = document.querySelector('.dot');

        toggle.addEventListener('change', function() {
            document.documentElement.classList.toggle('dark');
            document.body.classList.toggle('bg-gray-900');
            document.body.classList.toggle('text-white');

            if (this.checked) {
                dot.style.transform = 'translateX(100%)';
                dot.style.backgroundColor = '#c4b5fd';
            } else {
                dot.style.transform = 'translateX(0)';
                dot.style.backgroundColor = '#fff';
            }
        });
    </script>
</body>
</html>