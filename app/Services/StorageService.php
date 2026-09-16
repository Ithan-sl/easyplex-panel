<?php

namespace App\Services;

use App\Setting;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Http\File;
use Exception;

class StorageService
{
    /**
     * Upload a local file to the configured active storage destination.
     *
     * @param string $localFilePath Absolute path to the file on local disk
     * @param string|null $originalName Original filename
     * @return array
     * @throws Exception
     */
    public function uploadFile(string $localFilePath, ?string $originalName = null): array
    {
        if (!file_exists($localFilePath)) {
            throw new Exception("Source file does not exist: {$localFilePath}");
        }

        $settings = Setting::first();
        if (!$settings) {
            throw new Exception("System settings not found.");
        }

        // Determine extension
        $extension = pathinfo($originalName ?: $localFilePath, PATHINFO_EXTENSION);
        if (!$extension) {
            $extension = 'mp4';
        }
        $filename = Str::random(40) . '.' . strtolower($extension);

        // Determine active storage driver
        $driver = $this->determineDriver($settings);

        switch ($driver) {
            case 's3':
                $result = $this->uploadToS3($localFilePath, $filename, $settings);
                break;

            case 'wasabi':
                $result = $this->uploadToWasabi($localFilePath, $filename, $settings);
                break;

            case 'ftp':
                $result = $this->uploadToFtp($localFilePath, $filename, $settings);
                break;

            case 'webdav':
                $result = $this->uploadToWebdav($localFilePath, $filename, $settings);
                break;

            case 'sftp':
                $result = $this->uploadToSftp($localFilePath, $filename, $settings);
                break;

            case 'local':
            default:
                $result = $this->uploadToLocal($localFilePath, $filename, $settings);
                break;
        }

        // Handle optional local storage
        if ($driver !== 'local' && (int)$settings->keep_local_copy === 1) {
            // If admin opted to also keep a local copy, copy to videos disk
            try {
                Storage::disk('videos')->putFileAs('', new File($localFilePath), $filename);
            } catch (\Throwable $t) {
                \Log::warning("Could not create local copy: " . $t->getMessage());
            }
        }

        return $result;
    }

    /**
     * Determine active driver from settings.
     */
    public function determineDriver(Setting $settings): string
    {
        if (!empty($settings->active_storage)) {
            return strtolower(trim($settings->active_storage));
        }

        if ($settings->aws_s3_storage) return 's3';
        if ($settings->wasabi_storage) return 'wasabi';
        if ($settings->ftp_storage) return 'ftp';
        if ($settings->webdav_storage) return 'webdav';
        if ($settings->sftp_storage) return 'sftp';

        return 'local';
    }

    /**
     * Upload to AWS S3
     */
    protected function uploadToS3(string $localFilePath, string $filename, Setting $settings): array
    {
        if (
            empty($settings->aws_access_key_id) ||
            empty($settings->aws_secret_access_key) ||
            empty($settings->aws_default_region) ||
            empty($settings->aws_bucket)
        ) {
            throw new Exception("AWS S3 credentials incomplete.");
        }

        config([
            'filesystems.disks.s3.key'    => $settings->aws_access_key_id,
            'filesystems.disks.s3.secret' => $settings->aws_secret_access_key,
            'filesystems.disks.s3.region' => $settings->aws_default_region,
            'filesystems.disks.s3.bucket' => $settings->aws_bucket,
        ]);

        Storage::disk('s3')->putFileAs('', new File($localFilePath), $filename, 'public');
        $url = Storage::disk('s3')->url($filename);

        return [
            'status'     => 200,
            'video_path' => $url,
            'server'     => 'AWS S3',
            'filename'   => $filename,
            'message'    => 'Successfully uploaded to AWS S3'
        ];
    }

    /**
     * Upload to Wasabi
     */
    protected function uploadToWasabi(string $localFilePath, string $filename, Setting $settings): array
    {
        if (
            empty($settings->wasabi_access_key_id) ||
            empty($settings->wasabi_secret_access_key) ||
            empty($settings->wasabi_default_region) ||
            empty($settings->wasabi_bucket)
        ) {
            throw new Exception("Wasabi credentials incomplete.");
        }

        config([
            'filesystems.disks.wasabi.key'    => $settings->wasabi_access_key_id,
            'filesystems.disks.wasabi.secret' => $settings->wasabi_secret_access_key,
            'filesystems.disks.wasabi.region' => $settings->wasabi_default_region,
            'filesystems.disks.wasabi.bucket' => $settings->wasabi_bucket,
        ]);

        Storage::disk('wasabi')->putFileAs('', new File($localFilePath), $filename, 'public');
        $url = Storage::disk('wasabi')->url($filename);

        return [
            'status'     => 200,
            'video_path' => $url,
            'server'     => 'Wasabi',
            'filename'   => $filename,
            'message'    => 'Successfully uploaded to Wasabi'
        ];
    }

