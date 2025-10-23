<?php
// test-svg.php - Test SVG QR codes
require_once 'config.php';

// Get any visitor ID
$query = "SELECT VisitorID, FullName, BadgeNumber FROM visitors LIMIT 1";
$stmt = $pdo->prepare($query);
$stmt->execute();
$visitor = $stmt->fetch(PDO::FETCH_ASSOC);

$test_id = $visitor ? $visitor['VisitorID'] : 1;
?>
<!DOCTYPE html>
<html>
<head>
    <title>SVG QR Test</title>
    <style>
        body { font-family: Arial; padding: 20px; }
        .test-box { margin: 20px 0; padding: 20px; border: 1px solid #ccc; }
        img, object { border: 2px solid green; margin: 10px; }
    </style>
</head>
<body>
    <h1>SVG QR Code Test</h1>
    
    <div class="test-box">
        <h2>Test with Visitor ID: <?php echo $test_id; ?></h2>
        <p>Visitor: <?php echo htmlspecialchars($visitor['FullName'] ?? 'Test Visitor'); ?></p>
        <p>Badge: <?php echo $visitor['BadgeNumber'] ?? 'TEST001'; ?></p>
    </div>
    
    <div class="test-box">
        <h2>Method 1: IMG Tag</h2>
        <img src="generate-qr.php?id=<?php echo $test_id; ?>" 
             alt="QR via IMG"
             onerror="console.log('IMG tag failed')">
    </div>
    
    <div class="test-box">
        <h2>Method 2: OBJECT Tag (for SVG)</h2>
        <object data="generate-qr.php?id=<?php echo $test_id; ?>" 
                type="image/svg+xml" 
                width="200" 
                height="200"
                onerror="console.log('OBJECT tag failed')">
            SVG not supported
        </object>
    </div>
    
    <div class="test-box">
        <h2>Test Links:</h2>
        <ul>
            <li><a href="generate-qr.php?id=<?php echo $test_id; ?>" target="_blank">Open QR directly</a></li>
            <li><a href="print-qr.php?id=<?php echo $test_id; ?>" target="_blank">Printable version</a></li>
            <li><a href="visitor-management.php">Back to Visitor Management</a></li>
        </ul>
    </div>
    
    <script>
        console.log('Testing QR code generation...');
        
        // Test if QR loads
        fetch('generate-qr.php?id=<?php echo $test_id; ?>')
            .then(response => {
                console.log('QR Response:', {
                    status: response.status,
                    type: response.headers.get('content-type'),
                    ok: response.ok
                });
                return response.blob();
            })
            .then(blob => {
                console.log('QR Blob:', {
                    size: blob.size,
                    type: blob.type
                });
            })
            .catch(error => {
                console.error('QR Test Failed:', error);
            });
    </script>
</body>
</html>