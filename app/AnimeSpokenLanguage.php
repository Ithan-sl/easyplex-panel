<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AnimeSpokenLanguage extends Model
{

    protected $fillable = ['language_iso_639_1', 'anime_id', 'iso_639_1','name'];


    public function language()
    {
        return $this->belongsTo('App\Language', 'language_iso_639_1');
    }

    public function anime()
    {
        return $this->belongsTo('App\Anime', 'anime_id');
    }


   

}
