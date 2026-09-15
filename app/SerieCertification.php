<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SerieCertification extends Model
{
    protected $fillable = ['country_code','certification','meaning', 'serie_id'];



    public function certification() {
        return $this->belongsTo(Certification::class);
    }

    public function serie()
    {
        return $this->belongsTo('App\Serie', 'serie_id');
    }
}
