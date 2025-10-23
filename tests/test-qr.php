<?php
// test-qr.php - Debug QR code generation
require_once 'config.php';

echo "<h1>QR Code Debug Information</h1>";

// Test with a sample visitor ID
$test_visitor_id = 1;

// Check if visitor exists
$query = "SELECT * FROM visitors WHERE VisitorID = ?";
$stmt = $pdo->prepare($query);
$stmt->execute([$test_visitor_id]);
$visitor = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$visitor) {
    echo "<p style='color: red;'>No visitor found with ID $test_visitor_id. Please register a visitor first.</p>";
    // Create a test visitor
    $query = "INSERT INTO visitors (FullName, BadgeNumber, Status) VALUES (?, ?, ?)";
    $stmt = $pdo->prepare($query);
    $test_badge = "TEST" . date('Ymd') . rand(100, 999);
    $stmt->execute(['Test Visitor', $test_badge, 'Checked In']);
    $test_visitor_id = $pdo->lastInsertId();
    echo "<p>Created test visitor with ID: $test_visitor_id</p>";
    
    // Get the visitor again
    $query = "SELECT * FROM visitors WHERE VisitorID = ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$test_visitor_id]);
    $visitor = $stmt->fetch(PDO::FETCH_ASSOC);
}

echo "<h2>Visitor Information:</h2>";
echo "<pre>" . print_r($visitor, true) . "</pre>";

echo "<h2>Environment Information:</h2>";
echo "<p><strong>SITE_URL:</strong> " . SITE_URL . "</p>";
echo "<p><strong>Feedback URL:</strong> " . SITE_URL . "/feedback.php</p>";

// Test QR code generation
echo "<h2>QR Code Test:</h2>";
$qr_test_url = "generate-qr.php?id=" . $test_visitor_id;
echo "<p><strong>QR Generation URL:</strong> <a href='$qr_test_url' target='_blank'>$qr_test_url</a></p>";
echo "<p><strong>QR Image:</strong> <img src='$qr_test_url' style='border: 1px solid #ccc;'></p>";

// Test direct Google Charts API
$feedback_url = SITE_URL . "/feedback.php?token=test123";
$google_qr_url = "https://chart.googleapis.com/chart?chs=200x200&cht=qr&chl=" . urlencode($feedback_url);
echo "<h2>Direct Google Charts Test:</h2>";
echo "<p><strong>Google URL:</strong> " . htmlspecialchars($google_qr_url) . "</p>";
echo "<p><strong>Google QR:</strong> <img src='$google_qr_url' style='border: 1px solid #ccc;'></p>";

echo "<h2>Next Steps:</h2>";
echo "<ol>";
echo "<li>Check if the QR image above displays</li>";
echo "<li>If not, check browser console for errors</li>";
echo "<li>Test the feedback page: <a href='feedback.php'>feedback.php</a></li>";
echo "<li>Register a real visitor and try checkout with QR code</li>";
echo "</ol>";
?>