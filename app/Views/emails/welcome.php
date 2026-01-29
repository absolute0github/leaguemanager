<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background-color: #007bff; color: white; padding: 20px; border-radius: 4px 4px 0 0; }
        .content { background-color: #f9f9f9; padding: 20px; }
        .button { background-color: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 4px; display: inline-block; }
        .footer { background-color: #f0f0f0; padding: 10px; text-align: center; font-size: 12px; color: #666; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><?php echo htmlspecialchars($appName); ?></h1>
        </div>

        <div class="content">
            <p>Hi <?php echo htmlspecialchars($name); ?>,</p>

            <p>Welcome to <?php echo htmlspecialchars($appName); ?>!</p>

            <p>Your account has been successfully created. Here are your login details:</p>

            <div style="background-color: white; padding: 15px; border: 1px solid #ddd; border-radius: 4px; margin: 20px 0;">
                <p><strong>Username:</strong> <?php echo htmlspecialchars($username); ?></p>
                <p><strong>Login URL:</strong> <a href="<?php echo htmlspecialchars($loginUrl); ?>"><?php echo htmlspecialchars($loginUrl); ?></a></p>
            </div>

            <p>
                <a href="<?php echo htmlspecialchars($loginUrl); ?>" class="button">
                    Login to Your Account
                </a>
            </p>

            <p style="margin-top: 20px; color: #666; font-size: 14px;">
                If you did not create this account, please ignore this email or contact us immediately.
            </p>
        </div>

        <div class="footer">
            <p>&copy; <?php echo date('Y'); ?> <?php echo htmlspecialchars($appName); ?>. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
