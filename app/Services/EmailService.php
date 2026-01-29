<?php

namespace App\Services;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class EmailService
{
    private static PHPMailer $mailer;
    private static bool $initialized = false;

    /**
     * Initialize PHPMailer based on environment
     */
    private static function initialize(): void
    {
        if (self::$initialized) {
            return;
        }

        self::$mailer = new PHPMailer(true);
        self::$mailer->CharSet = PHPMailer::CHARSET_UTF8;

        try {
            if (self::isProduction()) {
                // Production: Use SMTP
                self::$mailer->isSMTP();
                self::$mailer->Host = $_ENV['MAIL_HOST'];
                self::$mailer->SMTPAuth = true;
                self::$mailer->Username = $_ENV['MAIL_USERNAME'];
                self::$mailer->Password = $_ENV['MAIL_PASSWORD'];
                self::$mailer->SMTPSecure = $_ENV['MAIL_ENCRYPTION'];
                self::$mailer->Port = (int)$_ENV['MAIL_PORT'];
            } else {
                // Development: Use PHP mail()
                self::$mailer->isMail();
            }

            self::$mailer->setFrom($_ENV['MAIL_FROM_ADDRESS'], $_ENV['MAIL_FROM_NAME']);
            self::$initialized = true;
        } catch (Exception $e) {
            error_log("PHPMailer initialization error: " . $e->getMessage());
        }
    }

    /**
     * Check if running in production environment
     */
    private static function isProduction(): bool
    {
        return $_ENV['APP_ENV'] === 'production';
    }

    /**
     * Render email template with variables
     */
    private static function renderTemplate(string $template, array $data = []): string
    {
        $templateFile = __DIR__ . '/../Views/emails/' . $template . '.php';

        if (!file_exists($templateFile)) {
            throw new \Exception("Email template not found: $template");
        }

        // Extract variables for use in template
        extract($data);

        // Capture output
        ob_start();
        include $templateFile;
        $html = ob_get_clean();

        return $html;
    }

    /**
     * Send email with error handling and logging
     */
    private static function send(
        string $to,
        string $toName,
        string $subject,
        string $template,
        array $templateData = [],
        array $attachments = []
    ): bool {
        try {
            self::initialize();

            // Clear previous recipients
            self::$mailer->clearAddresses();
            self::$mailer->clearAttachments();

            // Set recipient
            self::$mailer->addAddress($to, $toName);

            // Set subject and body
            self::$mailer->Subject = $subject;
            self::$mailer->Body = self::renderTemplate($template, $templateData);
            self::$mailer->isHTML(true);

            // Add plain text alternative
            self::$mailer->AltBody = strip_tags(self::$mailer->Body);

            // Add attachments if provided
            foreach ($attachments as $attachment) {
                self::$mailer->addAttachment($attachment);
            }

            // Send email
            $result = self::$mailer->send();

            // Log email
            self::logEmail($to, $toName, $subject, $template, 'sent');

            return $result;
        } catch (Exception $e) {
            error_log("Email sending error: " . $e->getMessage());
            self::logEmail($to, $toName, $subject, $template, 'failed', $e->getMessage());
            return false;
        }
    }

    /**
     * Log email to database
     */
    private static function logEmail(
        string $recipientEmail,
        ?string $recipientName,
        string $subject,
        string $templateName,
        string $status,
        ?string $errorMessage = null
    ): void {
        try {
            $db = \App\Core\Database::getInstance();

            $db->execute(
                "INSERT INTO email_log (recipient_email, recipient_name, subject, template_name, status, error_message, created_at)
                 VALUES (?, ?, ?, ?, ?, ?, NOW())",
                [$recipientEmail, $recipientName, $subject, $templateName, $status, $errorMessage]
            );
        } catch (\Exception $e) {
            error_log("Failed to log email: " . $e->getMessage());
        }
    }

    /**
     * Send welcome email to new user
     */
    public static function sendWelcomeEmail(string $email, string $name, string $username): bool
    {
        return self::send(
            $email,
            $name,
            'Welcome to ' . $_ENV['APP_NAME'],
            'welcome',
            [
                'name' => $name,
                'username' => $username,
                'appName' => $_ENV['APP_NAME'],
                'loginUrl' => $_ENV['APP_URL'] . '/login'
            ]
        );
    }

    /**
     * Send email verification email
     */
    public static function sendEmailVerification(string $email, string $name, string $token): bool
    {
        $verificationLink = $_ENV['APP_URL'] . '/verify-email?token=' . urlencode($token);

        return self::send(
            $email,
            $name,
            'Verify Your Email Address',
            'verify-email',
            [
                'name' => $name,
                'verificationCode' => $token, // 6-digit code
                'token' => $token,
                'verificationLink' => $verificationLink,
                'appName' => $_ENV['APP_NAME'],
                'expiresIn' => '1 hour'
            ]
        );
    }

