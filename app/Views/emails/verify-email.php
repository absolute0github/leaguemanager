<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background-color: #28a745; color: white; padding: 20px; border-radius: 4px 4px 0 0; }
        .content { background-color: #f9f9f9; padding: 20px; }
        .button { background-color: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 4px; display: inline-block; }
        .footer { background-color: #f0f0f0; padding: 10px; text-align: center; font-size: 12px; color: #666; }
        .code { font-size: 24px; font-weight: bold; color: #007bff; letter-spacing: 2px; text-align: center; padding: 15px; background-color: white; border: 2px dashed #007bff; border-radius: 4px; margin: 20px 0; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Verify Your Email Address</h1>
        </div>

        <div class="content">
            <p>Hi <?php echo htmlspecialchars($name); ?>,</p>

            <p>Thank you for registering with <?php echo htmlspecialchars($appName); ?>!</p>

            <p>To complete your registration, please verify your email address by entering the code below:</p>

            <div class="code">
                <?php echo htmlspecialchars($verificationCode); ?>
            </div>

            <p style="text-align: center;">
                Or click the link below to verify immediately:
            </p>

            <p style="text-align: center;">
                <a href="<?php echo htmlspecialchars($verificationLink); ?>" class="button">
                    Verify Email Address
                </a>
            </p>

            <p style="color: #666; font-size: 14px;">
                <strong>Note:</strong> This verification code expires in <?php echo htmlspecialchars($expiresIn); ?>.
            </p>

            <hr style="border: none; border-top: 1px solid #ddd; margin: 20px 0;">

            <p style="color: #666; font-size: 12px;">
                If you did not register for this account, please ignore this email.
            </p>
        </div>

        <div class="footer">
            <p>&copy; <?php echo date('Y'); ?> <?php echo htmlspecialchars($appName); ?>. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
