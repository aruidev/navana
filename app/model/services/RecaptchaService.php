<?php
declare(strict_types=1);

class RecaptchaService {
    private string $secret;

    public function __construct(string $secret) {
        $this->secret = trim($secret);
    }

    public function isConfigured(): bool {
        return $this->secret !== '';
    }

    /**
     * Verify a reCAPTCHA v2 token against Google's API.
     */
    public function verify(string $token, ?string $remoteIp = null): bool {
        if ($this->secret === '' || $token === '') {
            return false;
        }

        $payload = http_build_query([
            'secret'   => $this->secret,
            'response' => $token,
            'remoteip' => $remoteIp ?? ''
        ]);

        $context = stream_context_create([
            'http' => [
                'method'  => 'POST',
                'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                'content' => $payload,
                'timeout' => 5
            ]
        ]);

        $result = @file_get_contents('https://www.google.com/recaptcha/api/siteverify', false, $context);
        if ($result === false) {
            return false;
        }

        $data = json_decode($result, true);
        return is_array($data) && ($data['success'] ?? false) === true;
    }
}
