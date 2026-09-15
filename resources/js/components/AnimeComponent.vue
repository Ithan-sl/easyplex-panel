<template>
  <div class="row">
    <div class="col-md-12 grid-margin">
      <div class="d-flex justify-content-between flex-wrap">
        <div class="d-flex align-items-end flex-wrap">
          <div class="d-flex"></div>
        </div>
        <div class="d-flex justify-content-between align-items-end flex-wrap">
          <button @click="create()" class="btn btn-primary mt-2 mt-xl-0" v-if="index">Add Anime</button>
          <button @click="back()" class="btn btn-primary mt-2 mt-xl-0" v-if="!index">Back</button>
        </div>
      </div>
    </div>


        <div v-if="index" class="col-md-6 grid-margin">
   <ul class="navbar-nav mr-lg-4 w-100">
          <li class="nav-item nav-search d-none d-lg-block w-100">
            <div class="input-group">
              <div class="input-group-prepend">
                <span class="input-group-text" id="search">
                  <i class="mdi mdi-magnify"></i>
                </span>
              </div>
              <input v-model="search" type="text" class="form-control" placeholder="Search now" aria-label="search" aria-describedby="search">
            </div>
          </li>
        </ul>
    </div>

    <div class="col-lg-12 grid-margin stretch-card" v-if="index">
      <div class="card">
        <div class="card-body">
          <div class="table-responsive">
            <table aria-describedby="table animes" class="table" id="table animes">
              <thead>
                <tr>
                  <th class="text-center" id="cover">Cover</th>
                  <th class="text-center" id="id">ID</th>
                  <th class="text-center" id="name">Name</th>
                  <th class="text-center" id="vote">Vote</th>
                  <th class="text-center" id="options">Options</th>
                </tr>
              </thead>
              <tbody>
                <tr :key="index" v-for="(anime, index) in paginated('filteredAnimes')">
                  <td class="text-center">
                    <img :src="anime.poster_path" alt="poster path" height="80" width="80" />
                  </td>
                  <td class="text-center">{{anime.tmdb_id}}</td>
                  <td class="text-center">{{anime.name}}</td>
                  <td class="text-center">{{anime.vote_average}}</td>
                  <td class="text-center">
                    <div class="list-icons">
                      <a
                        @click="editing(anime)"
                        class="list-icons-item mr-2"
                        data-original-title="Edit"
                        rel="tooltip"
                        title
                      >
                        <em class="mdi mdi-pencil fa-lg" style="color:#4d83ff"></em>
                      </a>
                      <a
                        @click="destroy(anime.id, index)"
                        class="list-icons-item text-warning"
                        data-original-title="Delete"
                        rel="tooltip"
                        title
                      >
                        <em class="mdi mdi-delete fa-lg" style="color:red"></em>
                      </a>
                    </div>
                  </td>
                </tr>
              </tbody>
              <paginate
                :list="filteredAnimes"
                :per="5"
                name="filteredAnimes"
                tag="tbody"
                v-if="filteredAnimes.length"
              ></paginate>
            </table>
            <paginate-links
              :async="true"
              :classes="{
                                               'ul': 'pagination',
                                               'li': 'page-item',
                                               'a': 'page-link',
                                               '.next > a': 'next-link',
                                               '.prev > a': 'prev-link'}"
              :hide-single-page="true"
              :limit="5"
              :show-step-links="true"
              class="float-right"
              for="filteredAnimes"
              v-if="filteredAnimes.length"
            ></paginate-links>
          </div>
        </div>
      </div>
    </div>

    <!-- anime Create -->

    <div class="col-lg-12 grid-margin stretch-card" v-if="add || edit">
      <div class="card">
        <div class="card-body">
          <section>
            <div class="poster-container">
              <img
                :src="form.anime.poster_path"
                alt="anime poster"
                class="poster"
                data-loaded="true"
                height="316"
                v-if="form.anime.poster_path"
                width="210"
              />

              <div class="many-inputs">
                <div class="input-container">
          

                  <div v-if="add || edit" class="form-group">
                    <div class="input-group">
                      <input
                        type="text"
                        class="form-control"
                        placeholder="Example : tt7286456"
                        aria-label="Search Movie"
                        v-model="tmdb"
                      />
                      <div class="input-group-append">
                        <button
                          class="btn btn-sm btn-primary"
                          type="type"
                          :disabled="!settings.tmdb_api_key"
                          @click="getanime()"
                        >Search</button>
                      </div>
                    </div>
                  </div>

                  <div class="input-container">
                    <div class="row">
                      <div class="col-md-12">
                        <div class="form-group">
                          <label for="title">Name</label>
                          <input
                            class="form-control"
                            id="title"
                            type="text"
                            v-model="form.anime.name"
                          />
                        </div>
                      </div>
                    </div>
                  </div>

                  <div class="input-container">
                    <div class="row">
                      <div class="col-md-3">
                        <div class="form-group">
                          <div class="custom-control custom-checkbox">
                            <input
                              class="custom-control-input"
                              id="premuim"
                              type="checkbox"
                              v-model="form.anime.premuim"
                            />
                            <label class="custom-control-label" for="premuim">
                              Premuim
                              Only
                            </label>
                          </div>
                        </div>
                      </div>

                    <div v-if="add" class="col-md-3">
                        <div class="form-group">
                          <div class="custom-control custom-checkbox">
                            <input
                              class="custom-control-input"
                              id="notification"
                              type="checkbox"
                              v-model="form.notification"
                            />
                            <label class="custom-control-label" for="notification">Push Notification</label>
                          </div>
                        </div>
                      </div>


                    </div>
                  </div>

                  <div class="input-container">
                    <div class="row">
                      <div class="col-md-6">
                        <div class="form-group">
                          <label for="poster_path">Poster Path</label>
                          <input
                            class="form-control"
                            id="poster_path"
                            name="poster_path"
                            type="text"
                            v-model="form.anime.poster_path"
                          />
                        </div>
                      </div>

                      <div class="col-md-6">
                        <div class="form-group">
                          <label>Poster upload</label>
                          <input class="file-upload-default" />
                          <div class="input-group col-xs-12">
                            <input
                              @change="storePoster"
                              class="form-control file-upload-info"
                              id="poster"
                              placeholder="Upload Image"
                              type="file"
                            />
                            <span class="input-group-append">
                              <button
                                class="file-upload-browse btn btn-primary"
                                type="button"
                              >Upload</button>
                            </span>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>

                  <div class="row">
                    <div class="col-md-12">
                      <div class="form-group">
                        <multiselect
                          :clear-on-select="false"
                          :close-on-select="false"
                          :hideSelected="true"
                          :multiple="true"
                          :options="options"
                          :preserve-search="true"
                          @remove="destroyGenre"
                          label="name"
                          placeholder="Select Genre"
                          track-by="name"
                          v-model="form.anime.genres"
                        ></multiselect>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label for="poster_path">Backdrop Path</label>
                  <input
                    class="form-control"
                    id="backdrop_path"
                    name="backdrop_path"
                    type="text"
                    v-model="form.anime.backdrop_path"
                  />
                </div>
              </div>

              <div class="col-md-6">
                <div class="form-group">
                  <label>Backdrop upload</label>
                  <input class="file-upload-default" />
                  <div class="input-group col-xs-12">
                    <input
                      @change="storeBackdrop"
                      class="form-control file-upload-info"
                      id="backdrop"
                      placeholder="Upload Image"
                      type="file"
                    />
                    <span class="input-group-append">
                      <button class="file-upload-browse btn btn-primary" type="button">Upload</button>
                    </span>
                  </div>
                </div>
              </div>
            </div>

            <div class="row">
              <div class="col-md-12">
                <div class="form-group">
                  <label for="overview">Details</label>
                  <textarea
                    class="form-control pb-3"
                    id="overview"
                    rows="6"
                    type="text"
                    v-model="form.anime.overview"
                  ></textarea>
                </div>
              </div>
            </div>

            <div class="row">
              <div class="col-md-4">
                <div class="form-group">
                  <label for="release_date">Release Date</label>
                  <input
                    class="form-control"
                    id="release_date"
                    type="date"
                    v-model="form.anime.first_air_date"
                  />
                </div>
              </div>

              <div class="col-md-4">
                <div class="form-group">
                  <label for="preview_path">Youtube Trailer ID</label>
                  <input
                    class="form-control"
                    id="preview_path"
                    type="text"
                    v-model="form.anime.preview_path"
                  />
                </div>
              </div>

              <div class="col-md-4">
                <div class="form-group">
                  <label for="vote_average">Vote Average</label>
                  <input
                    class="form-control"
                    id="vote_average"
                    type="text"
                    v-model="form.anime.vote_average"
                  />
                </div>
              </div>
            </div>

            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label for="vote_count">Vote Count</label>
                  <input
                    class="form-control"
                    id="vote_count"
                    type="text"
                    v-model="form.anime.vote_count"
                  />
                </div>
              </div>

              <div class="col-md-6">
                <div class="form-group">
                  <label for="popularity">Popularity</label>
                  <input
                    class="form-control"
                    id="popularity"
                    type="text"
                    v-model="form.anime.popularity"
                  />
                </div>
              </div>
            </div>

            <!-- Seasons, Episodes and Videos -->

            <div class="row">
              <div class="col-md-6">
                <div class="input-group">
                  <input
                    v-model="addseason"
                    type="text"
                    class="form-control input"
                    placeholder="Season number"
                  />
                  <div class="input-group-btn">
                    <button
                      type="submit"
                      @click.prevent="getSeason()"
                      class="btn btn-primary ml-2"
                    >Add from TMDB</button>
                  </div>
                </div>

                <div class="form-group">
                  <label for="season">Season</label>
                  <multiselect
                    v-model="season"
                    deselect-label="Can't remove this value"
                    track-by="season_number"
                    label="season_number"
                    placeholder="Select season"
                    :options="form.anime.seasons"
                    :searchable="true"
                    :allow-empty="false"
                    @select="selectSeason"
                  >
                    <template slot="singleLabel" slot-scope="props">
                      <span class="option__title ml-1">{{ props.option.name }}</span>
                    </template>
                  </multiselect>
                </div>

                <div
                  class="form-group"
                  v-if="selectedSeason >= 0 && form.anime.seasons[selectedSeason]"
                >
                  <label for="seasonoverview">Season Overview</label>
                  <textarea
                    id="seasonoverview"
                    v-model="form.anime.seasons[selectedSeason].overview"
                    class="form-control"
                    required
                  ></textarea>
                </div>

                <div class="input-group" v-if="selectedSeason >= 0">
                  <div class="input-group-prepend">
                    <span class="input-group-text">Upload</span>
                  </div>
                  <div class="custom-file">
                    <input
                      @change="storePosterSeason()"
                      type="file"
                      class="custom-file-input"
                      id="posterSeason"
                    />
                    <label
                      class="custom-file-label"
                      for="posterSeason"
                    >{{(posterSeason.length) ? posterSeason.name : 'Choose poster season'}}</label>
                  </div>
                </div>

                <div v-if="selectedSeason >= 0" class="float-right my-2">
                  <button @click="destroySeason()" class="btn btn-danger mr-2">Delete Season</button>
                </div>
              </div>

              <template v-if="selectedSeason >= 0">
                <div class="col-md-6">
                  <div class="input-group">
                    <input
                      v-model="addepisode"
                      type="text"
                      class="form-control input"
                      placeholder="Episode number"
                    />
                    <div class="input-group-btn">
                      <button
                        type="submit"
                        @click.prevent="getEpisode"
                        class="btn btn-primary ml-2"
                      >>Add from TMDB</button>
                    </div>
                  </div>

                    <div class="form-group" v-if="selectedSeason >= 0">
                          <label for="episode">Episode</label>
                          <multiselect
                            v-model="episode"
                            deselect-label="Can't remove this value"
                            track-by="episode_number"
                            label="episode_number"
                            placeholder="Select episode"
                            :options="form.anime.seasons[selectedSeason].episodes"
                            :searchable="true"
                            :allow-empty="false"
                            @select="selectEpisode"
                          >
                            <template slot="singleLabel" slot-scope="{ option }">
                              <img
                                class="option__image"
                                :src="option.still_path"
                                :alt="option.episode_number"
                                height="90"
                              />
                              <strong>{{ option.episode_number }}</strong>:
                              <strong>{{ option.name }}</strong>
                            </template>
                          </multiselect>
                        </div>

                  <div
                    class="form-group"
                    v-if="selectedEpisode >= 0 && form.anime.seasons[selectedSeason].episodes[selectedEpisode]"
                  >
                    <label for="seasonoverview">Episode Overview</label>
                    <textarea
                      id="seasonoverview"
                      v-model="form.anime.seasons[selectedSeason].episodes[selectedEpisode].overview"
                      class="form-control"
                      required
                    ></textarea>
                  </div>

                <div class="input-group" v-if="selectedEpisode >= 0">
                          <div class="input-group-prepend">
                            <span class="input-group-text">Upload</span>
                          </div>
                          <div class="custom-file">
                            <input
                              @change="storeStillEpisode"
                              type="file"
                              class="custom-file-input"
                              id="stillEpisode"
                            />
                            <label
                              class="custom-file-label"
                              for="stillEpisode"
                            >{{(stillEpisode.length) ? stillEpisode.name : 'Choose still episode'}}</label>
                          </div>
                        </div>

                  <div v-if="selectedEpisode >= 0" class="float-right my-2">
                    <button @click="destroyEpisode()" type="button" class="btn btn-danger ml-2">
                      <em class="far fa-trash-alt"></em>
                    </button>
                  </div>
                </div>
              </template>
            </div>

            <section v-if="selectedEpisode >= 0">
              <div class="form-group">
                <form class="form-inline">
                  <em class="mdi mdi-video menu-icon fa-2x"></em>
                  <h3 class="card-title">VIDEOS</h3>
                </form>

                <div class="row">
                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="server">Server</label>
                      <multiselect
                        :options="servers"
                        id="server"
                        label="name"
                        placeholder="Select one"
                        track-by="id"
                        v-model="server"
                      ></multiselect>
                    </div>
                  </div>

                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="lang">Language</label>
                      <multiselect
                        :options="langs"
                        id="lang"
                        label="english_name"
                        placeholder="Select one (default ov)"
                        track-by="iso_639_1"
                        v-model="lang"
                      ></multiselect>
                    </div>
                  </div>
                </div>

                <div class="row">
                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="link">Link</label>
                      <input
                        class="form-control"
                        id="link"
                        placeholder="Upload or Insert Direct  Link"
                        type="text"
                        v-model="link"
                      />
                    </div>
                  </div>

                  <div class="row my-2">
                    <div class="col-md-12">
                      <div class="d-flex justify-content-center" v-if="loading">
                        <div class="spinner-border text-primary" role="status">
                          <span class="sr-only">Loading...</span>
                        </div>
                      </div>
                    </div>
                  </div>

                  <div class="col-md-6">
                    <div class="form-group">
                      <label>Upload Stream</label>
                      <input class="file-upload-default" />
                      <div class="input-group col-xs-12">
                        <input
                          @change="storeVideo"
                          class="form-control file-upload-info"
                          id="video"
                          placeholder="Upload Image"
                          type="file"
                        />
                        <span class="input-group-append">
                          <button class="file-upload-browse btn btn-primary" type="button">Upload</button>
                        </span>
                      </div>
                    </div>
                  </div>

                  <div class="col-md-1 my-auto">
                    <button @click.prevent="addLink()" class="btn btn-primary mr-2">Add</button>
                  </div>
                </div>

                <table aria-describedby="video links" class="table table-striped table-links">
                  <thead>
                    <tr class="row">
                      <th scope="col" class="col-md-2">Actions</th>
                      <th scope="col" class="col-md-1">Lang</th>
                      <th scope="col" class="col-md-2">Server</th>
                      <th scope="col" class="col-md-6">Link</th>
                    </tr>
                  </thead>
                  <tbody name="links">
                    <tr
                      v-for="(item, index) in form.anime.seasons[selectedSeason].episodes[selectedEpisode].videos"
                      :key="index"
                      class="row"
                    >
                      <td class="col-md-2">
                        <button
                          class="btn btn-sm btn-danger"
                          @click.prevent="deleteLink(item, index)"
                        >
                          <em class="far fa-trash-alt"></em>
                        </button>
                      </td>
                      <td class="col-md-1">{{item.lang}}</td>
                      <td class="col-md-2">{{item.server}}</td>
                      <td class="col-md-6">{{item.link}}</td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </section>

            <div class="d-flex justify-content-between align-items-end flex-wrap">
              <button
                @click.prevent="store()"
                class="btn btn-primary mt-2 mt-xl-0"
                type="submit"
                v-if="add"
              >Save</button>
              <button
                @click.prevent="update()"
                class="btn btn-primary mt-2 mt-xl-0"
                type="submit"
                v-if="edit"
              >Update</button>
            </div>
          </section>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { notifications } from "../mixins/notifications";
