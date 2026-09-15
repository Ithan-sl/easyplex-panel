<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class ContentQueryService
{
    public function getGenresMovies()
    {
        if (config('database.default') === 'pgsql') {
            return DB::raw('(SELECT genres.name
                FROM genres JOIN movie_genres ON genres.id = movie_genres.genre_id 
                WHERE movie_genres.movie_id = movies.id LIMIT 1) AS genre_name');
        }
        return DB::raw('(SELECT SUBSTRING_INDEX(GROUP_CONCAT(genres.name SEPARATOR ", "), ",", 1)
            FROM genres JOIN movie_genres ON genres.id = movie_genres.genre_id 
            WHERE movie_genres.movie_id = movies.id) AS genre_name');
    }

    public function getGenresSeries()
    {
        if (config('database.default') === 'pgsql') {
            return DB::raw('(SELECT string_agg(name, \', \') FROM (
                SELECT genres.name FROM genres JOIN serie_genres ON genres.id = serie_genres.genre_id 
                WHERE serie_genres.serie_id = series.id LIMIT 3) s) AS genre_name');
        }
        return DB::raw('(SELECT SUBSTRING_INDEX(GROUP_CONCAT(genres.name SEPARATOR ", "), ",", 3)
            FROM genres JOIN serie_genres ON genres.id = serie_genres.genre_id 
            WHERE serie_genres.serie_id = series.id) AS genre_name');
    }

    public function getGenresAnimes()
    {
        if (config('database.default') === 'pgsql') {
            return DB::raw('(SELECT genres.name
                FROM genres JOIN anime_genres ON genres.id = anime_genres.genre_id 
                WHERE anime_genres.anime_id = animes.id LIMIT 1) AS genre_name');
        }
        return DB::raw('(SELECT SUBSTRING_INDEX(GROUP_CONCAT(genres.name SEPARATOR ", "), ",", 1)
            FROM genres JOIN anime_genres ON genres.id = anime_genres.genre_id 
            WHERE anime_genres.anime_id = animes.id) AS genre_name');
    }

    public function getSelectSerie()
    {
        return [
            'series.id', 'series.name', 'poster_path', 'backdrop_path',
            'backdrop_path_tv', 'vote_average', 'subtitle', 'overview', 'first_air_date AS release_date',
            'pinned', 'series.created_at', 'series.updated_at', 'views', DB::raw("'serie' AS type"), 'newEpisodes'
        ];
    }

    public function getSelectAnime()
    {
        return [
            'animes.id', 'animes.name', 'poster_path', 'backdrop_path',
            'backdrop_path_tv', 'vote_average', 'subtitle', 'overview', 'first_air_date AS release_date', 
            'pinned', 'animes.created_at', 'animes.updated_at', 'views', DB::raw("'anime' AS type"), 'newEpisodes'
        ];
    }

    public function getSelectMovie()
    {
        return [
            'movies.id',
            'title',
            'poster_path',
            'backdrop_path',
            'backdrop_path_tv',
            'vote_average',
            'subtitle',
            'overview',
            'release_date',
            'pinned',
            'movies.created_at',
            'movies.updated_at',
            'views',
            DB::raw("'movie' AS type"),
            'featured as newEpisodes'
        ];
    }
}