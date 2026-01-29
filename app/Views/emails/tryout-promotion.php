<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: #28a745;
            color: #ffffff;
            padding: 20px;
            text-align: center;
            border-radius: 5px 5px 0 0;
        }
        .content {
            background-color: #f9f9f9;
            padding: 30px;
            border-radius: 0 0 5px 5px;
        }
        .info-box {
            background-color: #fff;
            border-left: 4px solid #28a745;
            padding: 15px;
            margin: 20px 0;
        }
        .success-banner {
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
            text-align: center;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            font-size: 12px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>🎉 Great News!</h1>
    </div>
    <div class="content">
        <p>Hello <?= htmlspecialchars($player_name) ?>,</p>

        <div class="success-banner">
            <h2 style="margin: 0;">A Spot Has Opened Up!</h2>
            <p style="margin: 10px 0 0 0;">You have been promoted from the waitlist to <strong>confirmed</strong> status.</p>
        </div>

        <p>Congratulations! You are now officially registered for the <strong><?= htmlspecialchars($age_group) ?></strong> tryout.</p>

        <div class="info-box">
            <h3>Tryout Details</h3>
            <p><strong>Age Group:</strong> <?= htmlspecialchars($age_group) ?></p>
            <p><strong>Date:</strong> <?= htmlspecialchars($tryout_date) ?></p>
            <p><strong>Time:</strong> <?= htmlspecialchars($start_time) ?></p>
            <p><strong>Location:</strong> <?= htmlspecialchars($location_name) ?></p>
        </div>

        <h3>What You Need to Do:</h3>
        <ul>
            <li><strong>Arrive 15 minutes early</strong> for check-in</li>
            <li><strong>Bring required items:</strong> Glove, cleats, athletic clothing, water bottle, and ID</li>
            <li>Review any special instructions for the location</li>
            <li>Be ready to participate and have fun!</li>
        </ul>

        <p><strong>Important:</strong> If you can no longer attend, please cancel your registration as soon as possible so we can offer your spot to someone on the waitlist.</p>

        <p>We're excited to see you at the tryout!</p>

        <div class="footer">
            <p>This email was sent by <?= htmlspecialchars($appName) ?></p>
            <p>If you have any questions, please contact the league office.</p>
        </div>
    </div>
</body>
</html>
