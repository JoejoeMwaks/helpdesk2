<?php
// debug-info.php - Check your environment
require_once 'config.php';
?>
<!DOCTYPE html>
<html>
<head>
    <title>Debug Info - KNBS</title>
</head>
<body>
    <h1>Environment Debug Information</h1>
    <ul>
        <li><strong>SITE_URL:</strong> <?php echo SITE_URL; ?></li>
        <li><strong>Protocol:</strong> <?php echo $_SERVER['REQUEST_SCHEME'] ?? 'https'; ?></li>
        <li><strong>Host:</strong> <?php echo $_SERVER['HTTP_HOST']; ?></li>
        <li><strong>Script Path:</strong> <?php echo $_SERVER['SCRIPT_NAME']; ?></li>
        <li><strong>Full URL:</strong> <?php echo SITE_URL . '/feedback.php'; ?></li>
    </ul>
    
    <h2>Test QR Code Generation</h2>
    <p>Test with visitor ID 1: <a href="generate-qr.php?id=1" target="_blank">generate-qr.php?id=1</a></p>
    
    <h2>Test Feedback Page</h2>
    <p><a href="feedback.php" target="_blank">feedback.php</a></p>
</body>
</html>