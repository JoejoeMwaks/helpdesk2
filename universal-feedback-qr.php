<?php
// universal-feedback-qr.php - Display Universal QR Code
require_once 'config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Universal Feedback QR Code - KNBS</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .universal-container {
            max-width: 500px;
            margin: 50px auto;
            padding: 30px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            text-align: center;
        }
        
        .knbs-header {
            background: linear-gradient(135deg, #663300 0%, #552700 100%);
            color: white;
            padding: 25px;
            margin: -30px -30px 30px -30px;
            border-radius: 15px 15px 0 0;
        }
        
        .qr-display {
            margin: 30px 0;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 10px;
        }
        
        .qr-display img {
            max-width: 250px;
            border: 2px solid #663300;
            padding: 15px;
            background: white;
            border-radius: 10px;
        }
        
        .instructions {
            background: #e7f3ff;
            padding: 20px;
            border-radius: 10px;
            margin: 20px 0;
            border-left: 4px solid #17a2b8;
        }
        
        .action-buttons {
            display: flex;
            gap: 15px;
            justify-content: center;
            margin-top: 25px;
            flex-wrap: wrap;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="universal-container">
            <div class="knbs-header">
                <h1><i class="fas fa-qrcode"></i> Universal Feedback QR Code</h1>
                <p>Kenya National Bureau of Statistics Visitor System</p>
            </div>
            
            <h2 style="color: #663300; margin-bottom: 20px;">Scan to Provide Feedback</h2>
            
            <p style="color: #666; margin-bottom: 25px; line-height: 1.6;">
                This universal QR code can be scanned by any visitor to provide feedback about their experience at KNBS. 
                It's perfect for reception areas, waiting rooms, or any public space.
            </p>
            
            <div class="qr-display">
                <img src="generate-universal-qr.php" alt="Universal Feedback QR Code">
                <p style="color: #666; margin-top: 15px; font-size: 0.9rem;">
                    <i class="fas fa-info-circle"></i> Point any smartphone camera at this code to scan
                </p>
            </div>
            
            <div class="instructions">
                <h3 style="color: #0c5460; margin-bottom: 10px;">
                    <i class="fas fa-lightbulb"></i> How to Use This QR Code
                </h3>
                <ul style="text-align: left; color: #0c5460; line-height: 1.6;">
                    <li>Display this QR code in visible areas around KNBS facilities</li>
                    <li>Visitors can scan it with their smartphone camera</li>
                    <li>They'll be directed to our feedback form</li>
                    <li>No app download required - works with any smartphone</li>
                </ul>
            </div>
            
            <div class="action-buttons">
                <a href="visitor-management.php" class="btn btn-primary">
                    <i class="fas fa-arrow-left"></i> Back to Management
                </a>
                <a href="feedback.php" class="btn btn-success" target="_blank">
                    <i class="fas fa-external-link-alt"></i> Test Feedback Page
                </a>
                <button onclick="window.print()" class="btn btn-info">
                    <i class="fas fa-print"></i> Print This Page
                </button>
            </div>
            
            <div style="margin-top: 25px; padding-top: 20px; border-top: 1px solid #dee2e6;">
                <p style="color: #999; font-size: 0.9rem;">
                    <i class="fas fa-shield-alt"></i> This QR code leads to our secure feedback system
                </p>
            </div>
        </div>
    </div>
    
    <script>
        // Auto-refresh QR code every 5 minutes to prevent caching
        setInterval(() => {
            const qrImage = document.querySelector('.qr-display img');
            if (qrImage) {
                qrImage.src = 'generate-universal-qr.php?t=' + new Date().getTime();
            }
        }, 300000); // 5 minutes
    </script>
</body>
</html>