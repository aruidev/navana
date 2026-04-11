<?php

declare(strict_types=1);

require_once __DIR__ . '/../../bootstrap.php';

class LogoDevService {
    private const BASE_URL = 'https://img.logo.dev/';
    private const MIN_SIZE = 16;
    private const MAX_SIZE = 800;

    private string $publicKey;

    /** @var array<string, string> */
    private array $urlCache = [];

    public function __construct(?array $config = null) {
        $appConfig = $config ?? config();
        $this->publicKey = trim((string) ($appConfig['logo_dev_public_key'] ?? ''));
    }

    public function getLogoUrlFromLink(string $link, int $size = 32): ?string {
        if ($this->publicKey === '') {
            return null;
        }

        $domain = $this->extractDomainFromUrl($link);
        if ($domain === null) {
            return null;
        }

        $cacheKey = $domain . ':' . $size;
        if (isset($this->urlCache[$cacheKey])) {
            return $this->urlCache[$cacheKey];
        }

        $logoUrl = $this->buildLogoUrl($domain, $size);
        $this->urlCache[$cacheKey] = $logoUrl;

        return $logoUrl;
    }

    public function extractDomainFromUrl(string $url): ?string {
        $trimmed = trim($url);
        if ($trimmed === '') {
            return null;
        }

        if (!preg_match('/^[a-z][a-z0-9+.-]*:\/\//i', $trimmed)) {
            $trimmed = 'https://' . $trimmed;
        }

        $host = parse_url($trimmed, PHP_URL_HOST);
        if (!is_string($host) || $host === '') {
            return null;
        }

        $domain = strtolower($host);
        if (str_starts_with($domain, 'www.')) {
            $domain = substr($domain, 4);
        }

        if (!$this->isValidDomain($domain)) {
            return null;
        }

        return $domain;
    }

    private function buildLogoUrl(string $domain, int $size): string {
        $safeSize = max(self::MIN_SIZE, min(self::MAX_SIZE, $size));

        $query = http_build_query([
            'token' => $this->publicKey,
            'size' => $safeSize,
            'format' => 'png',
            'retina' => 'true',
            'fallback' => '404',
        ]);

        return self::BASE_URL . rawurlencode($domain) . '?' . $query;
    }

    private function isValidDomain(string $domain): bool {
        if ($domain === '' || strlen($domain) > 253) {
            return false;
        }

        if (filter_var($domain, FILTER_VALIDATE_IP) !== false) {
            return true;
        }

        return preg_match('/^(?!-)(?:[a-z0-9-]{1,63}\.)+[a-z]{2,63}$/', $domain) === 1;
    }
}
