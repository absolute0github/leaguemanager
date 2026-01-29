<?php

namespace App\Core;

/**
 * ErrorHandler - Global error and exception handling
 *
 * Catches all errors and exceptions, logs them, and displays
 * user-friendly error pages in production.
 */
class ErrorHandler
{
    private static bool $registered = false;
    private static bool $isProduction = false;

    /**
     * Register error and exception handlers
     */
    public static function register(): void
    {
        if (self::$registered) {
            return;
        }

        self::$isProduction = ($_ENV['APP_ENV'] ?? 'development') === 'production';

        // Set error handler
        set_error_handler([self::class, 'handleError']);

        // Set exception handler
        set_exception_handler([self::class, 'handleException']);

        // Set shutdown function for fatal errors
        register_shutdown_function([self::class, 'handleShutdown']);

        self::$registered = true;
    }

    /**
     * Handle PHP errors
     */
    public static function handleError(int $errno, string $errstr, string $errfile, int $errline): bool
    {
        // Check if error should be reported
        if (!(error_reporting() & $errno)) {
            return false;
        }

        $logger = Logger::getInstance();

        $errorTypes = [
            E_ERROR => 'Error',
            E_WARNING => 'Warning',
            E_PARSE => 'Parse Error',
            E_NOTICE => 'Notice',
            E_CORE_ERROR => 'Core Error',
            E_CORE_WARNING => 'Core Warning',
            E_COMPILE_ERROR => 'Compile Error',
            E_COMPILE_WARNING => 'Compile Warning',
            E_USER_ERROR => 'User Error',
            E_USER_WARNING => 'User Warning',
            E_USER_NOTICE => 'User Notice',
            E_STRICT => 'Strict',
            E_RECOVERABLE_ERROR => 'Recoverable Error',
            E_DEPRECATED => 'Deprecated',
            E_USER_DEPRECATED => 'User Deprecated',
        ];

        $type = $errorTypes[$errno] ?? 'Unknown Error';
        $message = "{$type}: {$errstr} in {$errfile} on line {$errline}";

        $context = [
            'type' => $type,
            'file' => $errfile,
            'line' => $errline,
            'url' => $_SERVER['REQUEST_URI'] ?? '',
        ];

        // Log based on severity
        if (in_array($errno, [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR, E_RECOVERABLE_ERROR])) {
            $logger->error($message, $context);
        } elseif (in_array($errno, [E_WARNING, E_CORE_WARNING, E_COMPILE_WARNING, E_USER_WARNING])) {
            $logger->warning($message, $context);
        } else {
            $logger->notice($message, $context);
        }

        // Don't execute PHP internal error handler
        return true;
    }

    /**
     * Handle uncaught exceptions
     */
    public static function handleException(\Throwable $e): void
    {
        $logger = Logger::getInstance();
        $errorId = self::generateErrorId();

        // Log the exception
        $logger->exception($e, [
            'error_id' => $errorId,
            'url' => $_SERVER['REQUEST_URI'] ?? '',
            'method' => $_SERVER['REQUEST_METHOD'] ?? '',
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
        ]);

        // Display error page
        self::displayErrorPage(500, $e, $errorId);
    }

    /**
     * Handle fatal errors on shutdown
     */
    public static function handleShutdown(): void
    {
        $error = error_get_last();

        if ($error !== null && in_array($error['type'], [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_PARSE])) {
            $logger = Logger::getInstance();
            $errorId = self::generateErrorId();

            $message = "Fatal Error: {$error['message']} in {$error['file']} on line {$error['line']}";

            $logger->critical($message, [
                'error_id' => $errorId,
                'type' => $error['type'],
                'file' => $error['file'],
                'line' => $error['line'],
            ]);

            // Clear any output and show error page
            if (ob_get_level()) {
                ob_end_clean();
            }

            self::displayErrorPage(500, null, $errorId);
        }
    }

    /**
     * Display a user-friendly error page
     */
    public static function displayErrorPage(int $code, ?\Throwable $e = null, ?string $errorId = null): void
    {
        // Set HTTP response code
        http_response_code($code);

        $viewsPath = dirname(__DIR__) . '/Views/errors/';

        // In production, always show generic error page
        if (self::$isProduction) {
            $viewFile = $viewsPath . $code . '.php';
            if (file_exists($viewFile)) {
                include $viewFile;
            } else {
                echo "<h1>Error {$code}</h1><p>An error occurred. Please try again later.</p>";
            }
            exit;
        }

        // In development, show detailed error information
        if ($e !== null) {
            self::displayDevError($e, $errorId);
        } else {
            $viewFile = $viewsPath . $code . '.php';
            if (file_exists($viewFile)) {
                include $viewFile;
            } else {
                echo "<h1>Error {$code}</h1>";
            }
        }
        exit;
    }

