<template>
  <div class="row">
    <div class="col-md-12 grid-margin">
      <div class="d-flex justify-content-between flex-wrap">
        <div class="d-flex align-items-end flex-wrap">
          <div class="d-flex"></div>
        </div>
        <div class="d-flex justify-content-between align-items-end flex-wrap">
          <button @click="create()" class="btn btn-primary mt-2 mt-xl-0" v-if="index">Add Stream</button>
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
            <table aria-describedby="Streaming Table" class="table">
              <thead>
                <tr>
                  <th id="cover">Cover</th>
                  <th id="id">ID</th>
                  <th id="name">NAME</th>
                  <th id="options">OPTIONS</th>
                </tr>
              </thead>
              <tbody>
                <tr :key="index" v-for="(livetv, index) in paginated('filteredLivetvs')">
                  <td>
                    <img :src="livetv.poster_path" alt="poster path" height="80" width="80" />
                  </td>
                  <td>{{livetv.id}}</td>
                  <td>{{livetv.name}}</td>
                  <td>


             <div class="list-icons">
                      <a
                        @click="editing(livetv)"
                        class="list-icons-item mr-2"
                        data-original-title="Edit"
                        rel="tooltip"
                        title
                      >
                        <em class="mdi mdi-pencil fa-lg" style="color:#4d83ff"></em>
                      </a>
                      <a
                       @click="destroy(livetv.id, index)"
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
                :list="filteredLivetvs"
                :per="5"
                name="filteredLivetvs"
                tag="tbody"
                v-if="filteredLivetvs.length"
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
                for="filteredLivetvs"
                v-if="filteredLivetvs.length"
              ></paginate-links>
          </div>
        </div>
      </div>
    </div>

    <!-- LiveTV Create ||  Edit -->

    <div class="col-lg-12 grid-margin stretch-card" v-if="add || edit">
      <div class="card">
        <div class="card-body">
          <section>


            <div class="form-group">
                  <div class="col-md-3">
                    <div class="form-check">
                      <div class="custom-control custom-checkbox">
                        <input
                          class="custom-control-input"
                          id="vip"
                          type="checkbox"
                          v-model="form.livetv.vip"
                        />
                        <label class="custom-control-label" for="vip">
                         VIP
                        </label>
                      </div>
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
                              v-model="form.livetv.featured"
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
                            <label class="custom-control-label" for="notification">Push notification</label>
                          </div>
                        </div>
                      </div>


            <div class="row">
              <div class="col-md-12">
                <div class="form-group">
                  <label for="title">Title</label>
                  <input placeholder="Stream Name" class="form-control" id="title" type="text" v-model="form.livetv.name" />
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
                    placeholder="Stream Overview"
                    required
                    rows="8"
                    v-model="form.livetv.overview"
                  ></textarea>
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
                          v-model="form.livetv.genres"
                        ></multiselect>
                      </div>
                    </div>
                  </div>


            <div class="row">
              <div class="col-md-12">
                <div class="form-group">
                  <label for="title">BackDrop Path</label>
                  <input
                    class="form-control"
                    id="backdrop_path"
                    name="backdrop_path"
                    placeholder="Backdrop Image"
                    required
                    type="text"
                    v-model="form.livetv.backdrop_path"
                  />
                     <img
                    :src="form.livetv.poster_path"
                    alt="poster path"
                    class="back_poster"
                    data-loaded="true"
                    height="316"
                    v-if="form.livetv.poster_path"
                    width="210"
                  />
                </div>
              </div>
            </div>


            <div class="row">
              <div class="col-md-12">
                <div class="form-group">
                  <label for="poster_path">Poster Path</label>
                  <input
                    class="form-control"
                    id="poster_path"
                    name="poster_path"
                    placeholder="Poster Image Link or Upload"
                    required
                    type="text"
                    v-model="form.livetv.poster_path"
                  />

                </div>
              </div>
            </div>

            <div class="row">
              <div class="col-md-12">
                <div class="form-group">
                  <label>Poster Upload</label>
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
                      <button class="file-upload-browse btn btn-primary" type="button">Upload</button>
                    </span>
                  </div>
                </div>
              </div>
            </div>

            <div class="row">
              <div class="col-md-12">
                <div class="form-group">
                  <label for="title">Link</label>
                  <input
                    class="form-control"
                    id="link"
                    placeholder="HLS - M3U8 - DASH Steam Links"
                    required
                    type="text"
                    v-model="form.livetv.link"
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
      livetvs: [],
      paginate: ["livetvs", "filteredLivetvs"],
      form: {
        livetv: {
          name: "",
          overview: "",
          link: "",
          vip:"",
          featured:"",
          backdrop_path: "",
          poster_path: "",
          genres: [],
        },
        notification: false,
      },
      poster: null,
      backdrop: null,
      options: [],
      search: "",
    };
  },
  async mounted() {
    let response = await axios.get(url + "/admin/livetv/data");
    this.livetvs = response.data;

    response = await axios.get(url + "/admin/categories/data");
    this.options = response.data;


  },
  methods: {
    create() {
      this.index = false;
      this.edit = false;
      this.add = true;
    },
    back() {
      this.form.livetv = {
        name: "",
        overview: "",
        link: "",
        vip:"",
        featured:"",
        backdrop_path: "",
        poster_path: "",
      };
      this.add = false;
      this.edit = false;
      this.index = true;
    },
    editing(livetv) {
      this.index = false;
      this.add = false;
      this.edit = true;
      this.form.livetv = livetv;
    },
    // create a livetv in database
    async store() {
      try {
        const response = await axios.post(
          url + "/admin/livetv/store",
          this.form
        );

        this.add = false;
        this.edit = false;
        this.index = true;
        this.form.livetv = {
          name: "",
          overview: "",
          link: "",
          vip:"",
          backdrop_path: "",
          poster_path: "",
        };
        this.poster = null;
        this.backdrop = null;
        this.livetvs.unshift(response.data.body);
        this.showSuccess(response.data.message);
      } catch (error) {
        this.showError(error.response);
      }
    },
    // update a livetv from database
    async update() {
      try {
        const response = await axios.put(
          url + "/admin/livetv/update/" + this.form.livetv.id,
          this.form
        );

        this.add = false;
        this.edit = false;
        this.index = true;
        this.form.livetv = {
          name: "",
          overview: "",
          link: "",
          vip:"",
          backdrop_path: "",
          poster_path: "",
        };
        this.showSuccess(response.data.message);
      } catch (error) {
        this.showError(error.response);
      }
    },



// delete a genre of the movie
    destroyGenre(event) {
      if (event.genre !== undefined) {
        axios
          .delete(url + "/admin/livetv/genres/destroy/" + event.id)
          .then((response) => this.showSuccess(response.data.message))
          .catch((error) => this.showError());
      }
    },

    // delete a livetv from database
    destroy(id, index) {
      this.showConfirm(async () => {
        try {
          const response = await axios.delete(
            url + "/admin/livetv/destroy/" + id
          );
          const livetvIndex = this.livetvs.findIndex(
            (livetv) => livetv.id === id
          );
          this.livetvs.splice(livetvIndex, 1);
          this.paginate.filteredLivetvs.list.splice(index, 1);
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
            url + "/admin/livetv/image/store",
            data
          );
          this.form.livetv.poster_path = response.data.image_path;
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
            url + "/admin/livetv/image/store",
            data
          );
          this.form.livetv.backdrop_path = response.data.image_path;
        } catch (error) {
          this.showError(error.response);
        }
      }
    },
  },
  computed: {
    // returns the livetv array filtered by the search
    filteredLivetvs() {
      return this.livetvs.filter((livetv) => {
        return livetv.name.toLowerCase().match(this.search.toLowerCase());
      });
    },
  },
  mixins: [notifications, settings],
};
</script>


