<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use ChristianKuri\LaravelFavorite\Traits\Favoriteable;
use App\Http\ClearsResponseCache;
use BeyondCode\Comments\Traits\HasComments;
use Illuminate\Support\Facades\DB;

class Serie extends Model
{


    use Favoriteable,ClearsResponseCache,HasComments;

    protected $fillable = ['tmdb_id', 'name', 'overview', 'poster_path', 'backdrop_path','backdrop_path_tv', 'preview_path', 'vote_average',
     'vote_count', 'popularity','featured', 'premuim','active','views', 'first_air_date', 'tv','pinned','newEpisodes','imdb_external_id','original_name'
    ,'trailer_url','subtitle','rating'];

    protected $with = ['casters.cast','genres.genre', 'seasons','networks.network','spoken_languages'
    ,'belongs_to_collection.collection','certifications'];

    protected $appends = ['genreslist','casterslist','networkslist','genresname'];

    protected $casts = [
        'status' => 'int',
        'premuim' => 'int',
        'active' => 'int',
        'featured' => 'int',
        'pinned' => 'int',
        'newEpisodes' => 'int'

    ];


    public function certifications()
    {
        return $this->hasMany('App\SerieCertification');
    }


    public function belongs_to_collection()
    {
        return $this->hasMany('App\SerieCollection');
    }

    public function casters()
    {
        return $this->hasMany('App\SerieCast');
    }



    public function networks()
    {
        return $this->hasMany('App\SerieNetwork');
    }


    public function genres()
    {
        return $this->hasMany('App\SerieGenre');
    }

    public function seasons()
    {

        return $this->hasMany('App\Season');
    }


    public function spoken_languages()
    {
        return $this->hasMany('App\SerieSpokenLanguage');
    }


    public function getCasterslistAttribute()
    {
        $casters = [];
        foreach ($this->casters as $caster) {
            array_push($casters, $caster->cast);
        }
        return $casters;
    }

    public function getNetworkslistAttribute()
    {
        $networks = [];
        foreach ($this->networks as $network) {
            array_push($networks, $network->network);
        }
        return $networks;
    }



    public function getGenreslistAttribute()
    {
        $genres = [];
        foreach ($this->genres as $genre) {
            array_push($genres, $genre['name']);
        }
    return $genres;
    }


    public function getGenresNameAttribute()
    {
        $genres = "";
        foreach ($this->genres as $genre) {
            return $genre['name'];
        }

    }

}
