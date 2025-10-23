<?php
// print-qr.php - COMPLETE WORKING VERSION
require_once 'config.php';

$visitor_id = $_GET['id'] ?? 0;

if (!$visitor_id) {
    die("<h2>Error: No visitor ID provided</h2>");
}

// Get visitor information
$query = "SELECT * FROM visitors WHERE VisitorID = ?";
$stmt = $pdo->prepare($query);
$stmt->execute([$visitor_id]);
$visitor = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$visitor) {
    die("<h2>Error: Visitor not found</h2>");
}

// Generate QR code URL
$feedback_url = SITE_URL . "/feedback.php?token=" . ($visitor['FeedbackToken'] ?? 'test123');
$qr_url = "generate-qr.php?id=" . $visitor_id;

// Generate fallback SVG
$fallback_svg = '<?xml version="1.0" encoding="UTF-8"?>
<svg width="250" height="250" xmlns="http://www.w3.org/2000/svg">
    <rect width="250" height="250" fill="white" stroke="#663300" stroke-width="2"/>
    <rect x="25" y="25" width="200" height="200" fill="#f8f9fa" stroke="#663300" stroke-width="1"/>
    <text x="125" y="80" font-family="Arial" font-size="16" fill="#663300" text-anchor="middle" font-weight="bold">KENYA NATIONAL BUREAU</text>
    <text x="125" y="100" font-family="Arial" font-size="16" fill="#663300" text-anchor="middle" font-weight="bold">OF STATISTICS</text>
    <text x="125" y="120" font-family="Arial" font-size="14" fill="#666" text-anchor="middle">Visitor Feedback QR</text>
    <text x="125" y="140" font-family="Arial" font-size="12" fill="#999" text-anchor="middle">' . htmlspecialchars($visitor['FullName']) . '</text>
    <text x="125" y="155" font-family="Arial" font-size="10" fill="#999" text-anchor="middle">Badge: ' . $visitor['BadgeNumber'] . '</text>
    <text x="125" y="170" font-family="Arial" font-size="10" fill="#999" text-anchor="middle">' . date('M j, Y') . '</text>
    <text x="125" y="190" font-family="Arial" font-size="9" fill="#999" text-anchor="middle">Scan with camera app</text>
    <text x="125" y="205" font-family="Arial" font-size="8" fill="#999" text-anchor="middle">' . SITE_URL . '</text>