    /**
     * Display detailed error for development
     */
    private static function displayDevError(\Throwable $e, ?string $errorId): void
    {
        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Error - Development Mode</title>
            <style>
                * { margin: 0; padding: 0; box-sizing: border-box; }
                body {
                    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, monospace;
                    background: #1e1e2e;
                    color: #cdd6f4;
                    padding: 20px;
                    line-height: 1.6;
                }
                .container { max-width: 1200px; margin: 0 auto; }
                .error-header {
                    background: #f38ba8;
                    color: #1e1e2e;
                    padding: 20px;
                    border-radius: 8px 8px 0 0;
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                }
                .error-type { font-size: 14px; opacity: 0.8; }
                .error-message { font-size: 18px; font-weight: bold; margin-top: 5px; }
                .error-id { font-size: 12px; background: rgba(0,0,0,0.2); padding: 5px 10px; border-radius: 4px; }
                .error-body { background: #313244; padding: 20px; border-radius: 0 0 8px 8px; }
                .section { margin-bottom: 20px; }
                .section-title {
                    color: #89b4fa;
                    font-size: 14px;
                    text-transform: uppercase;
                    margin-bottom: 10px;
                    padding-bottom: 5px;
                    border-bottom: 1px solid #45475a;
                }
                .location { color: #a6adc8; font-size: 14px; }
                .trace {
                    background: #1e1e2e;
                    padding: 15px;
                    border-radius: 4px;
                    overflow-x: auto;
                    font-size: 13px;
                }
                .trace-line { padding: 3px 0; }
                .trace-line:hover { background: #313244; }
                .trace-file { color: #94e2d5; }
                .trace-line-num { color: #fab387; }
                .trace-function { color: #cba6f7; }
                pre { white-space: pre-wrap; word-wrap: break-word; }
                .warning-banner {
                    background: #f9e2af;
                    color: #1e1e2e;
                    padding: 10px 15px;
                    border-radius: 4px;
                    margin-bottom: 20px;
                    font-size: 14px;
                }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="warning-banner">
                    <strong>Development Mode:</strong> This error page is only shown in development. In production, users see a friendly error page.
                </div>

                <div class="error-header">
                    <div>
                        <div class="error-type"><?php echo htmlspecialchars(get_class($e)); ?></div>
                        <div class="error-message"><?php echo htmlspecialchars($e->getMessage()); ?></div>
                    </div>
                    <?php if ($errorId): ?>
                    <div class="error-id">ID: <?php echo htmlspecialchars($errorId); ?></div>
                    <?php endif; ?>
                </div>

                <div class="error-body">
                    <div class="section">
                        <div class="section-title">Location</div>
                        <div class="location">
                            <span class="trace-file"><?php echo htmlspecialchars($e->getFile()); ?></span>
                            : line <span class="trace-line-num"><?php echo $e->getLine(); ?></span>
                        </div>
                    </div>

                    <div class="section">
                        <div class="section-title">Stack Trace</div>
                        <div class="trace">
                            <?php foreach (explode("\n", $e->getTraceAsString()) as $line): ?>
                            <div class="trace-line"><?php echo htmlspecialchars($line); ?></div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="section">
                        <div class="section-title">Request Info</div>
                        <div class="trace">
                            <pre>
Method: <?php echo htmlspecialchars($_SERVER['REQUEST_METHOD'] ?? 'N/A'); ?>

URL: <?php echo htmlspecialchars($_SERVER['REQUEST_URI'] ?? 'N/A'); ?>

IP: <?php echo htmlspecialchars($_SERVER['REMOTE_ADDR'] ?? 'N/A'); ?>

User Agent: <?php echo htmlspecialchars($_SERVER['HTTP_USER_AGENT'] ?? 'N/A'); ?>

                            </pre>
                        </div>
                    </div>
                </div>
            </div>
        </body>
        </html>
        <?php
    }

    /**
     * Show a 404 Not Found page
     */
    public static function notFound(): void
    {
        self::displayErrorPage(404);
    }

    /**
     * Show a 403 Forbidden page
     */
    public static function forbidden(): void
    {
        self::displayErrorPage(403);
    }

    /**
     * Show a 500 Internal Server Error page
     */
    public static function serverError(?\Throwable $e = null): void
    {
        $errorId = self::generateErrorId();

        if ($e !== null) {
            $logger = Logger::getInstance();
            $logger->exception($e, ['error_id' => $errorId]);
        }

        self::displayErrorPage(500, $e, $errorId);
    }

    /**
     * Generate a unique error ID for tracking
     */
    private static function generateErrorId(): string
    {
        return date('Ymd') . '-' . substr(md5(uniqid('', true)), 0, 8);
    }
}
