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
            background-color: #6c757d;
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
            border-left: 4px solid #6c757d;
            padding: 15px;
            margin: 20px 0;
        }
        .warning-box {
            background-color: #fff3cd;
            border: 1px solid #ffeaa7;
            color: #856404;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
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
        <h1>Registration Cancelled</h1>
    </div>
    <div class="content">
        <p>Hello <?= htmlspecialchars($player_name) ?>,</p>

        <p>This email confirms that your tryout registration has been cancelled.</p>

        <div class="info-box">
            <h3>Cancelled Registration Details</h3>
            <p><strong>Age Group:</strong> <?= htmlspecialchars($age_group) ?></p>
            <p><strong>Date:</strong> <?= htmlspecialchars($tryout_date) ?></p>
            <?php if (!empty($reason)): ?>
                <p><strong>Reason:</strong> <?= htmlspecialchars($reason) ?></p>
            <?php endif; ?>
        </div>

        <?php if (!empty($refund_info)): ?>
            <div class="warning-box">
                <h3>Refund Information</h3>
                <p><?= htmlspecialchars($refund_info) ?></p>
            </div>
        <?php endif; ?>

        <h3>What This Means:</h3>
        <ul>
            <li>Your registration has been removed from the system</li>
            <li>Your spot may be offered to someone on the waitlist</li>
            <?php if (!empty($refund_info)): ?>
                <li>Any applicable refunds will be processed as indicated above</li>
            <?php endif; ?>
            <li>You will not receive further communications about this tryout</li>
        </ul>

        <p><strong>Still Interested?</strong> You can browse and register for other available tryouts by visiting your player dashboard.</p>

        <p>We're sorry to see you go, but we hope to see you at a future tryout!</p>

        <div class="footer">
            <p>This email was sent by <?= htmlspecialchars($appName) ?></p>
            <p>If you believe this cancellation was made in error, please contact the league office immediately.</p>
        </div>
    </div>
</body>
</html>
