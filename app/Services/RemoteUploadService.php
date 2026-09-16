<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Str;

class RemoteUploadService
{
    protected StorageService $storageService;

    public function __construct(StorageService $storageService)
    {
        $this->storageService = $storageService;
    }

    /**
     * Download a file from a remote URL directly on the server,
     * and upload it to the configured active storage destination.
     *
     * @param string $remoteUrl
     * @return array
     * @throws Exception
     */
    public function downloadAndUpload(string $remoteUrl): array
    {
        $remoteUrl = trim($remoteUrl);

        if (!filter_var($remoteUrl, FILTER_VALIDATE_URL)) {
            throw new Exception("Invalid remote URL provided: " . substr($remoteUrl, 0, 50));
        }

        // Ensure temporary directory exists
        $tempDir = storage_path('app/temp');
        if (!is_dir($tempDir)) {
            mkdir($tempDir, 0775, true);
        }

        // Temporary destination path
        $tempFilename = 'remote_' . uniqid() . '.tmp';
        $tempFilePath = $tempDir . DIRECTORY_SEPARATOR . $tempFilename;

        $fp = fopen($tempFilePath, 'w+');
        if (!$fp) {
            throw new Exception("Unable to create local temporary file for streaming download.");
        }

        $detectedFilename = null;

        // Initialize cURL for streaming download
        $ch = curl_init($remoteUrl);
        curl_setopt($ch, CURLOPT_FILE, $fp);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
        curl_setopt($ch, CURLOPT_TIMEOUT, 7200); // 2 hours max
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36');
        curl_setopt($ch, CURLOPT_BUFFERSIZE, 1024 * 1024); // 1MB buffer

        // Capture headers to extract Content-Disposition filename
        curl_setopt($ch, CURLOPT_HEADERFUNCTION, function ($ch, $header) use (&$detectedFilename) {
            $len = strlen($header);
            $parts = explode(':', $header, 2);
            if (count($parts) === 2 && strtolower(trim($parts[0])) === 'content-disposition') {
                if (preg_match('/filename\*?=(?:UTF-8\'\')?["\']?([^";\n]+)["\']?/i', $parts[1], $matches)) {
                    $detectedFilename = trim($matches[1], "\"' ");
                }
            }
            return $len;
        });

        $success = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $effectiveUrl = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
        $curlError = curl_error($ch);
        fclose($fp);
        curl_close($ch);

        if (!$success || $httpCode >= 400) {
            if (file_exists($tempFilePath)) {
                @unlink($tempFilePath);
            }
            throw new Exception("Remote download failed (HTTP {$httpCode}): " . ($curlError ?: "Connection error"));
        }

        $fileSize = file_exists($tempFilePath) ? filesize($tempFilePath) : 0;
        if ($fileSize === 0) {
            if (file_exists($tempFilePath)) {
                @unlink($tempFilePath);
            }
            throw new Exception("Downloaded file is empty (0 bytes).");
        }

        // Determine filename and extension
        if (!$detectedFilename) {
            $parsedPath = parse_url($effectiveUrl ?: $remoteUrl, PHP_URL_PATH);
            $detectedFilename = basename($parsedPath);
        }

        $ext = pathinfo($detectedFilename ?: '', PATHINFO_EXTENSION);
        if (!$ext || strlen($ext) > 5) {
            $ext = 'mp4';
            $detectedFilename = ($detectedFilename ?: 'video') . '.' . $ext;
        }

        try {
            // Upload downloaded temporary file to storage destination
            $uploadResult = $this->storageService->uploadFile($tempFilePath, $detectedFilename);
            $uploadResult['file_size'] = $fileSize;
            $uploadResult['original_name'] = $detectedFilename;
            return $uploadResult;
        } finally {
            // Always clean up local temporary file
            if (file_exists($tempFilePath)) {
                @unlink($tempFilePath);
            }
        }
    }
}