    /**
     * Send 2FA setup instructions
     */
    public static function send2faSetup(string $email, string $name): bool
    {
        return self::send(
            $email,
            $name,
            '2FA Setup Instructions',
            '2fa-setup',
            [
                'name' => $name,
                'appName' => $_ENV['APP_NAME'],
                'setupLink' => $_ENV['APP_URL'] . '/auth/2fa-setup'
            ]
        );
    }

    /**
     * Send password reset email
     */
    public static function sendPasswordReset(string $email, string $name, string $token): bool
    {
        $resetLink = $_ENV['APP_URL'] . '/reset-password?token=' . urlencode($token);

        return self::send(
            $email,
            $name,
            'Password Reset Request',
            'password-reset',
            [
                'name' => $name,
                'resetLink' => $resetLink,
                'appName' => $_ENV['APP_NAME'],
                'expiresIn' => '1 hour',
                'appUrl' => $_ENV['APP_URL']
            ]
        );
    }

    /**
     * Send tryout confirmation email
     */
    public static function sendTryoutConfirmation(
        string $email,
        string $playerName,
        array $tryout,
        array $location
    ): bool {
        return self::send(
            $email,
            $playerName,
            'Tryout Registration Confirmation',
            'tryout-confirmation',
            [
                'playerName' => $playerName,
                'appName' => $_ENV['APP_NAME'],
                'tryoutDate' => $tryout['tryout_date'],
                'tryoutTime' => $tryout['start_time'],
                'tryoutCost' => $tryout['cost'],
                'ageGroup' => $tryout['age_group'],
                'locationName' => $location['name'],
                'locationAddress' => $location['street_address'],
                'locationCity' => $location['city'],
                'locationState' => $location['state'],
                'locationZip' => $location['zip_code'],
                'mapLink' => $location['map_link'],
                'instructions' => $location['special_instructions'],
                'dashboardLink' => $_ENV['APP_URL'] . '/player/tryouts'
            ]
        );
    }

    /**
     * Send tryout waitlist notification email
     */
    public static function sendTryoutWaitlist(
        string $email,
        string $subject,
        array $data
    ): bool {
        return self::send(
            $email,
            $data['player_name'],
            $subject,
            'tryout-waitlist',
            array_merge($data, ['appName' => $_ENV['APP_NAME']])
        );
    }

    /**
     * Send tryout promotion from waitlist email
     */
    public static function sendTryoutPromotion(
        string $email,
        string $subject,
        array $data
    ): bool {
        return self::send(
            $email,
            $data['player_name'],
            $subject,
            'tryout-promotion',
            array_merge($data, ['appName' => $_ENV['APP_NAME']])
        );
    }

    /**
     * Send tryout cancellation email
     */
    public static function sendTryoutCancellation(
        string $email,
        string $subject,
        array $data
    ): bool {
        return self::send(
            $email,
            $data['player_name'],
            $subject,
            'tryout-cancellation',
            array_merge($data, ['appName' => $_ENV['APP_NAME']])
        );
    }

    /**
     * Send tryout reminder email
     */
    public static function sendTryoutReminder(
        string $email,
        string $subject,
        array $data
    ): bool {
        return self::send(
            $email,
            $data['player_name'],
            $subject,
            'tryout-reminder',
            array_merge($data, ['appName' => $_ENV['APP_NAME']])
        );
    }

    /**
     * Send new player registration notification to admins
     * Used when email matches existing player
     */
    public static function sendAdminNewPlayerNotification(
        string $adminEmail,
        string $adminName,
        array $player
    ): bool {
        $existingPlayer = $player['existing_player'] ?? false;
        $subject = $existingPlayer
            ? 'Account Registration - Linked to ' . $player['first_name'] . ' ' . $player['last_name']
            : 'New Player Registration - ' . $player['first_name'] . ' ' . $player['last_name'];

        return self::send(
            $adminEmail,
            $adminName,
            $subject,
            'admin-new-player',
            [
                'adminName' => $adminName,
                'playerName' => $player['first_name'] . ' ' . $player['last_name'],
                'playerEmail' => $player['email'],
                'playerPhone' => $player['phone'],
                'ageGroup' => $player['age_group'],
                'existingPlayer' => $existingPlayer,
                'appName' => $_ENV['APP_NAME'],
                'reviewLink' => ($_ENV['APP_URL'] ?? 'http://localhost') . '/admin/pending-registrations'
            ]
        );
    }

    /**
     * Send new user registration notification to admins
     * Used when no existing player found - user-only registration
     */
    public static function sendAdminNewUserNotification(
        string $adminEmail,
        string $adminName,
        array $userData
    ): bool {
        return self::send(
            $adminEmail,
            $adminName,
            'New User Registration - ' . $userData['email'],
            'admin-new-user',
            [
                'adminName' => $adminName,
                'userEmail' => $userData['email'],
                'userId' => $userData['user_id'],
                'appName' => $_ENV['APP_NAME'],
                'reviewLink' => ($_ENV['APP_URL'] ?? 'http://localhost') . '/admin/pending-registrations'
            ]
        );
    }

