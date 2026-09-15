<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use ChristianKuri\LaravelFavorite\Traits\Favoriteable;
use App\Http\ClearsResponseCache;
use BeyondCode\Comments\Traits\HasComments;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class Anime extends Model
{

  use Favoriteable,ClearsResponseCache,HasComments;



    protected $fillable = ['tmdb_id', 'name', 'overview', 'poster_path', 'backdrop_path','backdrop_path_tv',
    'preview_path', 'vote_average', 'vote_count', 'popularity', 'premuim','active','views',
     'featured', 'first_air_date', 'tv','pinned','newEpisodes','imdb_external_id','original_name','trailer_url','subtitle','rating'
    ,'manga','pdflink'];

     protected $with = ['casters.cast','genres.genre', 'seasons','networks.network','spoken_languages'
     ,'belongs_to_collection.collection','certifications'];

     protected $appends = ['genreslist','casterslist'];

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
        return $this->hasMany('App\AnimeCertification');
    }

    public function belongs_to_collection()
    {
        return $this->hasMany('App\AnimeCollection');
    }


    public function networks()
    {
        return $this->hasMany('App\AnimeNetwork');
    }

    public function casters()
    {
        return $this->hasMany('App\AnimeCast');
    }

    public function spoken_languages()
    {
        return $this->hasMany('App\AnimeSpokenLanguage');
    }


    public function genres()
    {
        return $this->hasMany('App\AnimeGenre');
    }

    public function seasons()
    {
        return $this->hasMany('App\AnimeSeason')->orderBy('season_number');
    }



    public function getCasterslistAttribute(): array
    {
        $casters = [];
        foreach ($this->casters as $caster) {
            $casters[] = $caster->cast;
        }
        return $casters;
    }


    public function getGenreslistAttribute()
    {
        $genres = [];
        foreach ($this->genres as $genre) {
            $genres[] = $genre['name'];
        }
        return $genres;
    }

}