    /**
     * Upload via FTP
     */
    protected function uploadToFtp(string $localFilePath, string $filename, Setting $settings): array
    {
        if (empty($settings->ftp_host)) {
            throw new Exception("FTP Host is required.");
        }

        $port = !empty($settings->ftp_port) ? (int)$settings->ftp_port : 21;
        $timeout = 90;

        if ($settings->ftp_ssl && function_exists('ftp_ssl_connect')) {
            $conn = @ftp_ssl_connect($settings->ftp_host, $port, $timeout);
        } else {
            $conn = @ftp_connect($settings->ftp_host, $port, $timeout);
        }

        if (!$conn) {
            throw new Exception("Could not connect to FTP host {$settings->ftp_host}:{$port}");
        }

        $user = $settings->ftp_username ?: 'anonymous';
        $pass = $settings->ftp_password ?: '';
        $login = @ftp_login($conn, $user, $pass);
        if (!$login) {
            @ftp_close($conn);
            throw new Exception("FTP authentication failed for user {$user}");
        }

        if ((int)$settings->ftp_pasv !== 0) {
            @ftp_pasv($conn, true);
        }

        $targetDir = trim($settings->ftp_path ?: '', '/');
        if ($targetDir !== '') {
            $parts = explode('/', $targetDir);
            foreach ($parts as $part) {
                if ($part === '') continue;
                if (!@ftp_chdir($conn, $part)) {
                    @ftp_mkdir($conn, $part);
                    @ftp_chdir($conn, $part);
                }
            }
        }

        $upload = @ftp_put($conn, $filename, $localFilePath, FTP_BINARY);
        @ftp_close($conn);

        if (!$upload) {
            throw new Exception("FTP upload failed for file {$filename}");
        }

        if (!empty($settings->ftp_url)) {
            $url = rtrim($settings->ftp_url, '/') . '/' . $filename;
        } else {
            $url = 'ftp://' . $settings->ftp_host . '/' . ($targetDir ? $targetDir . '/' : '') . $filename;
        }

        return [
            'status'     => 200,
            'video_path' => $url,
            'server'     => 'FTP',
            'filename'   => $filename,
            'message'    => 'Successfully uploaded to FTP'
        ];
    }

    /**
     * Upload via WebDAV (HTTP PUT via cURL)
     */
    protected function uploadToWebdav(string $localFilePath, string $filename, Setting $settings): array
    {
        if (empty($settings->webdav_url)) {
            throw new Exception("WebDAV URL is required.");
        }

        $base = rtrim($settings->webdav_url, '/');
        $sub = trim($settings->webdav_path ?: '', '/');
        $targetUrl = $base . ($sub !== '' ? '/' . $sub : '') . '/' . $filename;

        $fp = fopen($localFilePath, 'r');
        if (!$fp) {
            throw new Exception("Cannot read local file for WebDAV upload.");
        }

        $fileSize = filesize($localFilePath);

        $ch = curl_init($targetUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_PUT, true);
        curl_setopt($ch, CURLOPT_INFILE, $fp);
        curl_setopt($ch, CURLOPT_INFILESIZE, $fileSize);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 3600);

        if (!empty($settings->webdav_username)) {
            $auth = $settings->webdav_username . ':' . ($settings->webdav_password ?: '');
            curl_setopt($ch, CURLOPT_USERPWD, $auth);
            curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        fclose($fp);
        curl_close($ch);

        if ($error || !in_array($httpCode, [200, 201, 204])) {
            throw new Exception("WebDAV upload failed (HTTP {$httpCode}): " . ($error ?: $response));
        }

        if (!empty($settings->webdav_public_url)) {
            $url = rtrim($settings->webdav_public_url, '/') . '/' . $filename;
        } else {
            $url = $targetUrl;
        }

        return [
            'status'     => 200,
            'video_path' => $url,
            'server'     => 'WebDAV',
            'filename'   => $filename,
            'message'    => 'Successfully uploaded to WebDAV'
        ];
    }

