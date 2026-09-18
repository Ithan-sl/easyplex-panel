<template>
  <div class="row">
    <!-- Header -->
    <div class="col-md-12 grid-margin">
      <div class="d-flex justify-content-between flex-wrap align-items-center">
        <div>
          <h4 class="font-weight-bold mb-1 text-primary">
            <i class="mdi mdi-cloud-download mr-1"></i> MegaEmbed & TMDb Auto-Importer
          </h4>
          <p class="text-muted mb-0 font-weight-medium">
            Sincronize filmes e séries automaticamente com dublagem (pt-BR) e legendas via MegaEmbed e TheMovieDB.
          </p>
        </div>
        <div class="mt-2 mt-md-0">
          <button @click="loadStats" class="btn btn-outline-primary btn-sm" :disabled="loadingStats">
            <i class="mdi mdi-refresh" :class="{ 'mdi-spin': loadingStats }"></i> Atualizar Estatísticas
          </button>
        </div>
      </div>
    </div>

    <!-- Stats Cards -->
    <div class="col-md-3 grid-margin stretch-card">
      <div class="card bg-gradient-primary text-white">
        <div class="card-body">
          <h6 class="font-weight-normal mb-1">Filmes no MegaEmbed</h6>
          <h3 class="font-weight-bold mb-2">{{ stats.megaembed_movies_total || '...' }}</h3>
          <p class="mb-0 text-white-50 font-size-sm">
            Importados: <strong>{{ stats.movies_imported_count || 0 }}</strong> | Pendentes: <strong>{{ stats.movies_pending_count || 0 }}</strong>
          </p>
        </div>
      </div>
    </div>

    <div class="col-md-3 grid-margin stretch-card">
      <div class="card bg-gradient-success text-white">
        <div class="card-body">
          <h6 class="font-weight-normal mb-1">Séries no MegaEmbed</h6>
          <h3 class="font-weight-bold mb-2">{{ stats.megaembed_series_total || '...' }}</h3>
          <p class="mb-0 text-white-50 font-size-sm">
            Importadas: <strong>{{ stats.series_imported_count || 0 }}</strong> | Pendentes: <strong>{{ stats.series_pending_count || 0 }}</strong>
          </p>
        </div>
      </div>
    </div>

    <div class="col-md-3 grid-margin stretch-card">
      <div class="card bg-gradient-info text-white">
        <div class="card-body">
          <h6 class="font-weight-normal mb-1">Filmes no Catálogo Local</h6>
          <h3 class="font-weight-bold mb-2">{{ stats.local_movies_total || 0 }}</h3>
          <p class="mb-0 text-white-50 font-size-sm">Disponíveis no EasyPlex</p>
        </div>
      </div>
    </div>

    <div class="col-md-3 grid-margin stretch-card">
      <div class="card bg-gradient-warning text-white">
        <div class="card-body">
          <h6 class="font-weight-normal mb-1">Séries no Catálogo Local</h6>
          <h3 class="font-weight-bold mb-2">{{ stats.local_series_total || 0 }}</h3>
          <p class="mb-0 text-white-50 font-size-sm">Disponíveis no EasyPlex</p>
        </div>
      </div>
    </div>

    <!-- Main Navigation Tabs -->
    <div class="col-12 grid-margin stretch-card">
      <div class="card">
        <div class="card-body">
          <ul class="nav nav-tabs mb-4" role="tablist">
            <li class="nav-item">
              <a
                class="nav-link"
                :class="{ active: activeTab === 'batch' }"
                href="javascript:void(0)"
                @click="activeTab = 'batch'"
              >
                <i class="mdi mdi-buffer mr-1"></i> Importação em Lote (Automática)
              </a>
            </li>
            <li class="nav-item">
              <a
                class="nav-link"
                :class="{ active: activeTab === 'search' }"
                href="javascript:void(0)"
                @click="activeTab = 'search'"
              >
                <i class="mdi mdi-magnify mr-1"></i> Busca TMDb & Importação Rápida
              </a>
            </li>
            <li class="nav-item">
              <a
                class="nav-link"
                :class="{ active: activeTab === 'cli' }"
                href="javascript:void(0)"
                @click="activeTab = 'cli'"
              >
                <i class="mdi mdi-console mr-1"></i> Comandos de Terminal (CLI / Cron)
              </a>
            </li>
          </ul>

          <!-- TAB 1: BATCH IMPORT -->
          <div v-show="activeTab === 'batch'">
            <div class="row">
              <div class="col-lg-5 col-md-12">
                <div class="card border p-3 mb-3">
                  <h5 class="card-title font-weight-bold mb-3">Configurar Lote de Importação</h5>

                  <div class="form-group">
                    <label class="font-weight-bold">Tipo de Conteúdo:</label>
                    <select v-model="batchForm.type" class="form-control" :disabled="batchRunning">
                      <option value="movie">Filmes</option>
                      <option value="series">Séries</option>
                    </select>
                  </div>

                  <div class="form-group">
                    <label class="font-weight-bold">Quantidade a Importar:</label>
                    <select v-model="batchForm.limit" class="form-control" :disabled="batchRunning">
                      <option :value="5">5 itens (Teste Rápido)</option>
                      <option :value="10">10 itens</option>
                      <option :value="25">25 itens</option>
                      <option :value="50">50 itens</option>
                      <option :value="100">100 itens</option>
                      <option :value="250">250 itens</option>
                      <option :value="500">500 itens</option>
                    </select>
                  </div>

                  <div class="form-group">
                    <label class="font-weight-bold">Tamanho do Lote por Requisição (Chunk):</label>
                    <select v-model="batchForm.chunkSize" class="form-control" :disabled="batchRunning">
                      <option :value="3">3 por requisição (Conexões lentas)</option>
                      <option :value="5">5 por requisição (Recomendado)</option>
                      <option :value="10">10 por requisição (Rápido)</option>
                    </select>
                    <small class="form-text text-muted">
                      Evita timeouts de gateway (504/502) no PHP processando em pequenos lotes assíncronos.
                    </small>
                  </div>

                  <div class="form-group form-check">
                    <input
                      type="checkbox"
                      class="form-check-input"
                      id="overwriteCheck"
                      v-model="batchForm.overwrite"
                      :disabled="batchRunning"
                    />
                    <label class="form-check-label font-weight-bold" for="overwriteCheck">
                      Sobrescrever registros existentes
                    </label>
                    <small class="form-text text-muted">
                      Desmarcado: ignora filmes/séries já importados e apenas atualiza links de stream ausentes.
                    </small>
                  </div>

                  <div class="mt-4">
                    <button
                      v-if="!batchRunning"
                      @click="startBatchImport"
                      class="btn btn-success btn-block font-weight-bold"
                    >
                      <i class="mdi mdi-play mr-1"></i> Iniciar Importação
                    </button>
                    <button
                      v-else
                      @click="stopBatchImport"
                      class="btn btn-danger btn-block font-weight-bold"
                    >
                      <i class="mdi mdi-stop mr-1"></i> Interromper Importação
                    </button>
                  </div>
                </div>
              </div>

              <div class="col-lg-7 col-md-12">
                <div class="card border p-3">
                  <h5 class="card-title font-weight-bold mb-3 d-flex justify-content-between">
                    <span>Progresso em Tempo Real</span>
                    <span v-if="batchRunning" class="badge badge-success">Em execução...</span>
                    <span v-else-if="batchProgress.completed" class="badge badge-primary">Concluído</span>
                    <span v-else class="badge badge-secondary">Parado</span>
                  </h5>

                  <!-- Progress Bar -->
                  <div class="mb-2">
                    <div class="d-flex justify-content-between font-weight-bold mb-1">
                      <span>Progresso: {{ batchProgress.current }} / {{ batchProgress.target }}</span>
                      <span>{{ batchProgressPercent }}%</span>
                    </div>
                    <div class="progress" style="height: 22px;">
                      <div
                        class="progress-bar progress-bar-striped progress-bar-animated bg-success"
                        role="progressbar"
                        :style="{ width: batchProgressPercent + '%' }"
                        :aria-valuenow="batchProgressPercent"
                        aria-valuemin="0"
                        aria-valuemax="100"
                      >
                        {{ batchProgressPercent }}%
                      </div>
                    </div>
                  </div>

                  <p class="text-muted font-size-sm mt-2 mb-3">
                    <strong>Status Atual:</strong> {{ batchStatusText }}
                  </p>

                  <!-- Live Logs Box -->
                  <div class="d-flex justify-content-between align-items-center mb-1">
                    <label class="font-weight-bold mb-0">Logs da Operação:</label>
                    <button
                      @click="batchLogs = []"
                      class="btn btn-link btn-sm p-0 text-muted"
                      :disabled="!batchLogs.length"
                    >
                      Limpar logs
                    </button>
                  </div>
                  <div
                    class="bg-dark text-light p-3 rounded"
                    style="height: 250px; overflow-y: auto; font-family: monospace; font-size: 0.85rem;"
                    ref="logBox"
                  >
                    <div v-if="!batchLogs.length" class="text-muted">
                      Nenhuma atividade no momento. Clique em "Iniciar Importação" para começar.
                    </div>
                    <div v-for="(log, idx) in batchLogs" :key="idx" :class="log.color">
                      [{{ log.time }}] {{ log.text }}
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- TAB 2: SEARCH & SINGLE IMPORT -->
          <div v-show="activeTab === 'search'">
            <div class="row mb-3">
              <div class="col-md-8">
                <div class="input-group">
                  <input
                    v-model="searchQuery"
                    @keyup.enter="performSearch"
                    type="text"
                    class="form-control"
                    placeholder="Digite o nome do filme, série ou ID TMDb direto (ex: Deadpool, 533535, Breaking Bad)"
                  />
                  <div class="input-group-append">
                    <button
                      @click="performSearch"
                      class="btn btn-primary font-weight-bold"
                      :disabled="searching"
                    >
                      <i class="mdi mdi-magnify mr-1" :class="{ 'mdi-spin': searching }"></i> Buscar
                    </button>
                  </div>
                </div>
              </div>
              <div class="col-md-4">
                <select v-model="searchType" class="form-control" @change="performSearch">
                  <option value="multi">Todos (Filmes e Séries)</option>
                  <option value="movie">Apenas Filmes</option>
                  <option value="tv">Apenas Séries</option>
                </select>
              </div>
            </div>

            <!-- Search Results -->
            <div v-if="searching" class="text-center py-5">
              <div class="spinner-border text-primary" role="status">
                <span class="sr-only">Buscando...</span>
              </div>
              <p class="text-muted mt-2">Buscando na API do TheMovieDB em Português...</p>
            </div>

            <div v-else-if="searchResults && searchResults.length" class="row">
              <div
                v-for="item in searchResults"
                :key="item.id"
                class="col-md-6 col-lg-4 mb-4"
              >
                <div class="card border h-100 shadow-sm">
                  <div class="row no-gutters h-100">
                    <div class="col-4 bg-light text-center d-flex align-items-center justify-content-center">
                      <img
                        v-if="item.poster_path"
                        :src="'http://image.tmdb.org/t/p/w200' + item.poster_path"
                        class="img-fluid rounded-left"
                        style="max-height: 180px; object-fit: cover;"
                        alt="Poster"
                      />
                      <i v-else class="mdi mdi-image-broken text-muted" style="font-size: 3rem;"></i>
                    </div>
                    <div class="col-8 d-flex flex-column justify-content-between p-2">
                      <div>
                        <div class="d-flex justify-content-between align-items-start">
                          <span
                            class="badge"
                            :class="item.media_type === 'tv' || item.first_air_date ? 'badge-info' : 'badge-primary'"
                          >
                            {{ item.media_type === 'tv' || item.first_air_date ? 'SÉRIE' : 'FILME' }}
                          </span>
                          <span class="badge badge-secondary">TMDb: #{{ item.id }}</span>
                        </div>
                        <h6 class="font-weight-bold mt-2 mb-1 text-truncate" :title="item.title || item.name">
                          {{ item.title || item.name }}
                        </h6>
                        <small class="text-muted d-block mb-1">
                          Lançamento: {{ item.release_date || item.first_air_date || 'N/A' }} | ★ {{ item.vote_average || '0' }}
                        </small>
                        <p
                          class="text-muted font-size-sm mb-2"
                          style="display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden;"
                        >
                          {{ item.overview || 'Sem sinopse em português disponível.' }}
                        </p>
                      </div>

                      <button
                        @click="importItem(item)"
                        class="btn btn-success btn-sm btn-block font-weight-bold"
                        :disabled="item.importing"
                      >
                        <span v-if="item.importing">
                          <i class="mdi mdi-spin mdi-loading mr-1"></i> Importando...
                        </span>
                        <span v-else-if="item.imported">
                          <i class="mdi mdi-check mr-1"></i> Importado!
                        </span>
                        <span v-else>
                          <i class="mdi mdi-download mr-1"></i> Importar Agora
                        </span>
                      </button>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div v-else-if="searchPerformed" class="text-center py-5">
              <i class="mdi mdi-emoticon-sad-outline text-muted" style="font-size: 3rem;"></i>
              <h5 class="text-muted mt-2">Nenhum resultado encontrado para "{{ searchQuery }}".</h5>
              <p class="text-muted">Tente buscar pelo nome original ou ID numérico do TheMovieDB.</p>
            </div>
          </div>

          <!-- TAB 3: CLI / CRON GUIDE -->
          <div v-show="activeTab === 'cli'">
            <div class="card border bg-light p-4">
              <h5 class="font-weight-bold text-primary mb-3">
                <i class="mdi mdi-terminal mr-1"></i> Execução Massiva em Segundo Plano (CLI & Cron)
              </h5>
              <p class="text-muted">
                Para importar milhares de filmes e séries sem depender da janela do navegador aberta, você pode executar o comando oficial do EasyPlex no terminal do servidor ou agendá-lo no cron:
              </p>

              <h6 class="font-weight-bold mt-4">1. Importação Rápida de Filmes:</h6>
              <div class="bg-dark text-white p-3 rounded mb-3">
                <code>php artisan megaembed:import --type=movie --limit=50</code>
              </div>

              <h6 class="font-weight-bold mt-3">2. Importação Rápida de Séries (com temporadas e episódios):</h6>
              <div class="bg-dark text-white p-3 rounded mb-3">
                <code>php artisan megaembed:import --type=series --limit=25</code>
              </div>

              <h6 class="font-weight-bold mt-3">3. Importar um Filme ou Série Específico por ID TMDb:</h6>
              <div class="bg-dark text-white p-3 rounded mb-3">
                <code>php artisan megaembed:import --id=533535 --type=movie</code>
              </div>

              <h6 class="font-weight-bold mt-3">4. Importação Massiva Completa (com intervalo entre itens):</h6>
              <div class="bg-dark text-white p-3 rounded mb-3">
                <code>php artisan megaembed:import --type=all --limit=0 --delay=1</code>
              </div>

              <h6 class="font-weight-bold mt-3">5. Agendamento Automático via Cron (Crontab do Servidor):</h6>
              <p class="text-muted font-size-sm">
                Para sincronizar novos lançamentos diariamente às 03:00 da madrugada:
              </p>
              <div class="bg-dark text-white p-3 rounded mb-2">
                <code>0 3 * * * cd /caminho/do/painel && php artisan megaembed:import --type=all --limit=100 >> /dev/null 2>&1</code>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  data() {
    return {
      activeTab: 'batch',
      loadingStats: false,
      stats: {},

      // Batch import state
      batchForm: {
        type: 'movie',
        limit: 25,
        chunkSize: 5,
        overwrite: false,
      },
      batchRunning: false,
      batchProgress: {
        current: 0,
        target: 0,
        completed: false,
      },
      batchOffset: 0,
      batchStatusText: 'Aguardando início...',
      batchLogs: [],

      // Search state
      searchQuery: '',
      searchType: 'multi',
      searching: false,
      searchPerformed: false,
      searchResults: [],
    };
  },
  computed: {
    batchProgressPercent() {
      if (!this.batchProgress.target || this.batchProgress.target === 0) return 0;
      const pct = Math.round((this.batchProgress.current / this.batchProgress.target) * 100);
      return Math.min(100, Math.max(0, pct));
    },
  },
  mounted() {
    this.loadStats();
  },
  methods: {
    async loadStats() {
      this.loadingStats = true;
      try {
        const response = await axios.get(url + '/admin/megaembed/stats');
        if (response.data && response.data.data) {
          this.stats = response.data.data;
        }
      } catch (e) {
        console.error('Erro ao carregar estatísticas:', e);
      } finally {
        this.loadingStats = false;
      }
    },

    addLog(text, type = 'info') {
      const now = new Date().toLocaleTimeString();
      let color = 'text-light';
      if (type === 'success') color = 'text-success';
      if (type === 'error') color = 'text-danger';
      if (type === 'warning') color = 'text-warning';

      this.batchLogs.unshift({ time: now, text, color });
      if (this.batchLogs.length > 200) {
        this.batchLogs.pop();
      }
    },

    async startBatchImport() {
      this.batchRunning = true;
      this.batchProgress.current = 0;
      this.batchProgress.target = this.batchForm.limit;
      this.batchProgress.completed = false;
      this.batchOffset = 0;

      const typeLabel = this.batchForm.type === 'series' ? 'séries' : 'filmes';
      this.addLog(`Iniciando importação de até ${this.batchForm.limit} ${typeLabel}...`, 'info');
      this.batchStatusText = `Conectando ao catálogo de ${typeLabel}...`;

      await this.runNextBatchChunk();
    },

    async runNextBatchChunk() {
      if (!this.batchRunning) return;

      const remaining = this.batchProgress.target - this.batchProgress.current;
      if (remaining <= 0) {
        this.finishBatch();
        return;
      }

      const limit = Math.min(this.batchForm.chunkSize, remaining);
      this.batchStatusText = `Processando lote de ${limit} ${this.batchForm.type === 'series' ? 'séries' : 'filmes'}...`;

      try {
        const response = await axios.post(url + '/admin/megaembed/import-batch', {
          type: this.batchForm.type,
          offset: this.batchOffset,
          limit: limit,
          overwrite: this.batchForm.overwrite,
        });

        const data = response.data;
        if (data.results && data.results.length) {
          data.results.forEach((item) => {
            if (item.success) {
              const status = item.status === 'already_exists' ? 'Já existente' : 'Importado';
              this.addLog(`✔ [${status}] ${item.title} (TMDb: ${item.tmdb_id})`, item.status === 'already_exists' ? 'warning' : 'success');
            } else {
              this.addLog(`✖ [Erro] TMDb #${item.tmdb_id}: ${item.message}`, 'error');
            }
          });

          this.batchProgress.current += data.processed;
          this.batchOffset = data.next_offset;
        }

        if (!data.has_more || this.batchProgress.current >= this.batchProgress.target) {
          this.finishBatch();
          return;
        }

        // Small delay to keep UI reactive
        setTimeout(() => {
          this.runNextBatchChunk();
        }, 500);
      } catch (e) {
        const errMsg = e.response && e.response.data ? e.response.data.message : e.message;
        this.addLog(`Erro na requisição do lote: ${errMsg}`, 'error');
        this.stopBatchImport();
      }
    },

    stopBatchImport() {
      this.batchRunning = false;
      this.batchStatusText = 'Importação interrompida pelo usuário.';
      this.addLog('Importação interrompida.', 'warning');
      this.loadStats();
    },

    finishBatch() {
      this.batchRunning = false;
      this.batchProgress.completed = true;
      this.batchStatusText = `Concluído! ${this.batchProgress.current} itens processados.`;
      this.addLog(`Finalizado com sucesso! ${this.batchProgress.current} itens processados.`, 'success');
      this.loadStats();
    },

    async performSearch() {
      const q = this.searchQuery.trim();
      if (!q) return;

      this.searching = true;
      this.searchPerformed = true;

      // Check if user entered a direct numeric TMDb ID
      if (/^\d+$/.test(q)) {
        try {
          const type = this.searchType === 'tv' ? 'tv' : 'movie';
          const resp = await axios.get(`https://api.themoviedb.org/3/${type}/${q}?api_key=${this.stats.tmdb_api_key || '9c31b3aeb2e59aa2caf74c745ce15887'}&language=pt-BR`);
          if (resp.data) {
            resp.data.media_type = type;
            this.searchResults = [resp.data];
            this.searching = false;
            return;
          }
        } catch (err) {
          // If not found as single ID, proceed with text search
        }
      }

      try {
        const response = await axios.get(url + '/admin/megaembed/search', {
          params: { q, type: this.searchType },
        });
        this.searchResults = (response.data.results || []).map((item) => ({
          ...item,
          importing: false,
          imported: false,
        }));
      } catch (e) {
        console.error('Erro na busca TMDb:', e);
        this.searchResults = [];
      } finally {
        this.searching = false;
      }
    },

    async importItem(item) {
      this.$set(item, 'importing', true);
      const isSeries = item.media_type === 'tv' || !!item.first_air_date;
      const type = isSeries ? 'series' : 'movie';

      try {
        const response = await axios.post(url + '/admin/megaembed/import-single', {
          tmdb_id: item.id,
          type: type,
          overwrite: true,
        });

        if (response.data.success) {
          this.$set(item, 'imported', true);
          if (this.showSuccess) {
            this.showSuccess(response.data.message || 'Importado com sucesso!');
          }
          this.loadStats();
        } else {
          if (this.showError) {
            this.showError(response.data.message || 'Erro ao importar.');
          }
        }
      } catch (e) {
        const msg = e.response && e.response.data ? e.response.data.message : e.message;
        if (this.showError) {
          this.showError(msg);
        }
      } finally {
        this.$set(item, 'importing', false);
      }
    },
  },
};
</script>
