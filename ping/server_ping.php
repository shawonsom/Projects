<?php
header('Content-Type: application/json');
// Remove or restrict this in production! Only for local testing:
// header('Access-Control-Allow-Origin: *');

// -----------------------------------------------------
// Get and sanitize IP/hostname
// -----------------------------------------------------
$ip = trim($_GET['ip'] ?? '');

if (empty($ip)) {
    echo json_encode(['success' => false, 'error' => 'No IP provided']);
    exit;
}

// Allow IPv4, IPv6 (basic), and hostnames: letters, digits, dots, hyphens, colons, brackets
if (!preg_match('/^[\w\.\-:\[\]]+$/', $ip)) {
    echo json_encode(['success' => false, 'error' => 'Invalid characters in target']);
    exit;
}

// -----------------------------------------------------
// Build ping command based on OS
// -----------------------------------------------------
$isWindows = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';

if ($isWindows) {
    $command = 'ping -n 1 -w 1000 ' . escapeshellarg($ip);
    $successPattern = '/TTL=/i';                    // Case-insensitive TTL=
    $latencyPattern = '/Average\s*=\s*(\d+)ms/i';    // e.g., "Average = 12ms"
} else {
    $command = 'ping -c 1 -W 1 ' . escapeshellarg($ip);
    $successPattern = '/1 packets? received|1 received/i';
    $latencyPattern = '/time[=:]([\d\.]+)\s*ms/i';   // Covers "time=5.21 ms" or "time: 3 ms"
}

// -----------------------------------------------------
// Execute command safely
// -----------------------------------------------------
$output = shell_exec($command . ' 2>&1'); // Capture stderr too

// Default result
$result = [
    'success' => false,
    'latency' => null,
    'debug'   => false  // Set to true only for testing!
];

// -----------------------------------------------------
// Debug mode (REMOVE IN PRODUCTION!)
// -----------------------------------------------------
if ($result['debug']) {
    echo "<pre>Command: $command\nOutput:\n" . htmlspecialchars($output) . "</pre>";
    exit;
}

// -----------------------------------------------------
// Determine success and extract latency
// -----------------------------------------------------
if ($output !== null && preg_match($successPattern, $output)) {
    $result['success'] = true;

    if (preg_match($latencyPattern, $output, $matches)) {
        $result['latency'] = round((float)$matches[1]);
    }
}

// -----------------------------------------------------
// Output JSON result
// -----------------------------------------------------
echo json_encode($result);