<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AnimeNetwork extends Model
{
    protected $appends = ['name'];


    protected $hidden = [
        'network'];

    public function network()
    {

    return $this->belongsTo('App\Network', 'network_id');

    }

    public function anime()
    {
        return $this->belongsTo('App\Anime', 'anime_id');
    }

    public function getNameAttribute()
    {
        return $this->network->name;
    }
}
