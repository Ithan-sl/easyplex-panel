<?php

namespace App\Console\Commands;

use App\Services\MegaEmbedService;
use Illuminate\Console\Command;

class ImportMegaEmbed extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'megaembed:import
                            {--type=all : Type of content: movie, series, or all}
                            {--limit=50 : Number of items to import (0 for unlimited)}
                            {--offset=0 : Offset index to start from}
                            {--id= : Specific TMDb ID to import}
                            {--overwrite : Overwrite existing records}
                            {--refresh : Refresh and replace streams of existing DB items with direct MegaEmbed sources}
                            {--delay=1 : Seconds to wait between items to prevent rate-limiting}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import movies and series automatically from MegaEmbed and TheMovieDB (pt-BR)';

    /**
     * Execute the console command.
     *
     * @param MegaEmbedService $service
     * @return int
     */
    public function handle(MegaEmbedService $service)
    {
        $type = strtolower($this->option('type') ?? 'all');
        $limit = (int) $this->option('limit');
        $offset = (int) $this->option('offset');
        $specificId = $this->option('id');
        $overwrite = (bool) $this->option('overwrite');
        $refresh = (bool) $this->option('refresh');
        $delay = (int) $this->option('delay');

        $this->info("==================================================");
        $this->info("    MegaEmbed & TheMovieDB Auto-Importer (pt-BR)  ");
        $this->info("==================================================");

        // Refresh existing records with direct sources
        if ($refresh) {
            $this->info("Atualizando streams existentes para fontes diretas (MP4/HLS)...");
            $res = $service->refreshAllExistingStreams($type);
            $this->info("✔ Sucesso! Filmes atualizados: {$res['movies']}, Episódios atualizados: {$res['episodes']}");
            return 0;
        }

        // Single ID import
        if ($specificId) {
            $tmdbId = (int) $specificId;
            if ($type === 'series') {
                $this->info("Importando Série TMDb #{$tmdbId}...");
                $res = $service->importSeries($tmdbId, $overwrite);
            } else {
                $this->info("Importando Filme TMDb #{$tmdbId}...");
                $res = $service->importMovie($tmdbId, $overwrite);
            }

            if ($res['success']) {
                $this->info("✔ " . ($res['message'] ?? 'Importado com sucesso.'));
                return 0;
            } else {
                $this->error("✖ " . ($res['message'] ?? 'Falha na importação.'));
                return 1;
            }
        }

        // Batch Movies Import
        if ($type === 'movie' || $type === 'all') {
            $this->info("\nObtendo catálogo de Filmes do MegaEmbed...");
            $movies = $service->fetchMegaEmbedMoviesList();
            $totalMovies = count($movies);
            $this->info("Total de Filmes disponíveis no MegaEmbed: {$totalMovies}");

            if ($offset > 0) {
                $movies = array_slice($movies, $offset);
            }
            if ($limit > 0) {
                $movies = array_slice($movies, 0, $limit);
            }

            $countToProcess = count($movies);
            $this->info("Processando {$countToProcess} filmes (iniciando do offset {$offset})...\n");

            $bar = $this->output->createProgressBar($countToProcess);
            $bar->start();

            $imported = 0;
            $skipped = 0;
            $failed = 0;

            foreach ($movies as $tmdbId) {
                $res = $service->importMovie($tmdbId, $overwrite);
                if ($res['success']) {
                    if (($res['status'] ?? '') === 'already_exists') {
                        $skipped++;
                    } else {
                        $imported++;
                    }
                } else {
                    $failed++;
                }

                $bar->advance();

                if ($delay > 0) {
                    sleep($delay);
                }
            }

            $bar->finish();
            $this->info("\n");
            $this->info("Filmes finalizados: {$imported} novos importados, {$skipped} já existentes, {$failed} erros.\n");
        }

        // Batch Series Import
        if ($type === 'series' || $type === 'all') {
            $this->info("\nObtendo catálogo de Séries do MegaEmbed...");
            $series = $service->fetchMegaEmbedSeriesList();
            $totalSeries = count($series);
            $this->info("Total de Séries disponíveis no MegaEmbed: {$totalSeries}");

            if ($offset > 0) {
                $series = array_slice($series, $offset);
            }
            if ($limit > 0) {
                $series = array_slice($series, 0, $limit);
            }

            $countToProcess = count($series);
            $this->info("Processando {$countToProcess} séries (iniciando do offset {$offset})...\n");

            $bar = $this->output->createProgressBar($countToProcess);
            $bar->start();

            $imported = 0;
            $skipped = 0;
            $failed = 0;

            foreach ($series as $tmdbId) {
                $res = $service->importSeries($tmdbId, $overwrite);
                if ($res['success']) {
                    if (($res['status'] ?? '') === 'already_exists') {
                        $skipped++;
                    } else {
                        $imported++;
                    }
                } else {
                    $failed++;
                }

                $bar->advance();

                if ($delay > 0) {
                    sleep($delay);
                }
            }

            $bar->finish();
            $this->info("\n");
            $this->info("Séries finalizadas: {$imported} novas importadas, {$skipped} já existentes, {$failed} erros.\n");
        }

        $this->info("Operação concluída com sucesso!");
        return 0;
    }
}
