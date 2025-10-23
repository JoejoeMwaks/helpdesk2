<?php
// generate-universal-qr.php - Universal QR Code Generator
require_once 'config.php';

// Generate universal feedback URL - this will show the form immediately
$universal_url = SITE_URL . "/feedback.php";

// Use reliable QR code API
$qr_url = "https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=" . urlencode($universal_url) . "&format=png&margin=10";

// Try to get QR code
$context = stream_context_create([
    'http' => [
        'timeout' => 10,
        'ignore_errors' => true,
        'header' => "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36\r\n"
    ],
    'ssl' => [
        'verify_peer' => false,
        'verify_peer_name' => false,
    ]
]);

$qr_content = @file_get_contents($qr_url, false, $context);

if ($qr_content !== false && strlen($qr_content) > 100) {
    // Success - output the QR code
    header('Content-Type: image/png');
    header('Cache-Control: no-cache, no-store, must-revalidate');
    header('Pragma: no-cache');
    header('Expires: 0');
    echo $qr_content;
} else {
    // Create SVG fallback
    header('Content-Type: image/svg+xml');
    header('Cache-Control: no-cache, no-store, must-revalidate');
    header('Pragma: no-cache');
    header('Expires: 0');
    
    echo '<?xml version="1.0" encoding="UTF-8"?>
    <svg width="250" height="250" xmlns="http://www.w3.org/2000/svg">
        <rect width="250" height="250" fill="white" stroke="#663300" stroke-width="3"/>
        <rect x="20" y="20" width="210" height="210" fill="#f8f9fa" stroke="#dee2e6" stroke-width="1"/>
        
        <!-- QR Pattern Simulation -->
        <rect x="40" y="40" width="25" height="25" fill="#663300"/>
        <rect x="185" y="40" width="25" height="25" fill="#663300"/>
        <rect x="40" y="185" width="25" height="25" fill="#663300"/>
        
        <rect x="75" y="40" width="12" height="12" fill="#663300"/>
        <rect x="40" y="75" width="12" height="12" fill="#663300"/>
        <rect x="75" y="185" width="12" height="12" fill="#663300"/>
        <rect x="185" y="75" width="12" height="12" fill="#663300"/>
        
        <rect x="100" y="100" width="10" height="10" fill="#663300"/>
        <rect x="120" y="100" width="10" height="10" fill="#663300"/>
        <rect x="100" y="120" width="10" height="10" fill="#663300"/>
        
        <!-- Text Information -->
        <text x="125" y="80" font-family="Arial, sans-serif" font-size="14" fill="#663300" text-anchor="middle" font-weight="bold">KNBS FEEDBACK</text>
        <text x="125" y="100" font-family="Arial, sans-serif" font-size="12" fill="#666" text-anchor="middle">Scan to Provide</text>
        <text x="125" y="115" font-family="Arial, sans-serif" font-size="12" fill="#666" text-anchor="middle">Your Feedback</text>
        <text x="125" y="165" font-family="Arial, sans-serif" font-size="10" fill="#999" text-anchor="middle">Universal QR Code</text>
        <text x="125" y="180" font-family="Arial, sans-serif" font-size="9" fill="#999" text-anchor="middle">Opens feedback form</text>
        <text x="125" y="230" font-family="Arial, sans-serif" font-size="7" fill="#ccc" text-anchor="middle">' . htmlspecialchars($universal_url) . '</text>
    </svg>';
}
?>