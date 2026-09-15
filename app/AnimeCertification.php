<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AnimeCertification extends Model
{
    protected $fillable = ['country_code','certification','meaning', 'anime_id'];



    public function certification() {
        return $this->belongsTo(Certification::class);
    }

    public function anime()
    {
        return $this->belongsTo('App\Anime', 'anime_id');
    }
}
