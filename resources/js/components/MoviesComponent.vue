<template>
  <div class="row">

    
    <div class="col-md-12 grid-margin">
      <div class="d-flex justify-content-between flex-wrap">
        <div class="d-flex align-items-end flex-wrap">
          <div class="d-flex"></div>
        </div>
        <div class="d-flex justify-content-between align-items-end flex-wrap">
          <button @click="create()" class="btn btn-primary mt-2 mt-xl-0" v-if="index">Add Movie</button>
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
            <table aria-describedby="table movies" class="table" id="table movies">
              <thead>
                <tr>
                  <th class="text-center" id="cover">Cover</th>
                  <th class="text-center" id="id">ID</th>
                  <th class="text-center" id="name">Name</th>
                  <th class="text-center" id="vote">Vote</th>
                  <th class="text-center" id="featured">Featured</th>
                  <th class="text-center" id="options">Options</th>
                </tr>
              </thead>
              <tbody>
                <tr :key="index" v-for="(movie, index) in paginated('filteredMovies')">
                  <td class="text-center">
                    <img :src="movie.poster_path" alt="poster path" height="80" width="80" />
                  </td>
                  <td class="text-center">{{movie.tmdb_id}}</td>
                  <td class="text-center">{{movie.title}}</td>
                  <td class="text-center">{{movie.vote_average}}</td>
                  <td class="text-center" v-if="movie.featured === 1 ">
                    <em class="mdi mdi-check" style="color:green"></em>
                  </td>
                  <td class="text-center" v-else>
                    <em class="mdi mdi-close" style="color:red"></em>
                  </td>
                  <td class="text-center">
                    <div class="list-icons">
                      <a
                        @click="editing(movie)"
                        class="list-icons-item mr-2"
                        data-original-title="Edit"
                        rel="tooltip"
                        title
                      >
                        <em class="mdi mdi-pencil fa-lg" style="color:#4d83ff"></em>
                      </a>
                      <a
                        @click="destroy(movie.id, index)"
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
                :list="filteredMovies"
                :per="5"
                name="filteredMovies"
                tag="tbody"
                v-if="filteredMovies.length"
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
                :step-links="{
               next: 'Next',
              prev: 'Prev'
  }"
                class="float-right"
                for="filteredMovies"
                v-if="filteredMovies.length"
              ></paginate-links>

          </div>
        </div>
      </div>
    </div>

    <!-- Movie Create -->

    <div class="col-lg-12 grid-margin stretch-card" v-if="add || edit">
      <div class="card">
        <div class="card-body">
          <section>
            <div class="poster-container">
              <img
                :src="form.movie.poster_path"
                alt="movie poster"
                class="poster"
                data-loaded="true"
                height="316"
                v-if="form.movie.poster_path"
                width="210"
              />

              <div class="many-inputs">
                <div class="input-container">

                  
                  <div class="form-group">
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
                          @click="getMovie()"
                        >Search</button>
                      </div>
                    </div>
                  </div>

                  <div class="input-container">
                    <div class="row">
                      <div class="col-md-12">
                        <div class="form-group">
                          <label for="title">Movie Title</label>
                          <input
                            class="form-control"
                            id="title"
                            type="text"
                            v-model="form.movie.title"
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
                              v-model="form.movie.premuim"
                            />
                            <label class="custom-control-label" for="premuim">
                              Premuim
                              Only
                            </label>
                          </div>
                        </div>
                      </div>

                       <div  v-if="edit"  class="col-md-3">
                        <div class="form-group">
                          <div class="custom-control custom-checkbox">
                            <input
                              class="custom-control-input"
                              id="active"
                              type="checkbox"
                              v-model="form.movie.active"
                            />
                            <label class="custom-control-label" for="active">Active</label>
                          </div>
                        </div>
                      </div>

                      <div class="col-md-3">
                        <div class="form-group">
                          <div class="custom-control custom-checkbox">
                            <input
                              class="custom-control-input"
                              id="featured"
                              type="checkbox"
                              v-model="form.movie.featured"
                            />
                            <label class="custom-control-label" for="featured">Featured</label>
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
                            v-model="form.movie.poster_path"
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
                          v-model="form.movie.genres"
                        ></multiselect>
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
                    v-model="form.movie.backdrop_path"
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
                    v-model="form.movie.overview"
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
                    v-model="form.movie.release_date"
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
                    v-model="form.movie.preview_path"
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
                    v-model="form.movie.vote_average"
                  />
                </div>
              </div>
            </div>

            <div class="row">
              <div class="col-md-4">
                <div class="form-group">
                  <label for="vote_count">Vote Count</label>
                  <input
                    class="form-control"
                    id="vote_count"
                    type="text"
                    v-model="form.movie.vote_count"
                  />
                </div>
              </div>

              <div class="col-md-4">
                <div class="form-group">
                  <label for="popularity">Popularity</label>
                  <input
                    class="form-control"
                    id="popularity"
                    type="text"
                    v-model="form.movie.popularity"
                  />
                </div>
              </div>

              <div class="col-md-4">
                <div class="form-group">
                  <label for="runtime">Runtime</label>
                  <input class="form-control" id="runtime" type="text" v-model="form.movie.runtime" />
                </div>
              </div>
            </div>

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
                      placeholder="Select one (default EN)"
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

              <div class="table-responsive">
                <table aria-describedby="Links Table" class="table">
                  <thead>
                    <tr>
                      <th id="actions">Actions</th>
                      <th id="langs">Lang</th>
                      <th id="servers">Server</th>
                      <th id="links">Link</th>
                    </tr>
                  </thead>
                  <tbody name="links">
                    <tr :key="index" v-for="(item, index) in form.links">
                      <td>
                        <button
                          @click.prevent="destroyLink(item, index)"
                          class="btn btn-danger mr-2"
                        >Delete</button>
                      </td>
                      <td>{{item.lang}}</td>
                      <td>{{item.server}}</td>
                      <td>{{item.link}}</td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>

            <div class="form-group">
              <form class="form-inline">
                <em class="mdi mdi-closed-caption menu-icon fa-2x"></em>
                <h3 class="card-title">Substitles</h3>
              </form>

              <div class="row">
                <div class="col-md-6">
                  <div class="form-group">
                    <label for="linksubstitle">Substitle Path</label>
                    <input
                      class="form-control"
                      id="linksubstitle"
                      placeholder="Upload or Insert Direct  Link"
                      type="text"
                      v-model="linksubstitle"
                    />
                  </div>
                </div>

                  <div class="col-md-6">
                  <div class="form-group">
                    <label for="lang">Language</label>
                    <multiselect
                      :options="langsubs"
                      id="langsub"
                      label="english_name"
                      placeholder="Select one (default EN)"
                      track-by="iso_639_1"
                      v-model="langsub"
                    ></multiselect>
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

                <div class="col-md-1 my-auto">
                  <button @click.prevent="addSubs()" class="btn btn-primary mr-2">Add</button>
                </div>
              </div>

              <div class="table-responsive">
                <table aria-describedby="Substitle Table" class="table">
                  <thead>
                    <tr>
                      <th id="actions">Actions</th>
                      <th id="langs">Lang</th>
                      <th id="links">Link</th>
                    </tr>
                  </thead>
                  <tbody name="linksubs">
                    <tr :key="index" v-for="(item, index) in form.linksubs">
                      <td class="col-md-2">
                        <button
                          @click.prevent="destroySubs(item, index)"
                          class="btn btn-danger mr-2"
                        >Delete</button>
                      </td>
                      <td>{{item.lang}}</td>
                      <td>{{item.link}}</td>
                    </tr>
                  </tbody>
                </table>
              </div>


              <div class="d-flex justify-content-end align-items-end flex-wrap">
              
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
      tmdb: null,
      movies: [],
      paginate: ["filteredMovies"],
      form: {
        movie: {
          backdrop_path: "",
          poster_path: "",
          preview_path: "",
          genres: [],
        },
        links: [],
        linksubs: [],
        notification: false,
      },
      poster: null,
      backdrop: null,
      video: null,
      substitle: null,
      search: "",
      servers: [],
      server: "",
      link: "",
      linksubstitle: "",
      options: [],
      loading: false,
    };
  },
  async mounted() {
    let response = await axios.get(url + "/admin/movies/dataweb");
    this.movies = response.data.data;
    
    response = await axios.get(url + "/admin/servers/dataservers");
    this.servers = response.data;

    response = await axios.get(url + "/admin/genres/data");
    this.options = response.data;

    if (this.settings.tmdb_api_key == null) {
      this.showAlert("you must configure your TMDB api key in settings");
    }
  },
  methods: {
    // change the view to the create form
    create() {
      this.index = false;
      this.edit = false;
      this.add = true;
    },
    // change the view to the index
    back() {
      this.form.movie = "";
      this.form.links = [];
      this.form.linksubs = [];
      this.tmdb = null;
      this.server = "";
      this.link = "";
      this.linksubstitle = "";
      this.video = null;
      this.substitle = null;
      this.add = false;
      this.edit = false;
      this.index = true;
    },
    // change the view to the editing form
    editing(movie) {
      this.index = false;
      this.add = false;
      this.edit = true;
      this.form.movie = movie;
      this.tmdb = movie.tmdb_id;
      axios.get(url + "/api/movies/videos/" + movie.id).then((response) => {
        this.form.links = response.data;
      });

      axios.get(url + "/api/movies/substitles/" + movie.id).then((response) => {
        this.form.linksubs = response.data;
      });
    },

    // get the movie data from the tmdb api
    async getMovie() {



      try {

    let response = await http.get(
        "https://api.themoviedb.org/3/movie/" +
          this.tmdb +
          "?api_key=" +
          this.settings.tmdb_api_key +
          "&language=" +
          this.settings.tmdb_lang.iso_639_1 +
          "&append_to_response=videos"
      );

      const movie = _.mapKeys(response.data, function (value, key) {
        if (key === "id") {
          return "tmdb_id";
        }
        return key;
      });
      movie.id = this.form.movie.id;
      if (typeof movie.videos.results[0] !== "undefined") {
        movie.preview_path = movie.videos.results[0].key;
      } else if (this.edit && this.form.movie.preview_path) {
        movie.preview_path = this.form.movie.preview_path;
      } else {
        response = await http.get(
          "https://api.themoviedb.org/3/movie/" +
            this.tmdb +
            "/videos?api_key=" +
            this.settings.tmdb_api_key
        );
        if (response.data.results[0]) {
          movie.preview_path = response.data.results[0].key;
        }
      }
      if (movie.poster_path) {
        movie.poster_path = this.settings.imdb_cover_path + movie.poster_path;
      }
      if (movie.backdrop_path) {
        movie.backdrop_path =
          this.settings.imdb_cover_path + movie.backdrop_path;
      }

      this.form.movie = movie;

      } catch (error) {
          this.showError("you entred a serie id or the id is invalid !");
        }
    },
    // create a new record (movie) in the database
    store() {
      axios
        .post(url + "/admin/movies/store", this.form)
        .then((response) => {
  
          this.add = false;
          this.edit = false;
          this.index = true;
          this.form.movie = {};
          this.form.links = [];
          this.form.linksubs = [];
          this.tmdb = null;
          this.link = "";
          this.linksubstitle = "";
          this.server = "";
          this.video = null;
          this.substitle = null;
          this.movies.unshift(response.data.body);
          this.showSuccess(response.data.message);
        })
        .catch((error) => {
          this.showError(error.response);
        });
    },
    // update a record (movie) in the database
    update() {
      axios
        .put(url + "/admin/movies/update/" + this.form.movie.id, this.form)
        .then((response) => {
          this.add = false;
          this.edit = false;
          this.index = true;
          this.form.movie = {};
          this.form.links = [];
          this.form.linksubs = [];
          this.tmdb = null;
          this.link = "";
          this.linksubstitle = "";
          this.server = "";
          this.movies = response.data.body;
          this.showSuccess(response.data.message);
        })
        .catch((error) => {
          this.showError(error.response);
        });
    },
    // delete a record (movie) in the databse
    destroy(id, index) {
      this.showConfirm(async () => {
        try {
          const response = await axios.delete(
            url + "/admin/movies/destroy/" + id
          );
          const movieIndex = this.movies.findIndex((movie) => movie.id === id);
          this.movies.splice(movieIndex, 1);
          this.paginate.filteredMovies.list.splice(index, 1);
          this.showSuccess(response.data.message);
        } catch (error) {
          this.showError();
        }
      });
    },
    // save an image in storage and return the link
    async storePoster(event) {
      if (typeof event.target.files[0] !== "undefined") {
        try {
          this.poster = event.target.files[0];
          const data = new FormData();
          data.append("image", this.poster);

          const response = await axios.post(
            url + "/admin/movies/image/store",
            data
          );
          this.form.movie.poster_path = response.data.image_path;
          this.showSuccess(response.data.message);
        } catch (error) {
          this.showError(error.response);
        }
      }
    },
    // save an image in storage and return the link
    async storeBackdrop(event) {
      if (typeof event.target.files[0] !== "undefined") {
        try {
          this.backdrop = event.target.files[0];
          const data = new FormData();
          data.append("image", this.backdrop);

          const response = await axios.post(
            url + "/admin/movies/image/store",
            data
          );
          this.form.movie.backdrop_path = response.data.image_path;
          this.showSuccess(response.data.message);
        } catch (error) {
          this.showError(error.response);
        }
      }
    },
    // save an video in storage and return the link
    async storeVideo(event) {
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
        this.showError();
      } finally {
        this.loading = false;
      }
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

    // add a new link to the movie
    addLink() {
      if (this.server === "" || this.link === "" || this.form.movie === "")
        return;
      this.form.links.unshift({
        server: this.server.name,
        link: this.link,
        lang:
          this.lang.english_name && this.lang.english_name !== "No Language"
            ? this.lang.english_name
            : "English",
      });
      this.link = "";
      this.server = "";
      this.video = null;
    },

    addSubs() {
      if (this.linksubstitle === "" || this.form.susbstitle === "") return;
      this.form.linksubs.unshift({
        link: this.linksubstitle,
        lang:
          this.langsub.english_name && this.langsub.english_name !== "No Language"
            ? this.langsub.english_name
            : "English",
      });
      this.linksubstitle = "";
      this.substitle = null;
    },

    destroySubs(linksubstitle, index) {
      this.showConfirm(() => {
        if (linksubstitle.id) {
          axios
            .delete(
              url + "/admin/movies/substitles/destroy/" + linksubstitle.id
            )
            .then((response) => {
              this.showSuccess(response.data.message);
            })
            .catch((error) => {
              this.showError();
            });
        }
        this.form.linksubs.splice(index, 1);
      });
    },

    // delete a link of the movie
    destroyLink(link, index) {
      this.showConfirm(() => {
        if (link.id) {
          axios
            .delete(url + "/admin/movies/videos/destroy/" + link.id)
            .then((response) => {
              this.showSuccess(response.data.message);
            })
            .catch((error) => {
              this.showError();
            });
        }
        this.form.links.splice(index, 1);
      });
    },

    // delete a genre of the movie
    destroyGenre(event) {
      if (event.genre !== undefined) {
        axios
          .delete(url + "/admin/movies/genres/destroy/" + event.id)
          .then((response) => this.showSuccess(response.data.message))
          .catch((error) => this.showError());
      }
    },
  },
  computed: {
    // filter the movies array with the search matches and return the filtered array
    filteredMovies() {
      return this.movies.filter((movie) => {
        return (
          movie.title.toLowerCase().match(this.search.toLowerCase()) ||
          movie.tmdb_id.toString().match(this.search.toLowerCase())
        );
      });
    },
  },
  mixins: [notifications, settings],
};
</script>
