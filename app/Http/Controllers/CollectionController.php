<?php

namespace App\Http\Controllers;

use App\Collection;
use App\Movie;
use App\Serie;
use App\Anime;
use App\Setting;
use App\Http\Requests\CollectionRequest;
use App\Http\Requests\GenreRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use App\Http\Requests\StoreImageRequest;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Response;
use Validator;


class CollectionController extends Controller
{


    const STATUS = "status";
    const MESSAGE = "message";
    const VIEWS = "views";


     private $settings;

    public function __construct()
    {
        $this->settings = Setting::query()->first();

    }


    public function datawebcollections()
    {
        return response()->json(Collection::orderByDesc('created_at')->get(), 200);
    }




    public function showAllCollection($collection)
    {
        $collections = Collection::select(['id',
        'name', 'poster_path', 'backdrop_path'])->orderByDesc('created_at')->get();

    return response()->json($collections, 200);

    }



     // return all movies of a genre
     public function showCollection($collection)
     {

 
         $genresMovies =
         DB::raw('(SELECT genres.name FROM genres JOIN movie_genres ON genres.id = movie_genres.genre_id WHERE movie_genres.movie_id = movies.id LIMIT 1) AS genre_name');

         $genresSeries =
         DB::raw('(SELECT genres.name FROM genres JOIN serie_genres ON genres.id = serie_genres.genre_id WHERE serie_genres.serie_id = series.id LIMIT 1) AS genre_name');

         $genresAnimes =
         DB::raw('(SELECT genres.name FROM genres JOIN anime_genres ON genres.id = anime_genres.genre_id WHERE anime_genres.anime_id = animes.id LIMIT 1) AS genre_name');

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
             'movies.title AS name',
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


            $collections = DB::table(function ($query) use ($selectMovie,$selectSerie,$selectAnime,$genresMovies,$genresSeries,$genresAnimes,$collection) {
                $query->select(array_merge(
                    $selectMovie,
                    [
                        $genresMovies,
                    ]
                ))
                ->join('movie_collections', 'movies.id', '=', 'movie_collections.movie_id')
                ->where('movie_collections.collection_id', '=', $collection)
                    ->from('movies')
                    ->where('active', '=', 1)
                    ->orderBy('movies.created_at', 'desc');

                    $query->unionAll(function ($query) use ($selectSerie,$genresSeries,$collection) {
                        $query->select(array_merge(
                            $selectSerie,
                            [
                                $genresSeries,
                            ]
                        ))
                        ->join('serie_collections', 'series.id', '=', 'serie_collections.serie_id')
                        ->where('serie_collections.collection_id', '=', $collection)
                            ->from('series')
                            ->where('active', '=', 1)
                            ->orderBy('created_at', 'desc');
                    });

                    $query->unionAll(function ($query) use ($selectAnime,$genresAnimes,$collection) {
                        $query->select(array_merge(
                            $selectAnime,
                            [
                                $genresAnimes,
                            ]
                        ))
                        ->join('anime_collections', 'animes.id', '=', 'anime_collections.anime_id')
                        ->where('anime_collections.collection_id', '=', $collection)
                            ->from('animes')
                            ->where('active', '=', 1)
                            ->orderBy('created_at', 'desc');
                    });
         })
           ->orderByDesc('created_at');

         }else {

            $collections = DB::table(function ($query) use ($selectMovie,$genresMovies,$collection) {
                $query->select(array_merge(
                    $selectMovie,
                    [
                        $genresMovies,
                    ]
                ))
                ->join('movie_collections', 'movies.id', '=', 'movie_collections.movie_id')
                ->where('movie_collections.collection_id', '=', $collection)
                    ->from('movies')
                    ->where('active', '=', 1)
                    ->orderBy('movies.created_at', 'desc');

                    $query->unionAll(function ($query) use ($selectSerie,$genresSeries,$collection) {
                        $query->select(array_merge(
                            $selectSerie,
                            [
                                $genresSeries,
                            ]
                        ))
                        ->join('serie_collections', 'series.id', '=', 'serie_collections.serie_id')
                        ->where('serie_collections.collection_id', '=', $collection)
                            ->from('series')
                            ->where('active', '=', 1)
                            ->orderBy('created_at', 'desc');
                    });
                    })
           ->orderByDesc('created_at');
         }




       return response()->json($collections->paginate(12), 200);
 
    
     }
 


     // update a genre in the database
 public function update(CollectionRequest $request, Collection $collection)
 {


        if ($collection != null) {


            $collection->fill($request->all());
            $collection->save();
            $data = [
                self::STATUS => 200,
                self::MESSAGE => 'successfully updated',
                'body' => $collection
            ];


        } else {
            $data = [
                self::STATUS => 400,
                self::MESSAGE => 'could not be updated',
            ];
        }


        return response()->json($data, $data[self::STATUS]);
    
 }



 public function destroyCollection($genre)
 {
     if ($genre != null) {
         Collection::find($genre)->delete();
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


  // create a new genre in the database
  public function store(CollectionRequest $request)
  {
      $genre = new Collection();
      $genre->fill($request->all());
      $genre->save();
 
      $data = [
          'status' => 200,
          'message' => 'successfully created',
          'body' => $genre
      ];
 
      return response()->json($data, $data['status']);
  }

}
