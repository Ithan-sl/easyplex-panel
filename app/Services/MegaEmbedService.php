<?php

namespace App\Services;

use App\Movie;
use App\Serie;
use App\Season;
use App\Episode;
use App\MovieVideo;
use App\SerieVideo;
use App\Genre;
use App\MovieGenre;
use App\SerieGenre;
use App\Cast;
use App\MovieCast;
use App\SerieCast;
use App\Setting;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class MegaEmbedService
{
    protected $client;
    protected $apiKey;
    protected $tmdbLang;
    protected $coverPath;

    public function __construct()
    {
        $this->client = new Client([
            'timeout' => 20,
            'connect_timeout' => 10,
            'headers' => [
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                'Accept' => 'application/json, text/plain, */*',
            ]
        ]);

        $settings = Setting::first();
        $this->apiKey = $settings->tmdb_api_key ?? '9c31b3aeb2e59aa2caf74c745ce15887';
        $this->coverPath = $settings->imdb_cover_path ?? 'http://image.tmdb.org/t/p/w500';
        $this->tmdbLang = 'pt-BR';
    }

    /**
     * Get array of all Movie TMDb IDs from MegaEmbed.
     * Cached for 60 minutes to reduce unnecessary HTTP load.
     */
    public function fetchMegaEmbedMoviesList($forceRefresh = false)
    {
        $cacheKey = 'megaembed_movies_catalog';
        if ($forceRefresh) {
            Cache::forget($cacheKey);
        }

        return Cache::remember($cacheKey, 3600, function () {
            try {
                $response = $this->client->get('https://mgeb.top/api/movie');
                $data = json_decode($response->getBody()->getContents(), true);
                return is_array($data) ? $data : [];
            } catch (\Throwable $e) {
                Log::error('MegaEmbedService::fetchMegaEmbedMoviesList error: ' . $e->getMessage());
                return [];
            }
        });
    }

    /**
     * Get array of all Series TMDb IDs from MegaEmbed.
     * Cached for 60 minutes.
     */
    public function fetchMegaEmbedSeriesList($forceRefresh = false)
    {
        $cacheKey = 'megaembed_series_catalog';
        if ($forceRefresh) {
            Cache::forget($cacheKey);
        }

        return Cache::remember($cacheKey, 3600, function () {
            try {
                $response = $this->client->get('https://mgeb.top/api/series');
                $data = json_decode($response->getBody()->getContents(), true);
                return is_array($data) ? $data : [];
            } catch (\Throwable $e) {
                Log::error('MegaEmbedService::fetchMegaEmbedSeriesList error: ' . $e->getMessage());
                return [];
            }
        });
    }

    /**
     * Returns statistics of MegaEmbed catalog vs local database.
     */
    public function getStats()
    {
        $megaMovies = $this->fetchMegaEmbedMoviesList();
        $megaSeries = $this->fetchMegaEmbedSeriesList();

        $localMoviesCount = Movie::count();
        $localSeriesCount = Serie::count();

        $importedMovieIds = Movie::whereIn('tmdb_id', $megaMovies)->pluck('tmdb_id')->toArray();
        $importedSeriesIds = Serie::whereIn('tmdb_id', $megaSeries)->pluck('tmdb_id')->toArray();

        return [
            'megaembed_movies_total' => count($megaMovies),
            'megaembed_series_total' => count($megaSeries),
            'local_movies_total' => $localMoviesCount,
            'local_series_total' => $localSeriesCount,
            'movies_imported_count' => count($importedMovieIds),
            'series_imported_count' => count($importedSeriesIds),
            'movies_pending_count' => max(0, count($megaMovies) - count($importedMovieIds)),
            'series_pending_count' => max(0, count($megaSeries) - count($importedSeriesIds)),
        ];
    }

    /**
     * Search movies and series on TMDb in Portuguese.
     */
    public function searchTmdb($query, $type = 'multi')
    {
        try {
            $endpoint = $type === 'movie' ? 'search/movie' : ($type === 'tv' ? 'search/tv' : 'search/multi');
            $response = $this->client->get("https://api.themoviedb.org/3/{$endpoint}", [
                'query' => [
                    'api_key' => $this->apiKey,
                    'language' => $this->tmdbLang,
                    'query' => $query,
                    'page' => 1,
                    'include_adult' => false
                ]
            ]);

            $body = json_decode($response->getBody()->getContents(), true);
            return $body['results'] ?? [];
        } catch (\Throwable $e) {
            Log::error('MegaEmbedService::searchTmdb error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Fetch Movie details from TMDb with credits and genres in Portuguese.
     */
    public function getTmdbMovieDetails($tmdbId)
    {
        try {
            $response = $this->client->get("https://api.themoviedb.org/3/movie/{$tmdbId}", [
                'query' => [
                    'api_key' => $this->apiKey,
                    'language' => $this->tmdbLang,
                    'append_to_response' => 'credits,videos,genres'
                ]
            ]);

            $movie = json_decode($response->getBody()->getContents(), true);

            // Fallback to English if title or overview is empty
            if (empty($movie['overview']) || empty($movie['title'])) {
                try {
                    $enResp = $this->client->get("https://api.themoviedb.org/3/movie/{$tmdbId}", [
                        'query' => [
                            'api_key' => $this->apiKey,
                            'language' => 'en-US'
                        ]
                    ]);
                    $enMovie = json_decode($enResp->getBody()->getContents(), true);
                    if (empty($movie['title']) && !empty($enMovie['title'])) {
                        $movie['title'] = $enMovie['title'];
                    }
                    if (empty($movie['overview']) && !empty($enMovie['overview'])) {
                        $movie['overview'] = $enMovie['overview'];
                    }
                } catch (\Throwable $e) {}
            }

            return $movie;
        } catch (\Throwable $e) {
            Log::error("MegaEmbedService::getTmdbMovieDetails error for ID {$tmdbId}: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Fetch Series details from TMDb with credits and genres in Portuguese.
     */
    public function getTmdbSeriesDetails($tmdbId)
    {
        try {
            $response = $this->client->get("https://api.themoviedb.org/3/tv/{$tmdbId}", [
                'query' => [
                    'api_key' => $this->apiKey,
                    'language' => $this->tmdbLang,
                    'append_to_response' => 'credits,genres'
                ]
            ]);

            $serie = json_decode($response->getBody()->getContents(), true);

            // Fallback to English if overview or name is empty
            if (empty($serie['overview']) || empty($serie['name'])) {
                try {
                    $enResp = $this->client->get("https://api.themoviedb.org/3/tv/{$tmdbId}", [
                        'query' => [
                            'api_key' => $this->apiKey,
                            'language' => 'en-US'
                        ]
                    ]);
                    $enSerie = json_decode($enResp->getBody()->getContents(), true);
                    if (empty($serie['name']) && !empty($enSerie['name'])) {
                        $serie['name'] = $enSerie['name'];
                    }
                    if (empty($serie['overview']) && !empty($enSerie['overview'])) {
                        $serie['overview'] = $enSerie['overview'];
                    }
                } catch (\Throwable $e) {}
            }

            return $serie;
        } catch (\Throwable $e) {
            Log::error("MegaEmbedService::getTmdbSeriesDetails error for ID {$tmdbId}: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Fetch Season details with episodes from TMDb.
     */
    public function getTmdbSeasonDetails($tmdbId, $seasonNumber)
    {
        try {
            $response = $this->client->get("https://api.themoviedb.org/3/tv/{$tmdbId}/season/{$seasonNumber}", [
                'query' => [
                    'api_key' => $this->apiKey,
                    'language' => $this->tmdbLang,
                ]
            ]);

            return json_decode($response->getBody()->getContents(), true);
        } catch (\Throwable $e) {
            Log::error("MegaEmbedService::getTmdbSeasonDetails error for series {$tmdbId} season {$seasonNumber}: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Import a Movie by TMDb ID.
     */
    public function importMovie($tmdbId, $overwrite = false)
    {
        $existing = Movie::where('tmdb_id', $tmdbId)->first();
        if ($existing && !$overwrite) {
            // Already exists; ensure MegaEmbed video streams are present
            $this->attachMovieStreams($existing, $tmdbId);
            return [
                'success' => true,
                'status' => 'already_exists',
                'id' => $existing->id,
                'title' => $existing->title,
                'message' => "Filme '{$existing->title}' já existe. Streams atualizados."
            ];
        }

        $data = $this->getTmdbMovieDetails($tmdbId);
        if (!$data || empty($data['title'])) {
            return [
                'success' => false,
                'status' => 'not_found',
                'message' => "Filme TMDb #{$tmdbId} não encontrado na API do TheMovieDB."
            ];
        }

        // Preview trailer key
        $previewPath = null;
        if (!empty($data['videos']['results'])) {
            foreach ($data['videos']['results'] as $v) {
                if (($v['site'] ?? '') === 'YouTube' && ($v['type'] ?? '') === 'Trailer') {
                    $previewPath = $v['key'];
                    break;
                }
            }
            if (!$previewPath && isset($data['videos']['results'][0]['key'])) {
                $previewPath = $data['videos']['results'][0]['key'];
            }
        }

        $posterPath = !empty($data['poster_path']) ? $this->coverPath . $data['poster_path'] : null;
        $backdropPath = !empty($data['backdrop_path']) ? $this->coverPath . $data['backdrop_path'] : null;

        $movie = Movie::updateOrCreate(
            ['tmdb_id' => $tmdbId],
            [
                'title' => $data['title'],
                'overview' => $data['overview'] ?? '',
                'poster_path' => $posterPath,
                'backdrop_path' => $backdropPath,
                'backdrop_path_tv' => $backdropPath,
                'preview_path' => $previewPath,
                'vote_average' => $data['vote_average'] ?? 0,
                'vote_count' => $data['vote_count'] ?? 0,
                'popularity' => $data['popularity'] ?? 0,
                'release_date' => $data['release_date'] ?? null,
                'runtime' => $data['runtime'] ?? null,
                'status' => 1,
                'active' => 1,
            ]
        );

        // Genres
        if (!empty($data['genres'])) {
            foreach ($data['genres'] as $g) {
                Genre::updateOrCreate(['id' => $g['id']], ['name' => $g['name']]);
                MovieGenre::firstOrCreate(['movie_id' => $movie->id, 'genre_id' => $g['id']]);
            }
        }

        // Casters (top 10)
        if (!empty($data['credits']['cast'])) {
            $casts = array_slice($data['credits']['cast'], 0, 10);
            foreach ($casts as $c) {
                $castProfile = !empty($c['profile_path']) ? $this->coverPath . $c['profile_path'] : null;
                Cast::updateOrCreate(
                    ['id' => $c['id']],
                    [
                        'name' => $c['name'],
                        'original_name' => $c['original_name'] ?? $c['name'],
                        'character' => $c['character'] ?? '',
                        'profile_path' => $castProfile,
                        'gender' => $c['gender'] ?? 0,
                    ]
                );
                MovieCast::firstOrCreate(['movie_id' => $movie->id, 'cast_id' => $c['id']]);
            }
        }

        // Attach MegaEmbed streams
        $this->attachMovieStreams($movie, $tmdbId);

        return [
            'success' => true,
            'status' => 'imported',
            'id' => $movie->id,
            'title' => $movie->title,
            'message' => "Filme '{$movie->title}' importado com sucesso."
        ];
    }

    /**
     * Extract the real direct video sources (MP4, HLS m3u8, or iframe)
     * returned by the MegaEmbed player page.
     */
    public function extractDirectSources($tmdbId, $seasonNumber = null, $episodeNumber = null)
    {
        $path = ($seasonNumber !== null && $episodeNumber !== null)
            ? "/embed/{$tmdbId}/{$seasonNumber}/{$episodeNumber}"
            : "/embed/{$tmdbId}";

        $domains = [
            'https://mgeb.top',
            'https://megaembed.com',
            'https://embedplayer2.xyz'
        ];

        foreach ($domains as $domain) {
            try {
                $url = $domain . $path;
                $response = $this->client->get($url, [
                    'headers' => [
                        'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                        'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                    ],
                    'timeout' => 8,
                    'allow_redirects' => true,
                ]);
                $html = $response->getBody()->getContents();

                if (preg_match('/var\s+sources\s*=\s*(\[.*?\]);/s', $html, $matches)) {
                    $sources = json_decode($matches[1], true);
                    if (is_array($sources) && count($sources) > 0) {
                        return $sources;
                    }
                }
            } catch (\Throwable $e) {
                Log::warning("MegaEmbedService::extractDirectSources warning for {$domain}{$path}: " . $e->getMessage());
            }
        }

        return [];
    }

    /**
     * Attach MegaEmbed Dublado and Legendado streams to a movie.
     */
    public function attachMovieStreams($movie, $tmdbId)
    {
        // 1. Extrair fontes diretas da request do MegaEmbed
        $directSources = $this->extractDirectSources($tmdbId);
        if (!empty($directSources)) {
            // Remover links antigos genéricos do mgeb.top
            MovieVideo::where('movie_id', $movie->id)
                ->where('server', 'MegaEmbed (Dublado)')
                ->where('link', 'like', '%mgeb.top%')
                ->delete();

            foreach ($directSources as $index => $source) {
                $file = trim($source['file'] ?? '');
                if (empty($file)) continue;

                $type = strtolower($source['type'] ?? '');
                $label = $source['label'] ?? ('Opção ' . ($index + 1));
                $isHls = ($type === 'hls' || strpos($file, '.m3u8') !== false) ? 1 : 0;
                $isEmbed = ($type === 'iframe' || \App\Helpers\EmbedHelper::isEmbedUrl($file)) ? 1 : 0;

                MovieVideo::updateOrCreate(
                    [
                        'movie_id' => $movie->id,
                        'link' => $file
                    ],
                    [
                        'server' => "MegaEmbed ({$label})",
                        'lang' => 'Português',
                        'hls' => $isHls,
                        'embed' => $isEmbed,
                        'status' => 1,
                    ]
                );
            }
        } else {
            // Fallback para player Web apenas se a extração direta falhar
            MovieVideo::updateOrCreate(
                [
                    'movie_id' => $movie->id,
                    'link' => "https://mgeb.top/embed/{$tmdbId}"
                ],
                [
                    'server' => 'MegaEmbed (Player Web)',
                    'lang' => 'Português',
                    'embed' => 1,
                    'status' => 1,
                ]
            );
        }

        // 2. Legendado (Opcional / NHDAPI)
        MovieVideo::updateOrCreate(
            [
                'movie_id' => $movie->id,
                'link' => "https://nhdapi.com/embed/movie/{$tmdbId}"
            ],
            [
                'server' => 'MegaEmbed (Legendado)',
                'lang' => 'Legendado',
                'embed' => 1,
                'status' => 1,
            ]
        );
    }

    /**
     * Import a TV Series with seasons and episodes by TMDb ID.
     */
    public function importSeries($tmdbId, $overwrite = false)
    {
        $existing = Serie::where('tmdb_id', $tmdbId)->first();
        if ($existing && !$overwrite) {
            return [
                'success' => true,
                'status' => 'already_exists',
                'id' => $existing->id,
                'title' => $existing->name,
                'message' => "Série '{$existing->name}' já existe no banco de dados."
            ];
        }

        $data = $this->getTmdbSeriesDetails($tmdbId);
        if (!$data || empty($data['name'])) {
            return [
                'success' => false,
                'status' => 'not_found',
                'message' => "Série TMDb #{$tmdbId} não encontrada na API do TheMovieDB."
            ];
        }

        $posterPath = !empty($data['poster_path']) ? $this->coverPath . $data['poster_path'] : null;
        $backdropPath = !empty($data['backdrop_path']) ? $this->coverPath . $data['backdrop_path'] : null;

        $serie = Serie::updateOrCreate(
            ['tmdb_id' => $tmdbId],
            [
                'name' => $data['name'],
                'original_name' => $data['original_name'] ?? $data['name'],
                'overview' => $data['overview'] ?? '',
                'poster_path' => $posterPath,
                'backdrop_path' => $backdropPath,
                'backdrop_path_tv' => $backdropPath,
                'vote_average' => $data['vote_average'] ?? 0,
                'vote_count' => $data['vote_count'] ?? 0,
                'popularity' => $data['popularity'] ?? 0,
                'first_air_date' => $data['first_air_date'] ?? null,
                'status' => 1,
                'active' => 1,
            ]
        );

        // Genres
        if (!empty($data['genres'])) {
            foreach ($data['genres'] as $g) {
                Genre::updateOrCreate(['id' => $g['id']], ['name' => $g['name']]);
                SerieGenre::firstOrCreate(['serie_id' => $serie->id, 'genre_id' => $g['id']]);
            }
        }

        // Casters (top 10)
        if (!empty($data['credits']['cast'])) {
            $casts = array_slice($data['credits']['cast'], 0, 10);
            foreach ($casts as $c) {
                $castProfile = !empty($c['profile_path']) ? $this->coverPath . $c['profile_path'] : null;
                Cast::updateOrCreate(
                    ['id' => $c['id']],
                    [
                        'name' => $c['name'],
                        'original_name' => $c['original_name'] ?? $c['name'],
                        'character' => $c['character'] ?? '',
                        'profile_path' => $castProfile,
                        'gender' => $c['gender'] ?? 0,
                    ]
                );
                SerieCast::firstOrCreate(['serie_id' => $serie->id, 'cast_id' => $c['id']]);
            }
        }

        // Seasons & Episodes
        $totalEpisodesImported = 0;
        if (!empty($data['seasons'])) {
            foreach ($data['seasons'] as $s) {
                $seasonNumber = (int) $s['season_number'];
                // Skip specials (season 0)
                if ($seasonNumber <= 0) {
                    continue;
                }

                $seasonPoster = !empty($s['poster_path']) ? $this->coverPath . $s['poster_path'] : $posterPath;
                $season = Season::updateOrCreate(
                    [
                        'serie_id' => $serie->id,
                        'season_number' => $seasonNumber,
                    ],
                    [
                        'name' => $s['name'] ?? ("Temporada {$seasonNumber}"),
                        'overview' => $s['overview'] ?? null,
                        'poster_path' => $seasonPoster,
                        'air_date' => $s['air_date'] ?? null,
                    ]
                );

                // Fetch season episodes
                $seasonDetails = $this->getTmdbSeasonDetails($tmdbId, $seasonNumber);
                if (!empty($seasonDetails['episodes'])) {
                    foreach ($seasonDetails['episodes'] as $ep) {
                        $epNumber = (int) $ep['episode_number'];
                        $stillPath = !empty($ep['still_path']) ? $this->coverPath . $ep['still_path'] : null;

                        $episode = Episode::updateOrCreate(
                            [
                                'season_id' => $season->id,
                                'episode_number' => $epNumber,
                            ],
                            [
                                'name' => $ep['name'] ?? ("Episódio {$epNumber}"),
                                'overview' => $ep['overview'] ?? '',
                                'still_path' => $stillPath,
                                'still_path_tv' => $stillPath,
                                'vote_average' => $ep['vote_average'] ?? 0,
                                'air_date' => $ep['air_date'] ?? null,
                                'enable_stream' => 1,
                            ]
                        );

                        // Attach MegaEmbed episode streams
                        $this->attachEpisodeStreams($episode, $tmdbId, $seasonNumber, $epNumber);

                        $totalEpisodesImported++;
                    }
                }
            }
        }

        return [
            'success' => true,
            'status' => 'imported',
            'id' => $serie->id,
            'title' => $serie->name,
            'episodes_count' => $totalEpisodesImported,
            'message' => "Série '{$serie->name}' importada com {$totalEpisodesImported} episódios."
        ];
    }

    /**
     * Attach direct video sources to a series episode.
     */
    public function attachEpisodeStreams($episode, $tmdbId, $seasonNumber, $episodeNumber)
    {
        // 1. Extrair fontes diretas da request do MegaEmbed
        $directSources = $this->extractDirectSources($tmdbId, $seasonNumber, $episodeNumber);
        if (!empty($directSources)) {
            // Remover links antigos genéricos do mgeb.top
            SerieVideo::where('episode_id', $episode->id)
                ->where('server', 'MegaEmbed (Dublado)')
                ->where('link', 'like', '%mgeb.top%')
                ->delete();

            foreach ($directSources as $index => $source) {
                $file = trim($source['file'] ?? '');
                if (empty($file)) continue;

                $type = strtolower($source['type'] ?? '');
                $label = $source['label'] ?? ('Opção ' . ($index + 1));
                $isHls = ($type === 'hls' || strpos($file, '.m3u8') !== false) ? 1 : 0;
                $isEmbed = ($type === 'iframe' || \App\Helpers\EmbedHelper::isEmbedUrl($file)) ? 1 : 0;

                SerieVideo::updateOrCreate(
                    [
                        'episode_id' => $episode->id,
                        'link' => $file
                    ],
                    [
                        'server' => "MegaEmbed ({$label})",
                        'lang' => 'Português',
                        'hls' => $isHls,
                        'embed' => $isEmbed,
                    ]
                );
            }
        } else {
            // Fallback para player Web apenas se a extração direta falhar
            SerieVideo::updateOrCreate(
                [
                    'episode_id' => $episode->id,
                    'link' => "https://mgeb.top/embed/{$tmdbId}/{$seasonNumber}/{$episodeNumber}"
                ],
                [
                    'server' => 'MegaEmbed (Player Web)',
                    'lang' => 'Português',
                    'embed' => 1,
                ]
            );
        }

        // 2. Legendado (Opcional / NHDAPI)
        SerieVideo::updateOrCreate(
            [
                'episode_id' => $episode->id,
                'link' => "https://nhdapi.com/embed/tv/{$tmdbId}/{$seasonNumber}/{$episodeNumber}"
            ],
            [
                'server' => 'MegaEmbed (Legendado)',
                'lang' => 'Legendado',
                'embed' => 1,
            ]
        );
    }

    /**
     * Refresh streams for all movies and episodes that have TMDb IDs.
     */
    public function refreshAllExistingStreams($type = 'all')
    {
        $updated = ['movies' => 0, 'episodes' => 0];

        if ($type === 'movie' || $type === 'all') {
            $movies = Movie::whereNotNull('tmdb_id')->get();
            foreach ($movies as $movie) {
                $this->attachMovieStreams($movie, $movie->tmdb_id);
                $updated['movies']++;
            }
        }

        if ($type === 'series' || $type === 'all') {
            $episodes = Episode::with('season.serie')
                ->whereHas('season.serie', function ($q) {
                    $q->whereNotNull('tmdb_id');
                })
                ->get();

            foreach ($episodes as $episode) {
                $season = $episode->season;
                $serie = $season ? $season->serie : null;
                if ($serie && $serie->tmdb_id && $season->season_number && $episode->episode_number) {
                    $this->attachEpisodeStreams($episode, $serie->tmdb_id, $season->season_number, $episode->episode_number);
                    $updated['episodes']++;
                }
            }
        }

        return $updated;
    }
}

