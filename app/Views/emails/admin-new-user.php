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
        .badge { display: inline-block; background-color: #ffc107; color: #212529; padding: 4px 8px; border-radius: 3px; font-size: 12px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>New User Registration</h1>
        </div>

        <div class="content">
            <p>Hi <?php echo htmlspecialchars($adminName); ?>,</p>

            <p>A new user has registered for <?php echo htmlspecialchars($appName); ?>. This registration does not match any existing player in the database.</p>

            <div class="details">
                <h3 style="margin-top: 0;">User Information</h3>
                <p><strong>Email:</strong> <a href="mailto:<?php echo htmlspecialchars($userEmail); ?>"><?php echo htmlspecialchars($userEmail); ?></a></p>
                <p><strong>Status:</strong> <span class="badge">Pending Approval</span></p>
            </div>

            <p style="text-align: center;">
                <a href="<?php echo htmlspecialchars($reviewLink); ?>" class="button">
                    Review in Admin Panel
                </a>
            </p>

            <h3>Next Steps:</h3>
            <ol>
                <li>Review the user's email address</li>
                <li>Verify this is a legitimate registration</li>
                <li>Approve or reject the registration</li>
                <li>Once approved, the user can log in and add players to their account</li>
            </ol>

            <p style="color: #666; font-size: 14px; margin-top: 20px;">
                <strong>Note:</strong> This user has no player profiles linked yet. After approval, they will be able to add players from their dashboard.
            </p>
        </div>

        <div class="footer">
            <p>&copy; <?php echo date('Y'); ?> <?php echo htmlspecialchars($appName); ?>. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
