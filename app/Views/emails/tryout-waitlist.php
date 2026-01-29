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
            background-color: #ffc107;
            color: #000;
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
            border-left: 4px solid #ffc107;
            padding: 15px;
            margin: 20px 0;
        }
        .button {
            display: inline-block;
            padding: 12px 24px;
            background-color: #007bff;
            color: #ffffff !important;
            text-decoration: none;
            border-radius: 5px;
            margin: 15px 0;
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
        <h1>Added to Waitlist</h1>
    </div>
    <div class="content">
        <p>Hello <?= htmlspecialchars($player_name) ?>,</p>

        <p>Thank you for registering for the <strong><?= htmlspecialchars($age_group) ?></strong> tryout!</p>

        <p>This tryout is currently full, so you have been added to the waitlist.</p>

        <div class="info-box">
            <h3>Waitlist Information</h3>
            <p><strong>Your Position:</strong> #<?= htmlspecialchars($waitlist_position) ?></p>
            <p><strong>Age Group:</strong> <?= htmlspecialchars($age_group) ?></p>
            <p><strong>Date:</strong> <?= htmlspecialchars($tryout_date) ?></p>
            <p><strong>Location:</strong> <?= htmlspecialchars($location_name) ?></p>
        </div>

        <h3>What Happens Next?</h3>
        <ul>
            <li>If a spot opens up, you will be automatically promoted</li>
            <li>You will receive an email notification if you are promoted</li>
            <li>Your waitlist position may change if people ahead of you cancel</li>
            <li>We will notify you as soon as a spot becomes available</li>
        </ul>

        <p><strong>Please Note:</strong> Being on the waitlist does not guarantee a spot. However, we will do our best to accommodate everyone.</p>

        <p>Thank you for your patience and interest in our tryouts!</p>

        <div class="footer">
            <p>This email was sent by <?= htmlspecialchars($appName) ?></p>
            <p>If you have any questions, please contact the league office.</p>
        </div>
    </div>
</body>
</html>
