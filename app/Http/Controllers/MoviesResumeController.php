<?php

namespace App\Http\Controllers;

use App\Resume;
use Illuminate\Http\Request;


class MoviesResumeController extends Controller
{


    public function data()
    {


        return response()->json(Resume::all(), 200);

    }


    public function sendResume(Request $request)
    {
        $this->validate($request, [
            'user_resume_id' => 'required',
            'tmdb' => 'required',
            'resumeWindow' => 'required',
            'resumePosition' => 'required',
            'movieDuration' => 'required',
            'deviceId' => 'required',
        ]);
    
        $data = [
            'user_resume_id' => request('user_resume_id'),
            'tmdb' => request('tmdb'),
            'resumeWindow' => request('resumeWindow'),
            'resumePosition' => request('resumePosition'),
            'movieDuration' => request('movieDuration'),
            'deviceId' => request('deviceId'),
            'profileId' => request('profileId'),
        ];
    
        // Find an existing resume by deviceId or create a new one
        Resume::updateOrCreate([
            'deviceId' => $data['deviceId'],
            'profileId' => $data['profileId'], // Add the profile ID condition
        ], $data);
    
        $response = [
            'status' => 200,
            'message' => 'Resume info updated or created successfully',
        ];
    
        return response()->json($response, 200);
    }
    


    public function getUserProfileResumeById($movie,$profileId)
    {


        $movie = Resume::where('id', '=', $movie)->where('profileId', '=', $profileId)->orderByDesc('created_at')->first();
        
        return response()->json($movie, 200);


    }

    public function show($movie)
    {


        $movie = Resume::where('tmdb', '=', $movie)->orWhere('id', '=', $movie)->orderByDesc('created_at')->first();
        
        return response()->json($movie, 200);


    }


    public function destroy(Resume $resume)


    {


        if ($resume != null) {
            $resume->delete();

            $data = ['status' => 200, 'message' => 'successfully removed',];
        } else {
            $data = ['status' => 400, 'message' => 'could not be deleted',];
        }

        return response()->json($data, $data['status']);
    }
}
