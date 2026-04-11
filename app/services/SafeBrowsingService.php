<?php

declare(strict_types=1);

require_once __DIR__ . '/../../bootstrap.php';

class SafeBrowsingService {
    private const DEFAULT_ENDPOINT = 'https://safebrowsing.googleapis.com/v4/threatMatches:find';
    private const DEFAULT_TIMEOUT_SECONDS = 5;
    private const DEFAULT_CACHE_TTL_SECONDS = 21600;

    private string $apiKey;
    private string $endpoint;
    private int $timeoutSeconds;
    private int $cacheTtlSeconds;
    private bool $strictMode;
    private string $cacheDir;

    /** @var array<string, array<string, mixed>> */
    private array $requestCache = [];

    public function __construct(?array $config = null) {
        $appConfig = $config ?? config();

        $this->apiKey = trim((string) ($appConfig['safe_browsing_api_key'] ?? ''));
        $this->endpoint = trim((string) ($appConfig['safe_browsing_endpoint'] ?? self::DEFAULT_ENDPOINT));
        $this->timeoutSeconds = max(1, (int) ($appConfig['safe_browsing_timeout_seconds'] ?? self::DEFAULT_TIMEOUT_SECONDS));
        $this->cacheTtlSeconds = max(60, (int) ($appConfig['safe_browsing_cache_ttl_seconds'] ?? self::DEFAULT_CACHE_TTL_SECONDS));
        $this->strictMode = (bool) ($appConfig['safe_browsing_strict_mode'] ?? true);
        $this->cacheDir = __DIR__ . '/../../api-cache/safe-browsing';
    }

    public function isConfigured(): bool {
        return $this->apiKey !== '';
    }

