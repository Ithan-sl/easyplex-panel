<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SerieSpokenLanguage extends Model
{

    protected $fillable = ['language_iso_639_1', 'serie_id', 'iso_639_1','name'];


    public function language()
    {
        return $this->belongsTo('App\Language', 'language_iso_639_1');
    }

    public function serie()
    {
        return $this->belongsTo('App\Serie', 'serie_id');
    }


   

}
