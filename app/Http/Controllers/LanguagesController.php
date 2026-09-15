<?php

namespace App\Http\Controllers;

use App\MovieSpokenLanguage;
use App\SerieSpokenLanguage;
use App\AnimeSpokenLanguage;
use App\Language;
use App\MovieCast;
use App\Movie;
use App\Serie;
use App\Anime;
use App\Setting;
use App\Http\Requests\LanguageRequest;
use App\Http\Requests\LanguageUpdateRequest;
use App\Http\Requests\GenreRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use App\Http\Requests\StoreImageRequest;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Response;
use Validator;


class LanguagesController extends Controller
{


    const STATUS = "status";
    const MESSAGE = "message";
    const VIEWS = "views";


     private $settings;

    public function __construct()
    {
        $this->settings = Setting::query()->first();

    }



    public function data()
{
    return response()->json(Language::query()->orderByDesc('featured')->paginate(12), 200);
}



    public function datamobile()
    {
        return response()->json(Language::query()->get(), 200);
    }

    public function dataLibrary()
    {
        return response()->json(Language::query()->where('featured', 1)->get(), 200);
    }


    public function fetch(Request $request)
    {


        $languagesData = $request->all();
        foreach ($languagesData as $languageData) {
            if (!Language::where('iso_639_1', $languageData['iso_639_1'])->exists()) {
                Language::create([
                    'iso_639_1' => $languageData['iso_639_1'],
                    'english_name' => $languageData['english_name'],
                    'name' => $languageData['name'] ?? $languageData['english_name'],
                    'logo_path' => $request->root() . '/api/languages/image/avatar_default.png',
                    'featured' => 0,
                ]);
            }
        }
        

        return response()->json(['message' => 'Data inserted successfully']);
    }



        public function update($id, LanguageUpdateRequest $request)
    {
        $languageData = $request->validate([
            'iso_639_1' => 'required|string|max:2',
            'english_name' => 'required|string|max:255',
            'name' => 'required|string|max:255',
            'logo_path' => 'string',
            'featured' => 'boolean',
        ]);

        // Find the language by ID
        $language = Language::find($id);

        // Update the language if found, or create a new one if not found
        $language->updateOrCreate(['id' => $id], $languageData);

        return response()->json($language, 200);
    }


    
 public function destroy($genre)
 {
     if ($genre != null) {
        Language::find($genre)->delete();
         $data = [
             'status' => 200,
             'message' => 'successfully deleted'
         ];
     } else {
         $data = [
             'status' => 400,
             'message' => 'could not be deleted'
         ];
     }

     return response()->json($data, $data['status']);
 }




 public function destroyMovieLanguage($lang)
 {

     if ($lang != null) {

        MovieSpokenLanguage::find($lang)->delete();

         $data = ['status' => 200, 'message' => 'successfully deleted',];
     } else {
         $data = ['status' => 400, 'message' => 'could not be deleted',];
     }

     return response()->json($data, 200);

 }


 public function destroySerieLanguage($lang)
 {

     if ($lang != null) {

        SerieSpokenLanguage::find($lang)->delete();

         $data = ['status' => 200, 'message' => 'successfully deleted',];
     } else {
         $data = ['status' => 400, 'message' => 'could not be deleted',];
     }

     return response()->json($data, 200);

 }



 public function destroyAnimeLanguage($lang)
 {

     if ($lang != null) {

        AnimeSpokenLanguage::find($lang)->delete();

         $data = ['status' => 200, 'message' => 'successfully deleted',];
     } else {
         $data = ['status' => 400, 'message' => 'could not be deleted',];
     }

     return response()->json($data, 200);

 }


 public function storeImg(StoreImageRequest $request)
 {
     if ($request->hasFile('image')) {
         $filename = Storage::disk('languages')->put('', $request->image);
         $data = ['status' => 200, 'image_path' => $request->root() . '/api/languages/image/' . $filename, 'message' => 'successfully uploaded'];
     } else {
         $data = ['status' => 400, 'message' => 'could not be uploaded'];
     }

     return response()->json($data, $data['status']);
 }



 public function getImg($filename)
 {

     $image = Storage::disk('languages')->get($filename);

     $mime = Storage::disk('languages')->mimeType($filename);

     return (new Response($image, 200))->header('Content-Type', $mime);
 }




