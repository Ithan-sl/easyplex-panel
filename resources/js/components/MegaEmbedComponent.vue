<template>
  <div class="row">
    <!-- Header -->
    <div class="col-md-12 grid-margin">
      <div class="d-flex justify-content-between flex-wrap align-items-center">
        <div>
          <h4 class="font-weight-bold mb-1 text-primary">
            <i class="mdi mdi-play-network mr-1"></i> MegaEmbed Studio & Suite
          </h4>
          <p class="text-muted mb-0 font-weight-medium">
            Player em tempo real, inspetor de streams diretos, gerador de API, tendências TMDb, IPTV Xtream e sincronização do catálogo.
          </p>
        </div>
        <div class="mt-2 mt-md-0">
          <button @click="loadStats" class="btn btn-outline-primary btn-sm mr-2" :disabled="loadingStats">
            <i class="mdi mdi-refresh" :class="{ 'mdi-spin': loadingStats }"></i> Estatísticas
          </button>
          <a href="https://megaembed.com/moderador/dashboard" target="_blank" class="btn btn-outline-success btn-sm">
            <i class="mdi mdi-open-in-new mr-1"></i> MegaEmbed Oficial
          </a>
        </div>
      </div>
    </div>

    <!-- Stats Cards -->
    <div class="col-md-2 col-sm-6 grid-margin stretch-card">
      <div class="card bg-gradient-primary text-white shadow-sm">
        <div class="card-body p-3">
          <h6 class="font-weight-normal mb-1">Filmes MegaEmbed</h6>
          <h4 class="font-weight-bold mb-1">{{ stats.megaembed_movies_total || '...' }}</h4>
          <p class="mb-0 text-white-50 font-size-xs">
            Imp: <strong>{{ stats.movies_imported_count || 0 }}</strong> | Pend: <strong>{{ stats.movies_pending_count || 0 }}</strong>
          </p>
        </div>
      </div>
    </div>

    <div class="col-md-2 col-sm-6 grid-margin stretch-card">
      <div class="card bg-gradient-success text-white shadow-sm">
        <div class="card-body p-3">
          <h6 class="font-weight-normal mb-1">Séries MegaEmbed</h6>
          <h4 class="font-weight-bold mb-1">{{ stats.megaembed_series_total || '...' }}</h4>
          <p class="mb-0 text-white-50 font-size-xs">
            Imp: <strong>{{ stats.series_imported_count || 0 }}</strong> | Pend: <strong>{{ stats.series_pending_count || 0 }}</strong>
          </p>
        </div>
      </div>
    </div>

    <div class="col-md-2 col-sm-6 grid-margin stretch-card">
      <div class="card bg-gradient-danger text-white shadow-sm">
        <div class="card-body p-3">
          <h6 class="font-weight-normal mb-1">Animes MegaEmbed</h6>
          <h4 class="font-weight-bold mb-1">{{ stats.megaembed_animes_total || '...' }}</h4>
          <p class="mb-0 text-white-50 font-size-xs">
            Imp: <strong>{{ stats.animes_imported_count || 0 }}</strong> | Pend: <strong>{{ stats.animes_pending_count || 0 }}</strong>
          </p>
        </div>
      </div>
    </div>

    <div class="col-md-2 col-sm-6 grid-margin stretch-card">
      <div class="card bg-gradient-info text-white shadow-sm">
        <div class="card-body p-3">
          <h6 class="font-weight-normal mb-1">Filmes no Painel</h6>
          <h4 class="font-weight-bold mb-1">{{ stats.local_movies_total || 0 }}</h4>
          <p class="mb-0 text-white-50 font-size-xs">Catálogo Local</p>
        </div>
      </div>
    </div>

    <div class="col-md-2 col-sm-6 grid-margin stretch-card">
      <div class="card bg-gradient-warning text-white shadow-sm">
        <div class="card-body p-3">
          <h6 class="font-weight-normal mb-1">Séries no Painel</h6>
          <h4 class="font-weight-bold mb-1">{{ stats.local_series_total || 0 }}</h4>
          <p class="mb-0 text-white-50 font-size-xs">Catálogo Local</p>
        </div>
      </div>
    </div>

    <div class="col-md-2 col-sm-6 grid-margin stretch-card">
      <div class="card bg-gradient-dark text-white shadow-sm">
        <div class="card-body p-3">
          <h6 class="font-weight-normal mb-1">Animes no Painel</h6>
          <h4 class="font-weight-bold mb-1">{{ stats.local_animes_total || 0 }}</h4>
          <p class="mb-0 text-white-50 font-size-xs">Catálogo Local</p>
        </div>
      </div>
    </div>

    <!-- Main Navigation Tabs -->
    <div class="col-12 grid-margin stretch-card">
      <div class="card shadow-sm">
        <div class="card-body">
          <ul class="nav nav-tabs mb-4 flex-wrap" role="tablist">
            <li class="nav-item">
              <a
                class="nav-link font-weight-bold"
                :class="{ active: activeTab === 'player' }"
                href="javascript:void(0)"
                @click="activeTab = 'player'"
              >
                <i class="mdi mdi-play-circle mr-1 text-danger"></i> Player Tester (Ao Vivo)
              </a>
            </li>
            <li class="nav-item">
              <a
                class="nav-link font-weight-bold"
                :class="{ active: activeTab === 'stream_inspect' }"
                href="javascript:void(0)"
                @click="activeTab = 'stream_inspect'"
              >
                <i class="mdi mdi-flash mr-1 text-warning"></i> Inspetor de Streams NixPlay
              </a>
            </li>
            <li class="nav-item">
              <a
                class="nav-link font-weight-bold"
                :class="{ active: activeTab === 'api' }"
                href="javascript:void(0)"
                @click="activeTab = 'api'"
              >
                <i class="mdi mdi-code-tags mr-1 text-info"></i> Documentação & API
              </a>
            </li>
            <li class="nav-item">
              <a
                class="nav-link font-weight-bold"
                :class="{ active: activeTab === 'iptv' }"
                href="javascript:void(0)"
                @click="activeTab = 'iptv'"
              >
                <i class="mdi mdi-television-guide mr-1 text-primary"></i> Painel IPTV Xtream
              </a>
            </li>
            <li class="nav-item">
              <a
                class="nav-link font-weight-bold"
                :class="{ active: activeTab === 'batch' }"
                href="javascript:void(0)"
                @click="activeTab = 'batch'"
              >
                <i class="mdi mdi-buffer mr-1 text-success"></i> Importação em Lote
              </a>
            </li>
            <li class="nav-item">
              <a
                class="nav-link font-weight-bold"
                :class="{ active: activeTab === 'search' }"
                href="javascript:void(0)"
                @click="activeTab = 'search'"
              >
                <i class="mdi mdi-magnify mr-1"></i> Busca TMDb Rápida
              </a>
            </li>
            <li class="nav-item">
              <a
                class="nav-link font-weight-bold"
                :class="{ active: activeTab === 'cli' }"
                href="javascript:void(0)"
                @click="activeTab = 'cli'"
              >
                <i class="mdi mdi-console mr-1 text-secondary"></i> Comandos CLI
              </a>
            </li>
          </ul>

          <!-- TAB 1: PLAYER TESTER & SANDBOX EM TEMPO REAL -->
          <div v-show="activeTab === 'player'">
            <div class="row">
              <!-- Player Settings Form -->
              <div class="col-lg-4 col-md-12 mb-4">
                <div class="card border p-3 h-100">
                  <h5 class="card-title font-weight-bold mb-3 text-primary">
                    <i class="mdi mdi-tune mr-1"></i> Configurações do Player
                  </h5>

                  <div class="form-group">
                    <label class="font-weight-bold">Tipo de Conteúdo:</label>
                    <div class="btn-group btn-group-toggle w-100" data-toggle="buttons">
                      <button
                        type="button"
                        class="btn btn-sm"
                        :class="playerForm.type === 'movie' ? 'btn-primary' : 'btn-outline-primary'"
                        @click="setPlayerType('movie')"
                      >
                        Filme
                      </button>
                      <button
                        type="button"
                        class="btn btn-sm"
                        :class="playerForm.type === 'series' ? 'btn-primary' : 'btn-outline-primary'"
                        @click="setPlayerType('series')"
                      >
                        Série
                      </button>
                      <button
                        type="button"
                        class="btn btn-sm"
                        :class="playerForm.type === 'anime' ? 'btn-primary' : 'btn-outline-primary'"
                        @click="setPlayerType('anime')"
                      >
                        Anime
                      </button>
                    </div>
                  </div>

                  <div class="form-group">
                    <label class="font-weight-bold">ID do TMDb ou IMDb:</label>
                    <div class="input-group">
                      <input
                        type="text"
                        class="form-control"
                        v-model="playerForm.id"
                        placeholder="Ex: 1130022 ou tt7286456"
                        @keydown.enter="playCurrent"
                      />
                      <div class="input-group-append">
                        <button class="btn btn-primary" type="button" @click="playCurrent">
                          <i class="mdi mdi-play"></i> Carregar
                        </button>
                      </div>
                    </div>
                    <small class="text-muted">Exemplos rápidos: 
                      <a href="javascript:void(0)" @click="setExample('1130022', 'movie')">1130022 (Filme)</a> | 
                      <a href="javascript:void(0)" @click="setExample('1396', 'series', 1, 1)">1396 (Breaking Bad)</a> | 
                      <a href="javascript:void(0)" @click="setExample('31910', 'anime', 1, 1)">31910 (Attack on Titan)</a>
                    </small>
                  </div>

                  <div class="row" v-if="playerForm.type !== 'movie'">
                    <div class="col-6">
                      <div class="form-group">
                        <label class="font-weight-bold">Temporada:</label>
                        <input
                          type="number"
                          class="form-control"
                          v-model.number="playerForm.season"
                          min="1"
                          @change="updatePlayerUrl"
                        />
                      </div>
                    </div>
                    <div class="col-6">
                      <div class="form-group">
                        <label class="font-weight-bold">Episódio:</label>
                        <input
                          type="number"
                          class="form-control"
                          v-model.number="playerForm.episode"
                          min="1"
                          @change="updatePlayerUrl"
                        />
                      </div>
                    </div>
                  </div>

                  <div class="form-group">
                    <label class="font-weight-bold">Domínio Embed:</label>
                    <select class="form-control" v-model="playerForm.domain" @change="updatePlayerUrl">
                      <option value="mgeb.top">mgeb.top (Recomendado / CDN Principal)</option>
                      <option value="megaembed.com">megaembed.com (Oficial)</option>
                      <option value="player.megaembed.com">player.megaembed.com (Player Direct)</option>
                    </select>
                  </div>

                  <div class="form-group">
                    <label class="font-weight-bold">Motor do Player (?player=):</label>
                    <select class="form-control" v-model="playerForm.playerEngine" @change="updatePlayerUrl">
                      <option value="megaplay">megaplay (Padrão Completo)</option>
                      <option value="megatube">megatube (YouTube Style)</option>
                      <option value="vidstack">vidstack (Moderno / Leve)</option>
                      <option value="clappr">clappr (Clássico HLS)</option>
                    </select>
                  </div>

                  <div class="form-group">
                    <label class="font-weight-bold">Cor Primária do Player (#color:):</label>
                    <div class="d-flex align-items-center">
                      <input
                        type="color"
                        class="form-control form-control-color mr-2"
                        v-model="playerForm.colorHex"
                        @input="updatePlayerUrl"
                        style="width: 50px; height: 38px; padding: 2px;"
                      />
                      <input
                        type="text"
                        class="form-control"
                        v-model="playerForm.colorHex"
                        @input="updatePlayerUrl"
                        placeholder="#4d83ff"
                      />
                    </div>
                  </div>

                  <div class="mt-3">
                    <button class="btn btn-outline-secondary btn-sm mr-2 mb-2" @click="copyPlayerUrl">
                      <i class="mdi mdi-content-copy"></i> Copiar URL
                    </button>
                    <button class="btn btn-outline-info btn-sm mr-2 mb-2" @click="copyIframeCode">
                      <i class="mdi mdi-code-tags"></i> Copiar Iframe
                    </button>
                    <a :href="currentEmbedUrl" target="_blank" class="btn btn-outline-dark btn-sm mb-2">
                      <i class="mdi mdi-open-in-new"></i> Nova Aba
                    </a>
                  </div>
                </div>
              </div>

              <!-- Live Player Screen -->
              <div class="col-lg-8 col-md-12 mb-4">
                <div class="card bg-dark text-white border-0 shadow-lg h-100">
                  <div class="card-header bg-dark d-flex justify-content-between align-items-center border-bottom border-secondary py-2">
                    <div>
                      <span class="badge badge-danger mr-2">AO VIVO</span>
                      <strong class="font-size-sm">{{ currentEmbedUrl }}</strong>
                    </div>
                    <div>
                      <button class="btn btn-sm btn-outline-light py-0 px-2" @click="reloadPlayer" title="Recarregar Player">
                        <i class="mdi mdi-refresh"></i>
                      </button>
                    </div>
                  </div>
                  <div class="card-body p-0 d-flex align-items-center justify-content-center" style="background: #0b0e14; min-height: 480px; position: relative;">
                    <iframe
                      v-if="currentEmbedUrl"
                      :key="playerKey"
                      :src="currentEmbedUrl"
                      class="w-100 h-100 border-0"
                      style="min-height: 480px; width: 100%;"
                      allow="fullscreen; autoplay; picture-in-picture; encrypted-media"
                      allowfullscreen
                      loading="eager"
                    ></iframe>
                    <div v-else class="text-center p-5 text-muted">
                      <i class="mdi mdi-play-circle-outline mdi-48px"></i>
                      <p class="mt-2">Selecione um título ou digite um ID para testar o player em tempo real.</p>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Real-time Trending Carousel -->
            <div class="row mt-2">
              <div class="col-12">
                <div class="card border">
                  <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                      <div>
                        <h5 class="card-title font-weight-bold mb-0 text-primary">
                          <i class="mdi mdi-fire mr-1 text-danger"></i> Tendências em Tempo Real (Clique para Testar no Player)
                        </h5>
                        <small class="text-muted">Filmes e Séries em alta no TheMovieDB com carregamento instantâneo</small>
                      </div>
                      <div class="btn-group btn-group-toggle" data-toggle="buttons">
                        <button
                          type="button"
                          class="btn btn-sm"
                          :class="trendingWindow === 'day' ? 'btn-primary' : 'btn-outline-primary'"
                          @click="changeTrendingWindow('day')"
                        >
                          Hoje
                        </button>
                        <button
                          type="button"
                          class="btn btn-sm"
                          :class="trendingWindow === 'week' ? 'btn-primary' : 'btn-outline-primary'"
                          @click="changeTrendingWindow('week')"
                        >
                          Esta Semana
                        </button>
                      </div>
                    </div>

                    <div v-if="loadingTrending" class="text-center py-4">
                      <div class="spinner-border text-primary" role="status"></div>
                      <p class="text-muted mt-2">Carregando tendências...</p>
                    </div>

                    <div v-else class="trending-carousel d-flex overflow-auto py-2" style="gap: 15px; scroll-behavior: smooth;">
                      <div
                        v-for="item in trendingList"
                        :key="item.tmdb_id"
                        class="trending-card flex-shrink-0 cursor-pointer text-center"
                        style="width: 140px; cursor: pointer; transition: transform 0.2s;"
                        @click="selectTrending(item)"
                      >
                        <div class="position-relative mb-2">
                          <img
                            :src="item.poster_path || 'https://via.placeholder.com/140x210?text=Sem+Poster'"
                            :alt="item.title"
                            class="rounded shadow-sm"
                            style="width: 140px; height: 210px; object-fit: cover;"
                          />
                          <span class="badge badge-warning position-absolute" style="top: 5px; right: 5px;">
                            ★ {{ item.vote_average }}
                          </span>
                          <span
                            class="badge position-absolute"
                            :class="item.media_type === 'tv' ? 'badge-info' : 'badge-danger'"
                            style="bottom: 5px; left: 5px;"
                          >
                            {{ item.media_type === 'tv' ? 'Série' : 'Filme' }}
                          </span>
                        </div>
                        <p class="font-weight-bold mb-0 text-truncate font-size-xs" :title="item.title">
                          {{ item.title }}
                        </p>
                        <small class="text-muted">{{ (item.release_date || '').substring(0, 4) }}</small>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- TAB 2: INSPETOR DE STREAMS NIXPLAY (DIRETO MP4) -->
          <div v-show="activeTab === 'stream_inspect'">
            <div class="row">
              <div class="col-lg-5 col-md-12 mb-4">
                <div class="card border p-3">
                  <h5 class="card-title font-weight-bold mb-3 text-warning">
                    <i class="mdi mdi-flash mr-1"></i> Inspetor de Streams NixPlay / CDN
                  </h5>
                  <p class="text-muted font-size-sm">
                    Extraia e teste os links diretos `.mp4` permanentes da CDN NixPlay para qualquer título do catálogo.
                  </p>

                  <div class="form-group">
                    <label class="font-weight-bold">Tipo:</label>
                    <select v-model="inspectForm.type" class="form-control">
                      <option value="movie">Filme</option>
                      <option value="series">Série</option>
                      <option value="anime">Anime</option>
                    </select>
                  </div>

                  <div class="form-group">
                    <label class="font-weight-bold">TMDb ID:</label>
                    <input
                      type="number"
                      v-model.number="inspectForm.tmdb_id"
                      class="form-control"
                      placeholder="Ex: 1396"
                      @keydown.enter="runInspect"
                    />
                  </div>

                  <div class="row" v-if="inspectForm.type !== 'movie'">
                    <div class="col-6">
                      <div class="form-group">
                        <label class="font-weight-bold">Temporada:</label>
                        <input type="number" v-model.number="inspectForm.season" class="form-control" min="1" />
                      </div>
                    </div>
                    <div class="col-6">
                      <div class="form-group">
                        <label class="font-weight-bold">Episódio:</label>
                        <input type="number" v-model.number="inspectForm.episode" class="form-control" min="1" />
                      </div>
                    </div>
                  </div>

                  <button
                    class="btn btn-warning btn-block font-weight-bold"
                    @click="runInspect"
                    :disabled="inspecting"
                  >
                    <i class="mdi" :class="inspecting ? 'mdi-loading mdi-spin' : 'mdi-magnify'"></i>
                    {{ inspecting ? 'Examinando Fontes...' : 'Inspecionar Streams' }}
                  </button>
                </div>
              </div>

              <div class="col-lg-7 col-md-12 mb-4">
                <div class="card border p-3 h-100">
                  <h5 class="card-title font-weight-bold mb-3 text-primary">
                    <i class="mdi mdi-format-list-bulleted mr-1"></i> Fontes Encontradas
                  </h5>

                  <div v-if="inspectResult && inspectResult.streams">
                    <div class="alert alert-info py-2 mb-3">
                      <strong>TMDb ID:</strong> #{{ inspectResult.tmdb_id }} | 
                      <strong>Tipo:</strong> {{ inspectResult.type }} 
                      <span v-if="inspectResult.type !== 'movie'">
                        (T{{ inspectResult.season }}E{{ inspectResult.episode }})
                      </span>
                    </div>

                    <div class="list-group mb-3">
                      <div
                        v-for="(stream, idx) in inspectResult.streams"
                        :key="idx"
                        class="list-group-item list-group-item-action d-flex justify-content-between align-items-center flex-wrap"
                      >
                        <div class="mr-2 mb-2 mb-md-0" style="max-width: 75%;">
                          <span
                            class="badge mr-1"
                            :class="stream.is_direct ? 'badge-success' : 'badge-secondary'"
                          >
                            {{ stream.is_direct ? 'DIRETO MP4' : 'EMBED' }}
                          </span>
                          <strong>{{ stream.label }}</strong>
                          <p class="mb-0 text-muted font-size-xs text-truncate font-monospace" :title="stream.url">
                            {{ stream.url }}
                          </p>
                        </div>
                        <div>
                          <button
                            class="btn btn-sm btn-outline-primary mr-1"
                            @click="testNativeVideo(stream.url)"
                            title="Testar no player nativo HTML5"
                          >
                            <i class="mdi mdi-play"></i> Testar
                          </button>
                          <button
                            class="btn btn-sm btn-outline-secondary"
                            @click="copyText(stream.url)"
                            title="Copiar URL"
                          >
                            <i class="mdi mdi-content-copy"></i>
                          </button>
                        </div>
                      </div>
                    </div>

                    <!-- Native HTML5 Video Tester -->
                    <div v-if="activeNativeVideo" class="mt-3 p-3 bg-dark rounded text-white">
                      <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="badge badge-success">PLAYER HTML5 NATIVO (EXOPLAYER TEST)</span>
                        <button class="btn btn-sm btn-outline-light py-0" @click="activeNativeVideo = null">
                          <i class="mdi mdi-close"></i>
                        </button>
                      </div>
                      <video
                        :key="activeNativeVideo"
                        :src="activeNativeVideo"
                        controls
                        autoplay
                        class="w-100 rounded"
                        style="max-height: 360px;"
                      ></video>
                    </div>
                  </div>

                  <div v-else class="text-center py-5 text-muted">
                    <i class="mdi mdi-cloud-search-outline mdi-48px"></i>
                    <p class="mt-2">Informe um ID TMDb e clique em "Inspecionar Streams" para verificar as fontes.</p>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- TAB 3: DOCUMENTAÇÃO & GERADOR COMPLETO DA API -->
          <div v-show="activeTab === 'api'">
            <div class="row">
              <div class="col-lg-6 col-md-12 mb-4">
                <div class="card border p-3">
                  <h5 class="card-title font-weight-bold mb-3 text-info">
                    <i class="mdi mdi-book-open-page-variant mr-1"></i> Formatos de URLs de Embed
                  </h5>

                  <div class="table-responsive">
                    <table class="table table-sm table-bordered">
                      <thead class="thead-light">
                        <tr>
                          <th>Conteúdo</th>
                          <th>Estrutura da URL</th>
                          <th>Exemplo</th>
                        </tr>
                      </thead>
                      <tbody>
                        <tr>
                          <td><strong>Filme (TMDb)</strong></td>
                          <td><code>https://mgeb.top/embed/{tmdb_id}</code></td>
                          <td><code>/embed/1130022</code></td>
                        </tr>
                        <tr>
                          <td><strong>Filme (IMDb)</strong></td>
                          <td><code>https://mgeb.top/embed/{imdb_id}</code></td>
                          <td><code>/embed/tt7286456</code></td>
                        </tr>
                        <tr>
                          <td><strong>Série (Padrão)</strong></td>
                          <td><code>https://mgeb.top/embed/{id}/{temporada}/{episodio}</code></td>
                          <td><code>/embed/1396/1/1</code></td>
                        </tr>
                        <tr>
                          <td><strong>Série (Curto)</strong></td>
                          <td><code>https://mgeb.top/embed/{id}-{temporada}-{episodio}</code></td>
                          <td><code>/embed/1396-1-1</code></td>
                        </tr>
                        <tr>
                          <td><strong>Cor Primária</strong></td>
                          <td><code>#color:HEX_OU_NOME</code></td>
                          <td><code>#color:fb542b</code></td>
                        </tr>
                        <tr>
                          <td><strong>Motor Player</strong></td>
                          <td><code>?player=NOME_MOTOR</code></td>
                          <td><code>?player=megaplay</code></td>
                        </tr>
                      </tbody>
                    </table>
                  </div>

                  <h6 class="font-weight-bold mt-4 mb-2 text-primary">Endpoints do Catálogo MegaEmbed (JSON):</h6>
                  <ul class="list-group list-group-flush">
                    <li class="list-group-item px-0 d-flex justify-content-between align-items-center">
                      <div>
                        <strong>Filmes:</strong> <code>https://mgeb.top/api/movie</code>
                        <br><small class="text-muted">Retorna lista com todos os TMDB IDs de filmes disponíveis</small>
                      </div>
                      <a href="https://mgeb.top/api/movie" target="_blank" class="btn btn-sm btn-outline-info">Abrir</a>
                    </li>
                    <li class="list-group-item px-0 d-flex justify-content-between align-items-center">
                      <div>
                        <strong>Séries:</strong> <code>https://mgeb.top/api/series</code>
                        <br><small class="text-muted">Retorna lista com todos os TMDB IDs de séries disponíveis</small>
                      </div>
                      <a href="https://mgeb.top/api/series" target="_blank" class="btn btn-sm btn-outline-info">Abrir</a>
                    </li>
                  </ul>
                </div>
              </div>

              <div class="col-lg-6 col-md-12 mb-4">
                <div class="card border p-3">
                  <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="card-title font-weight-bold mb-0 text-success">
                      <i class="mdi mdi-code-braces mr-1"></i> Exemplos Prontos de Integração
                    </h5>
                    <select v-model="apiLangSnippet" class="form-control form-control-sm" style="width: 140px;">
                      <option value="html">HTML Iframe</option>
                      <option value="js">JavaScript Fetch</option>
                      <option value="php">PHP cURL</option>
                      <option value="python">Python</option>
                    </select>
                  </div>

                  <!-- HTML Snippet -->
                  <div v-show="apiLangSnippet === 'html'">
                    <div class="bg-dark text-white p-3 rounded position-relative">
                      <pre class="text-light mb-0 font-size-xs"><code>&lt;!-- Player Iframe Responsivo 16:9 --&gt;
&lt;div style="position: relative; padding-bottom: 56.25%; height: 0; overflow: hidden;"&gt;
  &lt;iframe 
    src="https://mgeb.top/embed/1130022?player=megaplay#color:fb542b" 
    style="position: absolute; top:0; left: 0; width: 100%; height: 100%; border: 0;" 
    allow="fullscreen; autoplay; picture-in-picture; encrypted-media" 
    allowfullscreen
    loading="eager"&gt;
  &lt;/iframe&gt;
&lt;/div&gt;</code></pre>
                    </div>
                  </div>

                  <!-- JS Snippet -->
                  <div v-show="apiLangSnippet === 'js'">
                    <div class="bg-dark text-white p-3 rounded position-relative">
                      <pre class="text-light mb-0 font-size-xs"><code>// Gerar URL de Embed Dinâmica no Frontend
function getMegaEmbedUrl(tmdbId, season = null, episode = null, player = 'megaplay', color = '#4d83ff') {
  const base = 'https://mgeb.top/embed/';
  let path = tmdbId;
  if (season && episode) {
    path = `${tmdbId}/${season}/${episode}`;
  }
  return `${base}${path}?player=${player}#color:${color.replace('#', '')}`;
}

// Exemplo de uso:
const url = getMegaEmbedUrl(1396, 1, 1);
console.log(url); // https://mgeb.top/embed/1396/1/1?player=megaplay#color:4d83ff</code></pre>
                    </div>
                  </div>

                  <!-- PHP Snippet -->
                  <div v-show="apiLangSnippet === 'php'">
                    <div class="bg-dark text-white p-3 rounded position-relative">
                      <pre class="text-light mb-0 font-size-xs"><code>&lt;?php
// Consultar o Catálogo Oficial de Filmes do MegaEmbed
$url = "https://mgeb.top/api/movie";
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_USERAGENT, "EasyPlex-Client/2.3");
$response = curl_exec($ch);
curl_close($ch);

