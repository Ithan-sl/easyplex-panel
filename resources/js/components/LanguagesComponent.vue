<template>
  <div class="row">
    <div class="col-lg-12 grid-margin stretch-card" v-if="index">
      <div class="card">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
              <button @click="create()" class="btn btn-primary mr-2">
                <em class="mdi mdi-plus"></em> Add Language
              </button>
              <button @click="fetchTmdb()" class="btn btn-dark" :disabled="loadingFetch">
                <em class="mdi mdi-download"></em> {{ loadingFetch ? 'Importing...' : 'Sync from TMDB' }}
              </button>
            </div>
            <div style="width: 300px;">
              <input
                class="form-control"
                placeholder="Search language..."
                type="text"
                v-model="search"
              />
            </div>
          </div>

          <div class="table-responsive">
            <table aria-describedby="Languages Table" class="table">
              <thead>
                <tr>
                  <th>ISO Code</th>
                  <th>English Name</th>
                  <th>Native Name</th>
                  <th>Featured</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                <tr :key="item.id" v-for="(item, idx) in paginated('filteredLanguages')">
                  <td><code>{{ item.iso_639_1 }}</code></td>
                  <td><strong>{{ item.english_name }}</strong></td>
                  <td>{{ item.name }}</td>
                  <td>
                    <span :class="item.featured ? 'badge badge-success' : 'badge badge-secondary'">
                      {{ item.featured ? 'Yes' : 'No' }}
                    </span>
                  </td>
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
                :list="filteredLanguages"
                :per="10"
                name="filteredLanguages"
                tag="tbody"
                v-if="filteredLanguages && filteredLanguages.length"
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
              for="filteredLanguages"
              v-if="filteredLanguages && filteredLanguages.length"
            ></paginate-links>
          </div>
        </div>
      </div>
    </div>

    <!-- Create / Edit -->
    <div class="col-lg-12 grid-margin stretch-card" v-if="add || edit">
      <div class="card">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="card-title mb-0">{{ edit ? 'Edit Language' : 'Add Language' }}</h4>
            <button @click="back()" class="btn btn-secondary btn-sm">
              <em class="mdi mdi-arrow-left"></em> Back
            </button>
          </div>

          <form @submit.prevent="edit ? update() : store()">
            <div class="row">
              <div class="col-md-4">
                <div class="form-group">
                  <label for="iso">ISO 639-1 Code *</label>
                  <input
                    class="form-control"
                    id="iso"
                    placeholder="e.g. pt, en, es"
                    required
                    type="text"
                    v-model="form.iso_639_1"
                  />
                </div>
              </div>

              <div class="col-md-4">
                <div class="form-group">
                  <label for="english_name">English Name *</label>
                  <input
                    class="form-control"
                    id="english_name"
                    placeholder="e.g. Portuguese"
                    required
                    type="text"
                    v-model="form.english_name"
                  />
                </div>
              </div>

              <div class="col-md-4">
                <div class="form-group">
                  <label for="name">Native Name *</label>
                  <input
                    class="form-control"
                    id="name"
                    placeholder="e.g. Português"
                    required
                    type="text"
                    v-model="form.name"
                  />
                </div>
              </div>
            </div>

            <div class="row">
              <div class="col-md-4">
                <div class="form-check mt-3">
                  <label class="form-check-label">
                    <input class="form-check-input" type="checkbox" v-model="form.featured" />
                    Featured Language
                  </label>
                </div>
              </div>
            </div>

            <div class="row justify-content-end mt-4">
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
      loadingFetch: false,
      languages: [],
      search: "",
      paginate: ["filteredLanguages"],
      form: {
        id: null,
        iso_639_1: "",
        english_name: "",
        name: "",
        featured: false,
      },
    };
  },
  async mounted() {
    this.loadData();
  },
  computed: {
    filteredLanguages() {
      if (!this.search) return this.languages;
      const s = this.search.toLowerCase();
      return this.languages.filter(
        (l) =>
          (l.english_name || "").toLowerCase().includes(s) ||
          (l.name || "").toLowerCase().includes(s) ||
          (l.iso_639_1 || "").toLowerCase().includes(s)
      );
    },
  },
  methods: {
    async loadData() {
      try {
        const response = await axios.get(url + "/admin/languages/admindata");
        this.languages = (response.data && response.data.data) ? response.data.data : (response.data || []);
      } catch (e) {
        this.showError();
      }
    },
    create() {
      this.form = { id: null, iso_639_1: "", english_name: "", name: "", featured: false };
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
      this.form = Object.assign({}, item);
      this.form.featured = !!item.featured;
      this.index = false;
      this.add = false;
      this.edit = true;
    },
    async store() {
      try {
        const response = await axios.post(url + "/admin/languages/store", this.form);
        this.loadData();
        this.back();
        this.showSuccess(response.data.message || "Created successfully");
      } catch (error) {
        this.showError(error.response);
      }
    },
    async update() {
      try {
        const response = await axios.put(
          url + "/admin/languages/update/" + this.form.id,
          this.form
        );
        this.loadData();
        this.back();
        this.showSuccess(response.data.message || "Updated successfully");
      } catch (error) {
        this.showError(error.response);
      }
    },
    destroy(id, index) {
      this.showConfirm(async () => {
        try {
          const response = await axios.delete(url + "/admin/languages/destroy/" + id);
          const lIndex = this.languages.findIndex((l) => l.id === id);
          if (lIndex !== -1) {
            this.languages.splice(lIndex, 1);
          }
          this.showSuccess(response.data.message || "Deleted successfully");
        } catch (error) {
          this.showError();
        }
      });
    },
    async fetchTmdb() {
      this.loadingFetch = true;
      try {
        const res = await axios.get(url + "/admin/languages/tmdb");
        if (Array.isArray(res.data) && res.data.length) {
          await axios.post(url + "/admin/languages/fetch", res.data);
          this.showSuccess("Languages synced from TMDB successfully");
          this.loadData();
        }
      } catch (e) {
        this.showError();
      } finally {
        this.loadingFetch = false;
      }
    },
  },
  mixins: [notifications, settings],
};
</script>
