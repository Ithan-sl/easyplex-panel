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

    public function tmdb()
    {
        $defaultLanguages = [
            ['iso_639_1' => 'pt-br', 'english_name' => 'Portuguese (Brazil)', 'name' => 'Português (Brasil)'],
            ['iso_639_1' => 'pt', 'english_name' => 'Portuguese', 'name' => 'Português'],
            ['iso_639_1' => 'en', 'english_name' => 'English', 'name' => 'English'],
            ['iso_639_1' => 'es-MX', 'english_name' => 'Español Latino', 'name' => 'Español Latino'],
            ['iso_639_1' => 'es', 'english_name' => 'Spanish', 'name' => 'Español'],
            ['iso_639_1' => 'fr', 'english_name' => 'French', 'name' => 'Français'],
            ['iso_639_1' => 'de', 'english_name' => 'German', 'name' => 'Deutsch'],
            ['iso_639_1' => 'it', 'english_name' => 'Italian', 'name' => 'Italiano'],
            ['iso_639_1' => 'ja', 'english_name' => 'Japanese', 'name' => '日本語'],
            ['iso_639_1' => 'ko', 'english_name' => 'Korean', 'name' => '한국어'],
            ['iso_639_1' => 'zh', 'english_name' => 'Mandarin', 'name' => '中文'],
            ['iso_639_1' => 'ru', 'english_name' => 'Russian', 'name' => 'Русский'],
            ['iso_639_1' => 'ar', 'english_name' => 'Arabic', 'name' => 'العربية'],
            ['iso_639_1' => 'hi', 'english_name' => 'Hindi', 'name' => 'हिन्दी'],
            ['iso_639_1' => 'tr', 'english_name' => 'Turkish', 'name' => 'Türkçe'],
            ['iso_639_1' => 'pl', 'english_name' => 'Polish', 'name' => 'Polski'],
            ['iso_639_1' => 'nl', 'english_name' => 'Dutch', 'name' => 'Nederlands'],
            ['iso_639_1' => 'sv', 'english_name' => 'Swedish', 'name' => 'Svenska'],
            ['iso_639_1' => 'no', 'english_name' => 'Norwegian', 'name' => 'Norsk'],
            ['iso_639_1' => 'da', 'english_name' => 'Danish', 'name' => 'Dansk'],
            ['iso_639_1' => 'fi', 'english_name' => 'Finnish', 'name' => 'Suomi'],
            ['iso_639_1' => 'el', 'english_name' => 'Greek', 'name' => 'Ελληνικά'],
            ['iso_639_1' => 'he', 'english_name' => 'Hebrew', 'name' => 'עִבְרִית'],
            ['iso_639_1' => 'id', 'english_name' => 'Indonesian', 'name' => 'Bahasa indonesia'],
            ['iso_639_1' => 'th', 'english_name' => 'Thai', 'name' => 'ภาษาไทย'],
            ['iso_639_1' => 'vi', 'english_name' => 'Vietnamese', 'name' => 'Tiếng Việt'],
            ['iso_639_1' => 'cs', 'english_name' => 'Czech', 'name' => 'Český'],
            ['iso_639_1' => 'hu', 'english_name' => 'Hungarian', 'name' => 'Magyar'],
            ['iso_639_1' => 'ro', 'english_name' => 'Romanian', 'name' => 'Română'],
            ['iso_639_1' => 'uk', 'english_name' => 'Ukrainian', 'name' => 'Український'],
        ];

        $apiKey = $this->settings->tmdb_api_key ?? null;
        if (!empty($apiKey)) {
            try {
                $response = \Illuminate\Support\Facades\Http::timeout(5)->get("https://api.themoviedb.org/3/configuration/languages?api_key={$apiKey}");
                if ($response->successful()) {
                    $languages = $response->json();
                    if (is_array($languages) && !empty($languages)) {
                        array_unshift(
                            $languages,
                            ['iso_639_1' => 'pt-br', 'english_name' => 'Portuguese (Brazil)', 'name' => 'Português (Brasil)'],
                            ['iso_639_1' => 'es-MX', 'english_name' => 'Español Latino', 'name' => 'Español Latino']
                        );
                        return response()->json(array_values($languages), 200);
                    }
                }
            } catch (\Exception $e) {
                // Fallback to default list
            }
        }

        return response()->json($defaultLanguages, 200);
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
                ->distinct();


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
