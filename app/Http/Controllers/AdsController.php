<?php

namespace App\Http\Controllers;

use App\Ads;
use App\Http\Requests\AdsRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdsController extends Controller
{




    const STATUS = "status";
    const MESSAGE = "message";
    const VIEWS = "views";


    public function data()
    {


     return response()->json(Ads::all(), 200);


    }


    public function ads(Request $request)
{

    $settings=Ads::inRandomOrder()->first();

        return response()->json($settings, 200);
}



public function vast($id)
{
    $vast = Ads::query()->where('id', '=', $id)->first();

    // Get the video URL, duration, and click-through URL from the fetched data
    $videoURL = $vast->link;
    $videoDuration = $vast->duration;
    $videoskipoffset = $vast->skipoffset;
    $clickThroughURL = $vast->clickThroughUrl;
    $title = $vast->title;

    // Your XML data template
    $xmlData = '<?xml version="1.0" encoding="UTF-8"?>
<VAST version="4.0">
    <Ad id="Test">
        <InLine>
            <AdSystem version="3.0">'.$title.'</AdSystem>
            <AdTitle>'.$title.'</AdTitle>
            <Creatives>
                <Creative sequence="1" AdID="7E7F3BE9-9717-4289-9094-B77EEDC4086A">
                    <Linear skipoffset="00:00:0'.$videoskipoffset.'">
                        <Duration>00:00:'.$videoDuration.'</Duration>
                        <MediaFiles>
                            <MediaFile id="video" delivery="progressive" type="video/mp4">'.$videoURL.'</MediaFile>
                        </MediaFiles>
                        <VideoClicks>
                            <ClickThrough>'.$clickThroughURL.'</ClickThrough>
                        </VideoClicks>
                    </Linear>
                </Creative>
            </Creatives>
        </InLine>
    </Ad>
</VAST>';

    echo $xmlData;
}




    public function store(AdsRequest $request)
    {


        if (isset($request->ads)) {

            $ads = new Ads();
            $ads->fill($request->ads);
            $ads->save();

            
            $data = [
                self::STATUS => 200,
                self::MESSAGE => 'successfully created',
                'body' => $ads
            ];
        } else {
            $data = [
                self::STATUS => 400,
                self::MESSAGE => 'could not be created',
            ];

        }

        return response()->json($data, $data[self::STATUS]);
    }


    public function destroy(Ads $ads)
    {
        if ($ads != null) {
            $ads->delete();
            $data = [
                self::STATUS => 200,
                self::MESSAGE => 'successfully deleted',
            ];
        } else {
            $data = [
                self::STATUS => 400,
                self::MESSAGE => 'could not be deleted',
            ];
        }

        return response()->json($data, $data[self::STATUS]);
    }


    public function update(AdsRequest $request, Ads $ads)


    {

        if ($ads != null) {


            $ads->fill($request->ads);
            $ads->save();
            $data = [
                self::STATUS => 200,
                self::MESSAGE => 'successfully updated',
                'body' => $ads
            ];


        } else {
            $data = [
                self::STATUS => 400,
                self::MESSAGE => 'could not be updated',
            ];
        }


        return response()->json($data, $data[self::STATUS]);
    }

}
