<?php
// test-final.php - Final comprehensive test
require_once 'config.php';

// Get any visitor or create test data
$query = "SELECT VisitorID, FullName, BadgeNumber FROM visitors LIMIT 1";
$stmt = $pdo->prepare($query);
$stmt->execute();
$visitor = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$visitor) {
    // Create test visitor
    $query = "INSERT INTO visitors (FullName, BadgeNumber, Status) VALUES (?, ?, ?)";
    $stmt = $pdo->prepare($query);
    $test_badge = "TEST" . date('YmdHis');
    $stmt->execute(['Test Visitor', $test_badge, 'Checked In']);
    $test_id = $pdo->lastInsertId();
    
    $query = "SELECT VisitorID, FullName, BadgeNumber FROM visitors WHERE VisitorID = ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$test_id]);
    $visitor = $stmt->fetch(PDO::FETCH_ASSOC);
}

$test_id = $visitor['VisitorID'];
?>
<!DOCTYPE html>
<html>
<head>
    <title>Final QR Test</title>
    <style>
        body { font-family: Arial; padding: 20px; max-width: 800px; margin: 0 auto; }
        .test-section { margin: 20px 0; padding: 20px; border: 2px solid #ddd; border-radius: 10px; }
        .success { border-color: #28a745; background: #f8fff9; }
        .warning { border-color: #ffc107; background: #fffef0; }
        .danger { border-color: #dc3545; background: #fff5f5; }
        .qr-display { margin: 15px 0; text-align: center; }
        .qr-display img { border: 3px solid #17a2b8; border-radius: 10px; padding: 10px; background: white; }
        .log { background: #f8f9fa; padding: 15px; border-radius: 5px; font-family: monospace; font-size: 12px; }
    </style>
</head>
<body>
    <h1>🔄 Final QR Code System Test</h1>
    
    <div class="test-section success">
        <h2>✅ Test Information</h2>
        <p><strong>Visitor ID:</strong> <?php echo $test_id; ?></p>
        <p><strong>Name:</strong> <?php echo htmlspecialchars($visitor['FullName']); ?></p>
        <p><strong>Badge:</strong> <?php echo $visitor['BadgeNumber']; ?></p>
        <p><strong>SITE_URL:</strong> <?php echo SITE_URL; ?></p>
    </div>
    
    <div class="test-section warning">
        <h2>🖼️ QR Code Display Test</h2>
        
        <div class="qr-display">
            <h3>New Reliable QR Generator</h3>
            <img src="generate-qr-simple.php?id=<?php echo $test_id; ?>" 
                 alt="QR Code Test"
                 onload="document.getElementById('status1').innerHTML='✅ Loaded successfully'"
                 onerror="document.getElementById('status1').innerHTML='❌ Failed to load'">
            <p id="status1">🔄 Loading...</p>
        </div>
        
        <div class="qr-display">
            <h3>Direct Link Test</h3>
            <a href="generate-qr-simple.php?id=<?php echo $test_id; ?>" target="_blank">
                Open QR Code Directly
            </a>
        </div>
    </div>
    
    <div class="test-section">
        <h2>🔗 System Links</h2>
        <ul>
            <li><a href="visitor-management.php" target="_blank">Visitor Management</a></li>
            <li><a href="print-qr.php?id=<?php echo $test_id; ?>" target="_blank">Printable QR Version</a></li>
            <li><a href="feedback.php" target="_blank">Feedback Page</a></li>
        </ul>
    </div>
    
    <div class="test-section">
        <h2>📊 Browser Console Log</h2>
        <div class="log" id="consoleLog">Waiting for JavaScript...</div>
    </div>

    <script>
        // Comprehensive testing
        console.log('=== FINAL QR CODE TEST ===');
        
        const testId = <?php echo $test_id; ?>;
        const qrUrl = 'generate-qr-simple.php?id=' + testId;
        
        console.log('Test ID:', testId);
        console.log('QR URL:', qrUrl);
        console.log('User Agent:', navigator.userAgent);
        
        // Test QR code accessibility
        fetch(qrUrl)
            .then(response => {
                const log = document.getElementById('consoleLog');
                log.innerHTML = `
                    ✅ QR Code Accessible\n
                    Status: ${response.status} ${response.statusText}\n
                    Type: ${response.headers.get('content-type')}\n
                    Size: ${response.headers.get('content-length') || 'unknown'} bytes
                `;
                
                console.log('QR Access Test:', {
                    status: response.status,
                    type: response.headers.get('content-type'),
                    ok: response.ok
                });
                
                return response.blob();
            })
            .then(blob => {
                console.log('QR Blob Details:', {
                    size: blob.size + ' bytes',
                    type: blob.type
                });
            })
            .catch(error => {
                const log = document.getElementById('consoleLog');
                log.innerHTML = `❌ QR Code Test Failed:\n${error.message}`;
                console.error('QR Test Failed:', error);
            });
            
        // Test image loading
        const testImage = new Image();
        testImage.onload = function() {
            console.log('✅ Image loaded successfully in JavaScript');
        };
        testImage.onerror = function() {
            console.error('❌ Image failed to load in JavaScript');
        };
        testImage.src = qrUrl;
    </script>
</body>
</html>