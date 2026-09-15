<?php

namespace App\Http\Controllers;

use FFMpeg;
use FFMpeg\Format\Video\X264;
use App\Jobs\VideoConversion;
use ProtoneMedia\LaravelFFMpeg\Filters\WatermarkFactory;
use App\Http\Requests\VideoRequest;
use App\Http\Requests\StreamingVideoRequest;
use App\Setting;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use FFMpeg\Filters\Video\VideoFilters;


class VideoController extends Controller
{



    const STATUS = "status";
    const MESSAGE = "message";
    const VIDEOS = "videos";


    // save a new video in AWS S3 or the videos disk of the storage
    public function store(VideoRequest $request)
    {
        if ($request->hasFile('video')) {

            $settings = Setting::first();

            if ($settings->aws_s3_storage) {
                if (
                    $settings->aws_access_key_id != null &&
                    $settings->aws_secret_access_key != null &&
                    $settings->aws_default_region != null &&
                    $settings->aws_bucket != null
                ) {
                    config(['filesystems.disks.s3.key' => $settings->aws_access_key_id]);
                    config(['filesystems.disks.s3.secret' => $settings->aws_secret_access_key]);
                    config(['filesystems.disks.s3.region' => $settings->aws_default_region]);
                    config(['filesystems.disks.s3.bucket' => $settings->aws_bucket]);

                    $filename = Storage::disk('s3')->put('', $request->video);
                    $url = Storage::disk('s3')->url($filename);

                    $data = [
                        self::STATUS => 200,
                        'video_path' => $url,
                        'server' => config('app.name', 'AWS'),
                        self::MESSAGE => 'successfully uploaded'
                    ];
                } else {
                    $data = [
                        self::STATUS => 400,
                        self::MESSAGE => 'could not be uploaded'
                    ];
                }
            } else if($settings->wasabi_storage) {
                if (
                    $settings->wasabi_access_key_id != null &&
                    $settings->wasabi_secret_access_key != null &&
                    $settings->wasabi_default_region != null &&
                    $settings->wasabi_bucket != null
                ) {
                    config(['filesystems.disks.wasabi.key' => $settings->wasabi_access_key_id]);
                    config(['filesystems.disks.wasabi.secret' => $settings->wasabi_secret_access_key]);
                    config(['filesystems.disks.wasabi.region' => $settings->wasabi_default_region]);
                    config(['filesystems.disks.wasabi.bucket' => $settings->wasabi_bucket]);

                    $filename = Storage::disk('wasabi')->put('', $request->video);
                    $url = Storage::disk('wasabi')->url($filename);

                    $data = [
                        self::STATUS => 200,
                        'video_path' => $url,
                        'server' => config('app.name', $settings->app_name),
                        self::MESSAGE => 'successfully uploaded'
                    ];
                } else {
                    $data = [
                        self::STATUS => 400,
                        self::MESSAGE => 'could not be uploaded'
                    ];
                }
            } else if($settings->ffmpeg) {


                $config = [
                    'ffmpeg.binaries'  => '/usr/bin/ffmpeg',
                    'ffprobe.binaries' => '/usr/bin/ffprobe',
                    'timeout'          => 3600, // The timeout for the underlying process
                    'ffmpeg.threads'   => 12,   // The number of threads that FFmpeg should use
                ];
                
                $log = new Logger('FFmpeg_Streaming');
                $log->pushHandler(new StreamHandler('/var/log/ffmpeg-streaming.log')); // path to log file
                    
                $ffmpeg = Streaming\FFMpeg::create($config, $log);
        
                $r_144p  = (new Representation)->setKiloBitrate(95)->setResize(256, 144);
                $r_240p  = (new Representation)->setKiloBitrate(150)->setResize(426, 240);
                $r_360p  = (new Representation)->setKiloBitrate(276)->setResize(640, 360);
                $r_480p  = (new Representation)->setKiloBitrate(750)->setResize(854, 480);
                $r_720p  = (new Representation)->setKiloBitrate(2048)->setResize(1280, 720);
                $r_1080p = (new Representation)->setKiloBitrate(4096)->setResize(1920, 1080);
                $r_2k    = (new Representation)->setKiloBitrate(6144)->setResize(2560, 1440);
                $r_4k    = (new Representation)->setKiloBitrate(17408)->setResize(3840, 2160);

                $video->dash()
                ->x264()
                ->addRepresentations([$r_144p, $r_240p, $r_360p, $r_480p, $r_720p, $r_1080p, $r_2k, $r_4k])
                ->save(Storage::disk('videos'));

            }else {


                $filename = Storage::disk('videos')->put('', $request->video);
                $data = [
                    self::STATUS => 200,
                    'video_path' => $request->root() . '/api/video/' . $filename,
                    'server' => config('app.name', 'EASYPLEX'),
                    self::MESSAGE => 'successfully uploaded'
                ];

            }
        } else {
            $data = [
                self::STATUS => 400,
                self::MESSAGE => 'could not be uploaded'
            ];
        }

        return response()->json($data, $data['status']);
    }





