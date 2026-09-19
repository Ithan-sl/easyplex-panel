<?php

namespace App\Services;

use App\Movie;
use App\Serie;
use App\Season;
use App\Episode;
use App\MovieVideo;
use App\SerieVideo;
use App\Anime;
use App\AnimeSeason;
use App\AnimeEpisode;
use App\AnimeVideo;
use App\Genre;
use App\MovieGenre;
use App\SerieGenre;
use App\AnimeGenre;
use App\Cast;
use App\MovieCast;
use App\SerieCast;
use App\AnimeCast;
use App\Setting;
use GuzzleHttp\Client;
use GuzzleHttp\Cookie\FileCookieJar;
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
     * Get array of all Anime TMDb IDs from MegaEmbed moderator panel.
     * Cached for 60 minutes.
     */
    public function fetchMegaEmbedAnimesList($forceRefresh = false)
    {
        $cacheKey = 'megaembed_animes_catalog';
        if ($forceRefresh) {
            Cache::forget($cacheKey);
        }

        return Cache::remember($cacheKey, 3600, function () {
            try {
                $modClient = $this->getModeratorSessionClient();
                if (!$modClient) {
                    return [];
                }

                $allIds = [];
                $firstPage = $modClient->get("https://megaembed.com/moderador/animes?page=1")->getBody()->getContents();
                preg_match_all('/<td[^>]*font-mono[^>]*>\s*#(\d+)/', $firstPage, $matches);
                if (!empty($matches[1])) {
                    foreach ($matches[1] as $id) {
                        $allIds[] = (int) $id;
                    }
                }

                $totalPages = 1;
                if (preg_match('/Página\s+\d+\s+de\s+(\d+)/i', $firstPage, $pMatches)) {
                    $totalPages = (int) $pMatches[1];
                }

                $maxPages = min($totalPages, 25);
                for ($p = 2; $p <= $maxPages; $p++) {
                    $html = $modClient->get("https://megaembed.com/moderador/animes?page={$p}")->getBody()->getContents();
                    preg_match_all('/<td[^>]*font-mono[^>]*>\s*#(\d+)/', $html, $m);
                    if (!empty($m[1])) {
                        foreach ($m[1] as $id) {
                            $allIds[] = (int) $id;
                        }
                    }
                }

                return array_values(array_unique(array_filter($allIds)));
            } catch (\Throwable $e) {
                Log::error('MegaEmbedService::fetchMegaEmbedAnimesList error: ' . $e->getMessage());
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
        $megaAnimes = $this->fetchMegaEmbedAnimesList();

        $localMoviesCount = Movie::count();
        $localSeriesCount = Serie::count();
        $localAnimesCount = Anime::count();

        $importedMovieIds = Movie::whereIn('tmdb_id', $megaMovies)->pluck('tmdb_id')->toArray();
        $importedSeriesIds = Serie::whereIn('tmdb_id', $megaSeries)->pluck('tmdb_id')->toArray();
        $importedAnimeIds = Anime::whereIn('tmdb_id', $megaAnimes)->pluck('tmdb_id')->toArray();

        return [
            'megaembed_movies_total' => count($megaMovies),
            'megaembed_series_total' => count($megaSeries),
            'megaembed_animes_total' => count($megaAnimes),
            'local_movies_total' => $localMoviesCount,
            'local_series_total' => $localSeriesCount,
            'local_animes_total' => $localAnimesCount,
            'movies_imported_count' => count($importedMovieIds),
            'series_imported_count' => count($importedSeriesIds),
            'animes_imported_count' => count($importedAnimeIds),
            'movies_pending_count' => max(0, count($megaMovies) - count($importedMovieIds)),
            'series_pending_count' => max(0, count($megaSeries) - count($importedSeriesIds)),
            'animes_pending_count' => max(0, count($megaAnimes) - count($importedAnimeIds)),
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
            'https://megaembed.com',
            'https://mgeb.top'
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
     * Normalize and sort sources so direct streams (MP4 / HLS, embed = 0) ALWAYS come first.
     */
    public function sortSourcesDirectFirst(array $sources): array
    {
        $normalized = [];

        foreach ($sources as $index => $source) {
            $file = '';
            $label = '';
            $type = '';

            if (is_string($source)) {
                $file = trim($source);
                $label = '';
            } elseif (is_array($source)) {
                $file = trim($source['file'] ?? $source['url'] ?? '');
                $label = trim($source['label'] ?? '');
                $type = strtolower($source['type'] ?? $source['format'] ?? '');
            }

            if (empty($file) || strpos($file, '******') !== false) {
                continue;
            }

            // Discard dead / broken hosts
            if (strpos($file, 'novix.x10.mx') !== false) {
                continue;
            }

            // Discard temporary presigned R2/S3 URLs that expire or get cut off if other permanent sources exist
            if (strpos($file, 'r2.cloudflarestorage.com') !== false && count($sources) > 1) {
                continue;
            }

            $isHls = ($type === 'hls' || strpos(strtolower($file), '.m3u8') !== false) ? 1 : 0;
            $isDirect = ($type === 'mp4' || $type === 'hls' || strpos(strtolower($file), '.mp4') !== false || strpos(strtolower($file), '.m3u8') !== false || !\App\Helpers\EmbedHelper::isEmbedUrl($file));
            $isEmbed = ($isDirect && $type !== 'iframe') ? 0 : 1;

            if (empty($label)) {
                if (strpos($file, '/dub') !== false) {
                    $label = 'Dublado';
                } elseif (strpos($file, '/leg') !== false) {
                    $label = 'Legendado';
                }
            }

            $normalized[] = [
                'file' => $file,
                'label' => $label,
                'hls' => $isHls,
                'embed' => $isEmbed,
                'weight' => $isEmbed === 0 ? 0 : 1,
                'orig_index' => $index,
            ];
        }

        // Sort so direct streams (weight = 0) are ALWAYS first before embeds (weight = 1)
        usort($normalized, function ($a, $b) {
            if ($a['weight'] !== $b['weight']) {
                return $a['weight'] <=> $b['weight'];
            }
            return $a['orig_index'] <=> $b['orig_index'];
        });

        // Set clean labels
        $directIdx = 1;
        foreach ($normalized as &$item) {
            if (empty($item['label']) || strpos($item['label'], 'Opção') === 0) {
                if ($item['embed'] === 0) {
                    $item['label'] = "Opção {$directIdx}";
                    $directIdx++;
                } else {
                    $item['label'] = "Player Web";
                }
            }
        }

        return $normalized;
    }

    /**
     * Attach MegaEmbed streams to a movie.
     * Prioritizes direct MP4/HLS links in first place.
     */
    public function attachMovieStreams($movie, $tmdbId)
    {
        // 1. Extrair fontes diretas da request do MegaEmbed
        $directSources = $this->extractDirectSources($tmdbId);
        $sortedSources = !empty($directSources) ? $this->sortSourcesDirectFirst($directSources) : [];

        // 2. Se vazio, tentar fallback em moderador (se unmasked)
        if (empty($sortedSources)) {
            $modLinks = $this->fetchModeratorOriginalLinks('movies', $tmdbId);
            if (!empty($modLinks)) {
                $sortedSources = $this->sortSourcesDirectFirst($modLinks);
            }
        }

        // Limpar registros anteriores deste filme para evitar links desatualizados ou duplicados
        MovieVideo::where('movie_id', $movie->id)->delete();

        if (!empty($sortedSources)) {
            foreach ($sortedSources as $source) {
                MovieVideo::create([
                    'movie_id' => $movie->id,
                    'server' => 'Servidor',
                    'link' => $source['file'],
                    'lang' => 'Português',
                    'hls' => $source['hls'],
                    'embed' => $source['embed'],
                    'status' => 1,
                ]);
            }
        } else {
            // Fallback para player Web apenas se a extração direta falhar
            MovieVideo::create([
                'movie_id' => $movie->id,
                'server' => 'Servidor',
                'link' => "https://mgeb.top/embed/{$tmdbId}",
                'lang' => 'Português',
                'hls' => 0,
                'embed' => 1,
                'status' => 1,
            ]);
        }
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

        // Se os metadados do TMDb indicam que é Anime (Animação japonesa), importar como Anime
        if ($this->isAnimeTmdb($data)) {
            return $this->importAnime($tmdbId, $overwrite);
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

                        // Attach direct video streams para os primeiros episódios; os demais usam fallback e são resolvidos sob demanda com fontes diretas no EpisodeController
                        if ($seasonNumber === 1 && $epNumber <= 5) {
                            $this->attachEpisodeStreams($episode, $tmdbId, $seasonNumber, $epNumber);
                        } else {
                            SerieVideo::updateOrCreate(
                                [
                                    'episode_id' => $episode->id,
                                    'link' => "https://mgeb.top/embed/{$tmdbId}/{$seasonNumber}/{$epNumber}"
                                ],
                                [
                                    'server' => 'Servidor',
                                    'lang' => 'Português',
                                    'embed' => 1,
                                    'status' => 1,
                                ]
                            );
                        }

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
     * Check if TMDb series metadata corresponds to an Anime.
     */
    public function isAnimeTmdb(array $data): bool
    {
        $originCountry = $data['origin_country'] ?? [];
        $originalLang = strtolower($data['original_language'] ?? '');
        $genreIds = array_column($data['genres'] ?? [], 'id');

        $isJapan = in_array('JP', $originCountry) || $originalLang === 'ja';
        $isAnimation = in_array(16, $genreIds);

        return $isJapan && $isAnimation;
    }

    /**
     * Get or create an authenticated Guzzle client with moderator cookie session.
     */
    public function getModeratorSessionClient()
    {
        try {
            $cookieFile = storage_path('app/megaembed_cookies.json');
            $jar = new FileCookieJar($cookieFile, true);
            $modClient = new Client([
                'cookies' => $jar,
                'verify' => false,
                'timeout' => 10,
                'headers' => [
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36'
                ]
            ]);

            // Check if existing session is alive
            $dashResp = $modClient->get('https://megaembed.com/moderador/dashboard', ['allow_redirects' => false]);
            if ($dashResp->getStatusCode() === 200) {
                return $modClient;
            }

            // Perform login if redirected or expired
            $loginHtml = $modClient->get('https://megaembed.com/moderador/login')->getBody()->getContents();
            if (preg_match('/name="csrf_token"\s+value="([^"]+)"/', $loginHtml, $m)) {
                $csrf = $m[1];
                $modClient->post('https://megaembed.com/moderador/login', [
                    'form_params' => [
                        'action' => 'login',
                        'csrf_token' => $csrf,
                        'website_hp' => '',
                        'username' => 'anome242@gmail.com',
                        'password' => 'Anome123456'
                    ]
                ]);
                return $modClient;
            }
        } catch (\Throwable $e) {
            Log::warning("MegaEmbed moderator session error: " . $e->getMessage());
        }

        return null;
    }

    /**
     * Fetch unmasked original embed/direct URLs added by moderators for a content and episode.
     */
    public function fetchModeratorOriginalLinks($type, $contentTmdbId, $seasonNumber = null, $episodeNumber = null)
    {
        $modClient = $this->getModeratorSessionClient();
        if (!$modClient) return [];

        try {
            $endpoint = ($type === 'anime') ? 'animes' : 'series';
            $searchHtml = $modClient->get("https://megaembed.com/moderador/{$endpoint}?search={$contentTmdbId}")->getBody()->getContents();
            $contentId = null;

            if (preg_match('/openEpisodesModal\((\d+)/', $searchHtml, $m)) {
                $contentId = $m[1];
            } elseif (preg_match('/deleteSeriesModAjax\((\d+)/', $searchHtml, $m)) {
                $contentId = $m[1];
            } elseif (preg_match('/' . $endpoint . '\?action=edit&id=(\d+)/', $searchHtml, $m)) {
                $contentId = $m[1];
            }

            if (!$contentId) return [];

            $seasons = json_decode($modClient->get("https://megaembed.com/moderador/{$endpoint}?action=get_seasons&series_id={$contentId}")->getBody()->getContents(), true);
            if (!is_array($seasons)) return [];

            foreach ($seasons as $season) {
                if ($seasonNumber !== null && (int)($season['season_number'] ?? 0) !== (int)$seasonNumber) {
                    continue;
                }

                $episodes = json_decode($modClient->get("https://megaembed.com/moderador/{$endpoint}?action=get_episodes&season_id={$season['id']}")->getBody()->getContents(), true);
                if (!is_array($episodes)) continue;

                foreach ($episodes as $ep) {
                    if ((int)($ep['episode_number'] ?? 0) === (int)$episodeNumber) {
                        $links = json_decode($modClient->get("https://megaembed.com/moderador/{$endpoint}?action=get_links&content_id={$contentId}&episode_id={$ep['id']}")->getBody()->getContents(), true);
                        if (is_array($links)) {
                            $extracted = [];
                            foreach ($links as $l) {
                                $u = trim($l['original_url'] ?? $l['url'] ?? '');
                                if (!empty($u) && strpos($u, '******') === false) {
                                    if (strpos($u, 'novix.x10.mx') !== false) {
                                        continue;
                                    }
                                    $fmt = strtolower($l['format'] ?? '');
                                    $lbl = '';
                                    if (strpos($u, '/dub') !== false) {
                                        $lbl = 'Dublado';
                                    } elseif (strpos($u, '/leg') !== false) {
                                        $lbl = 'Legendado';
                                    }
                                    $extracted[] = [
                                        'file' => $u,
                                        'type' => $fmt,
                                        'label' => $lbl
                                    ];
                                }
                            }
                            return $extracted;
                        }
                    }
                }
            }
        } catch (\Throwable $e) {
            Log::warning("fetchModeratorOriginalLinks warning: " . $e->getMessage());
        }

        return [];
    }

    /**
     * Attach direct video sources to a series episode.
     * Prioritizes direct MP4/HLS links in first place.
     */
    public function attachEpisodeStreams($episode, $tmdbId, $seasonNumber, $episodeNumber)
    {
        // 1. Tentar primeiro obter as fontes originais dos moderadores (nixplay.lat MP4 direto, api.embedplayer dub/leg)
        $moderatorLinks = $this->fetchModeratorOriginalLinks('series', $tmdbId, $seasonNumber, $episodeNumber);
        $sortedSources = !empty($moderatorLinks) ? $this->sortSourcesDirectFirst($moderatorLinks) : [];

        // 2. Se vazio, extrair fontes do player web do MegaEmbed
        if (empty($sortedSources)) {
            $directSources = $this->extractDirectSources($tmdbId, $seasonNumber, $episodeNumber);
            if (!empty($directSources)) {
                $sortedSources = $this->sortSourcesDirectFirst($directSources);
            }
        }

        // Limpar registros anteriores deste episódio para não acumular links desatualizados
        SerieVideo::where('episode_id', $episode->id)->delete();

        if (!empty($sortedSources)) {
            foreach ($sortedSources as $source) {
                SerieVideo::create([
                    'episode_id' => $episode->id,
                    'server' => 'Servidor',
                    'link' => $source['file'],
                    'lang' => ($source['label'] === 'Legendado') ? 'Legendado' : 'Português',
                    'hls' => $source['hls'],
                    'embed' => $source['embed'],
                    'status' => 1,
                ]);
            }
        } else {
            // Fallback para player Web apenas se nenhuma fonte direta for encontrada
            SerieVideo::create([
                'episode_id' => $episode->id,
                'server' => 'Servidor',
                'link' => "https://megaembed.com/embed/{$tmdbId}/{$seasonNumber}/{$episodeNumber}",
                'lang' => 'Português',
                'hls' => 0,
                'embed' => 1,
                'status' => 1,
            ]);
        }
    }

    /**
     * Import an Anime with seasons and episodes by TMDb ID.
     */
    public function importAnime($tmdbId, $overwrite = false)
    {
        // Se estava anteriormente salvo como Série comum por engano, limpa da tabela series
        $oldSerie = Serie::where('tmdb_id', $tmdbId)->first();
        if ($oldSerie) {
            $oldSeasonIds = Season::where('serie_id', $oldSerie->id)->pluck('id');
            $oldEpisodeIds = Episode::whereIn('season_id', $oldSeasonIds)->pluck('id');

            SerieVideo::whereIn('episode_id', $oldEpisodeIds)->delete();
            Episode::whereIn('id', $oldEpisodeIds)->delete();
            Season::whereIn('id', $oldSeasonIds)->delete();
            SerieGenre::where('serie_id', $oldSerie->id)->delete();
            SerieCast::where('serie_id', $oldSerie->id)->delete();
            $oldSerie->delete();
        }

        $existing = Anime::where('tmdb_id', $tmdbId)->first();
        if ($existing && !$overwrite) {
            return [
                'success' => true,
                'status' => 'already_exists',
                'type' => 'anime',
                'id' => $existing->id,
                'title' => $existing->name,
                'message' => "Anime '{$existing->name}' já existe no banco de dados."
            ];
        }

        $data = $this->getTmdbSeriesDetails($tmdbId);
        if (!$data || empty($data['name'])) {
            return [
                'success' => false,
                'status' => 'not_found',
                'type' => 'anime',
                'message' => "Anime TMDb #{$tmdbId} não encontrado na API do TheMovieDB."
            ];
        }

        $posterPath = !empty($data['poster_path']) ? $this->coverPath . $data['poster_path'] : null;
        $backdropPath = !empty($data['backdrop_path']) ? $this->coverPath . $data['backdrop_path'] : null;

        $trailerUrl = null;
        if (!empty($data['videos']['results'])) {
            foreach ($data['videos']['results'] as $video) {
                if (($video['site'] ?? '') === 'YouTube' && ($video['type'] ?? '') === 'Trailer') {
                    $trailerUrl = 'https://www.youtube.com/watch?v=' . $video['key'];
                    break;
                }
            }
        }

        $anime = Anime::updateOrCreate(
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
                'trailer_url' => $trailerUrl,
                'active' => 1,
                'is_anime' => 1,
                'newEpisodes' => 1,
            ]
        );

        // Genres
        if (!empty($data['genres'])) {
            foreach ($data['genres'] as $g) {
                Genre::updateOrCreate(['id' => $g['id']], ['name' => $g['name']]);
                AnimeGenre::firstOrCreate(['anime_id' => $anime->id, 'genre_id' => $g['id']]);
            }
        }

        // Casters
        if (!empty($data['credits']['cast'])) {
            foreach (array_slice($data['credits']['cast'], 0, 10) as $c) {
                $castProfile = !empty($c['profile_path']) ? $this->coverPath . $c['profile_path'] : null;
                $cast = Cast::updateOrCreate(
                    ['id' => $c['id']],
                    [
                        'name' => $c['name'],
                        'original_name' => $c['original_name'] ?? $c['name'],
                        'profile_path' => $castProfile,
                        'character' => $c['character'] ?? '',
                    ]
                );
                AnimeCast::firstOrCreate(['anime_id' => $anime->id, 'cast_id' => $cast->id]);
            }
        }

        // Seasons & Episodes
        $totalEpisodesImported = 0;
        if (!empty($data['seasons'])) {
            foreach ($data['seasons'] as $s) {
                $seasonNumber = (int) $s['season_number'];
                if ($seasonNumber < 1) continue; // Pula especiais (temporada 0)

                $seasonPoster = !empty($s['poster_path']) ? $this->coverPath . $s['poster_path'] : $posterPath;
                $season = AnimeSeason::updateOrCreate(
                    [
                        'anime_id' => $anime->id,
                        'season_number' => $seasonNumber,
                    ],
                    [
                        'name' => $s['name'] ?? ("Temporada {$seasonNumber}"),
                        'overview' => $s['overview'] ?? '',
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

                        $episode = AnimeEpisode::updateOrCreate(
                            [
                                'anime_season_id' => $season->id,
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

                        // Attach direct video streams para os primeiros episódios; os demais usam fallback e são resolvidos sob demanda com fontes diretas no EpisodeController
                        if ($seasonNumber === 1 && $epNumber <= 5) {
                            $this->attachAnimeEpisodeStreams($episode, $tmdbId, $seasonNumber, $epNumber);
                        } else {
                            AnimeVideo::updateOrCreate(
                                [
                                    'anime_episode_id' => $episode->id,
                                    'link' => "https://mgeb.top/embed/{$tmdbId}/{$seasonNumber}/{$epNumber}"
                                ],
                                [
                                    'server' => 'Servidor',
                                    'lang' => 'Português',
                                    'embed' => 1,
                                    'status' => 1,
                                ]
                            );
                        }

                        $totalEpisodesImported++;
                    }
                }
            }
        }

        return [
            'success' => true,
            'status' => 'imported',
            'type' => 'anime',
            'id' => $anime->id,
            'title' => $anime->name,
            'episodes_count' => $totalEpisodesImported,
            'message' => "Anime '{$anime->name}' importado com {$totalEpisodesImported} episódios."
        ];
    }

    /**
     * Attach direct video sources to an anime episode.
     * Prioritizes direct MP4/HLS links in first place.
     */
    public function attachAnimeEpisodeStreams($episode, $tmdbId, $seasonNumber, $episodeNumber)
    {
        // 1. Tentar primeiro obter as fontes originais dos moderadores (nixplay.lat MP4 direto, api.embedplayer dub/leg)
        $moderatorLinks = $this->fetchModeratorOriginalLinks('anime', $tmdbId, $seasonNumber, $episodeNumber);
        $sortedSources = !empty($moderatorLinks) ? $this->sortSourcesDirectFirst($moderatorLinks) : [];

        // 2. Se vazio, tentar fallback na extração do player web do MegaEmbed
        if (empty($sortedSources)) {
            $directSources = $this->extractDirectSources($tmdbId, $seasonNumber, $episodeNumber);
            if (!empty($directSources)) {
                $sortedSources = $this->sortSourcesDirectFirst($directSources);
            }
        }

        // Limpar registros anteriores deste episódio de anime
        AnimeVideo::where('anime_episode_id', $episode->id)->delete();

        if (!empty($sortedSources)) {
            foreach ($sortedSources as $source) {
                AnimeVideo::create([
                    'anime_episode_id' => $episode->id,
                    'server' => 'Servidor',
                    'link' => $source['file'],
                    'lang' => ($source['label'] === 'Legendado') ? 'Legendado' : 'Português',
                    'hls' => $source['hls'],
                    'embed' => $source['embed'],
                    'status' => 1,
                ]);
            }
        } else {
            // Fallback para player Web apenas se nenhuma fonte direta for encontrada
            AnimeVideo::create([
                'anime_episode_id' => $episode->id,
                'server' => 'Servidor',
                'link' => "https://mgeb.top/embed/{$tmdbId}/{$seasonNumber}/{$episodeNumber}",
                'lang' => 'Português',
                'hls' => 0,
                'embed' => 1,
                'status' => 1,
            ]);
        }
    }

    /**
     * Refresh streams for all movies, series, and animes that have TMDb IDs.
     */
    public function refreshAllExistingStreams($type = 'all')
    {
        $updated = ['movies' => 0, 'episodes' => 0, 'animes' => 0];

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

        if ($type === 'anime' || $type === 'all') {
            $animeEpisodes = AnimeEpisode::with('season.anime')
                ->whereHas('season.anime', function ($q) {
                    $q->whereNotNull('tmdb_id');
                })
                ->get();

            foreach ($animeEpisodes as $episode) {
                $season = $episode->season;
                $anime = $season ? $season->anime : null;
                if ($anime && $anime->tmdb_id && $season->season_number && $episode->episode_number) {
                    $this->attachAnimeEpisodeStreams($episode, $anime->tmdb_id, $season->season_number, $episode->episode_number);
                    $updated['animes']++;
                }
            }
        }

        return $updated;
    }

    /**
     * Get trending movies, series or all from TMDb.
     */
    public function getTrending($window = 'day', $mediaType = 'all')
    {
        $window = in_array($window, ['day', 'week']) ? $window : 'day';
        $mediaType = in_array($mediaType, ['all', 'movie', 'tv']) ? $mediaType : 'all';
        $cacheKey = "tmdb_trending_{$mediaType}_{$window}";

        return Cache::remember($cacheKey, 1800, function () use ($window, $mediaType) {
            try {
                $response = $this->client->get("https://api.themoviedb.org/3/trending/{$mediaType}/{$window}", [
                    'query' => [
                        'api_key' => $this->apiKey,
                        'language' => $this->tmdbLang,
                    ]
                ]);
                $data = json_decode($response->getBody()->getContents(), true);
                $results = [];
                if (!empty($data['results'])) {
                    foreach ($data['results'] as $item) {
                        $mType = $item['media_type'] ?? ($mediaType === 'all' ? 'movie' : $mediaType);
                        $title = $item['title'] ?? $item['name'] ?? 'Sem título';
                        $origTitle = $item['original_title'] ?? $item['original_name'] ?? $title;
                        $results[] = [
                            'tmdb_id' => $item['id'],
                            'title' => $title,
                            'original_title' => $origTitle,
                            'media_type' => $mType,
                            'poster_path' => !empty($item['poster_path']) ? 'https://image.tmdb.org/t/p/w342' . $item['poster_path'] : null,
                            'backdrop_path' => !empty($item['backdrop_path']) ? 'https://image.tmdb.org/t/p/w780' . $item['backdrop_path'] : null,
                            'vote_average' => round($item['vote_average'] ?? 0, 1),
                            'release_date' => $item['release_date'] ?? $item['first_air_date'] ?? '',
                            'overview' => $item['overview'] ?? '',
                        ];
                    }
                }
                return $results;
            } catch (\Throwable $e) {
                Log::error("MegaEmbedService::getTrending error: " . $e->getMessage());
                return [];
            }
        });
    }

    /**
     * Inspect and validate streams (direct NixPlay MP4 and Embed player) for a title.
     */
    public function inspectStream($tmdbId, $type = 'movie', $season = 1, $episode = 1)
    {
        $tmdbId = (int)$tmdbId;
        $season = max(1, (int)$season);
        $episode = max(1, (int)$episode);

        // Standard MegaEmbed URLs
        $embedDomain = 'mgeb.top';
        $embedUrl = ($type === 'movie') 
            ? "https://{$embedDomain}/embed/{$tmdbId}"
            : "https://{$embedDomain}/embed/{$tmdbId}/{$season}/{$episode}";

        // Extract sources from MegaEmbed/NixPlay
        $sources = [];
        try {
            if ($type === 'movie') {
                $rawSources = $this->fetchSources("https://mgeb.top/embed/{$tmdbId}");
            } else {
                $rawSources = $this->fetchSources("https://mgeb.top/embed/{$tmdbId}/{$season}/{$episode}");
            }
            $sources = $this->normalizeSources($rawSources);
        } catch (\Throwable $e) {
            Log::warning("inspectStream fetchSources error: " . $e->getMessage());
        }

        // Direct stream testing (NixPlay / CDN)
        $directStreams = [];
        if (!empty($sources)) {
            foreach ($sources as $src) {
                $url = $src['file'] ?? '';
                if (!empty($url)) {
                    $isDirect = ($src['embed'] === 0);
                    $directStreams[] = [
                        'url' => $url,
                        'label' => $src['label'] ?: ($isDirect ? 'Direct MP4' : 'Embed Player'),
                        'is_direct' => $isDirect,
                        'hls' => $src['hls'] ?? 0
                    ];
                }
            }
        }

        // Always include default player embed
        $directStreams[] = [
            'url' => $embedUrl,
            'label' => 'MegaEmbed Player (Fallback)',
            'is_direct' => false,
            'hls' => 0
        ];

        return [
            'tmdb_id' => $tmdbId,
            'type' => $type,
            'season' => $season,
            'episode' => $episode,
            'embed_url' => $embedUrl,
            'embed_iframe' => "<iframe src=\"{$embedUrl}\" width=\"100%\" height=\"100%\" frameborder=\"0\" allowfullscreen loading=\"eager\"></iframe>",
            'streams' => $directStreams,
            'total_streams' => count($directStreams)
        ];
    }

    /**
     * Get MegaEmbed IPTV & Xtream API details.
     */
    public function getIptvInfo()
    {
        return [
            'host' => 'http://megaembed.top',
            'port' => '80',
            'username' => 'anome242@gmail.com',
            'password' => 'Anome123456',
            'api_url' => 'http://megaembed.top/player_api.php',
            'm3u_live_url' => 'http://megaembed.top/get.php?username=anome242@gmail.com&password=Anome123456&type=m3u_plus&output=ts',
            'm3u_movie_url' => 'http://megaembed.top/get.php?username=anome242@gmail.com&password=Anome123456&type=m3u_plus&output=ts&vod=1',
            'm3u_series_url' => 'http://megaembed.top/get.php?username=anome242@gmail.com&password=Anome123456&type=m3u_plus&output=ts&series=1',
            'epg_url' => 'http://megaembed.top/xmltv.php?username=anome242@gmail.com&password=Anome123456'
        ];
    }
}


