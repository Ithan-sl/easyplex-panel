<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MovieSpokenLanguage extends Model
{

    protected $fillable = ['name', 'movie_id','name','iso_639_1'];


    public function language()
    {
        return $this->belongsTo('App\Language', 'language_iso_639_1');
    }

    public function movie()
    {
        return $this->belongsTo('App\Movie', 'movie_id');
    }


   

}
