<?php

namespace App\Http\Controllers;

use FFMpeg;
use FFMpeg\Format\Video\X264;
use App\Jobs\VideoConversion;
use ProtoneMedia\LaravelFFMpeg\Filters\WatermarkFactory;
use App\Http\Requests\VideoRequest;
use App\Http\Requests\StreamingVideoRequest;
use App\Setting;
use App\Services\StorageService;
use App\Services\RemoteUploadService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use FFMpeg\Filters\Video\VideoFilters;

class VideoController extends Controller
{
    const STATUS = "status";
    const MESSAGE = "message";
    const VIDEOS = "videos";

    protected StorageService $storageService;
    protected RemoteUploadService $remoteUploadService;

    public function __construct(StorageService $storageService, RemoteUploadService $remoteUploadService)
    {
        $this->storageService = $storageService;
        $this->remoteUploadService = $remoteUploadService;
    }

    // save a new video in configured storage (Local, S3, Wasabi, FTP, WebDAV, SFTP)
    public function store(VideoRequest $request)
    {
        if ($request->hasFile('video')) {
            try {
                $file = $request->file('video');
                $result = $this->storageService->uploadFile($file->getRealPath(), $file->getClientOriginalName());
                return response()->json($result, 200);
            } catch (\Throwable $e) {
                return response()->json([
                    self::STATUS => 400,
                    self::MESSAGE => 'Upload failed: ' . $e->getMessage()
                ], 400);
            }
        }

        return response()->json([
            self::STATUS => 400,
            self::MESSAGE => 'No video file provided'
        ], 400);
    }

    public function Streamingstore(StreamingVideoRequest $request)
    {
        if ($request->hasFile('video')) {
            try {
                $file = $request->file('video');
                $result = $this->storageService->uploadFile($file->getRealPath(), $file->getClientOriginalName());
                return response()->json($result, 200);
            } catch (\Throwable $e) {
                return response()->json([
                    self::STATUS => 400,
                    self::MESSAGE => 'Upload failed: ' . $e->getMessage()
                ], 400);
            }
        }

        return response()->json([
            self::STATUS => 400,
            self::MESSAGE => 'No video file provided'
        ], 400);
    }

    // Stream download from remote URL directly to storage
    public function remoteUpload(Request $request)
    {
        $request->validate([
            'url' => 'required|url'
        ]);

        try {
            $result = $this->remoteUploadService->downloadAndUpload($request->input('url'));
            return response()->json($result, 200);
        } catch (\Throwable $e) {
            return response()->json([
                self::STATUS => 400,
                self::MESSAGE => 'Remote upload failed: ' . $e->getMessage()
            ], 400);
        }
    }

    // Test connection to storage service
    public function testStorage(Request $request)
    {
        $driver = $request->input('driver', 'local');
        $result = $this->storageService->testConnection($driver, $request->all());
        return response()->json($result, $result['status'] ?? 200);
    }

    // return an video from the videos disk of the storage
    public function show($filename)
    {
        $video = Storage::disk(self::VIDEOS)->get("$filename");
        $mime = Storage::disk(self::VIDEOS)->mimeType("$filename");

        return (new Response($video, 200))
            ->header('Content-Type', $mime);
    }

    public function showFromMovieName($filename)
    {
        $video = Storage::disk(self::VIDEOS)->get("$filename");
        $mime = Storage::disk(self::VIDEOS)->mimeType("$filename");

        return (new Response($video, 200))
            ->header('Content-Type', $mime);
    }
}
