<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Server Error - IVL Baseball League</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #c0392b 0%, #e74c3c 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .error-container {
            text-align: center;
            color: white;
            padding: 40px;
        }
        .error-code {
            font-size: 150px;
            font-weight: bold;
            line-height: 1;
            text-shadow: 4px 4px 0 rgba(0,0,0,0.2);
        }
        .error-icon {
            font-size: 80px;
            margin-bottom: 20px;
            opacity: 0.8;
        }
        .error-message {
            font-size: 24px;
            margin-bottom: 30px;
        }
        .error-description {
            font-size: 16px;
            opacity: 0.9;
            margin-bottom: 30px;
            max-width: 500px;
            margin-left: auto;
            margin-right: auto;
        }
        .btn-home {
            background: white;
            color: #c0392b;
            padding: 12px 30px;
            font-weight: 600;
            border-radius: 50px;
            text-decoration: none;
            display: inline-block;
            transition: transform 0.2s, box-shadow 0.2s;
            margin: 5px;
        }
        .btn-home:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(0,0,0,0.3);
            color: #c0392b;
        }
        .btn-retry {
            background: transparent;
            color: white;
            border: 2px solid white;
            padding: 12px 30px;
            font-weight: 600;
            border-radius: 50px;
            text-decoration: none;
            display: inline-block;
            transition: all 0.2s;
            margin: 5px;
        }
        .btn-retry:hover {
            background: white;
            color: #c0392b;
        }
        .error-id {
            font-size: 12px;
            opacity: 0.6;
            margin-top: 30px;
        }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="error-icon">
            <i class="fas fa-exclamation-triangle"></i>
        </div>
        <div class="error-code">500</div>
        <div class="error-message">Technical Difficulties</div>
        <div class="error-description">
            Something went wrong on our end. Our team has been notified and is working to fix the issue.
            Please try again in a few moments.
        </div>
        <div>
            <a href="javascript:location.reload()" class="btn-retry">
                <i class="fas fa-redo me-2"></i>Try Again
            </a>
            <a href="/" class="btn-home">
                <i class="fas fa-home me-2"></i>Back to Home
            </a>
        </div>
        <?php if (isset($errorId)): ?>
        <div class="error-id">
            Error Reference: <?php echo htmlspecialchars($errorId); ?>
        </div>
        <?php endif; ?>
    </div>
</body>
</html>