    /**
     * Upload via SFTP / SCP / RSync
     */
    protected function uploadToSftp(string $localFilePath, string $filename, Setting $settings): array
    {
        if (empty($settings->sftp_host)) {
            throw new Exception("SFTP Host is required.");
        }

        $method = strtolower($settings->sftp_method ?: 'sftp');
        $port = !empty($settings->sftp_port) ? (int)$settings->sftp_port : 22;
        $user = $settings->sftp_username ?: 'root';
        $pass = $settings->sftp_password ?: '';
        $remoteDir = trim($settings->sftp_path ?: '', '/');
        $remotePath = ($remoteDir !== '' ? $remoteDir . '/' : '') . $filename;

        if ($method === 'rsync') {
            // Use rsync CLI
            $sshCmd = "ssh -p {$port} -o StrictHostKeyChecking=no";
            if (!empty($settings->sftp_key)) {
                $keyFile = tempnam(sys_get_temp_dir(), 'sftp_key_');
                file_put_contents($keyFile, $settings->sftp_key);
                chmod($keyFile, 0600);
                $sshCmd .= " -i " . escapeshellarg($keyFile);
            }

            $dest = escapeshellarg("{$user}@{$settings->sftp_host}:/{$remotePath}");
            $src = escapeshellarg($localFilePath);
            $cmd = "rsync -avz -e " . escapeshellarg($sshCmd) . " {$src} {$dest} 2>&1";

            if (!empty($pass) && empty($settings->sftp_key) && exec('which sshpass')) {
                $cmd = "sshpass -p " . escapeshellarg($pass) . " " . $cmd;
            }

            exec($cmd, $output, $returnCode);
            if (isset($keyFile) && file_exists($keyFile)) {
                @unlink($keyFile);
            }

            if ($returnCode !== 0) {
                throw new Exception("RSync upload failed: " . implode("\n", $output));
            }
        } else {
            // Use native cURL SFTP / SCP
            $proto = ($method === 'scp') ? 'scp' : 'sftp';
            $sftpUrl = "{$proto}://{$settings->sftp_host}:{$port}/" . ltrim($remotePath, '/');

            $fp = fopen($localFilePath, 'r');
            if (!$fp) {
                throw new Exception("Cannot read local file for {$proto} upload.");
            }
            $fileSize = filesize($localFilePath);

            $ch = curl_init($sftpUrl);
            curl_setopt($ch, CURLOPT_UPLOAD, true);
            curl_setopt($ch, CURLOPT_INFILE, $fp);
            curl_setopt($ch, CURLOPT_INFILESIZE, $fileSize);
            curl_setopt($ch, CURLOPT_TIMEOUT, 3600);
            curl_setopt($ch, CURLOPT_FTP_CREATE_MISSING_DIRS, CURLFTP_CREATE_DIR_RETRY);

            if (!empty($pass)) {
                curl_setopt($ch, CURLOPT_USERPWD, "{$user}:{$pass}");
            }

            if (!empty($settings->sftp_key)) {
                $keyFile = tempnam(sys_get_temp_dir(), 'sftp_key_');
                file_put_contents($keyFile, $settings->sftp_key);
                chmod($keyFile, 0600);
                curl_setopt($ch, CURLOPT_SSH_PRIVATE_KEYFILE, $keyFile);
                curl_setopt($ch, CURLOPT_SSH_AUTH_TYPES, CURLSSH_AUTH_PUBLICKEY);
            }

            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $result = curl_exec($ch);
            $error = curl_error($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            fclose($fp);
            curl_close($ch);

            if (isset($keyFile) && file_exists($keyFile)) {
                @unlink($keyFile);
            }

            if ($error) {
                throw new Exception(strtoupper($proto) . " upload failed: {$error}");
            }
        }

        if (!empty($settings->sftp_url)) {
            $url = rtrim($settings->sftp_url, '/') . '/' . $filename;
        } else {
            $url = "sftp://{$settings->sftp_host}/{$remotePath}";
        }

        return [
            'status'     => 200,
            'video_path' => $url,
            'server'     => strtoupper($method),
            'filename'   => $filename,
            'message'    => "Successfully uploaded to " . strtoupper($method)
        ];
    }

    /**
     * Upload to Local storage
     */
    protected function uploadToLocal(string $localFilePath, string $filename, Setting $settings): array
    {
        Storage::disk('videos')->putFileAs('', new File($localFilePath), $filename);
        $url = url('/api/video/' . $filename);

        return [
            'status'     => 200,
            'video_path' => $url,
            'server'     => config('app.name', 'EASYPLEX'),
            'filename'   => $filename,
            'message'    => 'Successfully uploaded to Local Storage'
        ];
    }

    /**
     * Test connection to a specific storage service.
     */
    public function testConnection(string $driver, ?array $params = null): array
    {
        $settings = Setting::first();
        if ($params) {
            foreach ($params as $k => $v) {
                $settings->{$k} = $v;
            }
        }

        try {
            switch ($driver) {
                case 'ftp':
                    return $this->testFtp($settings);
                case 'webdav':
                    return $this->testWebdav($settings);
                case 'sftp':
                    return $this->testSftp($settings);
                case 's3':
                    return $this->testS3($settings);
                case 'wasabi':
                    return $this->testWasabi($settings);
                default:
                    return ['status' => 200, 'message' => 'Local storage is available.'];
            }
        } catch (Exception $e) {
            return ['status' => 400, 'message' => $e->getMessage()];
        }
    }

    protected function testFtp(Setting $settings): array
    {
        if (empty($settings->ftp_host)) throw new Exception("FTP Host is empty.");
        $port = !empty($settings->ftp_port) ? (int)$settings->ftp_port : 21;
        $conn = ($settings->ftp_ssl && function_exists('ftp_ssl_connect'))
            ? @ftp_ssl_connect($settings->ftp_host, $port, 15)
            : @ftp_connect($settings->ftp_host, $port, 15);

        if (!$conn) throw new Exception("Could not connect to FTP host {$settings->ftp_host}:{$port}");
        $user = $settings->ftp_username ?: 'anonymous';
        $pass = $settings->ftp_password ?: '';
        if (!@ftp_login($conn, $user, $pass)) {
            @ftp_close($conn);
            throw new Exception("FTP Login failed for user {$user}");
        }
        @ftp_close($conn);
        return ['status' => 200, 'message' => 'FTP connection successful!'];
    }

    protected function testWebdav(Setting $settings): array
    {
        if (empty($settings->webdav_url)) throw new Exception("WebDAV URL is empty.");
        $ch = curl_init($settings->webdav_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PROPFIND');
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        if (!empty($settings->webdav_username)) {
            curl_setopt($ch, CURLOPT_USERPWD, $settings->webdav_username . ':' . ($settings->webdav_password ?: ''));
        }
        curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err = curl_error($ch);
        curl_close($ch);

        if ($err) throw new Exception("WebDAV cURL error: {$err}");
        if ($code >= 400 && $code !== 405) { // 405 Method Not Allowed could still mean server is reachable
            throw new Exception("WebDAV responded with HTTP status {$code}");
        }
        return ['status' => 200, 'message' => "WebDAV reachable (HTTP {$code})!"];
    }

    protected function testSftp(Setting $settings): array
    {
        if (empty($settings->sftp_host)) throw new Exception("SFTP Host is empty.");
        $port = !empty($settings->sftp_port) ? (int)$settings->sftp_port : 22;
        $url = "sftp://{$settings->sftp_host}:{$port}/";
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        curl_setopt($ch, CURLOPT_DIRLISTONLY, true);
        if (!empty($settings->sftp_username)) {
            curl_setopt($ch, CURLOPT_USERPWD, $settings->sftp_username . ':' . ($settings->sftp_password ?: ''));
        }
        curl_exec($ch);
        $err = curl_error($ch);
        curl_close($ch);
        if ($err) throw new Exception("SFTP connection failed: {$err}");
        return ['status' => 200, 'message' => 'SFTP connection successful!'];
    }

    protected function testS3(Setting $settings): array
    {
        config([
            'filesystems.disks.s3.key'    => $settings->aws_access_key_id,
            'filesystems.disks.s3.secret' => $settings->aws_secret_access_key,
            'filesystems.disks.s3.region' => $settings->aws_default_region,
            'filesystems.disks.s3.bucket' => $settings->aws_bucket,
        ]);
        Storage::disk('s3')->has('test.txt');
        return ['status' => 200, 'message' => 'AWS S3 connection successful!'];
    }

    protected function testWasabi(Setting $settings): array
    {
        config([
            'filesystems.disks.wasabi.key'    => $settings->wasabi_access_key_id,
            'filesystems.disks.wasabi.secret' => $settings->wasabi_secret_access_key,
            'filesystems.disks.wasabi.region' => $settings->wasabi_default_region,
            'filesystems.disks.wasabi.bucket' => $settings->wasabi_bucket,
        ]);
        Storage::disk('wasabi')->has('test.txt');
        return ['status' => 200, 'message' => 'Wasabi connection successful!'];
    }
}
