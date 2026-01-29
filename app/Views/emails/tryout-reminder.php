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
            background-color: #007bff;
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
            border-left: 4px solid #007bff;
            padding: 15px;
            margin: 20px 0;
        }
        .reminder-banner {
            background-color: #cfe2ff;
            border: 1px solid #b6d4fe;
            color: #084298;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
            text-align: center;
        }
        .checklist {
            background-color: #fff;
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
        <h1>⏰ Tryout Reminder</h1>
    </div>
    <div class="content">
        <p>Hello <?= htmlspecialchars($player_name) ?>,</p>

        <div class="reminder-banner">
            <h2 style="margin: 0;">Your Tryout is Tomorrow!</h2>
            <p style="margin: 10px 0 0 0;">Don't forget about your upcoming tryout</p>
        </div>

        <p>This is a friendly reminder that you are registered for the <strong><?= htmlspecialchars($age_group) ?></strong> tryout.</p>

        <div class="info-box">
            <h3>Tryout Details</h3>
            <p><strong>Date:</strong> Tomorrow - <?= htmlspecialchars($tryout_date) ?></p>
            <p><strong>Time:</strong> <?= htmlspecialchars($start_time) ?> - <?= htmlspecialchars($end_time) ?></p>
            <p><strong>Location:</strong> <?= htmlspecialchars($location_name) ?><br>
            <?= htmlspecialchars($location_address) ?></p>
            <?php if (!empty($map_link)): ?>
                <p><a href="<?= htmlspecialchars($map_link) ?>" style="color: #007bff; text-decoration: none;">📍 Get Directions</a></p>
            <?php endif; ?>
        </div>

        <?php if (!empty($special_instructions)): ?>
            <div class="info-box">
                <h3>Special Instructions</h3>
                <p><?= nl2br(htmlspecialchars($special_instructions)) ?></p>
            </div>
        <?php endif; ?>

        <div class="checklist">
            <h3>✓ Pre-Tryout Checklist</h3>
            <ul>
                <li>⏰ Plan to arrive 15 minutes early for check-in</li>
                <li>🧤 Baseball/softball glove</li>
                <li>👟 Cleats or athletic shoes</li>
                <li>👕 Athletic clothing appropriate for the weather</li>
                <li>💧 Water bottle (stay hydrated!)</li>
                <li>🪪 Valid ID for check-in</li>
                <?php if ($payment_status === 'pending'): ?>
                    <li>💵 Payment method (if paying at tryout)</li>
                <?php endif; ?>
            </ul>
        </div>

        <h3>Important Reminders:</h3>
        <ul>
            <li>Arrive early to allow time for parking and check-in</li>
            <li>Warm up before the tryout begins</li>
            <li>Be prepared to participate actively and give your best effort</li>
            <li>If you can no longer attend, please cancel your registration to free up the spot</li>
        </ul>

        <p>We're looking forward to seeing you tomorrow! Good luck!</p>

        <div class="footer">
            <p>This email was sent by <?= htmlspecialchars($appName) ?></p>
            <p>If you need to cancel or have questions, please contact the league office.</p>
        </div>
    </div>
</body>
</html>
