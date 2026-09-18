<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AnimeGenre extends Model
{
    protected $fillable = ['anime_id', 'genre_id'];

    protected $appends = ['name'];

   protected $hidden = [
    'genre'];


    public function genre(): BelongsTo
    {
        return $this->belongsTo(Genre::class, 'genre_id');
    }

    public function anime()
    {
        return $this->belongsTo(Anime::class, 'anime_id');
    }

    public function getNameAttribute()
    {
        return $this->genre->name;
    }
}
