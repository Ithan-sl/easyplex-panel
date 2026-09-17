<template>
  <div class="row">
    <!-- List View -->
    <div class="col-lg-12 grid-margin stretch-card" v-if="index">
      <div class="card">
        <div class="card-body">
          <div class="row mb-3">
            <div class="col-md-6">
              <button @click="create()" class="btn btn-primary">
                <em class="mdi mdi-plus"></em> Add Featured
              </button>
            </div>
            <div class="col-md-6">
              <input
                class="form-control"
                placeholder="Search by title..."
                type="text"
                v-model="search"
              />
            </div>
          </div>

          <div class="table-responsive">
            <table aria-describedby="Featured Table" class="table">
              <thead>
                <tr>
                  <th>Poster</th>
                  <th>Title</th>
                  <th>Type</th>
                  <th>Genre</th>
                  <th>Position</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                <tr :key="item.id" v-for="(item, idx) in paginated('filteredFeatureds')">
                  <td>
                    <img
                      :src="item.poster_path || '/img/placeholder.png'"
                      alt="poster"
                      height="70"
                      width="50"
                      style="object-fit: cover; border-radius: 4px;"
                    />
                  </td>
                  <td><strong>{{ item.title }}</strong></td>
                  <td><span class="badge badge-info">{{ item.type || 'Custom' }}</span></td>
                  <td>{{ item.genre || '-' }}</td>
                  <td>{{ item.position || 0 }}</td>
                  <td>
                    <div class="list-icons">
                      <a @click="editing(item)" class="list-icons-item mr-2" title="Edit" style="cursor: pointer;">
                        <em class="mdi mdi-pencil fa-lg" style="color: #4d83ff"></em>
                      </a>
                      <a @click="destroy(item.id, idx)" class="list-icons-item text-danger" title="Delete" style="cursor: pointer;">
                        <em class="mdi mdi-delete fa-lg" style="color: red"></em>
                      </a>
                    </div>
                  </td>
                </tr>
              </tbody>

              <paginate
                :list="filteredFeatureds"
                :per="10"
                name="filteredFeatureds"
                tag="tbody"
                v-if="filteredFeatureds && filteredFeatureds.length"
              ></paginate>
            </table>

            <paginate-links
              :async="true"
              :classes="{
                'ul': 'pagination',
                'li': 'page-item',
                'a': 'page-link',
                '.next > a': 'next-link',
                '.prev > a': 'prev-link'
              }"
              :hide-single-page="true"
              :limit="5"
              :show-step-links="true"
              class="float-right mt-3"
              for="filteredFeatureds"
              v-if="filteredFeatureds && filteredFeatureds.length"
            ></paginate-links>
          </div>
        </div>
      </div>
    </div>

    <!-- Create / Edit Form -->
    <div class="col-lg-12 grid-margin stretch-card" v-if="add || edit">
      <div class="card">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="card-title mb-0">{{ edit ? 'Edit Featured' : 'Add Featured' }}</h4>
            <button @click="back()" class="btn btn-secondary btn-sm">
              <em class="mdi mdi-arrow-left"></em> Back
            </button>
          </div>

          <form @submit.prevent="edit ? update() : store()">
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label for="title">Title *</label>
                  <input
                    class="form-control"
                    id="title"
                    placeholder="Title"
                    required
                    type="text"
                    v-model="form.featured.title"
                  />
                </div>
              </div>

              <div class="col-md-3">
                <div class="form-group">
                  <label for="type">Type</label>
                  <select class="form-control" id="type" v-model="form.featured.type">
                    <option value="movie">Movie</option>
                    <option value="serie">Series</option>
                    <option value="anime">Anime</option>
                    <option value="custom">Custom</option>
                  </select>
                </div>
              </div>

              <div class="col-md-3">
                <div class="form-group">
                  <label for="position">Position</label>
                  <input
                    class="form-control"
                    id="position"
                    placeholder="0"
                    type="number"
                    v-model="form.featured.position"
                  />
                </div>
              </div>
            </div>

            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label for="genre">Genre</label>
                  <input
                    class="form-control"
                    id="genre"
                    placeholder="e.g. Action, Drama"
                    type="text"
                    v-model="form.featured.genre"
                  />
                </div>
              </div>

              <div class="col-md-6">
                <div class="form-group">
                  <label for="custom_link">Custom Link / Stream URL</label>
                  <input
                    class="form-control"
                    id="custom_link"
                    placeholder="https://..."
                    type="text"
                    v-model="form.featured.custom_link"
                  />
                </div>
              </div>
            </div>

            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label for="poster_path">Poster Image URL</label>
                  <input
                    class="form-control"
                    id="poster_path"
                    placeholder="https://..."
                    type="text"
                    v-model="form.featured.poster_path"
                  />
                </div>
              </div>

              <div class="col-md-6">
                <div class="form-group">
                  <label for="backdrop_path">Backdrop Image URL</label>
                  <input
                    class="form-control"
                    id="backdrop_path"
                    placeholder="https://..."
                    type="text"
                    v-model="form.featured.backdrop_path"
                  />
                </div>
              </div>
            </div>

            <div class="row">
              <div class="col-md-12">
                <div class="form-group">
                  <label for="overview">Overview</label>
                  <textarea
                    class="form-control"
                    id="overview"
                    placeholder="Description / Overview"
                    rows="4"
                    v-model="form.featured.overview"
                  ></textarea>
                </div>
              </div>
            </div>

            <div class="row justify-content-end mt-3">
              <div class="col-auto">
                <button @click="back()" class="btn btn-light mr-2" type="button">Cancel</button>
                <button class="btn btn-primary" type="submit">
                  {{ edit ? 'Update' : 'Save' }}
                </button>
              </div>
            </div>
          </form>
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
      featureds: [],
      search: "",
      paginate: ["filteredFeatureds"],
      form: {
        featured: {
          title: "",
          type: "movie",
          position: 0,
          genre: "",
          custom_link: "",
          poster_path: "",
          backdrop_path: "",
          overview: "",
        },
      },
    };
  },
  async mounted() {
    try {
      const response = await axios.get(url + "/admin/featured/data");
      this.featureds = response.data || [];
    } catch (e) {
      this.showError();
    }
  },
  computed: {
    filteredFeatureds() {
      if (!this.search) return this.featureds;
      return this.featureds.filter((item) =>
        (item.title || "").toLowerCase().includes(this.search.toLowerCase())
      );
    },
  },
  methods: {
    create() {
      this.form.featured = {
        title: "",
        type: "movie",
        position: 0,
        genre: "",
        custom_link: "",
        poster_path: "",
        backdrop_path: "",
        overview: "",
      };
      this.index = false;
      this.edit = false;
      this.add = true;
    },
    back() {
      this.add = false;
      this.edit = false;
      this.index = true;
    },
    editing(item) {
      this.form.featured = Object.assign({}, item);
      this.index = false;
      this.add = false;
      this.edit = true;
    },
    async store() {
      try {
        const response = await axios.post(url + "/admin/featured/store", this.form);
        this.featureds.unshift(response.data.body);
        this.back();
        this.showSuccess(response.data.message);
      } catch (error) {
        this.showError(error.response);
      }
    },
    async update() {
      try {
        const response = await axios.put(
          url + "/admin/featured/update/" + this.form.featured.id,
          this.form
        );
        const idx = this.featureds.findIndex((f) => f.id === this.form.featured.id);
        if (idx !== -1) {
          this.$set(this.featureds, idx, response.data.body);
        }
        this.back();
        this.showSuccess(response.data.message);
      } catch (error) {
        this.showError(error.response);
      }
    },
    destroy(id, index) {
      this.showConfirm(async () => {
        try {
          const response = await axios.delete(url + "/admin/featured/destroy/" + id);
          const fIndex = this.featureds.findIndex((f) => f.id === id);
          if (fIndex !== -1) {
            this.featureds.splice(fIndex, 1);
          }
          this.showSuccess(response.data.message);
        } catch (error) {
          this.showError();
        }
      });
    },
  },
  mixins: [notifications, settings],
};
</script>
