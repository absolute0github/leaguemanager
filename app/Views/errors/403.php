<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Access Denied - IVL Baseball League</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%);
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
            color: #e67e22;
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
            color: #e67e22;
        }
        .btn-login {
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
        .btn-login:hover {
            background: white;
            color: #e67e22;
        }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="error-icon">
            <i class="fas fa-hand-paper"></i>
        </div>
        <div class="error-code">403</div>
        <div class="error-message">Access Denied</div>
        <div class="error-description">
            You don't have permission to access this page.
            If you believe this is an error, please contact an administrator.
        </div>
        <div>
            <a href="/login" class="btn-login">
                <i class="fas fa-sign-in-alt me-2"></i>Login
            </a>
            <a href="/" class="btn-home">
                <i class="fas fa-home me-2"></i>Back to Home
            </a>
        </div>
    </div>
</body>
</html>
