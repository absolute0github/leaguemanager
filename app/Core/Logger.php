<?php

namespace App\Core;

/**
 * Logger - Simple file-based logging system
 *
 * Supports multiple log levels and automatic log rotation.
 */
class Logger
{
    private static ?Logger $instance = null;
    private string $logPath;
    private string $logLevel;

    // Log levels in order of severity
    private const LEVELS = [
        'debug' => 0,
        'info' => 1,
        'notice' => 2,
        'warning' => 3,
        'error' => 4,
        'critical' => 5,
        'alert' => 6,
        'emergency' => 7,
    ];

    private function __construct()
    {
        $this->logPath = dirname(__DIR__, 2) . '/storage/logs';
        $this->logLevel = $_ENV['LOG_LEVEL'] ?? 'debug';

        // Ensure log directory exists
        if (!is_dir($this->logPath)) {
            mkdir($this->logPath, 0755, true);
        }
    }

    public static function getInstance(): Logger
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Log a debug message
     */
    public function debug(string $message, array $context = []): void
    {
        $this->log('debug', $message, $context);
    }

    /**
     * Log an info message
     */
    public function info(string $message, array $context = []): void
    {
        $this->log('info', $message, $context);
    }

    /**
     * Log a notice
     */
    public function notice(string $message, array $context = []): void
    {
        $this->log('notice', $message, $context);
    }

    /**
     * Log a warning
     */
    public function warning(string $message, array $context = []): void
    {
        $this->log('warning', $message, $context);
    }

    /**
     * Log an error
     */
    public function error(string $message, array $context = []): void
    {
        $this->log('error', $message, $context);
    }

    /**
     * Log a critical error
     */
    public function critical(string $message, array $context = []): void
    {
        $this->log('critical', $message, $context);
    }

    /**
     * Log an alert
     */
    public function alert(string $message, array $context = []): void
    {
        $this->log('alert', $message, $context);
    }

    /**
     * Log an emergency
     */
    public function emergency(string $message, array $context = []): void
    {
        $this->log('emergency', $message, $context);
    }

    /**
     * Log an exception
     */
    public function exception(\Throwable $e, array $context = []): void
    {
        $context['exception'] = [
            'class' => get_class($e),
            'message' => $e->getMessage(),
            'code' => $e->getCode(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString(),
        ];

        $this->error($e->getMessage(), $context);
    }

    /**
     * Log an audit event (user actions)
     */
    public function audit(string $action, array $context = []): void
    {
        $context['audit'] = true;
        $context['action'] = $action;
        $context['ip'] = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $context['user_agent'] = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';

        $this->log('info', "AUDIT: {$action}", $context, 'audit.log');
    }

    /**
     * Log an access event
     */
    public function access(): void
    {
        $message = sprintf(
            '%s %s %s',
            $_SERVER['REQUEST_METHOD'] ?? 'GET',
            $_SERVER['REQUEST_URI'] ?? '/',
            $_SERVER['SERVER_PROTOCOL'] ?? 'HTTP/1.1'
        );

        $context = [
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
            'referer' => $_SERVER['HTTP_REFERER'] ?? '',
        ];

        $this->log('info', $message, $context, 'access.log');
    }

    /**
     * Write log entry
     */
    private function log(string $level, string $message, array $context = [], ?string $file = null): void
    {
        // Check if this level should be logged
        if (self::LEVELS[$level] < self::LEVELS[$this->logLevel]) {
            return;
        }

        $file = $file ?? 'app.log';
        $logFile = $this->logPath . '/' . $file;

        // Rotate log if too large (10MB)
        $this->rotateIfNeeded($logFile);

        // Format log entry
        $timestamp = date('Y-m-d H:i:s');
        $levelUpper = strtoupper($level);
        $contextStr = !empty($context) ? ' ' . json_encode($context, JSON_UNESCAPED_SLASHES) : '';

        $entry = "[{$timestamp}] [{$levelUpper}] {$message}{$contextStr}" . PHP_EOL;

        // Write to file
        file_put_contents($logFile, $entry, FILE_APPEND | LOCK_EX);

        // For critical errors, also write to error.log
        if (self::LEVELS[$level] >= self::LEVELS['error'] && $file !== 'error.log') {
            file_put_contents(
                $this->logPath . '/error.log',
                $entry,
                FILE_APPEND | LOCK_EX
            );
        }
    }

    /**
     * Rotate log file if it exceeds max size
     */
    private function rotateIfNeeded(string $logFile, int $maxSize = 10485760): void
    {
        if (!file_exists($logFile)) {
            return;
        }

        if (filesize($logFile) < $maxSize) {
            return;
        }

        $rotatedFile = $logFile . '.' . date('Y-m-d-His');
        rename($logFile, $rotatedFile);

        // Keep only last 5 rotated files
        $pattern = $logFile . '.*';
        $rotatedFiles = glob($pattern);
        if (count($rotatedFiles) > 5) {
            rsort($rotatedFiles);
            $toDelete = array_slice($rotatedFiles, 5);
            foreach ($toDelete as $file) {
                unlink($file);
            }
        }
    }

    /**
     * Get recent log entries
     */
    public function getRecent(string $file = 'app.log', int $lines = 100): array
    {
        $logFile = $this->logPath . '/' . $file;

        if (!file_exists($logFile)) {
            return [];
        }

        $content = file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        return array_slice($content, -$lines);
    }
}