    /**
     * @return array{status:string,isSafe:bool,canPersist:bool,message:string,normalizedUrl:?string,source:string,threatTypes:array<int,string>}
     */
    public function checkUrlSafety(string $url): array {
        $normalizedUrl = $this->normalizeUrl($url);
        if ($normalizedUrl === null) {
            return [
                'status' => 'invalid',
                'isSafe' => false,
                'canPersist' => false,
                'message' => 'Invalid URL format.',
                'normalizedUrl' => null,
                'source' => 'local',
                'threatTypes' => [],
            ];
        }

        if (isset($this->requestCache[$normalizedUrl])) {
            return $this->requestCache[$normalizedUrl];
        }

        $cachedResult = $this->readCache($normalizedUrl);
        if ($cachedResult !== null) {
            $this->requestCache[$normalizedUrl] = $cachedResult;
            return $cachedResult;
        }

        if (!$this->isConfigured()) {
            $result = $this->resultFromPolicy(
                'not_configured',
                $normalizedUrl,
                'Safe Browsing is not configured. URL verification unavailable.',
                []
            );
            $this->requestCache[$normalizedUrl] = $result;
            return $result;
        }

        $requestUrl = $this->endpoint . '?key=' . rawurlencode($this->apiKey);

        $payload = [
            'client' => [
                'clientId' => 'navana',
                'clientVersion' => '1.0.0',
            ],
            'threatInfo' => [
                'threatTypes' => ['MALWARE', 'SOCIAL_ENGINEERING', 'UNWANTED_SOFTWARE', 'POTENTIALLY_HARMFUL_APPLICATION'],
                'platformTypes' => ['ANY_PLATFORM'],
                'threatEntryTypes' => ['URL'],
                'threatEntries' => [
                    ['url' => $normalizedUrl],
                ],
            ],
        ];

        $context = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => "Content-Type: application/json\r\n",
                'content' => json_encode($payload),
                'timeout' => $this->timeoutSeconds,
            ],
        ]);

        $rawResponse = @file_get_contents($requestUrl, false, $context);
        if ($rawResponse === false) {
            $result = $this->resultFromPolicy(
                'error',
                $normalizedUrl,
                'Could not verify URL security at this time. Please try again.',
                []
            );
            $this->requestCache[$normalizedUrl] = $result;
            return $result;
        }

        $decoded = json_decode($rawResponse, true);
        if (!is_array($decoded)) {
            $result = $this->resultFromPolicy(
                'error',
                $normalizedUrl,
                'Could not verify URL security at this time. Please try again.',
                []
            );
            $this->requestCache[$normalizedUrl] = $result;
            return $result;
        }

        if (isset($decoded['error']) && is_array($decoded['error'])) {
            $result = $this->resultFromPolicy(
                'error',
                $normalizedUrl,
                'Could not verify URL security at this time. Please try again.',
                []
            );
            $this->requestCache[$normalizedUrl] = $result;
            return $result;
        }

        $matches = $decoded['matches'] ?? [];
        if (!is_array($matches)) {
            $result = $this->resultFromPolicy(
                'error',
                $normalizedUrl,
                'Could not verify URL security at this time. Please try again.',
                []
            );
            $this->requestCache[$normalizedUrl] = $result;
            return $result;
        }

        $threatTypes = [];
        foreach ($matches as $match) {
            if (!is_array($match)) {
                continue;
            }
            $type = $match['threatType'] ?? null;
            if (!is_string($type) || $type === '') {
                continue;
            }
            $threatTypes[] = $type;
        }
        $threatTypes = array_values(array_unique($threatTypes));

        if ($threatTypes !== []) {
            $result = [
                'status' => 'unsafe',
                'isSafe' => false,
                'canPersist' => false,
                'message' => 'This URL is reported as unsafe and cannot be saved.',
                'normalizedUrl' => $normalizedUrl,
                'source' => 'api',
                'threatTypes' => $threatTypes,
            ];
            $this->writeCache($normalizedUrl, $result, $this->extractCacheTtlSeconds($decoded));
            $this->requestCache[$normalizedUrl] = $result;
            return $result;
        }

        $result = [
            'status' => 'safe',
            'isSafe' => true,
            'canPersist' => true,
            'message' => '',
            'normalizedUrl' => $normalizedUrl,
            'source' => 'api',
            'threatTypes' => [],
        ];

        $this->writeCache($normalizedUrl, $result, $this->extractCacheTtlSeconds($decoded));
        $this->requestCache[$normalizedUrl] = $result;
        return $result;
    }

    private function normalizeUrl(string $url): ?string {
        $trimmed = trim($url);
        if ($trimmed === '') {
            return null;
        }

        if (!preg_match('/^[a-z][a-z0-9+.-]*:\/\//i', $trimmed)) {
            $trimmed = 'https://' . $trimmed;
        }

        $parts = parse_url($trimmed);
        if (!is_array($parts)) {
            return null;
        }

        $scheme = strtolower((string) ($parts['scheme'] ?? ''));
        $host = strtolower((string) ($parts['host'] ?? ''));

        if (($scheme !== 'http' && $scheme !== 'https') || $host === '') {
            return null;
        }

        $port = isset($parts['port']) ? ':' . (int) $parts['port'] : '';
        $path = (string) ($parts['path'] ?? '/');
        if ($path === '') {
            $path = '/';
        }

        $query = isset($parts['query']) ? '?' . $parts['query'] : '';
        $fragment = isset($parts['fragment']) ? '#' . $parts['fragment'] : '';

        $normalized = $scheme . '://' . $host . $port . $path . $query . $fragment;
        return filter_var($normalized, FILTER_VALIDATE_URL) !== false ? $normalized : null;
    }

    /**
     * @return array{status:string,isSafe:bool,canPersist:bool,message:string,normalizedUrl:?string,source:string,threatTypes:array<int,string>}|null
     */
    private function readCache(string $normalizedUrl): ?array {
        $file = $this->getCacheFilePath($normalizedUrl);
        if (!is_file($file)) {
            return null;
        }

        $raw = @file_get_contents($file);
        if (!is_string($raw) || $raw === '') {
            return null;
        }

        $decoded = json_decode($raw, true);
        if (!is_array($decoded)) {
            return null;
        }

        $expiresAt = (int) ($decoded['expires_at'] ?? 0);
        if ($expiresAt < time()) {
            @unlink($file);
            return null;
        }

        $status = (string) ($decoded['status'] ?? 'error');
        $threatTypes = $decoded['threat_types'] ?? [];
        if (!is_array($threatTypes)) {
            $threatTypes = [];
        }

        if ($status === 'safe') {
            return [
                'status' => 'safe',
                'isSafe' => true,
                'canPersist' => true,
                'message' => '',
                'normalizedUrl' => $normalizedUrl,
                'source' => 'cache',
                'threatTypes' => [],
            ];
        }

        if ($status === 'unsafe') {
            return [
                'status' => 'unsafe',
                'isSafe' => false,
                'canPersist' => false,
                'message' => 'This URL is reported as unsafe and cannot be saved.',
                'normalizedUrl' => $normalizedUrl,
                'source' => 'cache',
                'threatTypes' => array_values(array_filter($threatTypes, 'is_string')),
            ];
        }

        return null;
    }

    /**
     * @param array{status:string,isSafe:bool,canPersist:bool,message:string,normalizedUrl:?string,source:string,threatTypes:array<int,string>} $result
     */
    private function writeCache(string $normalizedUrl, array $result, int $ttlSeconds): void {
        $dir = $this->cacheDir;
        if (!is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }
        if (!is_dir($dir)) {
            return;
        }

        $file = $this->getCacheFilePath($normalizedUrl);
        $payload = [
            'status' => $result['status'],
            'threat_types' => $result['threatTypes'],
            'expires_at' => time() + max(60, $ttlSeconds),
        ];

        @file_put_contents($file, json_encode($payload));
    }

    private function getCacheFilePath(string $normalizedUrl): string {
        return $this->cacheDir . '/' . hash('sha256', $normalizedUrl) . '.json';
    }

    private function extractCacheTtlSeconds(array $apiResponse): int {
        $rawDuration = (string) ($apiResponse['cacheDuration'] ?? '');
        if (preg_match('/^(\d+)(?:\.\d+)?s$/', $rawDuration, $matches) !== 1) {
            return $this->cacheTtlSeconds;
        }

        return max(60, min((int) $matches[1], 86400));
    }

    /**
     * @param array<int, string> $threatTypes
     * @return array{status:string,isSafe:bool,canPersist:bool,message:string,normalizedUrl:?string,source:string,threatTypes:array<int,string>}
     */
    private function resultFromPolicy(string $status, string $normalizedUrl, string $message, array $threatTypes): array {
        $allowOnFailure = !$this->strictMode;

        return [
            'status' => $status,
            'isSafe' => $allowOnFailure,
            'canPersist' => $allowOnFailure,
            'message' => $message,
            'normalizedUrl' => $normalizedUrl,
            'source' => 'policy',
            'threatTypes' => $threatTypes,
        ];
    }
}