 public function showNetworks($network)
    {

    
        $selectSerie = [
            'series.id', 'series.name', 'poster_path', 'backdrop_path',
                        'backdrop_path_tv', 'vote_average', 'subtitle', 'overview', 'first_air_date AS release_date',
                        'pinned', 'series.created_at','series.updated_at', 'views', DB::raw("'serie' AS type")
        ];


        $selectAnime = [
            'animes.id', 'animes.name', 'poster_path', 'backdrop_path',
                    'backdrop_path_tv', 'vote_average', 'subtitle', 'overview', 'first_air_date AS release_date', 
                    'pinned', 'animes.created_at','animes.updated_at','views', DB::raw("'anime' AS type")
        ];


        $selectMovie = [
            'movies.id',
            'title AS name',
            'poster_path',
            'backdrop_path',
            'backdrop_path_tv',
            'vote_average',
            'subtitle',
            'overview',
            'release_date',
            'pinned',
            'movies.created_at',
            'movies.updated_at',
            'views',
            DB::raw("'movie' AS type")
        ];


        if ($this->settings->anime) {



            $latest = DB::table(function ($query) use ($selectMovie, $selectSerie, $selectAnime, $network) {
                $query->select(array_merge(
                    $selectMovie,
                ))
                ->join('movie_spoken_languages', 'movies.id', '=', 'movie_spoken_languages.movie_id')
                ->where('movie_spoken_languages.iso_639_1', '=', $network)
                    ->from('movies')
                    ->where('active', '=', 1)
                    ->orderBy('movies.created_at', 'desc');
        
                    $query->unionAll(function ($query) use ($selectSerie,$network) {
                        $query->select(array_merge(
                            $selectSerie,
                        ))
                        ->join('serie_spoken_languages', 'series.id', '=', 'serie_spoken_languages.serie_id')
                ->where('serie_spoken_languages.iso_639_1', '=', $network)
                            ->from('series')
                            ->where('active', '=', 1)
                            ->orderBy('created_at', 'desc');
                    });


                    $query->unionAll(function ($query) use ($selectAnime,$network) {
                        $query->select(array_merge(
                            $selectAnime,
                        ))
                        ->join('anime_spoken_languages', 'animes.id', '=', 'anime_spoken_languages.anime_id')
                        ->where('anime_spoken_languages.iso_639_1', '=', $network)
                            ->from('animes')
                            ->where('active', '=', 1)
                            ->orderBy('created_at', 'desc');
                    });
            })
                ->orderByDesc('created_at');


        }else {


            $latest = DB::table(function ($query) use ($selectMovie, $selectSerie, $selectAnime, $network) {
                $query->select(array_merge(
                    $selectMovie,
                ))
                ->join('movie_spoken_languages', 'movies.id', '=', 'movie_spoken_languages.movie_id')
                ->where('movie_spoken_languages.iso_639_1', '=', $network)
                    ->from('movies')
                    ->where('active', '=', 1)
                    ->orderBy('movies.created_at', 'desc');
        
                    $query->unionAll(function ($query) use ($selectSerie,$network) {
                        $query->select(array_merge(
                            $selectSerie,
                        ))
                        ->join('serie_spoken_languages', 'series.id', '=', 'serie_spoken_languages.serie_id')
                ->where('serie_spoken_languages.iso_639_1', '=', $network)
                            ->from('series')
                            ->where('active', '=', 1)
                            ->orderBy('created_at', 'desc');
                    });
            })
                ->orderByDesc('created_at');

        }
    
   
    
    return response()->json($latest->paginate(12), 200);
    

    }



    public function showLangsByNames($network)
    {

    
        $selectSerie = [
            'series.id', 'series.name', 'poster_path', 'backdrop_path',
                        'backdrop_path_tv', 'vote_average', 'subtitle', 'overview', 'first_air_date AS release_date',
                        'pinned', 'series.created_at','series.updated_at', 'views', DB::raw("'serie' AS type")
        ];


        $selectAnime = [
            'animes.id', 'animes.name', 'poster_path', 'backdrop_path',
                    'backdrop_path_tv', 'vote_average', 'subtitle', 'overview', 'first_air_date AS release_date', 
                    'pinned', 'animes.created_at','animes.updated_at','views', DB::raw("'anime' AS type")
        ];


        $selectMovie = [
            'movies.id',
            'title AS name',
            'poster_path',
            'backdrop_path',
            'backdrop_path_tv',
            'vote_average',
            'subtitle',
            'overview',
            'release_date',
            'pinned',
            'movies.created_at',
            'movies.updated_at',
            'views',
            DB::raw("'movie' AS type")
        ];


        if ($this->settings->anime) {



            $latest = DB::table(function ($query) use ($selectMovie, $selectSerie, $selectAnime, $network) {
                $query->select(array_merge(
                    $selectMovie,
                ))
                ->join('movie_spoken_languages', 'movies.id', '=', 'movie_spoken_languages.movie_id')
                ->where('movie_spoken_languages.name', '=', $network)
                    ->from('movies')
                    ->where('active', '=', 1)
                    ->orderBy('movies.created_at', 'desc');
        
                    $query->unionAll(function ($query) use ($selectSerie,$network) {
                        $query->select(array_merge(
                            $selectSerie,
                        ))
                        ->join('serie_spoken_languages', 'series.id', '=', 'serie_spoken_languages.serie_id')
                ->where('serie_spoken_languages.name', '=', $network)
                            ->from('series')
                            ->where('active', '=', 1)
                            ->orderBy('created_at', 'desc');
                    });


                    $query->unionAll(function ($query) use ($selectAnime,$network) {
                        $query->select(array_merge(
                            $selectAnime,
                        ))
                        ->join('anime_spoken_languages', 'animes.id', '=', 'anime_spoken_languages.anime_id')
                        ->where('anime_spoken_languages.name', '=', $network)
                            ->from('animes')
                            ->where('active', '=', 1)
                            ->orderBy('created_at', 'desc');
                    });
            })
                ->orderByDesc('created_at')
                ->groupBy('id');


        }else {


            $latest = DB::table(function ($query) use ($selectMovie, $selectSerie, $selectAnime, $network) {
                $query->select(array_merge(
                    $selectMovie,
                ))
                ->join('movie_spoken_languages', 'movies.id', '=', 'movie_spoken_languages.movie_id')
                ->where('movie_spoken_languages.name', '=', $network)
                    ->from('movies')
                    ->where('active', '=', 1)
                    ->orderBy('movies.created_at', 'desc');
        
                    $query->unionAll(function ($query) use ($selectSerie,$network) {
                        $query->select(array_merge(
                            $selectSerie,
                        ))
                        ->join('serie_spoken_languages', 'series.id', '=', 'serie_spoken_languages.serie_id')
                ->where('serie_spoken_languages.name', '=', $network)
                            ->from('series')
                            ->where('active', '=', 1)
                            ->orderBy('created_at', 'desc');
                    });
            })
                ->orderByDesc('created_at');

        }
    
   
    
    return response()->json($latest->paginate(12), 200);
    

    }
}
