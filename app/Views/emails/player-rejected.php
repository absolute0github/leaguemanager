<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background-color: #dc3545; color: white; padding: 20px; border-radius: 4px 4px 0 0; }
        .content { background-color: #f9f9f9; padding: 20px; }
        .footer { background-color: #f0f0f0; padding: 10px; text-align: center; font-size: 12px; color: #666; }
        .alert { background-color: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 15px; border-radius: 4px; margin: 20px 0; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Registration Update</h1>
        </div>

        <div class="content">
            <p>Hi <?php echo htmlspecialchars($name); ?>,</p>

            <div class="alert">
                Thank you for your interest in <?php echo htmlspecialchars($appName); ?>. Unfortunately, your registration request has been declined at this time.
            </div>

            <?php if (!empty($reason)): ?>
                <h3>Reason:</h3>
                <p><?php echo nl2br(htmlspecialchars($reason)); ?></p>
            <?php endif; ?>

            <h3>What Now?</h3>
            <p>
                If you have questions about this decision, please contact us:
            </p>
            <p>
                <strong>Email:</strong> <a href="mailto:<?php echo htmlspecialchars($contactEmail); ?>"><?php echo htmlspecialchars($contactEmail); ?></a>
            </p>

            <p style="color: #666; font-size: 14px; margin-top: 20px;">
                We appreciate your interest and wish you the best of luck!
            </p>
        </div>

        <div class="footer">
            <p>&copy; <?php echo date('Y'); ?> <?php echo htmlspecialchars($appName); ?>. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
