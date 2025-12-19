<?php

namespace App\Services;
use Aws\S3\S3Client;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class S3PresignedUrlService
{
    /**
     * Convert S3 URI or URL to a pre-signed URL
     * 
     * @param string $imageUrl The S3 URI (s3://bucket/path) or URL
     * @param int|null $expirationMinutes Expiration time in minutes (default from config)
     * @return string Pre-signed URL or original URL if already signed
     */
    public function convertToPresignedUrl($imageUrl, $expirationMinutes = null)
    {
        if (!$imageUrl) {
            return $imageUrl;
        }

        // Use config value if not provided
        if ($expirationMinutes === null) {
            $expirationMinutes = config('s3.presigned_url_expiration_minutes', 60);
        }

        // Check if already a presigned URL (contains Signature parameter)
        if ($this->isPresignedUrl($imageUrl)) {
            return $imageUrl;
        }

        try {
            // Parse S3 URI/URL to get bucket and key
            $parsed = $this->parseS3Uri($imageUrl);
            if (! $parsed) {
                return $imageUrl;
            }

            $bucket = $parsed['bucket'];
            $key = $parsed['key'];

            // If AWS credentials are configured, use AWS SDK to create a presigned URL
            $accessKey = env('AWS_ACCESS_KEY_ID');
            $secret = env('AWS_SECRET_ACCESS_KEY');
            $region = env('AWS_DEFAULT_REGION', 'us-east-1');

            if ($accessKey && $secret) {
                return $this->generatePresignedUrlWithSdk($bucket, $key, $expirationMinutes, $region);
            }

            // No credentials — assume bucket is public and return the standard object URL
            $encodedKey = implode('/', array_map('rawurlencode', explode('/', $key)));
            $usePathStyle = filter_var(env('AWS_USE_PATH_STYLE_ENDPOINT', false), FILTER_VALIDATE_BOOLEAN);
            $endpoint = env('AWS_ENDPOINT');

            if ($usePathStyle && $endpoint) {
                // path style endpoint: {endpoint}/{bucket}/{key}
                return rtrim($endpoint, '/') . '/' . $bucket . '/' . $encodedKey;
            }

            // virtual-hosted-style
            $regionPart = $region ? (".s3." . $region . ".amazonaws.com") : ".s3.amazonaws.com";
            return "https://{$bucket}{$regionPart}/{$encodedKey}";
        } catch (\Exception $e) {
            \Log::error('Failed to generate presigned URL: ' . $e->getMessage());
            return $imageUrl;
        }
    }

    /**
     * Check if URL is already a presigned URL
     */
    private function isPresignedUrl($url)
    {
        return strpos($url, 'X-Amz-Signature') !== false;
    }

    /**
     * Check if URL is an S3 URL
     */
    private function isS3Url($url)
    {
        return strpos($url, '.s3') !== false || strpos($url, 'amazonaws.com') !== false;
    }

    /**
     * Extract object key from S3 URI or URL
     */
    private function extractKeyFromUrl($url)
    {
        // Parse s3://bucket/key format
        if (strpos($url, 's3://') === 0) {
            $matches = [];
            if (preg_match('#s3://([^/]+)/(.+)#', $url, $matches)) {
                return $matches[2];
            }
        }

        // Parse standard S3 URL formats
        if ($this->isS3Url($url)) {
            $config = config('filesystems.disks.s3');
            $bucket = $config['bucket'] ?? '';
            $parsedUrl = parse_url($url);
            $path = $parsedUrl['path'] ?? '';

            // Remove leading slash
            $key = ltrim($path, '/');

            // If key starts with bucket name, remove it
            if ($bucket && strpos($key, $bucket) === 0) {
                $key = substr($key, strlen($bucket));
                $key = ltrim($key, '/');
            }

            return $key ?: null;
        }

        return null;
    }

    /**
     * Parse an S3 URI or URL and return ['bucket'=>..., 'key'=>...] or null
     */
    private function parseS3Uri($url)
    {
        // s3://bucket/key
        if (strpos($url, 's3://') === 0) {
            $matches = [];
            if (preg_match('#s3://([^/]+)/(.+)#', $url, $matches)) {
                return ['bucket' => $matches[1], 'key' => $matches[2]];
            }
            return null;
        }

        // https://bucket.s3.region.amazonaws.com/key or https://s3.region.amazonaws.com/bucket/key
        if ($this->isS3Url($url)) {
            $parsed = parse_url($url);
            $host = $parsed['host'] ?? '';
            $path = $parsed['path'] ?? '';

            // virtual-hosted-style: bucket.s3.region.amazonaws.com
            if (preg_match('/^([^.]+)\.s3(\.|-)[a-z0-9-]+\.amazonaws\.com$/', $host, $m)) {
                $bucket = $m[1];
                $key = ltrim($path, '/');
                return ['bucket' => $bucket, 'key' => $key];
            }

            // path-style: s3.region.amazonaws.com/bucket/key or s3.amazonaws.com/bucket/key
            $segments = explode('/', ltrim($path, '/'));
            if (count($segments) >= 2) {
                $bucket = array_shift($segments);
                $key = implode('/', $segments);
                return ['bucket' => $bucket, 'key' => $key];
            }
        }

        return null;
    }

    /**
     * Generate presigned URL using AWS SDK
     */
    private function generatePresignedUrlWithSdk($bucket, $key, $expirationMinutes, $region = 'us-east-1')
    {
        try {
            $config = [
                'version' => 'latest',
                'region' => $region,
            ];

            $accessKey = env('AWS_ACCESS_KEY_ID');
            $secret = env('AWS_SECRET_ACCESS_KEY');
            if ($accessKey && $secret) {
                $config['credentials'] = [
                    'key' => $accessKey,
                    'secret' => $secret,
                ];
            }

            $endpoint = env('AWS_ENDPOINT');
            if ($endpoint) {
                $config['endpoint'] = $endpoint;
                $config['use_path_style_endpoint'] = filter_var(env('AWS_USE_PATH_STYLE_ENDPOINT', false), FILTER_VALIDATE_BOOLEAN);
            }

            $s3 = new S3Client($config);

            $cmd = $s3->getCommand('GetObject', ['Bucket' => $bucket, 'Key' => $key]);
            $request = $s3->createPresignedRequest($cmd, '+' . (int)$expirationMinutes . ' minutes');
            return (string)$request->getUri();
        } catch (\Exception $e) {
            \Log::error('Failed to generate presigned URL (SDK): ' . $e->getMessage());
            return "s3://{$bucket}/{$key}";
        }
    }
}
