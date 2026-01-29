<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background-color: #17a2b8; color: white; padding: 20px; border-radius: 4px 4px 0 0; }
        .content { background-color: #f9f9f9; padding: 20px; }
        .details { background-color: white; border-left: 4px solid #17a2b8; padding: 15px; margin: 20px 0; }
        .button { background-color: #17a2b8; color: white; padding: 10px 20px; text-decoration: none; border-radius: 4px; display: inline-block; }
        .footer { background-color: #f0f0f0; padding: 10px; text-align: center; font-size: 12px; color: #666; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Tryout Registration Confirmation</h1>
        </div>

        <div class="content">
            <p>Hi <?php echo htmlspecialchars($playerName); ?>,</p>

            <p>Your tryout registration has been confirmed! Here are your details:</p>

            <div class="details">
                <h3 style="margin-top: 0;">Tryout Information</h3>
                <p><strong>Age Group:</strong> <?php echo htmlspecialchars($ageGroup); ?></p>
                <p><strong>Date:</strong> <?php echo date('l, F j, Y', strtotime($tryoutDate)); ?></p>
                <p><strong>Time:</strong> <?php echo date('g:i A', strtotime($tryoutTime)); ?></p>
                <p><strong>Fee:</strong> $<?php echo number_format($tryoutCost, 2); ?></p>
            </div>

            <div class="details">
                <h3 style="margin-top: 0;">Location</h3>
                <p><strong><?php echo htmlspecialchars($locationName); ?></strong></p>
                <p>
                    <?php echo htmlspecialchars($locationAddress); ?><br>
                    <?php echo htmlspecialchars($locationCity); ?>, <?php echo htmlspecialchars($locationState); ?> <?php echo htmlspecialchars($locationZip); ?>
                </p>
                <?php if (!empty($mapLink)): ?>
                    <p>
                        <a href="<?php echo htmlspecialchars($mapLink); ?>" target="_blank">View on Map →</a>
                    </p>
                <?php endif; ?>
            </div>

            <?php if (!empty($instructions)): ?>
                <div class="details">
                    <h3 style="margin-top: 0;">Special Instructions</h3>
                    <p><?php echo nl2br(htmlspecialchars($instructions)); ?></p>
                </div>
            <?php endif; ?>

            <p>
                <a href="<?php echo htmlspecialchars($dashboardLink); ?>" class="button">
                    View in Dashboard
                </a>
            </p>

            <p style="color: #666; font-size: 14px; margin-top: 20px;">
                Please arrive 15 minutes early to check in. Bring any required documents and be ready to play!
            </p>
        </div>

        <div class="footer">
            <p>&copy; <?php echo date('Y'); ?> <?php echo htmlspecialchars($appName); ?>. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