import { settings } from "../mixins/settings";

export default {
  data() {
    return {
      index: true,
      add: false,
      edit: false,
      animes: [],
      paginate: ["animes", "filteredAnimes"],
      tmdb: null,
      form: {
        anime: {
          backdrop_path: "",
          poster_path: "",
          preview_path: "",
          genres: [],
          seasons: [{ episodes: [{ videos: [], substitles: [] }] }],
        },
        notification: false,
      },
      poster: "",
      backdrop: "",
      posterSeason: "",
      stillEpisode: "",
      search: "",
      servers: [],
      server: "",
      season: null,
      selectedSeason: -1,
      oldSeason: {},
      episode: null,
      selectedEpisode: -1,

      link: "",
      linksubstitle: "",
      options: [],
      addseason: "",
      addepisode: "",
      video: null,
      substitle: null,
      loading: false,
    };
  },
  async mounted() {
    let response = await axios.get(url + "/admin/animes/data");
    this.animes = response.data.data;
    response = await axios.get(url + "/admin/servers/dataservers");
    this.servers = response.data;
    response = await axios.get(url + "/admin/genres/datagenres");
    this.options = response.data;
  },
  methods: {
    create() {
      this.index = false;
      this.edit = false;
      this.add = true;
    },
    back() {
      this.form.anime = {
        backdrop_path: "",
        poster_path: "",
        preview_path: "",
        genres: [],
        seasons: [{ episodes: [{ videos: [], substitles: [] }] }],
      };
      this.link = "";
      this.linksubstitle = "";
      this.server = "";
      this.video = null;
      this.substitle = null;
      this.tmdb = null;
      this.add = false;
      this.edit = false;
      this.index = true;
    },
    editing(anime) {
      this.index = false;
      this.add = false;
      this.edit = true;
      this.form.anime = anime;
      this.tmdb = anime.tmdb_id;
    },

    // Get Anime from themoviedb, including seasons and episodes.

  
    async getanime() {
      let anime = "";
      const oldanime = this.form.anime;
      let oldSeason = "";
      let oldEpisode = "";

      try {
        let response = await http.get(
          "https://api.themoviedb.org/3/tv/" +
            this.tmdb +
            "?api_key=" +
            this.settings.tmdb_api_key +
            "&language=" +
            this.settings.tmdb_lang.iso_639_1 +
            "&append_to_response=videos"
        );

        anime = _.mapKeys(response.data, function (value, key) {
          return key == "id" ? "tmdb_id" : key;
        });

        if (this.edit) {
          anime.id = oldanime.id;
        }

        if (typeof anime.videos.results[0] !== "undefined") {
          anime.preview_path = anime.videos.results[0].key;
        } else if (this.edit && oldanime.preview_path) {
          anime.preview_path = oldanime.preview_path;
        } else {
          response = await http.get(
            "https://api.themoviedb.org/3/tv/" +
              this.tmdb +
              "/videos?api_key=" +
              this.settings.tmdb_api_key
          );
          if (response.data.results[0]) {
            anime.preview_path = response.data.results[0].key;
          }
        }

        if (anime.poster_path) {
          anime.poster_path = this.settings.imdb_cover_path + anime.poster_path;
        }
        if (anime.backdrop_path) {
          anime.backdrop_path =
            this.settings.imdb_cover_path + anime.backdrop_path;
        }

        for (const [index, season] of anime.seasons.entries()) {
          anime.seasons[index] = _.mapKeys(season, function (value, key) {
            return key == "id" ? "tmdb_id" : key;
          });

          if (this.edit) {
            oldSeason = oldanime.seasons.find(
              (s) => s.season_number === season.season_number
            );

            if (typeof oldSeason !== "undefined") {
              anime.seasons[index].id = oldSeason.id;
              anime.seasons[index].anime_id = oldSeason.anime_id;
            }
          }

          if (anime.seasons[index].poster_path) {
            anime.seasons[index].poster_path =
              this.settings.imdb_cover_path + anime.seasons[index].poster_path;
          }

          const episodes = [];
          response = await http.get(
            "https://api.themoviedb.org/3/tv/" +
              this.tmdb +
              "/season/" +
              season.season_number +
              "?api_key=" +
              this.settings.tmdb_api_key +
              "&language=" +
              this.settings.tmdb_lang.iso_639_1
          );

          for (let episode of response.data.episodes) {
            episode = _.mapKeys(episode, function (value, key) {
              return key == "id" ? "tmdb_id" : key;
            });

            if (episode.still_path) {
              episode.still_path =
                this.settings.imdb_cover_path + episode.still_path;
            }

            if (this.edit && typeof oldSeason !== "undefined") {
              oldEpisode = oldSeason.episodes.find(
                (e) => e.tmdb_id === episode.tmdb_id
              );
              if (typeof oldEpisode !== "undefined") {
                episode.id = oldEpisode.id;
                episode.season_id = oldEpisode.season_id;
                episode.videos = oldEpisode.videos;
              }
            }
            episodes.push(episode);
          }
          anime.seasons[index].episodes = episodes;
        }

        this.form.anime = anime;
      } catch (error) {
        this.showError();
      }
    },

    // create a new anime in database
    async store() {
      try {
        const response = await axios.post(
          url + "/admin/animes/store",
          this.form
        );

        this.add = false;
        this.edit = false;
        this.index = true;
        this.form.anime = {
          backdrop_path: "",
          poster_path: "",
          preview_path: "",
          genres: [],
          seasons: [{ episodes: [{ videos: [], substitles: [] }] }],
        };
        this.tmdb = null;
        this.selectedSeason = -1;
        this.selectedEpisode = -1;
        this.season = null;
        this.episode = null;
        this.link = "";
        this.linksubstitle = "";
        this.server = "";
        this.video = null;
        this.substitle = null;
        this.animes.unshift(response.data.body);
        this.showSuccess(response.data.message);
      } catch (error) {
        this.showError(error.response);
      }
    },
    // update Anime from database
    async update() {
      try {
        const response = await axios.put(
          url + "/admin/animes/update/" + this.form.anime.id,
          this.form
        );

        this.add = false;
        this.edit = false;
        this.index = true;
        this.form.anime = {
          backdrop_path: "",
          poster_path: "",
          preview_path: "",
          genres: [],
          seasons: [{ episodes: [{ videos: [] }] }],
        };
        this.tmdb = null;
        this.selectedSeason = -1;
        this.selectedEpisode = -1;
        this.season = null;
        this.episode = null;
        this.link = "";
        this.server = "";
        this.video = null;
        this.showSuccess(response.data.message);
        this.animes = response.data.body;
      } catch (error) {
        this.showError(error.response);
      }
    },
    // delete Anime from database
    destroy(id, index) {
      this.showConfirm(async () => {
        try {
          const response = await axios.delete(
            url + "/admin/animes/destroy/" + id
          );
          const animeIndex = this.animes.findIndex((anime) => anime.id === id);
          this.animes.splice(animeIndex, 1);
          this.paginate.filteredanimes.list.splice(index, 1);
          this.selectedSeason = -1;
          this.selectedEpisode = -1;
          this.season = null;
          this.episode = null;
          this.showSuccess(response.data.message);
        } catch (error) {
          this.showError();
        }
      });
    },
    // store a new poster in storage
    async storePoster(event) {
      try {
        this.poster = event.target.files[0];
        const data = new FormData();
        data.append("image", this.poster);

        const response = await axios.post(
          url + "/admin/animes/image/store",
          data
        );

        this.form.anime.poster_path = response.data.image_path;
        this.showSuccess(response.data.message);
      } catch (error) {
        this.showError(error.response);
      }
    },
    // store a new poster for season in storage
    async storePosterSeason(event) {
      try {
        this.posterSeason = event.target.files[0];
        const data = new FormData();
        data.append("image", this.posterSeason);

        const response = await axios.post(
          url + "/admin/animes/image/store",
          data
        );

        this.form.anime.seasons[this.selectedSeason].poster_path =
          response.data.image_path;
        this.showSuccess(response.data.message);
      } catch (error) {
        this.showError(error.response);
      }
    },
    // storage new still episode in storage
    async storeStillEpisode(event) {
      try {
        this.stillEpisode = event.target.files[0];
        const data = new FormData();
        data.append("image", this.stillEpisode);

        const response = await axios.post(
          url + "/admin/animes/image/store",
          data
        );

        this.form.anime.seasons[this.selectedSeason].episodes[
          this.selectedEpisode
        ].still_path = response.data.image_path;
        this.showSuccess(response.data.message);
      } catch (error) {
        this.showError(error.response);
      }
    },
    // storage new backdrop in storage
    async storeBackdrop(event) {
      try {
        this.backdrop = event.target.files[0];
        const data = new FormData();
        data.append("image", this.backdrop);

        const response = await axios.post(
          url + "/admin/animes/image/store",
          data
        );

        this.form.anime.backdrop_path = response.data.image_path;
        this.showSuccess(response.data.message);
      } catch (error) {
        this.showError(error.response);
      }
    },
    // save an video in storage and return the link
    async storeVideo(event) {
      if (typeof event.target.files[0] !== "undefined") {
        try {
          this.loading = true;
          this.video = event.target.files[0];
          const data = new FormData();
          data.append("video", this.video);
          const response = await axios.post(url + "/admin/video/store", data);
          this.link = response.data.video_path;
          this.server = { name: response.data.server };
          this.showSuccess(response.data.message);
        } catch (error) {
          this.showError(error.response);
        } finally {
          this.loading = false;
        }
      }
    },
    // add a video link to an episode
    addLink() {
      if (
        this.selectedServer === "" ||
        this.link === "" ||
        this.form.anime === "" ||
        this.selectedServer === 0
      ) {
        return;
      }
      const episode = this.form.anime.seasons[this.selectedSeason].episodes[
        this.selectedEpisode
      ];

      if (!episode.videos) {
        Vue.set(episode, "videos", []);
      }

      episode.videos.unshift({
        server: this.server.name,
        link: this.link,
        lang:
          this.lang.iso_639_1 && this.lang.iso_639_1 !== "xx"
            ? this.lang.iso_639_1
            : "en",
      });
      this.link = "";
      this.server = "";
      this.video = null;
    },

    // delete a video link from an episode
    deleteLink(link, index) {
      this.showConfirm(async () => {
        try {
          if (link.id) {
            const response = await axios.delete(
              url + "/admin/animes/videos/destroy/" + link.id
            );

            this.showSuccess(response.data.message);
          }
          this.form.anime.seasons[this.selectedSeason].episodes[
            this.selectedEpisode
          ].videos.splice(index, 1);
        } catch (error) {
          this.showError();
        }
      });
    },

    // add a Substitle link to an episode
    addSubs() {
      if (this.linksubstitle === "" || this.form.anime === "") {
        return;
      }
      const episode = this.form.anime.seasons[this.selectedSeason].episodes[
        this.selectedEpisode
      ];

      if (!episode.substitles) {
        Vue.set(episode, "substitles", []);
      }

      episode.substitles.unshift({
        link: this.linksubstitle,
        lang:
          this.lang.iso_639_1 && this.lang.iso_639_1 !== "xx"
            ? this.lang.iso_639_1
            : "en",
      });
      this.linksubstitle = "";
      this.server = "";
      this.substitle = null;
    },

    async storeSubstitle(event) {
      try {
        this.loading = true;
        this.substitle = event.target.files[0];
        const data = new FormData();
        data.append("substitle", this.substitle);
        const response = await axios.post(url + "/admin/substitle/store", data);
        this.linksubstitle = response.data.substitle_path;
        this.showSuccess(response.data.message);
      } catch (error) {
        this.showError();
      } finally {
        this.loading = false;
      }
    },

    // delete a substitle link from an episode
    deleteLinkSubs(linksubstitle, index) {
      this.showConfirm(async () => {
        try {
          if (linksubstitle.id) {
            const response = await axios.delete(
              url + "/admin/animes/substitles/destroy/" + linksubstitle.id
            );

            this.showSuccess(response.data.message);
          }
          this.form.anime.seasons[this.selectedSeason].episodes[
            this.selectedEpisode
          ].substitles.splice(index, 1);
        } catch (error) {
          this.showError();
        }
      });
    },

    // assign the index of the selected season
    selectSeason(event) {
      this.selectedSeason = this.form.anime.seasons.indexOf(event);
      this.episode = null;
      this.selectedEpisode = -1;
    },
    // assign the index of the selected episode
    selectEpisode(event) {
      this.selectedEpisode = this.form.anime.seasons[
        this.selectedSeason
      ].episodes.indexOf(event);
    },
    // delete a anime genre from the database
    async destroyGenre(event) {
      if (typeof event.genre !== "undefined") {
        try {
          const response = await axios.delete(
            url + "/admin/animes/genres/destroy/" + event.id
          );
          this.showSuccess(response.data.message);
        } catch (error) {
          this.showError();
        }
      }
    },
    // get a new season from themoviedb
    async getSeason() {
      try {
        const response = await http.get(
          "https://api.themoviedb.org/3/tv/" +
            this.form.anime.tmdb_id +
            "/season/" +
            this.addseason +
            "?api_key=" +
            this.settings.tmdb_api_key +
            "&language=" +
            this.settings.tmdb_lang.iso_639_1
        );

        response.data = _.mapKeys(response.data, function (value, key) {
          return key == "id" ? "tmdb_id" : key;
        });

        if (response.data.poster_path) {
          response.data.poster_path =
            this.settings.imdb_cover_path + response.data.poster_path;
        }

        for (const [
          indexEpisode,
          episode,
        ] of response.data.episodes.entries()) {
          response.data.episodes[indexEpisode] = _.mapKeys(episode, function (
            value,
            key
          ) {
            return key == "id" ? "tmdb_id" : key;
          });

          if (episode.still_path) {
            response.data.episodes[indexEpisode].still_path =
              this.settings.imdb_cover_path + episode.still_path;
          }
        }

        const index = this.form.anime.seasons.findIndex(
          (season) => season.season_number === response.data.season_number
        );
        if (index < 0) {
          this.form.anime.seasons.push(response.data);
          this.showSuccess();
        } else {
          this.showError("this season already exists");
        }

        this.addseason = "";
      } catch (error) {
        this.showError();
      }
    },
    // delete a season from database
    destroySeason() {
      this.showConfirm(async () => {
        const season = this.form.anime.seasons[this.selectedSeason];
        if (season.id) {
          try {
            const response = await axios.delete(
              url + "/admin/animes/seasons/destroy/" + season.id
            );
            this.showSuccess(response.data.message);
          } catch (error) {
            this.showError();
          }
        }
        this.form.anime.seasons.splice(this.selectedSeason, 1);
        this.season = null;
        this.episode = null;
        this.selectedSeason = -1;
        this.selectedEpisode = -1;
      });
    },
    // get a new episode from themoviedb
    async getEpisode() {
      try {
        const response = await http.get(
          "https://api.themoviedb.org/3/tv/" +
            this.tmdb +
            "/season/" +
            this.form.anime.seasons[this.selectedSeason].season_number +
            "/episode/" +
            this.addepisode +
            "?api_key=" +
            this.settings.tmdb_api_key
        );

        const index = this.form.anime.seasons[
          this.selectedSeason
        ].episodes.findIndex(
          (episode) => episode.episode_number === response.data.episode_number
        );
        if (index < 0) {
          const episode = _.mapKeys(response.data, function (value, key) {
            return key == "id" ? "tmdb_id" : key;
          });

          if (episode.still_path) {
            episode.still_path =
              this.settings.imdb_cover_path + episode.still_path;
          }
          this.form.anime.seasons[this.selectedSeason].episodes.push(episode);
          this.showSuccess();
        } else {
          this.showError("this episode already exists");
        }

        this.addepisode = "";
      } catch (error) {
        this.showError();
      }
    },
    // delete a episode from database
    destroyEpisode() {
      this.showConfirm(async () => {
        const episode = this.form.anime.seasons[this.selectedSeason].episodes[
          this.selectedEpisode
        ];
        if (episode.id) {
          try {
            const response = await axios.delete(
              url + "/admin/animes/episodes/destroy/" + episode.id
            );
            this.showSuccess(response.data.message);
          } catch (error) {
            this.showError();
          }
        }
        this.form.anime.seasons[this.selectedSeason].episodes.splice(
          this.selectedEpisode,
          1
        );
        this.episode = null;
        this.selectedEpisode = -1;
      });
    },
  },
  computed: {
    // filter the animes array with the search matches and return the filtered array
    filteredAnimes() {
      return this.animes.filter((anime) => {
        return anime.name.toLowerCase().match(this.search.toLowerCase());
      });
    },
  },
  mixins: [notifications, settings],
};
</script>