    public function Streamingstore(StreamingVideoRequest $request)
    {
        if ($request->hasFile('video')) {

            $settings = Setting::first();

            if ($settings->aws_s3_storage) {
                if (
                    $settings->aws_access_key_id != null &&
                    $settings->aws_secret_access_key != null &&
                    $settings->aws_default_region != null &&
                    $settings->aws_bucket != null
                ) {
                    config(['filesystems.disks.s3.key' => $settings->aws_access_key_id]);
                    config(['filesystems.disks.s3.secret' => $settings->aws_secret_access_key]);
                    config(['filesystems.disks.s3.region' => $settings->aws_default_region]);
                    config(['filesystems.disks.s3.bucket' => $settings->aws_bucket]);

                    $filename = Storage::disk('s3')->put('', $request->video);
                    $url = Storage::disk('s3')->url($filename);

                    $data = [
                        self::STATUS => 200,
                        'video_path' => $url,
                        'server' => config('app.name', 'AWS'),
                        self::MESSAGE => 'successfully uploaded'
                    ];
                } else {
                    $data = [
                        self::STATUS => 400,
                        self::MESSAGE => 'could not be uploaded'
                    ];
                }
            } else if($settings->wasabi_storage) {
                if (
                    $settings->wasabi_access_key_id != null &&
                    $settings->wasabi_secret_access_key != null &&
                    $settings->wasabi_default_region != null &&
                    $settings->wasabi_bucket != null
                ) {
                    config(['filesystems.disks.wasabi.key' => $settings->wasabi_access_key_id]);
                    config(['filesystems.disks.wasabi.secret' => $settings->wasabi_secret_access_key]);
                    config(['filesystems.disks.wasabi.region' => $settings->wasabi_default_region]);
                    config(['filesystems.disks.wasabi.bucket' => $settings->wasabi_bucket]);

                    $filename = Storage::disk('wasabi')->put('', $request->video);
                    $url = Storage::disk('wasabi')->url($filename);

                    $data = [
                        self::STATUS => 200,
                        'video_path' => $url,
                        'server' => config('app.name', $settings->app_name),
                        self::MESSAGE => 'successfully uploaded'
                    ];
                } else {
                    $data = [
                        self::STATUS => 400,
                        self::MESSAGE => 'could not be uploaded'
                    ];
                }
            } else {


                $filename = Storage::disk('videos')->put('', $request->video);
                $data = [
                    self::STATUS => 200,
                    'video_path' => $request->root() . '/api/video/' . $filename,
                    'server' => config('app.name', 'EASYPLEX'),
                    self::MESSAGE => 'successfully uploaded'
                ];

                

            }
        } else {
            $data = [
                self::STATUS => 400,
                self::MESSAGE => 'could not be uploaded'
            ];
        }

        return response()->json($data, $data['status']);
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
