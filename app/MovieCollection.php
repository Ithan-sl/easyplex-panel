<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MovieCollection extends Model
{
    protected $fillable = ['name', 'movie_id'];


    protected $hidden = [
        'collection'
    ];

    public function collection() {
        return $this->belongsTo(Collection::class);
    }

    public function movie()
    {
        return $this->belongsTo('App\Movie', 'movie_id');
    }

    public function getNameAttribute()
    {
        return $this->collection->name;
    }
}
