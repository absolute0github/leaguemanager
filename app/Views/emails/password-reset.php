<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background-color: #dc3545; color: white; padding: 20px; border-radius: 4px 4px 0 0; }
        .content { background-color: #f9f9f9; padding: 20px; }
        .button { background-color: #dc3545; color: white; padding: 10px 20px; text-decoration: none; border-radius: 4px; display: inline-block; }
        .footer { background-color: #f0f0f0; padding: 10px; text-align: center; font-size: 12px; color: #666; }
        .alert { background-color: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 15px; border-radius: 4px; margin: 20px 0; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Password Reset Request</h1>
        </div>

        <div class="content">
            <p>Hi <?php echo htmlspecialchars($name); ?>,</p>

            <p>We received a request to reset the password for your <?php echo htmlspecialchars($appName); ?> account.</p>

            <div class="alert">
                ⚠️ <strong>Security Note:</strong> If you did not request this password reset, please ignore this email. Your account remains secure.
            </div>

            <p>
                To reset your password, click the button below:
            </p>

            <p style="text-align: center;">
                <a href="<?php echo htmlspecialchars($resetLink); ?>" class="button">
                    Reset Your Password
                </a>
            </p>

            <p style="color: #666; font-size: 14px;">
                Or copy and paste this link into your browser:<br>
                <code style="word-break: break-all;"><?php echo htmlspecialchars($resetLink); ?></code>
            </p>

            <h3>Password Requirements:</h3>
            <ul style="font-size: 14px;">
                <li>At least 8 characters long</li>
                <li>Contains at least one uppercase letter (A-Z)</li>
                <li>Contains at least one lowercase letter (a-z)</li>
                <li>Contains at least one number (0-9)</li>
                <li>Contains at least one special character (!@#$%^&*)</li>
            </ul>

            <hr style="border: none; border-top: 1px solid #ddd; margin: 20px 0;">

            <p style="color: #666; font-size: 12px;">
                <strong>Important:</strong> This password reset link expires in <?php echo htmlspecialchars($expiresIn); ?>. After that, you'll need to request a new password reset.
            </p>
        </div>

        <div class="footer">
            <p>&copy; <?php echo date('Y'); ?> <?php echo htmlspecialchars($appName); ?>. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
