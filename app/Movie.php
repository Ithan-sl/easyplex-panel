<?php

namespace App;
use Illuminate\Database\Eloquent\Model;
use ChristianKuri\LaravelFavorite\Traits\Favoriteable;
use App\Http\ClearsResponseCache;
use BeyondCode\Comments\Traits\HasComments;
use Illuminate\Support\Facades\DB;

class Movie extends Model
{


    protected $with = ['casters.cast','genres.genre','videos','downloads','substitles','networks.network'
                        ,'spoken_languages','belongs_to_collection.collection','certifications'];

    use Favoriteable,ClearsResponseCache,HasComments;

    protected $fillable = ['tmdb_id','imdb_external_id', 'title', 'overview', 'poster_path', 'backdrop_path','backdrop_path_tv', 'preview_path',
     'vote_average', 'vote_count', 'popularity', 'runtime', 'views','featured','pinned', 'premuim','hasrecap','skiprecap_start_in','active', 'release_date'
    ,'minicover','linkpreview','preview','original_name','trailer_url','subtitle','rating','enable_stream','enable_media_download','enable_ads_unlock','user_with_vip_code'];

    protected $appends = ['casterslist','substype','networkslist','genresname'];

    protected $casts = [
        'status' => 'int',
        'premuim' => 'int',
        'skiprecap_start_in' => 'int',
        'hasrecap' => 'int',
        'featured' => 'int',
        'pinned' => 'int',
        'active' => 'int',
        'preview' => 'int',
        'enable_stream' => 'int',
        'enable_media_download' => 'int',
        'enable_ads_unlock' => 'int'

    ];


    public function scopeActive($query)
    {
        return $query->where('active', '=', 1);
    }


    public function casters()
    {
        return $this->hasMany(MovieCast::class);
    }


    public function networks()
    {
        return $this->hasMany('App\MovieNetwork');
    }


    public function spoken_languages()
    {
        return $this->hasMany('App\MovieSpokenLanguage');
    }


    public function belongs_to_collection()
    {
        return $this->hasMany('App\MovieCollection');
    }


    public function certifications()
    {
        return $this->hasMany('App\MovieCertification');
    }


    public function genres()
    {
        return $this->hasMany('App\MovieGenre');
    }

    public function videos()
    {
        return $this->hasMany('App\MovieVideo');
    }


    public function downloads()
    {
        return $this->hasMany('App\MovieDownload');
    }


    public function substitles()
    {
        return $this->hasMany('App\MovieSubstitle');
    }



    public function getSubsTypeAttribute()
    {
        $substype = 0;
        $substitles = $this->substitles;
        if ($substitles) {
            foreach ($substitles as $substitle) {
                if ($substitle->type) {
                    $substype = $substitle->type;
                }
            }
        }

        return $substype;
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



    public function getGenresNameAttribute()
    {
        $genres = "";
        foreach ($this->genres as $genre) {
            return $genre['name'];
        }

    }

}
