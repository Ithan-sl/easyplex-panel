<?php

namespace App\Http\Controllers;


use App\Http\Requests\Request;
use App\Movie;
use App\Serie;
use App\Livetv;
use App\Anime;
use App\User;
use App\Cast;
use App\Language;
use App\Certification;
use BeyondCode\Comments\Comment;
use App\Setting;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class SearchController extends Controller
{
    

    public function index($search)
    {
        $search = '%' . str_replace(['%', '_', ' '], ['\%', '\_', '%'], $search) . '%';
        $settings = Setting::query()->first();
    
        $genreSubquery = "
            (SELECT :genre_table.name
             FROM :genre_table
             JOIN :join_table ON :genre_table.id = :join_table.:genre_id
             WHERE :join_table.:content_id = :content_table.id LIMIT 1) AS genre_name
        ";
    
        $unionQueries = [];
    
        // Movies
        $movieQuery = DB::table('movies')
            ->where(function ($query) use ($search) {
                $query->where('title', 'LIKE', $search)
                      ->orWhere('original_name', 'LIKE', $search);
            })
            ->where('active', '=', 1)
            ->select([
                'id', 'title as name', 'original_name', 'poster_path', 'backdrop_path', 'backdrop_path_tv',
                'vote_average', 'subtitle', 'overview', 'release_date', 'pinned', 'created_at', 'updated_at', 'views',
                DB::raw("'movie' AS type"),
                DB::raw(str_replace(
                    [':genre_table', ':join_table', ':genre_id', ':content_id', ':content_table'],
                    ['genres', 'movie_genres', 'genre_id', 'movie_id', 'movies'],
                    $genreSubquery
                )),
                DB::raw("CASE 
                    WHEN title LIKE '$search' OR original_name LIKE '$search' THEN 3
                    WHEN title LIKE '$search%' OR original_name LIKE '$search%' THEN 2
                    WHEN title LIKE '%$search%' OR original_name LIKE '%$search%' THEN 1
                    ELSE 0
                END AS match_score")
            ]);
        $unionQueries[] = $movieQuery;
    
        // Series
        $seriesQuery = DB::table('series')
            ->where(function ($query) use ($search) {
                $query->where('name', 'LIKE', $search)
                      ->orWhere('original_name', 'LIKE', $search);
            })
            ->where('active', '=', 1)
            ->select([
                'id', 'name', 'original_name', 'poster_path', 'backdrop_path', 'backdrop_path_tv',
                'vote_average', 'subtitle', 'overview', 'first_air_date as release_date', 'pinned', 'created_at', 'updated_at', 'views',
                DB::raw("'serie' AS type"),
                DB::raw(str_replace(
                    [':genre_table', ':join_table', ':genre_id', ':content_id', ':content_table'],
                    ['genres', 'serie_genres', 'genre_id', 'serie_id', 'series'],
                    $genreSubquery
                )),
                DB::raw("CASE 
                    WHEN name LIKE '$search' OR original_name LIKE '$search' THEN 3
                    WHEN name LIKE '$search%' OR original_name LIKE '$search%' THEN 2
                    WHEN name LIKE '%$search%' OR original_name LIKE '%$search%' THEN 1
                    ELSE 0
                END AS match_score")
            ]);
        $unionQueries[] = $seriesQuery;
    
        // Animes (if enabled)
        if ($settings->anime) {
            $animeQuery = DB::table('animes')
                ->where(function ($query) use ($search) {
                    $query->where('name', 'LIKE', $search)
                          ->orWhere('original_name', 'LIKE', $search);
                })
                ->where('active', '=', 1)
                ->select([
                    'id', 'name', 'original_name', 'poster_path', 'backdrop_path', 'backdrop_path_tv',
                    'vote_average', 'subtitle', 'overview', 'first_air_date as release_date', 'pinned', 'created_at', 'updated_at', 'views',
                    DB::raw("'anime' AS type"),
                    DB::raw(str_replace(
                        [':genre_table', ':join_table', ':genre_id', ':content_id', ':content_table'],
                        ['genres', 'anime_genres', 'genre_id', 'anime_id', 'animes'],
                        $genreSubquery
                    )),
                    DB::raw("CASE 
                        WHEN name LIKE '$search' OR original_name LIKE '$search' THEN 3
                        WHEN name LIKE '$search%' OR original_name LIKE '$search%' THEN 2
                        WHEN name LIKE '%$search%' OR original_name LIKE '%$search%' THEN 1
                        ELSE 0
                    END AS match_score")
                ]);
            $unionQueries[] = $animeQuery;
        }
    
        // LiveTV (if enabled)
        if ($settings->livetv) {
            $liveTVQuery = DB::table('livetvs')
                ->where('name', 'LIKE', $search)
                ->where('active', '=', 1)
                ->select([
                    'id', 'name', 'name as original_name', 'poster_path', 'backdrop_path', 'backdrop_path',
                    'status as vote_average', 'status as subtitle', 'overview', 'status as release_date', 'status as pinned', 'created_at', 'updated_at', 'views',
                    DB::raw("'Streaming' AS type"),
                    DB::raw(str_replace(
                        [':genre_table', ':join_table', ':genre_id', ':content_id', ':content_table'],
                        ['categories', 'livetv_genres', 'category_id', 'livetv_id', 'livetvs'],
                        $genreSubquery
                    )),
                    DB::raw("CASE 
                        WHEN name LIKE '$search' THEN 3
                        WHEN name LIKE '$search%' THEN 2
                        WHEN name LIKE '%$search%' THEN 1
                        ELSE 0
                    END AS match_score")
                ]);
            $unionQueries[] = $liveTVQuery;
        }
    
        // Combine all queries
        $query = array_shift($unionQueries);
        foreach ($unionQueries as $unionQuery) {
            $query->union($unionQuery);
        }
    
        $results = $query->orderByDesc('match_score')
            ->orderByDesc('created_at')
            ->limit(40)
            ->get();
    
        return response()->json(['search' => $results], 200);
    }


    public function searchFeatured()
    {

        $query = \Request::get('q');
    	$movies = Movie::select('*')->whereRaw("title LIKE '%" . $query . "%'")
        ->orWhereRaw("original_name LIKE '%" . $query . "%'")
        ->where('active', '=', 1)
        ->addSelect(DB::raw("'Movie' as type"))->limit(50)->get();

        $series = Serie::select('*')->whereRaw("name LIKE '%" . $query . "%'")
        ->orWhereRaw("original_name LIKE '%" . $query . "%'")->where('active', '=', 1)
        ->addSelect(DB::raw("'Serie' as type"))->limit(50)->get();


        $anime = Anime::select('*')->whereRaw("name LIKE '%" . $query . "%'")
        ->orWhereRaw("original_name LIKE '%" . $query . "%'")->where('active', '=', 1)
        ->addSelect(DB::raw("'Anime' as type"))->limit(50)->get();

        $livetv = Livetv::withOnly('genres.genre')->select('*')->whereRaw("name LIKE '%" . $query . "%'")
        ->addSelect(DB::raw("'Streaming' as type"))->limit(50)->get();

        $array = array_merge($movies->toArray(),
         $series->toArray()
         ,$anime->toArray(),$livetv->toArray());

        return response()->json(['search' => $array], 200);

    }



    
    public function searchLangs()
    {
    	$query = \Request::get('q');

        $casts = Language::select('*')->where('english_name', 'LIKE', "%$query%")
        ->orWhere('name', 'LIKE', "%$query%")->limit(50)->get();

    	return response()->json([ 'langs' => $casts ],Response::HTTP_OK);
    }


    public function searchCasts()
    {
    	$query = \Request::get('q');

        $casts = Cast::select('*')->where('name', 'LIKE', "%$query%")->limit(50)->get();

    	return response()->json([ 'casts' => $casts ],Response::HTTP_OK);
    }


    public function searchCertifications()
    {
    	$query = \Request::get('q');

        $casts = Certification::select('*')->where('country_code', 'LIKE', "%$query%")->limit(50)->paginate(12);

    	return response()->json($casts,Response::HTTP_OK);
    }


    public function searchComments()
    {
    	$query = \Request::get('q');

        $comments = Comment::select('*')->where('comment', 'LIKE', "%$query%")->limit(50)->paginate(12);

    	return response()->json([ 'comments' => $comments ],Response::HTTP_OK);
    }


    public function searchMovies()
    {
    	$query = \Request::get('q');
        $movies = Movie::select('*')->where('title', 'LIKE', "%$query%")->limit(100)->get();

    	return response()->json([ 'movies' => $movies ],Response::HTTP_OK);
    }


    public function searchSeries()
    {
    	$query = \Request::get('q');
        $movies = Serie::select('*')->where('name', 'LIKE', "%$query%")->limit(100)->get();

    	return response()->json([ 'series' => $movies ],Response::HTTP_OK);
    }


    
    public function searchAnimes()
    {
    	$query = \Request::get('q');
        $movies = Anime::select('*')->where('name', 'LIKE', "%$query%")->limit(100)->get();

    	return response()->json([ 'animes' => $movies ],Response::HTTP_OK);
    }



    public function searchStreaming()
    {
    	$query = \Request::get('q');
        $movies = Livetv::select('*')->where('name', 'LIKE', "%$query%")->limit(100)->get();

    	return response()->json([ 'streaming' => $movies ],Response::HTTP_OK);
    }

    public function searchUsers()
    {
    	$query = \Request::get('q');
        $movies = User::select('*')->where('email', 'LIKE', "%$query%")->get();

    	return response()->json([ 'users' => $movies ],Response::HTTP_OK);
    }


}