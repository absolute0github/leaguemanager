<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background-color: #007bff; color: white; padding: 20px; border-radius: 4px 4px 0 0; }
        .content { background-color: #f9f9f9; padding: 20px; }
        .details { background-color: white; border-left: 4px solid #007bff; padding: 15px; margin: 20px 0; }
        .button { background-color: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 4px; display: inline-block; }
        .footer { background-color: #f0f0f0; padding: 10px; text-align: center; font-size: 12px; color: #666; }
        .badge { display: inline-block; background-color: #e9ecef; color: #495057; padding: 4px 8px; border-radius: 3px; font-size: 12px; margin: 0 4px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>New Player Registration</h1>
        </div>

        <div class="content">
            <p>Hi <?php echo htmlspecialchars($adminName); ?>,</p>

            <p>A new player has registered for <?php echo htmlspecialchars($appName); ?>. Please review their information below:</p>

            <div class="details">
                <h3 style="margin-top: 0;">Player Information</h3>
                <p><strong>Name:</strong> <?php echo htmlspecialchars($playerName); ?></p>
                <p><strong>Email:</strong> <a href="mailto:<?php echo htmlspecialchars($playerEmail); ?>"><?php echo htmlspecialchars($playerEmail); ?></a></p>
                <p><strong>Phone:</strong> <?php echo htmlspecialchars($playerPhone ?? 'N/A'); ?></p>
                <p><strong>Age Group:</strong> <span class="badge"><?php echo htmlspecialchars($ageGroup); ?></span></p>
            </div>

            <p style="text-align: center;">
                <a href="<?php echo htmlspecialchars($reviewLink); ?>" class="button">
                    Review in Admin Panel
                </a>
            </p>

            <h3>Next Steps:</h3>
            <ol>
                <li>Review the player's registration information</li>
                <li>Verify contact details</li>
                <li>Check for duplicate accounts</li>
                <li>Approve or reject the registration</li>
                <li>Add to appropriate team/age group (if approved)</li>
            </ol>

            <p style="color: #666; font-size: 14px; margin-top: 20px;">
                <strong>Note:</strong> Players are pending approval until you take action.
            </p>
        </div>

        <div class="footer">
            <p>&copy; <?php echo date('Y'); ?> <?php echo htmlspecialchars($appName); ?>. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