$movieTmdbIds = json_decode($response, true);
echo "Total de filmes disponíveis: " . count($movieTmdbIds);
?&gt;</code></pre>
                    </div>
                  </div>

                  <!-- Python Snippet -->
                  <div v-show="apiLangSnippet === 'python'">
                    <div class="bg-dark text-white p-3 rounded position-relative">
                      <pre class="text-light mb-0 font-size-xs"><code>import urllib.request, json

# Baixar lista de séries do MegaEmbed
url = "https://mgeb.top/api/series"
req = urllib.request.Request(url, headers={"User-Agent": "EasyPlex/2.3"})
with urllib.request.urlopen(req) as res:
    series_ids = json.loads(res.read().decode())
    print(f"Total de séries no MegaEmbed: {len(series_ids)}")</code></pre>
                    </div>
                  </div>

                  <button class="btn btn-outline-success btn-sm mt-3" @click="copySnippet">
                    <i class="mdi mdi-content-copy"></i> Copiar Código
                  </button>
                </div>
              </div>
            </div>
          </div>

          <!-- TAB 4: PAINEL IPTV & XTREAM -->
          <div v-show="activeTab === 'iptv'">
            <div class="row">
              <div class="col-lg-6 col-md-12 mb-4">
                <div class="card border p-3">
                  <h5 class="card-title font-weight-bold mb-3 text-primary">
                    <i class="mdi mdi-server-network mr-1"></i> Credenciais Xtream API
                  </h5>

                  <div class="table-responsive">
                    <table class="table table-bordered table-sm">
                      <tbody>
                        <tr>
                          <td class="font-weight-bold">Host / Servidor:</td>
                          <td><code>http://megaembed.top</code></td>
                        </tr>
                        <tr>
                          <td class="font-weight-bold">Porta:</td>
                          <td><code>80</code></td>
                        </tr>
                        <tr>
                          <td class="font-weight-bold">Usuário:</td>
                          <td><code>anome242@gmail.com</code></td>
                        </tr>
                        <tr>
                          <td class="font-weight-bold">Senha:</td>
                          <td><code>Anome123456</code></td>
                        </tr>
                        <tr>
                          <td class="font-weight-bold">API Endpoint:</td>
                          <td><code>http://megaembed.top/player_api.php</code></td>
                        </tr>
                      </tbody>
                    </table>
                  </div>

                  <p class="text-muted font-size-sm mt-3 mb-0">
                    Compatível com <strong>IPTV Smarters, XCIPTV, TiviMate</strong> e qualquer aplicativo com suporte à API Xtream Codes.
                  </p>
                </div>
              </div>

              <div class="col-lg-6 col-md-12 mb-4">
                <div class="card border p-3">
                  <h5 class="card-title font-weight-bold mb-3 text-success">
                    <i class="mdi mdi-playlist-play mr-1"></i> Links de Download M3U Plus
                  </h5>

                  <div class="form-group mb-3">
                    <label class="font-weight-bold font-size-sm">Playlist Completa (Canais + Filmes + Séries):</label>
                    <div class="input-group">
                      <input type="text" class="form-control form-control-sm" readonly :value="iptvData.m3u_live_url || 'http://megaembed.top/get.php?username=anome242@gmail.com&password=Anome123456&type=m3u_plus&output=ts'">
                      <div class="input-group-append">
                        <button class="btn btn-sm btn-outline-secondary" @click="copyText(iptvData.m3u_live_url)">Copiar</button>
                      </div>
                    </div>
                  </div>

                  <div class="form-group mb-3">
                    <label class="font-weight-bold font-size-sm">Playlist VOD (Filmes):</label>
                    <div class="input-group">
                      <input type="text" class="form-control form-control-sm" readonly :value="iptvData.m3u_movie_url || 'http://megaembed.top/get.php?username=anome242@gmail.com&password=Anome123456&type=m3u_plus&output=ts&vod=1'">
                      <div class="input-group-append">
                        <button class="btn btn-sm btn-outline-secondary" @click="copyText(iptvData.m3u_movie_url)">Copiar</button>
                      </div>
                    </div>
                  </div>

                  <div class="form-group mb-3">
                    <label class="font-weight-bold font-size-sm">Playlist Séries:</label>
                    <div class="input-group">
                      <input type="text" class="form-control form-control-sm" readonly :value="iptvData.m3u_series_url || 'http://megaembed.top/get.php?username=anome242@gmail.com&password=Anome123456&type=m3u_plus&output=ts&series=1'">
                      <div class="input-group-append">
                        <button class="btn btn-sm btn-outline-secondary" @click="copyText(iptvData.m3u_series_url)">Copiar</button>
                      </div>
                    </div>
                  </div>

                  <div class="form-group mb-0">
                    <label class="font-weight-bold font-size-sm">Guia EPG (XMLTV):</label>
                    <div class="input-group">
                      <input type="text" class="form-control form-control-sm" readonly :value="iptvData.epg_url || 'http://megaembed.top/xmltv.php?username=anome242@gmail.com&password=Anome123456'">
                      <div class="input-group-append">
                        <button class="btn btn-sm btn-outline-secondary" @click="copyText(iptvData.epg_url)">Copiar</button>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- TAB 5: BATCH IMPORT -->
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
                      <option value="anime">Animes</option>
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
                  </div>

                  <div class="form-check mb-3">
                    <label class="form-check-label text-muted">
                      <input type="checkbox" class="form-check-input" v-model="batchForm.overwrite" :disabled="batchRunning">
                      Sobrescrever títulos existentes (atualizar metadados e streams)
                    </label>
                  </div>

                  <div class="mt-4">
                    <button
                      v-if="!batchRunning"
                      @click="startBatchImport"
                      class="btn btn-success btn-block font-weight-bold"
                    >
                      <i class="mdi mdi-play mr-1"></i> Iniciar Importação em Lote
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

              <!-- Batch Progress & Terminal Log -->
              <div class="col-lg-7 col-md-12">
                <div class="card border p-3 mb-3">
                  <h5 class="card-title font-weight-bold mb-2">Progresso do Lote</h5>
                  <p class="text-muted mb-2 font-size-sm">
                    Status: <strong :class="batchRunning ? 'text-primary' : (batchProgress.completed ? 'text-success' : 'text-dark')">{{ batchStatusText }}</strong>
                  </p>

                  <div class="progress progress-lg mb-3" style="height: 22px;">
                    <div
                      class="progress-bar progress-bar-striped font-weight-bold"
                      :class="batchRunning ? 'progress-bar-animated bg-success' : 'bg-primary'"
                      role="progressbar"
                      :style="{ width: batchProgressPercent + '%' }"
                      :aria-valuenow="batchProgressPercent"
                      aria-valuemin="0"
                      aria-valuemax="100"
                    >
                      {{ batchProgress.current }} / {{ batchProgress.target }} ({{ batchProgressPercent }}%)
                    </div>
                  </div>

                  <h6 class="font-weight-bold mb-2">Terminal de Execução (Logs em Tempo Real):</h6>
                  <div
                    class="bg-dark p-3 rounded"
                    style="height: 280px; overflow-y: auto; font-family: monospace; font-size: 13px;"
                  >
                    <div v-if="batchLogs.length === 0" class="text-muted">
                      Nenhum processo iniciado. Clique em "Iniciar Importação em Lote" para começar.
                    </div>
                    <div v-for="(log, idx) in batchLogs" :key="idx" :class="log.color">
                      <span class="text-muted">[{{ log.time }}]</span> {{ log.text }}
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- TAB 6: TMDB SEARCH & INSTANT IMPORT -->
          <div v-show="activeTab === 'search'">
            <div class="row">
              <div class="col-12 mb-3">
                <div class="card border p-3">
                  <h5 class="card-title font-weight-bold mb-3">Pesquisar no TheMovieDB & Importar com Links MegaEmbed</h5>
                  <div class="input-group">
                    <div class="input-group-prepend">
                      <select v-model="searchType" class="custom-select" style="border-top-right-radius: 0; border-bottom-right-radius: 0;">
                        <option value="multi">Tudo (Filmes & Séries)</option>
                        <option value="movie">Apenas Filmes</option>
                        <option value="tv">Apenas Séries / Animes</option>
                      </select>
                    </div>
                    <input
                      type="text"
                      v-model="searchQuery"
                      class="form-control"
                      placeholder="Digite o título (ex: Vingadores, Breaking Bad, Naruto) ou o ID numérico do TMDb..."
                      @keydown.enter="performSearch"
                    />
                    <div class="input-group-append">
                      <button @click="performSearch" class="btn btn-primary" :disabled="searching">
                        <i class="mdi" :class="searching ? 'mdi-loading mdi-spin' : 'mdi-magnify'"></i> Pesquisar
                      </button>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Search Results Grid -->
              <div class="col-12">
                <div v-if="searching" class="text-center py-5">
                  <div class="spinner-border text-primary" role="status"></div>
                  <p class="text-muted mt-2">Buscando no TMDb...</p>
                </div>

                <div v-else-if="searchPerformed && searchResults.length === 0" class="text-center py-5 text-muted">
                  <i class="mdi mdi-alert-circle-outline mdi-48px"></i>
                  <p class="mt-2">Nenhum resultado encontrado no TMDb para esta busca.</p>
                </div>

                <div v-else class="row">
                  <div
                    v-for="item in searchResults"
                    :key="item.id"
                    class="col-xl-3 col-lg-4 col-md-6 col-sm-12 grid-margin stretch-card"
                  >
                    <div class="card border h-100">
                      <div class="position-relative text-center bg-light">
                        <img
                          :src="item.poster_path ? 'https://image.tmdb.org/t/p/w342' + item.poster_path : 'https://via.placeholder.com/342x513?text=Sem+Poster'"
                          class="card-img-top"
                          alt="Poster"
                          style="height: 320px; object-fit: cover;"
                        />
                        <span
                          class="badge position-absolute"
                          :class="item.media_type === 'tv' ? 'badge-info' : 'badge-danger'"
                          style="top: 10px; right: 10px;"
                        >
                          {{ item.media_type === 'tv' ? 'Série' : 'Filme' }}
                        </span>
                      </div>
                      <div class="card-body d-flex flex-column p-3">
                        <h6 class="card-title font-weight-bold mb-1 text-truncate" :title="item.title || item.name">
                          {{ item.title || item.name }}
                        </h6>
                        <small class="text-muted mb-2">
                          {{ (item.release_date || item.first_air_date || '').substring(0, 4) }} | TMDb #{{ item.id }}
                        </small>
                        <p class="card-text text-muted font-size-xs flex-grow-1" style="display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden;">
                          {{ item.overview || 'Sem sinopse em português disponível.' }}
                        </p>
                        <div class="mt-2">
                          <button
                            v-if="!item.imported"
                            @click="importItem(item)"
                            class="btn btn-primary btn-sm btn-block"
                            :disabled="item.importing"
                          >
                            <i class="mdi" :class="item.importing ? 'mdi-loading mdi-spin' : 'mdi-download'"></i>
                            {{ item.importing ? 'Importando...' : 'Importar para o Painel' }}
                          </button>
                          <button v-else class="btn btn-success btn-sm btn-block" disabled>
                            <i class="mdi mdi-check"></i> Importado!
                          </button>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- TAB 7: CLI COMMANDS -->
          <div v-show="activeTab === 'cli'">
            <div class="card border p-4">
              <h5 class="card-title font-weight-bold mb-3">Comandos de Terminal & Cron (Automação em Background)</h5>
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
                <code>0 3 * * * cd /var/www/html && php artisan megaembed:import --type=all --limit=100 >> /dev/null 2>&1</code>
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
      activeTab: 'player',
      loadingStats: false,
      stats: {},

      // Player Tester state
      playerForm: {
        type: 'movie',
        id: '1130022',
        season: 1,
        episode: 1,
        domain: 'mgeb.top',
        playerEngine: 'megaplay',
        colorHex: '#4d83ff',
      },
      currentEmbedUrl: '',
      playerKey: 1,

      // Trending state
      trendingWindow: 'day',
      loadingTrending: false,
      trendingList: [],

      // Stream Inspector state
      inspectForm: {
        type: 'movie',
        tmdb_id: 1130022,
        season: 1,
        episode: 1,
      },
      inspecting: false,
      inspectResult: null,
      activeNativeVideo: null,

      // API Docs state
      apiLangSnippet: 'html',

      // IPTV state
      iptvData: {
        host: 'http://megaembed.top',
        port: '80',
        username: 'anome242@gmail.com',
        password: 'Anome123456',
        api_url: 'http://megaembed.top/player_api.php',
        m3u_live_url: 'http://megaembed.top/get.php?username=anome242@gmail.com&password=Anome123456&type=m3u_plus&output=ts',
        m3u_movie_url: 'http://megaembed.top/get.php?username=anome242@gmail.com&password=Anome123456&type=m3u_plus&output=ts&vod=1',
        m3u_series_url: 'http://megaembed.top/get.php?username=anome242@gmail.com&password=Anome123456&type=m3u_plus&output=ts&series=1',
        epg_url: 'http://megaembed.top/xmltv.php?username=anome242@gmail.com&password=Anome123456'
      },

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
    this.updatePlayerUrl();
    this.loadTrending('day');
    this.loadIptvInfo();
  },
  methods: {
    // STATS & COMMON
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

    copyText(text) {
      if (!text) return;
      navigator.clipboard.writeText(text);
      if (this.showSuccess) {
        this.showSuccess('Copiado para a área de transferência!');
      } else {
        alert('Copiado: ' + text);
      }
    },

    // PLAYER TESTER METHODS
    setPlayerType(t) {
      this.playerForm.type = t;
      if (t === 'series' && this.playerForm.id === '1130022') {
        this.playerForm.id = '1396';
      }
      this.updatePlayerUrl();
    },

    setExample(id, type, s = 1, e = 1) {
      this.playerForm.id = id;
      this.playerForm.type = type;
      this.playerForm.season = s;
      this.playerForm.episode = e;
      this.updatePlayerUrl();
    },

    updatePlayerUrl() {
      const p = this.playerForm;
      const id = (p.id || '').trim();
      if (!id) {
        this.currentEmbedUrl = '';
        return;
      }

      let path = id;
      if (p.type !== 'movie') {
        const s = p.season || 1;
        const e = p.episode || 1;
        path = `${id}/${s}/${e}`;
      }

      const domain = p.domain || 'mgeb.top';
      const engine = p.playerEngine ? `?player=${p.playerEngine}` : '';
      const color = p.colorHex ? `#color:${p.colorHex.replace('#', '')}` : '';

      this.currentEmbedUrl = `https://${domain}/embed/${path}${engine}${color}`;
      this.playerKey++;
    },

    playCurrent() {
      this.updatePlayerUrl();
    },

    reloadPlayer() {
      this.playerKey++;
    },

    copyPlayerUrl() {
      this.copyText(this.currentEmbedUrl);
    },

    copyIframeCode() {
      const code = `<iframe src="${this.currentEmbedUrl}" width="100%" height="100%" frameborder="0" allow="fullscreen; autoplay; picture-in-picture; encrypted-media" allowfullscreen loading="eager"></iframe>`;
      this.copyText(code);
    },

    // TRENDING CAROUSEL
    async loadTrending(window = 'day') {
      this.trendingWindow = window;
      this.loadingTrending = true;
      try {
        const response = await axios.get(url + '/admin/megaembed/trending', {
          params: { window: window, media_type: 'all' }
        });
        this.trendingList = response.data.results || [];
      } catch (e) {
        console.error('Erro ao carregar tendências:', e);
      } finally {
        this.loadingTrending = false;
      }
    },

    changeTrendingWindow(w) {
      this.loadTrending(w);
    },

    selectTrending(item) {
      this.playerForm.id = item.tmdb_id.toString();
      this.playerForm.type = item.media_type === 'tv' ? 'series' : 'movie';
      this.playerForm.season = 1;
      this.playerForm.episode = 1;
      this.updatePlayerUrl();
      window.scrollTo({ top: 150, behavior: 'smooth' });
    },

    // STREAM INSPECTOR (NIXPLAY / MP4)
    async runInspect() {
      const f = this.inspectForm;
      if (!f.tmdb_id) return;

      this.inspecting = true;
      this.activeNativeVideo = null;
      try {
        const response = await axios.get(url + '/admin/megaembed/inspect-stream', {
          params: {
            tmdb_id: f.tmdb_id,
            type: f.type,
            season: f.season || 1,
            episode: f.episode || 1
          }
        });
        this.inspectResult = response.data.data;
      } catch (e) {
        console.error('Erro ao inspecionar stream:', e);
        if (this.showError) this.showError('Erro ao consultar fontes para este título.');
      } finally {
        this.inspecting = false;
      }
    },

    testNativeVideo(streamUrl) {
      this.activeNativeVideo = streamUrl;
    },

    // API SNIPPETS
    copySnippet() {
      let code = '';
      if (this.apiLangSnippet === 'html') {
        code = '<div style="position: relative; padding-bottom: 56.25%; height: 0; overflow: hidden;">\n  <iframe src="https://mgeb.top/embed/1130022?player=megaplay#color:fb542b" style="position: absolute; top:0; left: 0; width: 100%; height: 100%; border: 0;" allow="fullscreen; autoplay; picture-in-picture; encrypted-media" allowfullscreen loading="eager"></iframe>\n</div>';
      } else if (this.apiLangSnippet === 'js') {
        code = 'function getMegaEmbedUrl(tmdbId, season, episode, player, color) {\n  var base = "https://mgeb.top/embed/";\n  var path = tmdbId;\n  if (season && episode) path = tmdbId + "/" + season + "/" + episode;\n  return base + path + "?player=" + (player || "megaplay") + "#color:" + (color || "4d83ff");\n}';
      } else if (this.apiLangSnippet === 'php') {
        code = '<?php\n$url = "https://mgeb.top/api/movie";\n$ch = curl_init($url);\ncurl_setopt($ch, CURLOPT_RETURNTRANSFER, true);\ncurl_setopt($ch, CURLOPT_USERAGENT, "EasyPlex-Client/2.3");\n$response = curl_exec($ch);\ncurl_close($ch);\n$movieTmdbIds = json_decode($response, true);\n?>';
      } else if (this.apiLangSnippet === 'python') {
        code = 'import urllib.request, json\nurl = "https://mgeb.top/api/series"\nreq = urllib.request.Request(url, headers={"User-Agent": "EasyPlex/2.3"})\nwith urllib.request.urlopen(req) as res:\n    series_ids = json.loads(res.read().decode())\n    print(f"Total: {len(series_ids)}")';
      }
      this.copyText(code);
    },

    // IPTV
    async loadIptvInfo() {
      try {
        const response = await axios.get(url + '/admin/megaembed/iptv-info');
        if (response.data && response.data.data) {
          this.iptvData = response.data.data;
        }
      } catch (e) {
        console.error('Erro ao carregar IPTV info:', e);
      }
    },

    // BATCH IMPORT
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

      const typeLabel = this.batchForm.type === 'anime' ? 'animes' : (this.batchForm.type === 'series' ? 'séries' : 'filmes');
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
      const typeLabel = this.batchForm.type === 'anime' ? 'animes' : (this.batchForm.type === 'series' ? 'séries' : 'filmes');
      this.batchStatusText = `Processando lote de ${limit} ${typeLabel}...`;

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

    // TMDB SEARCH & IMPORT
    async performSearch() {
      const q = this.searchQuery.trim();
      if (!q) return;

      this.searching = true;
      this.searchPerformed = true;

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
        } catch (err) {}
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
      const isJapan = (item.origin_country && item.origin_country.includes('JP')) || item.original_language === 'ja';
      const isSeries = item.media_type === 'tv' || !!item.first_air_date;
      const type = (isSeries && isJapan) ? 'anime' : (isSeries ? 'series' : 'movie');

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
