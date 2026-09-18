<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AnimeSeason extends Model
{
    protected $fillable = ['anime_id', 'tmdb_id', 'season_number', 'name', 'overview', 'poster_path', 'air_date'];


    protected $with = ['episodes'];

    public function episodes()
    {
        return $this->hasMany(AnimeEpisode::class, 'anime_season_id')->orderBy('episode_number');
    }

    public function anime()
    {
        return $this->belongsTo(Anime::class, 'anime_id');
    }
}
