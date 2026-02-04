<?php
declare(strict_types=1);

require_once __DIR__ . '/../../../bootstrap.php';
require_once __DIR__ . '/../../../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class MailService
{
    private array $config;

    public function __construct(?array $config = null)
    {
        $this->config = $config ?? config();
    }

    public function send(string $toEmail, string $toName, string $subject, string $body): bool
    {
        $host = $this->config['smtp_host'] ?? '';
        $user = $this->config['smtp_user'] ?? '';
        $pass = $this->config['smtp_pass'] ?? '';
        $port = (int)($this->config['smtp_port'] ?? 587);
        $secure = $this->config['smtp_secure'] ?? 'tls';
        $fromEmail = $this->config['smtp_from_email'] ?? '';
        $fromName = $this->config['smtp_from_name'] ?? 'Navana';

        if ($host === '' || $user === '' || $pass === '' || $fromEmail === '') {
            return false;
        }

        $mailer = new PHPMailer(true);

        try {
            $mailer->isSMTP();
            $mailer->Host = $host;
            $mailer->SMTPAuth = true;
            $mailer->Username = $user;
            $mailer->Password = $pass;
            $mailer->SMTPSecure = $secure;
            $mailer->Port = $port;
            $mailer->CharSet = 'UTF-8';

            $mailer->setFrom($fromEmail, $fromName);
            $mailer->addAddress($toEmail, $toName);

            $mailer->Subject = $subject;
            $mailer->Body = $body;
            $mailer->isHTML(false);

            return $mailer->send();
        } catch (Exception $e) {
            return false;
        }
    }
}
