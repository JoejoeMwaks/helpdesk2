<?php
// feedback.php - Universal Feedback Page for QR Codes
require_once 'config.php';

$token = $_GET['token'] ?? '';
$message = '';
$error = '';
$visitor_data = null;
$feedback_submitted = false;

// Validate token and get visitor data
if ($token) {
    $query = "SELECT v.*, TIMESTAMPDIFF(HOUR, CheckInTime, NOW()) as hours_since_visit 
              FROM visitors v 
              WHERE FeedbackToken = ? AND Status = 'Checked Out'";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$token]);
    $visitor_data = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$visitor_data) {
        $error = "Invalid or expired feedback link. Please contact the front desk for assistance.";
    } elseif (!empty($visitor_data['Feedback'])) {
        $feedback_submitted = true;
        $message = "Thank you! Your feedback has already been submitted. We appreciate your time.";
    } else {
        // Mark QR code as scanned
        $update_query = "UPDATE visitors SET QRCodeScanned = TRUE, QRScanTimestamp = NOW() WHERE FeedbackToken = ?";
        $update_stmt = $pdo->prepare($update_query);
        $update_stmt->execute([$token]);
    }
}

// Handle feedback submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_feedback'])) {
    $token = $_POST['token'];
    $rating = $_POST['rating'] ?? 0;
    $comments = sanitize($_POST['comments'] ?? '');
    $recommend = $_POST['recommend'] ?? '';
    $visit_rating = $_POST['visit_rating'] ?? '';
    
    if ($token && $rating > 0) {
        // Prepare feedback data
        $feedback_data = [
            'overall_rating' => $rating,
            'visit_experience' => $visit_rating,
            'recommend' => $recommend,
            'comments' => $comments,
            'submitted_at' => date('Y-m-d H:i:s'),
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown'
        ];
        
        $query = "UPDATE visitors SET 
                  Feedback = ?, 
                  FeedbackSubmitted = TRUE, 
                  FeedbackTimestamp = NOW() 
                  WHERE FeedbackToken = ?";
        $stmt = $pdo->prepare($query);
        
        if ($stmt->execute([json_encode($feedback_data), $token])) {
            $message = "Thank you for your feedback! We appreciate you taking the time to help us improve our services.";
            $feedback_submitted = true;
            
            // Log the feedback submission
            logActivity(1, 'Feedback Submitted', "Visitor feedback submitted via QR code: " . ($visitor_data['FullName'] ?? 'Unknown'));
        } else {
            $error = "There was an error submitting your feedback. Please try again.";
        }
    } else {
        $error = "Please provide an overall rating before submitting.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Visitor Feedback - Kenya National Bureau of Statistics</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .feedback-container {
            max-width: 600px;
            margin: 20px auto;
            padding: 0;
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .knbs-header {
            background: linear-gradient(135deg, #663300 0%, #552700 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        
        .feedback-content {
            padding: 30px;
        }
        
        .rating-stars {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin: 25px 0;
        }
        
        .rating-star {
            font-size: 2.5rem;
            color: #ddd;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .rating-star:hover,
        .rating-star.active {
            color: #ffc107;
            transform: scale(1.1);
        }
        
        .visitor-info {
            background: #e7f3ff;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 25px;
            border-left: 4px solid #17a2b8;
        }
        
        .feedback-success {
            text-align: center;
            padding: 40px;
        }
        
        .feedback-success i {
            font-size: 4rem;
            color: #28a745;
            margin-bottom: 20px;
        }
        
        .form-group {
            margin-bottom: 25px;
        }
        
        .rating-label {
            text-align: center;
            font-size: 1.2rem;
            margin-bottom: 15px;
            color: #333;
            font-weight: 600;
        }
        
        .recommend-options {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 10px;
            margin-top: 15px;
        }
        
        .recommend-option {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 8px;
            padding: 15px 10px;
            border: 2px solid #dee2e6;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.3s;
            text-align: center;
        }
        
        .recommend-option:hover {
            border-color: #663300;
            background: #f8f9fa;
        }
        
        .recommend-option.selected {
            border-color: #663300;
            background: #663300;
            color: white;
        }
        
        .recommend-option i {
            font-size: 1.5rem;
        }
        
        .visit-rating {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
            gap: 10px;
            margin-top: 15px;
        }
        
        .visit-option {
            padding: 15px 10px;
            border: 2px solid #dee2e6;
            border-radius: 10px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .visit-option:hover {
            border-color: #663300;
        }
        
        .visit-option.selected {
            border-color: #663300;
            background: #663300;
            color: white;
        }
        
        .visit-option i {
            font-size: 1.5rem;
            margin-bottom: 5px;
            display: block;
        }
        
        .submit-btn {
            background: linear-gradient(135deg, #663300 0%, #552700 100%);
            color: white;
            border: none;
            padding: 15px 30px;
            font-size: 1.1rem;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s;
            width: 100%;
            font-weight: 600;
        }
        
        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 51, 0, 0.3);
        }
        
        .submit-btn:disabled {
            background: #6c757d;
            cursor: not-allowed;
            transform: none;
        }
        
        .welcome-message {
            text-align: center;
            padding: 30px;
            background: #f8f9fa;
            border-radius: 10px;
            margin-bottom: 25px;
        }
        
        @media (max-width: 768px) {
            .feedback-container {
                margin: 10px;
                border-radius: 10px;
            }
            
            .knbs-header {
                padding: 20px;
            }
            
            .feedback-content {
                padding: 20px;
            }
            
            .rating-star {
                font-size: 2rem;
            }
            
            .recommend-options {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="feedback-container">
            <div class="knbs-header">
                <h1 style="margin: 0; font-size: 1.8rem;">
                    <i class="fas fa-comments"></i> Visitor Feedback
                </h1>
                <p style="margin: 10px 0 0 0; opacity: 0.9; font-size: 1rem;">
                    Kenya National Bureau of Statistics
                </p>
            </div>
            
            <div class="feedback-content">
                <?php if ($message): ?>
                    <!-- Success Message -->
                    <div class="feedback-success">
                        <i class="fas fa-check-circle"></i>
                        <h2 style="color: #28a745; margin-bottom: 15px;">Thank You!</h2>
                        <p style="font-size: 1.1rem; margin-bottom: 25px; line-height: 1.6;"><?php echo $message; ?></p>
                        <p style="color: #666; margin-bottom: 20px; line-height: 1.6;">
                            Your feedback helps us improve our services and provide better experiences for all visitors.
                        </p>
                        <a href="<?php echo SITE_URL; ?>" class="submit-btn" style="text-decoration: none; display: inline-block; width: auto; padding: 12px 25px;">
                            <i class="fas fa-home"></i> Return to KNBS Website
                        </a>
                    </div>
                    
                <?php elseif ($error): ?>
                    <!-- Error Message -->
                    <div style="text-align: center; padding: 30px;">
                        <i class="fas fa-exclamation-triangle" style="font-size: 3rem; color: #dc3545; margin-bottom: 15px;"></i>
                        <h3 style="color: #dc3545; margin-bottom: 15px;">Unable to Process Feedback</h3>
                        <p style="color: #666; margin-bottom: 25px; line-height: 1.6;"><?php echo $error; ?></p>
                        <div style="display: flex; gap: 10px; justify-content: center; flex-wrap: wrap;">
                            <a href="<?php echo SITE_URL; ?>" class="submit-btn" style="text-decoration: none; display: inline-block; width: auto; padding: 10px 20px;">
                                <i class="fas fa-home"></i> KNBS Website
                            </a>
                            <a href="mailto:info@knbs.or.ke" class="btn btn-secondary" style="padding: 10px 20px;">
                                <i class="fas fa-envelope"></i> Contact Support
                            </a>
                        </div>
                    </div>
                    
                <?php elseif (!$token): ?>
                    <!-- Universal Feedback Form (No specific token) -->
                    <div class="welcome-message">
                        <h2 style="color: #663300; margin-bottom: 15px;">Welcome to KNBS Feedback System</h2>
                        <p style="color: #666; line-height: 1.6; margin-bottom: 0;">
                            Thank you for visiting Kenya National Bureau of Statistics. We value your feedback to help us improve our services.
                        </p>
                    </div>
                    
                    <form method="POST" id="feedbackForm">
                        <input type="hidden" name="token" value="universal">
                        
                        <!-- Overall Satisfaction -->
                        <div class="form-group">
                            <div class="rating-label">
                                How would you rate your overall experience? *
                            </div>
                            <div class="rating-stars" id="ratingStars">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <i class="fas fa-star rating-star" data-rating="<?php echo $i; ?>"></i>
                                <?php endfor; ?>
                            </div>
                            <input type="hidden" name="rating" id="selectedRating" required>
                            <div style="text-align: center; color: #666; font-size: 0.9rem; margin-top: 10px;">
                                <span style="color: #dc3545;">*</span> Required field
                            </div>
                        </div>

                        <!-- Visit Experience -->
                        <div class="form-group">
                            <label class="rating-label">How was your visit experience?</label>
                            <div class="visit-rating" id="visitRating">
                                <div class="visit-option" data-value="Excellent">
                                    <i class="fas fa-grin-stars"></i>
                                    Excellent
                                </div>
                                <div class="visit-option" data-value="Good">
                                    <i class="fas fa-smile"></i>
                                    Good
                                </div>
                                <div class="visit-option" data-value="Average">
                                    <i class="fas fa-meh"></i>
                                    Average
                                </div>
                                <div class="visit-option" data-value="Poor">
                                    <i class="fas fa-frown"></i>
                                    Poor
                                </div>
                            </div>
                            <input type="hidden" name="visit_rating" id="selectedVisitRating">
                        </div>

                        <!-- Recommendation -->
                        <div class="form-group">
                            <label class="rating-label">Would you recommend KNBS to others? *</label>
                            <div class="recommend-options" id="recommendOptions">
                                <div class="recommend-option" data-value="yes">
                                    <i class="fas fa-thumbs-up"></i>
                                    <span>Yes, definitely</span>
                                </div>
                                <div class="recommend-option" data-value="maybe">
                                    <i class="fas fa-meh"></i>
                                    <span>Maybe</span>
                                </div>
                                <div class="recommend-option" data-value="no">
                                    <i class="fas fa-thumbs-down"></i>
                                    <span>Probably not</span>
                                </div>
                            </div>
                            <input type="hidden" name="recommend" id="selectedRecommend" required>
                        </div>

                        <!-- Comments -->
                        <div class="form-group">
                            <label for="comments" style="display: block; margin-bottom: 10px; font-weight: 600; color: #333;">
                                <i class="fas fa-comment-dots"></i> Additional Comments
                            </label>
                            <textarea name="comments" id="comments" rows="5" 
                                      placeholder="What did you like about your visit? How can we improve our services? Any specific suggestions?"
                                      style="width: 100%; padding: 15px; border: 2px solid #dee2e6; border-radius: 8px; font-size: 1rem; resize: vertical;"></textarea>
                        </div>

                        <button type="submit" name="submit_feedback" class="submit-btn" id="submitButton">
                            <i class="fas fa-paper-plane"></i> Submit Feedback
                        </button>
                    </form>
                    
                <?php elseif ($visitor_data && !$feedback_submitted): ?>
                    <!-- Specific Visitor Feedback Form -->
                    <div class="visitor-info">
                        <h3 style="color: #0c5460; margin-bottom: 15px;">
                            <i class="fas fa-user-check"></i> Welcome, <?php echo sanitize($visitor_data['FullName']); ?>!
                        </h3>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; font-size: 0.9rem;">
                            <div>
                                <strong>Visit Date:</strong> <?php echo date('F j, Y', strtotime($visitor_data['CheckInTime'])); ?>
                            </div>
                            <div>
                                <strong>Badge Number:</strong> <?php echo $visitor_data['BadgeNumber']; ?>
                            </div>
                            <div>
                                <strong>Purpose:</strong> <?php echo sanitize($visitor_data['PurposeOfVisit']); ?>
                            </div>
                            <div>
                                <strong>Host:</strong> <?php echo sanitize($visitor_data['HostName'] ?: 'Not specified'); ?>
                            </div>
                        </div>
                    </div>

                    <h2 style="text-align: center; color: #663300; margin-bottom: 30px;">
                        How was your experience at KNBS?
                    </h2>

                    <form method="POST" id="feedbackForm">
                        <input type="hidden" name="token" value="<?php echo $token; ?>">
                        
                        <!-- Overall Satisfaction -->
                        <div class="form-group">
                            <div class="rating-label">
                                Overall Satisfaction Rating *
                            </div>
                            <div class="rating-stars" id="ratingStars">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <i class="fas fa-star rating-star" data-rating="<?php echo $i; ?>"></i>
                                <?php endfor; ?>
                            </div>
                            <input type="hidden" name="rating" id="selectedRating" required>
                            <div style="text-align: center; color: #666; font-size: 0.9rem; margin-top: 10px;">
                                <span style="color: #dc3545;">*</span> Required field
                            </div>
                        </div>

                        <!-- Visit Experience -->
                        <div class="form-group">
                            <label class="rating-label">How would you rate your visit experience?</label>
                            <div class="visit-rating" id="visitRating">
                                <div class="visit-option" data-value="Excellent">
                                    <i class="fas fa-grin-stars"></i>
                                    Excellent
                                </div>
                                <div class="visit-option" data-value="Good">
                                    <i class="fas fa-smile"></i>
                                    Good
                                </div>
                                <div class="visit-option" data-value="Average">
                                    <i class="fas fa-meh"></i>
                                    Average
                                </div>
                                <div class="visit-option" data-value="Poor">
                                    <i class="fas fa-frown"></i>
                                    Poor
                                </div>
                            </div>
                            <input type="hidden" name="visit_rating" id="selectedVisitRating">
                        </div>

                        <!-- Recommendation -->
                        <div class="form-group">
                            <label class="rating-label">Would you recommend KNBS to others? *</label>
                            <div class="recommend-options" id="recommendOptions">
                                <div class="recommend-option" data-value="yes">
                                    <i class="fas fa-thumbs-up"></i>
                                    <span>Yes, definitely</span>
                                </div>
                                <div class="recommend-option" data-value="maybe">
                                    <i class="fas fa-meh"></i>
                                    <span>Maybe</span>
                                </div>
                                <div class="recommend-option" data-value="no">
                                    <i class="fas fa-thumbs-down"></i>
                                    <span>Probably not</span>
                                </div>
                            </div>
                            <input type="hidden" name="recommend" id="selectedRecommend" required>
                        </div>

                        <!-- Comments -->
                        <div class="form-group">
                            <label for="comments" style="display: block; margin-bottom: 10px; font-weight: 600; color: #333;">
                                <i class="fas fa-comment-dots"></i> Additional Comments (Optional)
                            </label>
                            <textarea name="comments" id="comments" rows="5" 
                                      placeholder="What did you like about your visit? How can we improve our services? Any specific suggestions?"></textarea>
                        </div>

                        <button type="submit" name="submit_feedback" class="submit-btn" id="submitButton">
                            <i class="fas fa-paper-plane"></i> Submit Feedback
                        </button>
                    </form>
                    
                <?php else: ?>
                    <!-- Invalid Token -->
                    <div style="text-align: center; padding: 40px;">
                        <i class="fas fa-exclamation-triangle" style="font-size: 3rem; color: #dc3545; margin-bottom: 15px;"></i>
                        <h3 style="color: #dc3545; margin-bottom: 15px;">Invalid QR Code</h3>
                        <p style="color: #666; margin-bottom: 25px; line-height: 1.6;">
                            This feedback link is invalid or has expired. Please scan a valid QR code provided during your visit.
                        </p>
                        <a href="<?php echo SITE_URL; ?>" class="submit-btn" style="text-decoration: none; display: inline-block; width: auto; padding: 12px 25px;">
                            <i class="fas fa-home"></i> Return to KNBS Website
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        // Star rating functionality
        const stars = document.querySelectorAll('.rating-star');
        const selectedRating = document.getElementById('selectedRating');
        
        // Visit rating functionality
        const visitOptions = document.querySelectorAll('.visit-option');
        const selectedVisitRating = document.getElementById('selectedVisitRating');
        
        // Recommendation functionality
        const recommendOptions = document.querySelectorAll('.recommend-option');
        const selectedRecommend = document.getElementById('selectedRecommend');
        const submitButton = document.getElementById('submitButton');

        // Star rating
        stars.forEach(star => {
            star.addEventListener('click', function() {
                const rating = this.getAttribute('data-rating');
                selectedRating.value = rating;
                
                // Update star display
                stars.forEach((s, index) => {
                    if (index < rating) {
                        s.classList.add('active');
                    } else {
                        s.classList.remove('active');
                    }
                });
                
                updateSubmitButton();
            });
        });

        // Visit rating
        visitOptions.forEach(option => {
            option.addEventListener('click', function() {
                const value = this.getAttribute('data-value');
                selectedVisitRating.value = value;
                
                // Update selection
                visitOptions.forEach(opt => opt.classList.remove('selected'));
                this.classList.add('selected');
            });
        });

        // Recommendation
        recommendOptions.forEach(option => {
            option.addEventListener('click', function() {
                const value = this.getAttribute('data-value');
                selectedRecommend.value = value;
                
                // Update selection
                recommendOptions.forEach(opt => opt.classList.remove('selected'));
                this.classList.add('selected');
                
                updateSubmitButton();
            });
        });

        // Update submit button state
        function updateSubmitButton() {
            if (submitButton) {
                const hasRating = selectedRating.value !== '';
                const hasRecommendation = selectedRecommend.value !== '';
                
                if (hasRating && hasRecommendation) {
                    submitButton.disabled = false;
                    submitButton.innerHTML = '<i class="fas fa-paper-plane"></i> Submit Feedback';
                } else {
                    submitButton.disabled = true;
                    submitButton.innerHTML = '<i class="fas fa-lock"></i> Please complete required fields';
                }
            }
        }

        // Form validation
        document.getElementById('feedbackForm')?.addEventListener('submit', function(e) {
            if (!selectedRating.value) {
                e.preventDefault();
                alert('Please provide an overall rating before submitting.');
                return;
            }
            
            if (!selectedRecommend.value) {
                e.preventDefault();
                alert('Please indicate if you would recommend KNBS to others.');
                return;
            }
            
            // Add loading state
            const submitBtn = this.querySelector('button[type="submit"]');
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Submitting...';
            submitBtn.disabled = true;
        });

        // Add hover effects for stars
        stars.forEach(star => {
            star.addEventListener('mouseover', function() {
                const rating = this.getAttribute('data-rating');
                stars.forEach((s, index) => {
                    if (index < rating) {
                        s.style.color = '#ffc107';
                    }
                });
            });

            star.addEventListener('mouseout', function() {
                const currentRating = selectedRating.value;
                stars.forEach((s, index) => {
                    if (!currentRating || index >= currentRating) {
                        s.style.color = '#ddd';
                    }
                });
            });
        });

        // Initialize submit button state
        updateSubmitButton();
        
        // Auto-focus on first star for better UX
        document.addEventListener('DOMContentLoaded', function() {
            if (stars.length > 0 && !selectedRating.value) {
                stars[0].focus();
            }
        });
    </script>
</body>
</html>