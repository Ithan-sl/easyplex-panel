<template>
  <div class="row">
    <div class="col-md-12 grid-margin">
      <div class="d-flex justify-content-between flex-wrap">
        <div class="d-flex align-items-end flex-wrap">
          <div class="d-flex"></div>
        </div>
        <div class="d-flex justify-content-between align-items-end flex-wrap">
          <button @click="create()" class="btn btn-primary mt-2 mt-xl-0" v-if="index">Add Upcoming</button>
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
            <table aria-describedby="Upcoming Table" class="table">
              <thead>
                <tr>
                  <th id="cover">Cover</th>
                  <th id="id">ID</th>
                  <th id="name">Title</th>
                  <th id="options">OPTIONS</th>
                </tr>
              </thead>
              <tbody>
                <tr :key="index" v-for="(upcoming, index) in paginated('filteredUpcomings')">
                  <td>
                    <img :src="upcoming.poster_path" alt="poster path" height="80" width="80" />
                  </td>
                  <td>{{upcoming.id}}</td>
                  <td>{{upcoming.title}}</td>
                  <td>

                        <div class="list-icons">
                      <a
                        @click="editing(upcoming)"
                        class="list-icons-item mr-2"
                        data-original-title="Edit"
                        rel="tooltip"
                        title
                      >
                        <em class="mdi mdi-pencil fa-lg" style="color:#4d83ff"></em>
                      </a>
                      <a
                        @click="destroy(upcoming.id, index)"
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
                :list="filteredUpcomings"
                :per="5"
                name="filteredUpcomings"
                tag="tbody"
                v-if="filteredUpcomings.length"
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
                for="filteredUpcomings"
                v-if="filteredUpcomings.length"
              ></paginate-links>

          </div>
        </div>
      </div>
    </div>

    <!-- Upcoming Create ||  Edit -->

    <div class="col-lg-12 grid-margin stretch-card" v-if="add || edit">
      <div class="card">
        <div class="card-body">
          <section>


   
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


            <div class="row">
              <div class="col-md-12">
                <div class="form-group">
                  <label for="title">Upcoming Title</label>
                  <input class="form-control" id="title" type="text" v-model="form.upcoming.title" />
                </div>
              </div>
            </div>

            <div class="row">
              <div class="col-md-12">
                <div class="form-group">
                  <label for="title">Overview</label>
                  <textarea
                    class="form-control pb-3"
                    id="overview"
                    required
                    rows="8"
                    v-model="form.upcoming.overview"
                  ></textarea>
                </div>
              </div>
            </div>

            <div class="row">
              <div class="col-md-12">
                <div class="form-group">
                  <img
                    :src="form.upcoming.poster_path"
                    alt="poster path"
                    class="poster"
                    data-loaded="true"
                    height="316"
                    v-if="form.upcoming.poster_path"
                    width="210"
                  />
                  <input
                    class="form-control"
                    id="poster_path"
                    name="poster_path"
                    placeholder="Poster Image Link or Upload"
                    required
                    type="text"
                    v-model="form.upcoming.poster_path"
                  />
                </div>
              </div>
            </div>

            <div class="row">
              <div class="col-md-12">
                <div class="form-group">
                  <label for="title">Backdrop Path</label>
                  <input
                    class="form-control"
                    id="backdrop_path"
                    name="backdrop_path"
                    placeholder="Poster Image Link"
                    required
                    type="text"
                    v-model="form.upcoming.backdrop_path"
                  />
                </div>
              </div>
            </div>

            <div class="row">
              <div class="col-md-12">
                <div class="form-group">
                  <label for="title">Genre</label>
                  <input
                    class="form-control"
                    id="link"
                    placeholder="Trailer ID"
                    required
                    type="text"
                    v-model="form.upcoming.genre"
                  />
                </div>
              </div>
            </div>

            <div class="row">
              <div class="col-md-12">
                <div class="form-group">
                  <label for="release_date">Release Date</label>
                  <input
                    class="form-control"
                    id="release_date"
                    placeholder="Release Date"
                    required
                    type="Date"
                    v-model="form.upcoming.release_date"
                  />
                </div>
              </div>
            </div>

            <div class="row">
              <div class="col-md-12">
                <div class="form-group">
                  <label for="title">Trailer</label>
                  <input
                    class="form-control"
                    id="link"
                    placeholder="Trailer ID"
                    required
                    type="text"
                    v-model="form.upcoming.trailer_id"
                  />
                </div>
              </div>
            </div>

            <div class="row justify-content-end">

              <div class="col-auto">
                <button
                  @click.prevent="store()"
                  class="btn btn-primary mr-2"
                  type="submit"
                  v-if="add"
                >Save</button>
                <button
                  @click.prevent="update()"
                  class="btn btn-primary mr-2"
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
      upcomings: [],
      paginate: ["upcomings", "filteredUpcomings"],
      form: {
        upcoming: {
          name: "",
          overview: "",
          link: "",
          backdrop_path: "",
          poster_path: "",
        },
        notification: false,
      },
      poster: null,
      backdrop: null,
      search: "",
    };
  },
  async mounted() {
    const response = await axios.get(url + "/admin/upcoming/data");
    this.upcomings = response.data;
  },
  methods: {
    create() {
      this.index = false;
      this.edit = false;
      this.add = true;
      this.tmdb = null;
    },
    back() {
      this.form.upcoming = "";
      this.tmdb = null;
      this.add = false;
      this.edit = false;
      this.index = true;
    },
    editing(upcoming) {
      this.index = false;
      this.add = false;
      this.edit = true;
      this.form.upcoming = upcoming;
      this.tmdb = upcoming.tmdb_id;
    },

    // get the movie data from the tmdb api
    async getMovie() {
      let response = await http.get(
        "https://api.themoviedb.org/3/movie/" +
          this.tmdb +
          "?api_key=" +
          this.settings.tmdb_api_key +
          "&language=" +
          this.settings.tmdb_lang.iso_639_1 +
          "&append_to_response=videos"
      );

      const upcoming = _.mapKeys(response.data, function (value, key) {
        if (key === "id") {
          return "tmdb_id";
        }
        return key;
      });
      upcoming.id = this.form.upcoming.id;
      if (typeof upcoming.videos.results[0] !== "undefined") {
        upcoming.trailer_id = upcoming.videos.results[0].key;
      } else if (this.edit && this.form.upcoming.trailer_id) {
        upcoming.trailer_id = this.form.upcoming.trailer_id;
      } else {
        response = await http.get(
          "https://api.themoviedb.org/3/movie/" +
            this.tmdb +
            "/videos?api_key=" +
            this.settings.tmdb_api_key
        );
        if (response.data.results[0]) {
          upcoming.trailer_id = response.data.results[0].key;
        }
      }

      if (upcoming.genres[0] !== "undefined") {
        upcoming.genre = upcoming.genres[0].name;
      }

      if (upcoming.poster_path) {
        upcoming.poster_path =
          this.settings.imdb_cover_path + upcoming.poster_path;
      }
      if (upcoming.backdrop_path) {
        upcoming.backdrop_path =
          this.settings.imdb_cover_path + upcoming.backdrop_path;
      }

      this.form.upcoming = upcoming;
    },
    

    // create a upcoming in database
    async store() {
      try {
        const response = await axios.post(
          url + "/admin/upcoming/store",
          this.form
        );

        this.add = false;
        this.edit = false;
        this.index = true;
        this.form.upcoming = {
          name: "",
          overview: "",
          link: "",
          backdrop_path: "",
          poster_path: "",
        };
        this.poster = null;
        this.backdrop = null;
        this.upcomings.unshift(response.data.body);
        this.showSuccess(response.data.message);
      } catch (error) {
        this.showError(error.response);
      }
    },
    // update a upcoming from database
    async update() {
      try {
        const response = await axios.put(
          url + "/admin/upcoming/update/" + this.form.upcoming.id,
          this.form
        );

        this.add = false;
        this.edit = false;
        this.index = true;
        this.form.upcoming = {
          name: "",
          overview: "",
          link: "",
          backdrop_path: "",
          poster_path: "",
        };
        this.showSuccess(response.data.message);
      } catch (error) {
        this.showError(error.response);
      }
    },
    // delete a upcoming from database
    destroy(id, index) {
      this.showConfirm(async () => {
        try {
          const response = await axios.delete(
            url + "/admin/upcoming/destroy/" + id
          );
          const upcomingIndex = this.upcomings.findIndex(
            (upcoming) => upcoming.id === id
          );
          this.upcomings.splice(upcomingIndex, 1);
          this.paginate.filteredUpcomings.list.splice(index, 1);
          this.showSuccess(response.data.message);
        } catch (error) {
          this.showError();
        }
      });
    },
    // store a new poster in storage
    async storePoster(event) {
      if (typeof event.target.files[0] !== "undefined") {
        try {
          this.poster = event.target.files[0];
          const data = new FormData();
          data.append("image", this.poster);

          const response = await axios.post(
            url + "/admin/upcoming/image/store",
            data
          );
          this.form.upcoming.poster_path = response.data.image_path;
        } catch (error) {
          this.showError(error.response);
        }
      }
    },
    // store a new backdrop in storage
    async storeBackdrop(event) {
      if (typeof event.target.files[0] !== "undefined") {
        try {
          this.backdrop = event.target.files[0];
          const data = new FormData();
          data.append("image", this.backdrop);

          const response = await axios.post(
            url + "/admin/upcoming/image/store",
            data
          );
          this.form.upcoming.backdrop_path = response.data.image_path;
        } catch (error) {
          this.showError(error.response);
        }
      }
    },
  },
  computed: {
    // returns the upcoming array filtered by the search
    filteredUpcomings() {
      return this.upcomings.filter((upcoming) => {
        return upcoming.title.toLowerCase().match(this.search.toLowerCase());
      });
    },
  },
  mixins: [notifications, settings],
};
</script>
