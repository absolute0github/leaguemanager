<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background-color: #198754; color: white; padding: 20px; border-radius: 4px 4px 0 0; }
        .content { background-color: #f9f9f9; padding: 20px; }
        .message-box { background-color: white; padding: 20px; border: 1px solid #ddd; border-radius: 4px; margin: 20px 0; white-space: pre-wrap; }
        .footer { background-color: #f0f0f0; padding: 10px; text-align: center; font-size: 12px; color: #666; }
        .coach-info { background-color: #e7f5ee; padding: 10px 15px; border-radius: 4px; margin-top: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><?php echo htmlspecialchars($teamName); ?></h1>
            <p style="margin: 0; opacity: 0.9;">Team Message</p>
        </div>

        <div class="content">
            <p>Hi <?php echo htmlspecialchars($recipientName); ?>,</p>

            <p>You have received a message from your coach:</p>

            <div class="message-box">
                <p style="margin-top: 0;"><strong>Subject:</strong> <?php echo htmlspecialchars($subject); ?></p>
                <hr style="border: none; border-top: 1px solid #ddd; margin: 15px 0;">
                <?php echo nl2br(htmlspecialchars($message)); ?>
            </div>

            <div class="coach-info">
                <p style="margin: 0;">
                    <strong>From:</strong> Coach <?php echo htmlspecialchars($coachName); ?><br>
                    <strong>Team:</strong> <?php echo htmlspecialchars($teamName); ?>
                </p>
            </div>

            <p style="margin-top: 20px; color: #666; font-size: 14px;">
                This message was sent through the <?php echo htmlspecialchars($appName); ?> team communication system.
                Please do not reply directly to this email.
            </p>
        </div>

        <div class="footer">
            <p>&copy; <?php echo date('Y'); ?> <?php echo htmlspecialchars($appName); ?>. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
