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
        .alert { background-color: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 15px; border-radius: 4px; margin: 20px 0; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>✓ Registration Approved</h1>
        </div>

        <div class="content">
            <p>Hi <?php echo htmlspecialchars($name); ?>,</p>

            <div class="alert">
                <strong>Great news!</strong> Your registration has been approved by <?php echo htmlspecialchars($appName); ?>!
            </div>

            <p>You now have full access to your account and can:</p>
            <ul>
                <li>View upcoming tryouts</li>
                <li>Register for teams</li>
                <li>Manage your player profile</li>
                <li>Track your registrations</li>
                <li>View team assignments</li>
            </ul>

            <p>
                <a href="<?php echo htmlspecialchars($dashboardLink); ?>" class="button">
                    Go to Dashboard
                </a>
            </p>

            <h3>What's Next?</h3>
            <ol>
                <li>Log in to your account</li>
                <li>Complete your player profile</li>
                <li>Review available tryouts</li>
                <li>Register for tryouts</li>
                <li>Complete any required waivers</li>
            </ol>

            <p style="color: #666; font-size: 14px; margin-top: 20px;">
                Welcome to <?php echo htmlspecialchars($appName); ?>! We're excited to have you on our team.
            </p>
        </div>

        <div class="footer">
            <p>&copy; <?php echo date('Y'); ?> <?php echo htmlspecialchars($appName); ?>. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
