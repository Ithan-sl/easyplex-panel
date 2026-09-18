<?php

namespace App\Http\Controllers;

use App\Services\MegaEmbedService;
use Illuminate\Http\Request;

class MegaEmbedController extends Controller
{
    protected $service;

    public function __construct(MegaEmbedService $service)
    {
        $this->service = $service;
    }

    /**
     * Show the MegaEmbed Management Page in Admin Panel.
     */
    public function index()
    {
        return view('admin.megaembed');
    }

    /**
     * Get statistics comparing MegaEmbed catalog with local database.
     */
    public function stats()
    {
        try {
            $stats = $this->service->getStats();
            return response()->json([
                'status' => 200,
                'data' => $stats
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => 500,
                'message' => 'Erro ao obter estatísticas: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Search movies and series on TMDb.
     */
    public function search(Request $request)
    {
        $query = trim($request->input('q', ''));
        $type = $request->input('type', 'multi');

        if (empty($query)) {
            return response()->json(['status' => 200, 'results' => []]);
        }

        try {
            $results = $this->service->searchTmdb($query, $type);
            return response()->json([
                'status' => 200,
                'results' => $results
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => 500,
                'message' => 'Erro na busca: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Import a single Movie or Series by TMDb ID.
     */
    public function importSingle(Request $request)
    {
        $this->validate($request, [
            'tmdb_id' => 'required|integer',
            'type' => 'required|in:movie,series',
            'overwrite' => 'nullable|boolean'
        ]);

        $tmdbId = (int) $request->input('tmdb_id');
        $type = $request->input('type');
        $overwrite = (bool) $request->input('overwrite', false);

        try {
            if ($type === 'series') {
                $result = $this->service->importSeries($tmdbId, $overwrite);
            } else {
                $result = $this->service->importMovie($tmdbId, $overwrite);
            }

            $statusCode = $result['success'] ? 200 : 400;
            return response()->json($result, $statusCode);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao importar: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Import a chunk/batch of items to support non-blocking frontend progress bar.
     */
    public function importBatch(Request $request)
    {
        $this->validate($request, [
            'type' => 'required|in:movie,series',
            'offset' => 'nullable|integer|min:0',
            'limit' => 'nullable|integer|min:1|max:25',
            'overwrite' => 'nullable|boolean'
        ]);

        $type = $request->input('type');
        $offset = (int) $request->input('offset', 0);
        $limit = (int) $request->input('limit', 5);
        $overwrite = (bool) $request->input('overwrite', false);

        try {
            $catalog = $type === 'series'
                ? $this->service->fetchMegaEmbedSeriesList()
                : $this->service->fetchMegaEmbedMoviesList();

            $totalCatalog = count($catalog);
            $slice = array_slice($catalog, $offset, $limit);

            $results = [];
            foreach ($slice as $tmdbId) {
                if ($type === 'series') {
                    $res = $this->service->importSeries($tmdbId, $overwrite);
                } else {
                    $res = $this->service->importMovie($tmdbId, $overwrite);
                }
                $results[] = [
                    'tmdb_id' => $tmdbId,
                    'success' => $res['success'],
                    'status' => $res['status'] ?? 'unknown',
                    'title' => $res['title'] ?? ('TMDb #' . $tmdbId),
                    'message' => $res['message'] ?? ''
                ];
            }

            $processedCount = count($slice);
            $nextOffset = $offset + $processedCount;
            $hasMore = $nextOffset < $totalCatalog;

            return response()->json([
                'status' => 200,
                'type' => $type,
                'offset' => $offset,
                'next_offset' => $nextOffset,
                'total' => $totalCatalog,
                'processed' => $processedCount,
                'has_more' => $hasMore,
                'results' => $results
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => 500,
                'message' => 'Erro durante processamento em lote: ' . $e->getMessage()
            ], 500);
        }
    }
}
