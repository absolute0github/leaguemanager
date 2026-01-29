<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background-color: #ffc107; color: #333; padding: 20px; border-radius: 4px 4px 0 0; }
        .content { background-color: #f9f9f9; padding: 20px; }
        .button { background-color: #ffc107; color: #333; padding: 10px 20px; text-decoration: none; border-radius: 4px; display: inline-block; }
        .footer { background-color: #f0f0f0; padding: 10px; text-align: center; font-size: 12px; color: #666; }
        .alert { background-color: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; border-radius: 4px; margin: 20px 0; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Two-Factor Authentication Setup</h1>
        </div>

        <div class="content">
            <p>Hi <?php echo htmlspecialchars($name); ?>,</p>

            <p>For enhanced security, we recommend setting up two-factor authentication (2FA) on your <?php echo htmlspecialchars($appName); ?> account.</p>

            <div class="alert">
                <strong>🔒 What is 2FA?</strong><br>
                Two-factor authentication adds an extra layer of security by requiring you to provide two forms of identification when logging in:
                <ol>
                    <li>Your password (something you know)</li>
                    <li>A code from an authenticator app (something you have)</li>
                </ol>
            </div>

            <p>
                <a href="<?php echo htmlspecialchars($setupLink); ?>" class="button">
                    Setup Two-Factor Authentication
                </a>
            </p>

            <h3>What You'll Need:</h3>
            <ul>
                <li>Google Authenticator, Microsoft Authenticator, or Authy app</li>
                <li>Your smartphone or device</li>
                <li>2-3 minutes to complete setup</li>
            </ul>

            <h3>During Setup, You'll:</h3>
            <ol>
                <li>Scan a QR code with your authenticator app</li>
                <li>Enter a verification code to confirm</li>
                <li>Save 10 backup codes for emergency access</li>
            </ol>

            <p style="color: #666; font-size: 14px; margin-top: 20px;">
                <strong>Note:</strong> You can skip 2FA setup for now, but we recommend enabling it for account security.
            </p>
        </div>

        <div class="footer">
            <p>&copy; <?php echo date('Y'); ?> <?php echo htmlspecialchars($appName); ?>. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
