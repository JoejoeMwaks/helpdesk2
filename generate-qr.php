<?php
// generate-qr.php - Updated for both specific and universal QR codes
require_once 'config.php';

// Get visitor ID
$visitor_id = $_GET['id'] ?? 0;

// If no ID provided, redirect to universal QR
if (!$visitor_id) {
    header('Location: generate-universal-qr.php');
    exit();
}

// Get visitor from database
$query = "SELECT * FROM visitors WHERE VisitorID = ?";
$stmt = $pdo->prepare($query);
$stmt->execute([$visitor_id]);
$visitor = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$visitor) {
    // Visitor not found, use universal QR
    header('Location: generate-universal-qr.php');
    exit();
}

// Generate or get token
if (empty($visitor['FeedbackToken'])) {
    $token = bin2hex(random_bytes(16));
    $update_query = "UPDATE visitors SET FeedbackToken = ? WHERE VisitorID = ?";
    $update_stmt = $pdo->prepare($update_query);
    $update_stmt->execute([$token, $visitor_id]);
} else {
    $token = $visitor['FeedbackToken'];
}

// Generate specific feedback URL for this visitor
$feedback_url = SITE_URL . "/feedback.php?token=" . $token;

// Use reliable QR code API
$qr_url = "https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=" . urlencode($feedback_url);

// Try to get QR code
$context = stream_context_create([
    'http' => [
        'timeout' => 5,
        'ignore_errors' => true
    ],
    'ssl' => [
        'verify_peer' => false,
        'verify_peer_name' => false,
    ]
]);

$qr_content = @file_get_contents($qr_url, false, $context);

if ($qr_content === false) {
    // Create SVG fallback with visitor info
    header('Content-Type: image/svg+xml');
    
    $short_name = substr($visitor['FullName'], 0, 15);
    $short_badge = substr($visitor['BadgeNumber'], 0, 10);
    
    echo '<?xml version="1.0" encoding="UTF-8"?>
    <svg width="200" height="200" xmlns="http://www.w3.org/2000/svg">
        <rect width="200" height="200" fill="white" stroke="#663300" stroke-width="2"/>
        <rect x="25" y="25" width="150" height="150" fill="#f8f9fa" stroke="#663300" stroke-width="1"/>
        
        <!-- QR Pattern -->
        <rect x="35" y="35" width="20" height="20" fill="#663300"/>
        <rect x="145" y="35" width="20" height="20" fill="#663300"/>
        <rect x="35" y="145" width="20" height="20" fill="#663300"/>
        
        <rect x="65" y="35" width="10" height="10" fill="#663300"/>
        <rect x="35" y="65" width="10" height="10" fill="#663300"/>
        <rect x="65" y="145" width="10" height="10" fill="#663300"/>
        <rect x="145" y="65" width="10" height="10" fill="#663300"/>
        
        <!-- Text -->
        <text x="100" y="85" font-family="Arial" font-size="12" fill="#663300" text-anchor="middle" font-weight="bold">KNBS</text>
        <text x="100" y="100" font-family="Arial" font-size="10" fill="#666" text-anchor="middle">VISITOR FEEDBACK</text>
        <text x="100" y="115" font-family="Arial" font-size="8" fill="#999" text-anchor="middle">' . htmlspecialchars($short_name) . '</text>
        <text x="100" y="125" font-family="Arial" font-size="7" fill="#999" text-anchor="middle">' . htmlspecialchars($short_badge) . '</text>
        <text x="100" y="180" font-family="Arial" font-size="6" fill="#999" text-anchor="middle">Scan for Feedback</text>
    </svg>';
} else {
    header('Content-Type: image/png');
    echo $qr_content;
}
?>