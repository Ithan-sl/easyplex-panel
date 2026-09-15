<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MovieCertification extends Model
{
    protected $fillable = ['country_code','certification','meaning', 'movie_id'];



    public function certification() {
        return $this->belongsTo(Certification::class);
    }

    public function movie()
    {
        return $this->belongsTo('App\Movie', 'movie_id');
    }
}
