<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AnimeCollection extends Model
{
    protected $fillable = ['name', 'anime_id'];


    protected $hidden = [
        'collection'
    ];

    public function collection() {
        return $this->belongsTo(Collection::class);
    }

    public function movie()
    {
        return $this->belongsTo('App\Anime', 'anime_id');
    }

    public function getNameAttribute()
    {
        return $this->collection->name;
    }
}
