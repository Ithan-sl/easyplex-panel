<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SerieCollection extends Model
{
    protected $fillable = ['name', 'serie_id'];


    protected $hidden = [
        'collection'
    ];

    public function collection() {
        return $this->belongsTo(Collection::class);
    }

    public function movie()
    {
        return $this->belongsTo('App\Serie', 'serie_id');
    }

    public function getNameAttribute()
    {
        return $this->collection->name;
    }
}