    /**
     * Send player approval email
     */
    public static function sendPlayerApprovalEmail(string $email, string $name): bool
    {
        return self::send(
            $email,
            $name,
            'Your Registration Has Been Approved',
            'player-approved',
            [
                'name' => $name,
                'appName' => $_ENV['APP_NAME'],
                'dashboardLink' => $_ENV['APP_URL'] . '/dashboard'
            ]
        );
    }

    /**
     * Send player rejection email
     */
    public static function sendPlayerRejectionEmail(string $email, string $name, string $reason = ''): bool
    {
        return self::send(
            $email,
            $name,
            'Registration Status Update',
            'player-rejected',
            [
                'name' => $name,
                'reason' => $reason,
                'appName' => $_ENV['APP_NAME'],
                'contactEmail' => $_ENV['MAIL_FROM_ADDRESS']
            ]
        );
    }

    /**
     * Send team assignment email
     */
    public static function sendTeamAssignmentEmail(
        string $email,
        string $playerName,
        array $team,
        array $coach
    ): bool {
        return self::send(
            $email,
            $playerName,
            'Team Assignment - ' . $team['name'],
            'team-assignment',
            [
                'playerName' => $playerName,
                'teamName' => $team['name'],
                'ageGroup' => $team['age_group'],
                'coachName' => $coach['first_name'] . ' ' . $coach['last_name'],
                'coachEmail' => $coach['email'],
                'coachPhone' => $coach['phone'] ?? 'N/A',
                'appName' => $_ENV['APP_NAME'],
                'dashboardLink' => $_ENV['APP_URL'] . '/dashboard'
            ]
        );
    }

    /**
     * Send team message from coach
     */
    public function sendTeamMessage(
        string $email,
        string $recipientName,
        string $subject,
        string $message,
        string $teamName,
        string $coachName
    ): bool {
        return self::send(
            $email,
            $recipientName,
            "[{$teamName}] " . $subject,
            'team-message',
            [
                'recipientName' => $recipientName,
                'teamName' => $teamName,
                'coachName' => $coachName,
                'subject' => $subject,
                'message' => $message,
                'appName' => $_ENV['APP_NAME']
            ]
        );
    }

    /**
     * Send bulk email (admin feature)
     */
    public static function sendBulkEmail(
        array $recipients,
        string $subject,
        string $body,
        ?string $templateName = null
    ): array {
        $results = [
            'sent' => 0,
            'failed' => 0,
            'errors' => []
        ];

        foreach ($recipients as $recipient) {
            try {
                self::initialize();
                self::$mailer->clearAddresses();

                self::$mailer->addAddress($recipient['email'], $recipient['name'] ?? $recipient['email']);
                self::$mailer->Subject = $subject;
                self::$mailer->Body = $body;
                self::$mailer->isHTML(true);
                self::$mailer->AltBody = strip_tags($body);

                if (self::$mailer->send()) {
                    $results['sent']++;
                    self::logEmail($recipient['email'], $recipient['name'] ?? null, $subject, $templateName ?? 'bulk', 'sent');
                } else {
                    $results['failed']++;
                    $results['errors'][] = $recipient['email'] . ': Send failed';
                    self::logEmail($recipient['email'], $recipient['name'] ?? null, $subject, $templateName ?? 'bulk', 'failed', 'Send failed');
                }
            } catch (Exception $e) {
                $results['failed']++;
                $results['errors'][] = $recipient['email'] . ': ' . $e->getMessage();
                self::logEmail($recipient['email'], $recipient['name'] ?? null, $subject, $templateName ?? 'bulk', 'failed', $e->getMessage());
            }
        }

        return $results;
    }

    /**
     * Get email log (for admin review)
     */
    public static function getEmailLog(int $limit = 100, int $offset = 0): array
    {
        try {
            $db = \App\Core\Database::getInstance();
            return $db->fetchAll(
                "SELECT * FROM email_log ORDER BY created_at DESC LIMIT ? OFFSET ?",
                [$limit, $offset]
            );
        } catch (\Exception $e) {
            error_log("Failed to retrieve email log: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Resend a failed email
     */
    public static function resendFailedEmail(int $emailLogId): bool
    {
        try {
            $db = \App\Core\Database::getInstance();
            $email = $db->fetchOne(
                "SELECT * FROM email_log WHERE id = ? AND status = 'failed'",
                [$emailLogId]
            );

            if (!$email) {
                return false;
            }

            // Re-send with template if available
            if ($email['template_name']) {
                return self::send(
                    $email['recipient_email'],
                    $email['recipient_name'],
                    $email['subject'],
                    $email['template_name']
                );
            }

            return false;
        } catch (\Exception $e) {
            error_log("Failed to resend email: " . $e->getMessage());
            return false;
        }
    }
}
