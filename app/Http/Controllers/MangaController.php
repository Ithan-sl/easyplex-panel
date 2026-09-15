<?php

namespace App\Http\Controllers;



use App\Language;
use App\Embed;
use App\AnimeEpisode;
use App\AnimeCast;
use App\Genre;
use App\Cast;
use App\Network;
use App\AnimeCertification;
use App\Certification;
use App\Http\Requests\MangaStoreRequest;
use App\Http\Requests\MangaUpdateRequest;
use App\Http\Requests\StoreImageRequest;
use App\Jobs\SendNotification;
use App\MangaChapter;
use App\Manga;
use App\MangaPdf;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MangaController extends Controller
{


    public function __construct()
    {
        $this->middleware('doNotCacheResponse', ['only' => ['moviecomment','addcomment']]);
    }



// returns all animes except children animes, for api.
public function index()
{
    $anime = Anime::whereDoesntHave('genres', function ($genre) {
        $genre->where('genre_id', '=', 10762);
    })->orderByDesc('id')->paginate(12);

    return response()->json($anime, 200);

}



public function moviecomment($id)
{

    $movie = Anime::query()
    ->with(['comments' => function ($query) {
        $query->orderByDesc('created_at');
    }])
    ->where('id', '=', $id)
    ->first();

    return response()->json(['comments' => $movie->comments], 200);

}



public function addcomment(Request $request)
{


    $user = Auth::user();


    $this->validate($request, [
        'comments_message' => 'required',
        'movie_id' => 'required'
    ]);

    $movie = Anime::where('id', '=', $request->movie_id)->first();

    $comment = $movie->commentAsUser($user, $request->comments_message);

    return response()->json($comment, 200);

}

public function addtofav($id,Request $request)
{

    $serie = Anime::where('id', '=', $id)->first()->addFavorite($request->user()->id);

    return response()->json("Success", 200);

}



public function removefromfav($id,Request $request)
{

    $movie = Anime::where('id', '=', $id)->first()->removeFavorite($request->user()->id);

    return response()->json("Added", 200);

}


public function isMovieFavorite($id,Request $request)
{

    $movie = Anime::where('id', '=', $id)->first();

    if($movie->isFavorited($request->user()->id)) {

        $data = ['status' => 200, 'status' => 1,];

    }else {

        $data = ['status' => 400, 'status' => 0,];
    }

    return response()->json($data, 200);
}




// returns all animes for admin panel
public function data()
{


    return response()->json(
        Anime::with(['seasons' => function ($query) {
            $query->orderBy('season_number');
        }, 'seasons.episodes.videos', 'genres', 'casters', 'networks'])
        ->orderByDesc('created_at')
        ->paginate(6), 
        200
    );

}

// returns a specific anime
public function show($id)
{
    // 1. Fetch Anime with Eager Loading & Optimization:
    $anime = Anime::with([
        'casters.cast' => function ($query) {
            $query->select('id', 'name', 'original_name', 'profile_path','character');
        }
    ])
    ->where('id', $id)
    ->first();

    // 2. Handle Missing Anime:
    if (!$anime) {
        return response()->json(['message' => 'Anime not found'], 404);
    }

    // 3. Exclude Unnecessary Data:
    $anime->makeHidden(['casters', 'networks']);

    // 4. Increment Views:
    $anime->increment('views');

    // 5. Return JSON Response:
    return response()->json($anime);
}



// create a new anime in the database
public function store(AnimeStoreRequest $request)
{
    $anime = new Anime();
    $anime->fill($request->anime);
    $anime->save();


    $this->onSaveAnimeGenre($request,$anime);
    $this->onSaveAnimeSeasons($request,$anime);
    $this->onSaveAnimeCasters($request,$anime);
    $this->onSaveAnimeNetworks($request,$anime);
    $this->onStoreAnimeLanguages($request,$anime);
    $this->onStoreAnimeCollections($request,$anime);
    $this->onStoreAnimeCertifications($request, $anime);

    if ($request->notification) {
        $this->dispatch(new SendNotification($anime));
    }

    $data = [
        'status' => 200,
        'message' => 'successfully created',
        'body' => $anime->load('seasons.episodes.videos')
    ];

    return response()->json($data, $data['status']);
}



        public function onStoreAnimeCertifications($request, $anime)
        {



            if ($request->anime['certifications']) {
                foreach ($request->anime['certifications'] as $certification) {
                    $find = Certification::find($certification['id']);
                    if ($find == null) {
                        $find = new Certification();
                        $find->fill($certification);
                        $find->save();
                    }
                    $movieGenre = new AnimeCertification();
                    $movieGenre->certification_id = $certification['id'];
                    $movieGenre->country_code = $certification['country_code'];
                    $movieGenre->certification = $certification['certification'];
                    $movieGenre->meaning = $certification['meaning'];
                    $movieGenre->anime_id = $anime->id;
                    $movieGenre->save();
                }
                }
}

public function onStoreAnimeCollections($request, $anime) {
    if ($request->anime['belongs_to_collection']) {
        $network = $request->anime['belongs_to_collection'];

        // Extract relevant properties from $network
        $id = $network['id'];
        $name = $network['name'];
        $poster_path = $network['poster_path'];
        $backdrop_path = $network['backdrop_path'];

        // Ensure $networkData is an array
        $networkData = [
            'name' => $name,
            'poster_path' => $poster_path,
            'backdrop_path' => $backdrop_path,
        ];

        // Using Eloquent's updateOrCreate to update or create a record
        Collection::updateOrCreate(['id' => $id], $networkData);

        $serieNetwork = new AnimeCollection();
        $serieNetwork->collection_id = $id;
        $serieNetwork->name = $name;
        $serieNetwork->anime_id = $anime->id;
        $serieNetwork->save();
    }
}

public function onStoreAnimeLanguages($request,$anime) {

    if ($request->anime['spoken_languages']) {
        foreach ($request->anime['spoken_languages'] as $network) {
            $iso_639_1 = $network['iso_639_1'];
            $name = $network['name'] ?? $network['english_name'] ;
            Language::updateOrCreate(['iso_639_1' => $iso_639_1], $network);
            
            AnimeSpokenLanguage::updateOrCreate([
                'name' => $name,
                'iso_639_1' => $iso_639_1,
                'anime_id' => $anime->id
            ]);
        }
    }
    
    
}

public function onSaveAnimeNetworks($request,$anime) {

    if ($request->anime['networks']) {
        foreach ($request->anime['networks'] as $network) {
            $find = Network::find($network['id']);
            if ($find == null) {
                $find = new Network();
                $find->fill($network);
                $find->save();
            }
            $serieNetwork = new AnimeNetwork();
            $serieNetwork->network_id = $network['id'];
            $serieNetwork->anime_id = $anime->id;
            $serieNetwork->save();
        }
    }

}

public function onSaveAnimeCasters($request,$anime) {

    if ($request->anime['casterslist']) {
        foreach ($request->anime['casterslist'] as $cast) {
            $find = Cast::find($cast['id']);
            if ($find == null) {
                $find = new Cast();
                $find->fill($cast);
                $find->save();
            }
            $movieGenre = new AnimeCast();
            $movieGenre->cast_id = $cast['id'];
            $movieGenre->anime_id = $anime->id;
            $movieGenre->save();
        }
    }

}


public function onSaveAnimeGenre($request,$anime) {

    if ($request->anime['genres']) {
        foreach ($request->anime['genres'] as $genre) {
            $find = Genre::find($genre['id']);
            if ($find == null) {
                $find = new Genre();
                $find->fill($genre);
                $find->save();
            }
            $animeGenre = new AnimeGenre();
            $animeGenre->genre_id = $genre['id'];
            $animeGenre->anime_id = $anime->id;
            $animeGenre->save();
        }
    }

}


public function onSaveAnimeSeasons($request , $anime){

    if ($request->anime['seasons']) {
        foreach ($request->anime['seasons'] as $reqSeason) {
            $season = new AnimeSeason();
            $season->fill($reqSeason);
            $season->anime_id = $anime->id;
            $season->save();

            $this->onSaveEpisodes($request,$reqSeason,$season);


        }
    }

}


public function onSaveEpisodes($request, $reqSeason,$season) {

    if ($reqSeason['episodes']) {
        foreach ($reqSeason['episodes'] as $reqEpisode) {
            $episode = new AnimeEpisode();
            $episode->fill($reqEpisode);
            $episode->anime_season_id = $season->id;
            $episode->save();


            if (isset($reqEpisode['videos'])) {
                foreach ($reqEpisode['videos'] as $reqVideo) {
                    $video = AnimeVideo::find($reqVideo['id'] ?? 0) ?? new AnimeVideo();
                    $video->fill($reqVideo);
                    $video->anime_episode_id = $episode->id;
                    $video->save();
                }
            }

            $this->onSaveEpisodeSubstitle($request,$reqEpisode,$episode);
            $this->onSaveEpisodeDownload($request,$reqEpisode,$episode);

        }
    }


}


public function onSaveEpisodeDownload($request,$reqEpisode,$episode) {

    if (isset($reqEpisode['downloads'])) {
        foreach ($reqEpisode['downloads'] as $reqVideo) {
            $video = AnimeDownload::find($reqVideo['id'] ?? 0) ?? new AnimeDownload();
            $video->fill($reqVideo);
            $video->anime_episode_id = $episode->id;
            $video->save();
        }
    }

}


public function onSaveEpisodeSubstitle($request,$reqEpisode,$episode) {


    if (isset($reqEpisode['substitles'])) {
               foreach ($reqEpisode['substitles'] as $reqVideo) {
                   $video = new AnimeSubstitle();
                   $video->fill($reqVideo);
                   $video->anime_episode_id = $episode->id;
                   $video->save();
               }
           }
}



// update a anime in the database
public function update(AnimeUpdateRequest $request, Anime $anime)
{

    $anime->fill($request->anime);
    $anime->save();

    $this->onUpdateAnimeGenre($request,$anime);
    $this->onUpdateAnimeSeasons($request,$anime);
    $this->onUpdateAnimeCasts($request,$anime);
    $this->onUpdateAnimeNetwork($request,$anime);
    $this->onUpdateAnimeLanguage($request,$anime);
    $this->onUpdateAnimeCollection($request,$anime);
    $this->onUpdatAnimeCertification($request, $anime);

    $data = [
        'status' => 200,
        'message' => 'successfully updated',
        'body' => "Success"
    ];

    return response()->json($data, $data['status']);
}



public function onUpdatAnimeCertification($request, $anime)
    {
        if ($request->anime['certifications']) {

            foreach ($request->anime['certifications'] as $network) {
                if (!isset($network['certification_id'])) {
                    $find = Certification::query()->find($network['id']) ?? new Certification();
                    $find->fill($network);
                    $find->save();
                    $movieNetwork = AnimeCertification::query()->where('anime_id', $anime->id)
                        ->where('certification_id', $network['id'])->get();
                    if (count($movieNetwork) < 1) {
                        $movieNetwork = new AnimeCertification();
                        $movieNetwork->certification_id = $network['id'];
                        $movieNetwork->country_code = $network['country_code'];
                        $movieNetwork->certification = $network['certification'];
                        $movieNetwork->meaning = $network['meaning'];
                        $movieNetwork->anime_id = $anime->id;
                        $movieNetwork->save();
                    }
                }

            }

        }
    }


public function onUpdateAnimeCollection($request, $anime) {
    if ($request->anime['belongs_to_collection']) {

        foreach ($request->anime['belongs_to_collection'] as $network) {
            if (!isset($network['collection_id'])) {
                $find = Collection::query()->find($network['id']) ?? new Collection();
                $find->fill($network);
                $find->save();
                $movieNetwork = AnimeCollection::query()->where('anime_id', $anime->id)
                ->where('collection_id', $network['id'])->get();
                if (count($movieNetwork) < 1) {
                    $movieNetwork = new AnimeCollection();
                    $movieNetwork->collection_id = $network['id'];
                    $movieNetwork->name = $network['name'];
                    $movieNetwork->anime_id = $anime->id;
                    $movieNetwork->save();
                }
            }

        }

   
    }
}


public function onUpdateAnimeLanguage($request,$anime) {

    if ($request->anime['spoken_languages']) {
        foreach ($request->anime['spoken_languages'] as $network) {
            $iso_639_1 = $network['iso_639_1'];
            $name = $network['name'] ?? $network['english_name'] ;
            Language::updateOrCreate(['iso_639_1' => $iso_639_1], $network);
            
            AnimeSpokenLanguage::updateOrCreate([
                'name' => $name,
                'iso_639_1' => $iso_639_1,
                'anime_id' => $anime->id
            ]);
        }
    }
    

}



public function onUpdateAnimeNetwork ($request,$anime) {

    if ($request->anime['networks']) {
        foreach ($request->anime['networks'] as $netwok) {
            if (!isset($netwok['network_id'])) {
                $find = Network::find($netwok['id']) ?? new Network();
                $find->fill($netwok);
                $find->save();
                $serieNetwork = AnimeNetwork::where('anime_id', $anime->id)->where('network_id', $netwok['id'])->get();
                if (count($serieNetwork) < 1) {
                    $serieNetwork = new AnimeNetwork();
                    $serieNetwork->network_id = $netwok['id'];
                    $serieNetwork->anime_id = $anime->id;
                    $serieNetwork->save();
                }
            }
        }
    }

}


public function onUpdateAnimeCasts ($request,$anime) {


    if ($request->anime['casterslist']) {
        foreach ($request->anime['casterslist'] as $genre) {

                $find = Cast::find($genre['id'] ?? 0) ?? new Cast();
                $find->fill($genre);
                $find->save();
                $movieGenre = AnimeCast::where('anime_id', $anime->id)
                    ->where('cast_id', $genre['id'])->get();

                if (count($movieGenre) < 1) {
                    $movieGenre = new AnimeCast();
                    $movieGenre->cast_id = $genre['id'];
                    $movieGenre->anime_id = $anime->id;
                    $movieGenre->save();

                }

        }
    }

}


public function onUpdateAnimeGenre ($request,$anime) {

    if ($request->anime['genres']) {
        foreach ($request->anime['genres'] as $genre) {
            if (!isset($genre['genre_id'])) {
                $find = Genre::find($genre['id']) ?? new Genre();
                $find->fill($genre);
                $find->save();
                $animeGenre = AnimeGenre::where('anime_id', $anime->id)->where('genre_id', $genre['id'])->get();
                if (count($animeGenre) < 1) {
                    $animeGenre = new AnimeGenre();
                    $animeGenre->genre_id = $genre['id'];
                    $animeGenre->anime_id = $anime->id;
                    $animeGenre->save();
                }
            }
        }
    }

}


public function onUpdateAnimeSeasons($request,$anime){


    if ($request->anime['seasons']) {
        foreach ($request->anime['seasons'] as $reqSeason) {
            $season = AnimeSeason::find($reqSeason['id'] ?? 0) ?? new AnimeSeason();
            $season->fill($reqSeason);
            $season->anime_id = $anime->id;
            $season->save();


            $this->onUpdateAnimeEpisodes($request,$reqSeason,$season);

        }
    }
}




public function onUpdateAnimeEpisodes ($request,$reqSeason,$season) {

    if ($reqSeason['episodes']) {
                foreach ($reqSeason['episodes'] as $reqEpisode) {
                    $episode = AnimeEpisode::find($reqEpisode['id'] ?? 0) ?? new AnimeEpisode();
                    $episode->fill($reqEpisode);
                    $episode->anime_season_id = $season->id;
                    $episode->save();
                    if (isset($reqEpisode['videos'])) {
                        foreach ($reqEpisode['videos'] as $reqVideo) {
                            $video = AnimeVideo::find($reqVideo['id'] ?? 0) ?? new AnimeVideo();
                            $video->fill($reqVideo);
                            $video->anime_episode_id = $episode->id;
                            $video->save();
                        }
                    }

                    $this->onUpdateAnimeSubstitle($request,$reqEpisode,$episode);
                    $this->onUpdateAnimeDownload($request,$reqEpisode,$episode);
                }
            }

}


public function onUpdateAnimeDownload ($request,$reqEpisode,$episode) {

    if (isset($reqEpisode['downloads'])) {
        foreach ($reqEpisode['downloads'] as $reqVideo) {

            $substitle = AnimeDownload::find($reqVideo['id'] ?? 0) ?? new AnimeDownload();
            $substitle->fill($reqVideo);
            $substitle->anime_episode_id = $episode->id;
            $substitle->save();
        }

}

}

public function onUpdateAnimeSubstitle ($request,$reqEpisode,$episode) {

    if (isset($reqEpisode['substitles'])) {
        foreach ($reqEpisode['substitles'] as $reqVideo) {

            $substitle = AnimeSubstitle::find($reqVideo['id'] ?? 0) ?? new AnimeSubstitle();
            $substitle->fill($reqVideo);
            $substitle->anime_episode_id = $episode->id;
            $substitle->save();
        }

}

}

        public function destroyCertification($id)
        {

            if ($id != null) {

                AnimeCertification::find($id)->delete();
                $data = ['status' => 200, 'message' => 'successfully deleted'];
            } else {
                $data = [
                    'status' => 400,
                    'message' => 'could not be deleted',
                ];
            }

            return response()->json($data, $data['status']);

        }

// delete a anime from the database

    public function destroy(Anime $anime)
    {
        if ($anime != null) {
            $anime->delete();

            $data = [
                'status' => 200,
                'message' => 'successfully deleted',
            ];
        } else {
            $data = [
                'status' => 400,
                'message' => 'could not be deleted',
            ];
        }


        return response()->json($data, $data['status']);
    }


// remove a genre from a animes from the database
public function destroyGenre($genre)
{
    if ($genre != null) {

        AnimeGenre::find($genre)->delete();

        $data = ['status' => 200, 'message' => 'successfully deleted',];
    } else {
        $data = ['status' => 400, 'message' => 'could not be deleted',];
    }

    return response()->json($data, 200);
}

// save a new image in the animes folder of the storage
public function storeImg(StoreImageRequest $request)
{

    if ($request->hasFile('image')) {
        $filename = Storage::disk('animes')->put('', $request->image);
        $data = [
            'status' => 200,
            'image_path' => $request->root() . '/api/animes/image/' . $filename,
            'message' => 'image uploaded successfully'
        ];
    } else {
        $data = [
            'status' => 400,
            'message' => 'there was an error uploading the image'
        ];
    }

    return response()->json($data, $data['status']);
}

// return an image from the animes folder of the storage
public function getImg($filename)
{

    $image = Storage::disk('animes')->get($filename);

    $mime = Storage::disk('animes')->mimeType($filename);

    return (new Response($image, 200))
        ->header('Content-Type', $mime);
}


// returns a specific anime
public function showbyimdb($anime)
{

    $anime_by_imdbid = Anime::where('tmdb_id', '=', $anime)->first();


    return response()->json($anime_by_imdbid, 200);
}


// returns the last 10 animes added in the month
public function recents()
{



    $movies = Anime::select('animes.id','animes.name','animes.poster_path','animes.vote_average',
    'animes.is_anime','animes.vote_average','animes.newEpisodes','animes.subtitle')
    ->where('created_at', '>', Carbon::now()->subMonth(3))
        ->where('active', '=', 1)
        ->orderByDesc('created_at')
        ->limit(10)->get();



    return response()->json(['anime' => $movies->makeHidden(['casterslist','casters','seasons','overview','backdrop_path','preview_path','videos'
    ,'substitles','vote_count','popularity','runtime','release_date','imdb_external_id','hd','pinned','preview'])], 200);

}




// remove Network from  a movie
public function destroyNetworks($id)
{

    if ($id != null) {

        AnimeNetwork::find($id)->delete();
        $data = ['status' => 200, 'message' => 'successfully deleted',];
    } else {
        $data = [
            'status' => 400,
            'message' => 'could not be deleted',
        ];
    }

    return response()->json($data, $data['status']);

}


  // remove the cast of a movie from the database
  public function destroyCast($id)
  {

      if ($id != null) {

          $movie = AnimeCast::where('cast_id', '=', $id)->first();
          $movie->delete();
          $data = ['status' => 200, 'message' => 'successfully deleted',];
      } else {
          $data = [
              'status' => 400,
              'message' => 'could not be deleted',
          ];
      }

      return response()->json($data, $data['status']);

  }

  public function relateds($serie)
  {

      $selectAnime = [
          'animes.id', 'name', 'poster_path', 'backdrop_path',
                      'backdrop_path_tv', 'vote_average', 'subtitle', 'overview', 'first_air_date AS release_date',
                      'pinned', 'animes.created_at','animes.updated_at', 'views', DB::raw("'anime' AS type"),"newEpisodes"
      ];



      $genresAnimes =
        DB::raw('(SELECT SUBSTRING_INDEX(GROUP_CONCAT(genres.name, ', '), ",", 1)
        FROM genres JOIN anime_genres ON genres.id = anime_genres.genre_id WHERE anime_genres.anime_id = animes.id) AS genre_name');


      $moviesGenre = Anime::withOnly('genres.genre')
       ->where('id', '=', $serie)
       ->select(array_merge(
        $selectAnime,
        [
            $genresAnimes,
        ]
    ))
       ->where('active', '=', 1)
       ->limit(1)
       ->orderByDesc('created_at')
       ->get();



       foreach( $moviesGenre as $movie){


          $genre = $movie->genres[0]->genre_id;

      }






      $movies = DB::table('animes')->join('anime_genres', 'animes.id', '=', 'anime_genres.anime_id')
       ->where('anime_genres.genre_id', '=', $genre)
       ->where('anime_genres.anime_id', '!=', $serie)
       ->select(array_merge(
        $selectAnime,
        [
            $genresAnimes,
        ]
    ))
        ->where('active', '=', 1)
       ->limit(10)
       ->orderByDesc('animes.created_at')
       ->get();


     return response()->json(['relateds' => $movies], 200);
  }


public function newEpisodes()
{
    $order = 'desc';

    $animes = Anime::where('active', '=', 1)->join('anime_seasons', 'anime_seasons.anime_id', '=', 'animes.id')
    ->join('anime_episodes', 'anime_episodes.anime_season_id', '=', 'anime_seasons.id')
    ->join('anime_videos', 'anime_videos.anime_episode_id', '=', 'anime_episodes.id')
    ->orderBy('anime_videos.updated_at', $order)->orderBy('anime_videos.anime_episode_id', $order)->select('anime_videos.anime_episode_id','animes.id'
    ,'animes.name','anime_episodes.still_path','anime_episodes.anime_season_id','anime_episodes.name as episode_name','anime_videos.link','anime_videos.server','anime_videos.lang'
    ,'anime_videos.embed','anime_videos.youtubelink','anime_videos.hls','anime_seasons.name as seasons_name','anime_seasons.season_number','anime_episodes.vote_average'
    ,'animes.premuim','animes.tmdb_id','anime_episodes.episode_number','animes.poster_path',
    'anime_episodes.hasrecap',
    'anime_episodes.skiprecap_start_in','anime_videos.supported_hosts'
    ,'anime_videos.drmuuid','anime_videos.drmlicenceuri','anime_videos.drm','animes.imdb_external_id')
    ->addSelect(DB::raw("'anime' as type"))->limit(10)->get()->unique('anime_episode_id')->makeHidden(['seasons','episodes','casterslist','']);


    $newEpisodes = [];
    foreach ($animes as $item) {
        array_push($newEpisodes, $item);
    }

    return response()->json(['latest_episodes' => $newEpisodes], 200);

}



public function animesEpisodesAll()
{
    // Determine the sorting order
    $order = 'desc';

    // Fetch anime episodes with necessary information
    $animeEpisodes = DB::table('animes')
        ->where('animes.active', '=', 1)
        ->join('anime_seasons', 'anime_seasons.anime_id', '=', 'animes.id')
        ->join('anime_episodes', 'anime_episodes.anime_season_id', '=', 'anime_seasons.id')
        ->join('anime_videos', 'anime_videos.anime_episode_id', '=', 'anime_episodes.id')
        ->orderBy('anime_videos.updated_at', $order)
        ->orderBy('anime_videos.anime_episode_id', $order)
        ->select(
            'anime_videos.anime_episode_id',
            'animes.id',
            'animes.tmdb_id as serieTmdb',
            'animes.name',
            'anime_episodes.still_path',
            'anime_episodes.anime_season_id',
            'anime_episodes.name as episode_name',
            'anime_videos.link',
            'anime_videos.server',
            'anime_videos.lang',
            'anime_videos.embed',
            'anime_videos.youtubelink',
            'anime_videos.hls',
            'anime_seasons.name as seasons_name',
            'anime_seasons.season_number',
            'anime_episodes.vote_average',
            'animes.premuim',
            'animes.tmdb_id',
            'anime_episodes.episode_number',
            'animes.poster_path',
            'anime_episodes.hasrecap',
            'anime_episodes.skiprecap_start_in',
            'anime_videos.supported_hosts',
            'animes.is_anime',
            'anime_videos.drmuuid',
            'anime_videos.drmlicenceuri',
            'anime_videos.drm',
            'anime_episodes.enable_stream',
            'animes.imdb_external_id'
        )
        ->addSelect(DB::raw("'anime' as type"))
        ->groupBy('anime_episode_id')
        ->paginate(12);

    // Hide unnecessary fields before returning
    foreach ($animeEpisodes as $episode) {
        unset($episode->seasons);
        unset($episode->episodes);
        unset($episode->casterslist);
    }

    return response()->json($animeEpisodes, 200);
}




public function showEpisodeFromNotifcation($id)
{

    $order = 'desc';
    $animes = Anime::where('active', '=', 1)->join('anime_seasons', 'anime_seasons.anime_id', '=', 'animes.id')
    ->join('anime_episodes', 'anime_episodes.anime_season_id', '=', 'anime_seasons.id')
    ->join('anime_videos', 'anime_videos.anime_episode_id', '=', 'anime_episodes.id')
    ->orderBy('anime_videos.updated_at', $order)->orderBy('anime_videos.anime_episode_id', $order)->select('anime_videos.anime_episode_id','animes.id'
    ,'animes.name','anime_episodes.still_path','anime_episodes.anime_season_id','anime_episodes.name as episode_name','anime_videos.link','anime_videos.server','anime_videos.lang'
    ,'anime_videos.embed','anime_videos.hls','anime_seasons.name as seasons_name','anime_seasons.season_number','anime_episodes.vote_average'
    ,'animes.premuim','animes.tmdb_id','anime_episodes.episode_number','animes.poster_path',
    'anime_episodes.hasrecap','anime_episodes.skiprecap_start_in',
    'anime_videos.supported_hosts','animes.imdb_external_id','anime_videos.header'
    ,'anime_videos.useragent','animes.imdb_external_id','anime_videos.drmuuid','anime_videos.drmlicenceuri','anime_videos.drm'
    )->addSelect(DB::raw("'anime' as type"))->where('anime_episodes.id', '=', $id)->limit(1)
    ->get()->makeHidden(['seasons','episodes','casterslist']);


    return response()->json(['latest_episodes' => $animes], 200);

}


}
