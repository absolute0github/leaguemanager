<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Page Not Found - IVL Baseball League</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
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
            opacity: 0.8;
            margin-bottom: 30px;
            max-width: 500px;
            margin-left: auto;
            margin-right: auto;
        }
        .btn-home {
            background: white;
            color: #1e3c72;
            padding: 12px 30px;
            font-weight: 600;
            border-radius: 50px;
            text-decoration: none;
            display: inline-block;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .btn-home:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(0,0,0,0.3);
            color: #1e3c72;
        }
        .baseball-animation {
            position: absolute;
            font-size: 30px;
            opacity: 0.1;
            animation: float 6s ease-in-out infinite;
        }
        @keyframes float {
            0%, 100% { transform: translateY(0) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(180deg); }
        }
    </style>
</head>
<body>
    <div class="baseball-animation" style="top: 10%; left: 10%;"><i class="fas fa-baseball"></i></div>
    <div class="baseball-animation" style="top: 20%; right: 15%; animation-delay: 1s;"><i class="fas fa-baseball"></i></div>
    <div class="baseball-animation" style="bottom: 15%; left: 20%; animation-delay: 2s;"><i class="fas fa-baseball"></i></div>
    <div class="baseball-animation" style="bottom: 25%; right: 10%; animation-delay: 3s;"><i class="fas fa-baseball"></i></div>

    <div class="error-container">
        <div class="error-icon">
            <i class="fas fa-baseball"></i>
        </div>
        <div class="error-code">404</div>
        <div class="error-message">Swing and a Miss!</div>
        <div class="error-description">
            The page you're looking for seems to have left the ballpark.
            It might have been moved, deleted, or maybe it never existed.
        </div>
        <a href="/" class="btn-home">
            <i class="fas fa-home me-2"></i>Back to Home
        </a>
    </div>
</body>
</html>
