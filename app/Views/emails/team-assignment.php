<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background-color: #6f42c1; color: white; padding: 20px; border-radius: 4px 4px 0 0; }
        .content { background-color: #f9f9f9; padding: 20px; }
        .details { background-color: white; border-left: 4px solid #6f42c1; padding: 15px; margin: 20px 0; }
        .button { background-color: #6f42c1; color: white; padding: 10px 20px; text-decoration: none; border-radius: 4px; display: inline-block; }
        .footer { background-color: #f0f0f0; padding: 10px; text-align: center; font-size: 12px; color: #666; }
        .alert { background-color: #d1ecf1; border: 1px solid #bee5eb; color: #0c5460; padding: 15px; border-radius: 4px; margin: 20px 0; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Team Assignment</h1>
        </div>

        <div class="content">
            <p>Hi <?php echo htmlspecialchars($playerName); ?>,</p>

            <div class="alert">
                <strong>🎉 Congratulations!</strong> You have been assigned to a team!
            </div>

            <div class="details">
                <h3 style="margin-top: 0;">Team Information</h3>
                <p><strong>Team Name:</strong> <?php echo htmlspecialchars($teamName); ?></p>
                <p><strong>Age Group:</strong> <?php echo htmlspecialchars($ageGroup); ?></p>
            </div>

            <div class="details">
                <h3 style="margin-top: 0;">Your Coach</h3>
                <p><strong>Name:</strong> <?php echo htmlspecialchars($coachName); ?></p>
                <p><strong>Email:</strong> <a href="mailto:<?php echo htmlspecialchars($coachEmail); ?>"><?php echo htmlspecialchars($coachEmail); ?></a></p>
                <p><strong>Phone:</strong> <?php echo htmlspecialchars($coachPhone); ?></p>
            </div>

            <p>
                <a href="<?php echo htmlspecialchars($dashboardLink); ?>" class="button">
                    View Team Details
                </a>
            </p>

            <h3>What's Next?</h3>
            <ul>
                <li>Contact your coach to introduce yourself</li>
                <li>Check your dashboard for practice schedule</li>
                <li>Complete any required waivers or forms</li>
                <li>Prepare for your first practice or game</li>
                <li>Get to know your teammates!</li>
            </ul>

            <p style="color: #666; font-size: 14px; margin-top: 20px;">
                <strong>Note:</strong> Your coach may reach out with additional information, schedules, or forms. Make sure to check your email regularly!
            </p>
        </div>

        <div class="footer">
            <p>&copy; <?php echo date('Y'); ?> <?php echo htmlspecialchars($appName); ?>. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