</svg>';
$fallback_svg_encoded = base64_encode($fallback_svg);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print QR Code - KNBS Visitor System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #f5f5f5 0%, #e9ecef 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .print-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            overflow: hidden;
            max-width: 400px;
            width: 100%;
        }
        
        .header {
            background: linear-gradient(135deg, #663300 0%, #552700 100%);
            color: white;
            padding: 25px;
            text-align: center;
        }
        
        .header h1 {
            font-size: 1.4rem;
            margin-bottom: 5px;
            font-weight: 600;
        }
        
        .header p {
            font-size: 0.9rem;
            opacity: 0.9;
        }
        
        .visitor-info {
            padding: 20px;
            background: #f8f9fa;
            border-bottom: 1px solid #dee2e6;
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            font-size: 0.9rem;
        }
        
        .info-item {
            display: flex;
            flex-direction: column;
        }
        
        .info-label {
            font-weight: 600;
            color: #663300;
            font-size: 0.8rem;
            margin-bottom: 2px;
        }
        
        .info-value {
            color: #333;
        }
        
        .qr-section {
            padding: 30px;
            text-align: center;
            background: white;
        }
        
        .qr-container {
            background: white;
            border: 2px solid #663300;
            border-radius: 10px;
            padding: 20px;
            display: inline-block;
            margin: 0 auto 20px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .qr-image {
            width: 250px;
            height: 250px;
            object-fit: contain;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            background: white;
        }
        
        .instructions {
            background: #e7f3ff;
            padding: 15px;
            border-radius: 8px;
            margin: 20px 0;
            border-left: 4px solid #17a2b8;
        }
        
        .instructions h3 {
            color: #0c5460;
            margin-bottom: 8px;
            font-size: 1rem;
        }
        
        .instructions p {
            color: #0c5460;
            font-size: 0.9rem;
            line-height: 1.4;
        }
        
        .footer {
            background: #f8f9fa;
            padding: 20px;
            text-align: center;
            border-top: 1px solid #dee2e6;
        }
        
        .footer p {
            color: #6c757d;
            font-size: 0.8rem;
            margin-bottom: 5px;
        }
        
        .action-buttons {
            display: flex;
            gap: 10px;
            justify-content: center;
            margin-top: 20px;
            flex-wrap: wrap;
        }
        
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s;
            font-size: 0.9rem;
        }
        
        .btn-primary {
            background: #663300;
            color: white;
        }
        
        .btn-primary:hover {
            background: #552700;
            transform: translateY(-2px);
        }
        
        .btn-success {
            background: #28a745;
            color: white;
        }
        
        .btn-success:hover {
            background: #218838;
            transform: translateY(-2px);
        }
        
        .btn-info {
            background: #17a2b8;
            color: white;
        }
        
        .btn-info:hover {
            background: #138496;
            transform: translateY(-2px);
        }
        
        .btn-secondary {
            background: #6c757d;
            color: white;
        }
        
        .btn-secondary:hover {
            background: #5a6268;
            transform: translateY(-2px);
        }
        
        /* Print Styles */
        @media print {
            body {
                background: white !important;
                padding: 0 !important;
                margin: 0 !important;
            }
            
            .print-container {
                box-shadow: none !important;
                border-radius: 0 !important;
                max-width: none !important;
            }
            
            .no-print {
                display: none !important;
            }
            
            .header {
                background: #663300 !important;
                -webkit-print-color-adjust: exact;
            }
            
            .instructions {
                background: #e7f3ff !important;
                -webkit-print-color-adjust: exact;
            }
            
            .qr-container {
                border: 2px solid #663300 !important;
                -webkit-print-color-adjust: exact;
            }
        }
        
        /* Responsive */
        @media (max-width: 480px) {
            .print-container {
                margin: 10px;
            }
            
            .info-grid {
                grid-template-columns: 1fr;
            }
            
            .qr-image {
                width: 200px;
                height: 200px;
            }
            
            .action-buttons {
                flex-direction: column;
                align-items: center;
            }
            
            .btn {
                width: 200px;
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <div class="print-container">
        <div class="header">
            <h1><i class="fas fa-qrcode"></i> VISITOR FEEDBACK QR CODE</h1>
            <p>Kenya National Bureau of Statistics</p>
        </div>
        
        <div class="visitor-info">
            <div class="info-grid">
                <div class="info-item">
                    <span class="info-label">VISITOR NAME</span>
                    <span class="info-value"><?php echo htmlspecialchars($visitor['FullName']); ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">BADGE NUMBER</span>
                    <span class="info-value"><?php echo $visitor['BadgeNumber']; ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">ORGANIZATION</span>
                    <span class="info-value"><?php echo htmlspecialchars($visitor['Organization'] ?: 'Not specified'); ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">DATE PRINTED</span>
                    <span class="info-value"><?php echo date('F j, Y \a\t g:i A'); ?></span>
                </div>
            </div>
        </div>
        
        <div class="qr-section">
            <div class="qr-container">
                <img src="<?php echo $qr_url; ?>" 
                     alt="Visitor Feedback QR Code" 
                     class="qr-image"
                     onerror="this.onerror=null; this.src='data:image/svg+xml;base64,<?php echo $fallback_svg_encoded; ?>'">
            </div>
            
            <div class="instructions">
                <h3><i class="fas fa-info-circle"></i> HOW TO USE THIS QR CODE</h3>
                <p>Scan this code with your smartphone camera to provide feedback about your visit experience at KNBS.</p>
            </div>
        </div>
        
        <div class="footer">
            <p><strong>Feedback URL:</strong> <?php echo SITE_URL; ?>/feedback.php</p>
            <p><strong>Generated by:</strong> KNBS Visitor Management System</p>
            <p><i class="fas fa-shield-alt"></i> This QR code contains a secure token for visitor identification</p>
            
            <div class="action-buttons no-print">
                <button onclick="window.print()" class="btn btn-primary">
                    <i class="fas fa-print"></i> Print QR Code
                </button>
                <button onclick="testQRCode()" class="btn btn-info">
                    <i class="fas fa-bolt"></i> Test QR Code
                </button>
                <button onclick="window.close()" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Close Window
                </button>
                <a href="visitor-management.php" class="btn btn-success">
                    <i class="fas fa-arrow-left"></i> Back to Management
                </a>
            </div>
        </div>
    </div>

    <script>
        // Auto-print when page loads
        window.onload = function() {
            setTimeout(() => {
                console.log('Auto-printing QR code...');
                window.print();
            }, 1000);
        };
        
        // Test QR code functionality
        function testQRCode() {
            const qrUrl = '<?php echo $qr_url; ?>';
            console.log('Testing QR code URL:', qrUrl);
            
            // Open QR code in new window for testing
            const testWindow = window.open(qrUrl, '_blank', 'width=400,height=400');
            
            if (!testWindow) {
                alert('Popup blocked! Please allow popups to test the QR code.');
                return;
            }
            
            // Test if QR code is accessible
            fetch(qrUrl)
                .then(response => {
                    if (response.ok) {
                        alert('✅ QR code is working correctly!\n\nYou can now print this page.');
                    } else {
                        alert('❌ QR code returned an error: ' + response.status);
                    }
                })
                .catch(error => {
                    alert('❌ QR code test failed: ' + error.message);
                });
        }
        
        // Handle window after print
        window.onafterprint = function() {
            console.log('Print completed or cancelled');
            // Optional: Close window after print
            // setTimeout(() => { window.close(); }, 1000);
        };
        
        // Add keyboard shortcut for print (Ctrl+P)
        document.addEventListener('keydown', function(e) {
            if ((e.ctrlKey || e.metaKey) && e.key === 'p') {
                e.preventDefault();
                window.print();
            }
        });
        
        console.log('QR Print Page Loaded');
        console.log('Visitor ID:', <?php echo $visitor_id; ?>);
        console.log('QR URL:', '<?php echo $qr_url; ?>');
        console.log('Site URL:', '<?php echo SITE_URL; ?>');
    </script>
</body>
</html>