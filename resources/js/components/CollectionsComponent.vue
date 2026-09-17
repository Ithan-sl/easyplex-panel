<template>
  <div class="row">
    <div class="col-lg-12 grid-margin stretch-card" v-if="index">
      <div class="card">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
              <button @click="create()" class="btn btn-primary">
                <em class="mdi mdi-plus"></em> Add Collection
              </button>
            </div>
            <div style="width: 300px;">
              <input
                class="form-control"
                placeholder="Search collection..."
                type="text"
                v-model="search"
              />
            </div>
          </div>

          <div class="table-responsive">
            <table aria-describedby="Collections Table" class="table">
              <thead>
                <tr>
                  <th>Poster</th>
                  <th>Name</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                <tr :key="item.id" v-for="(item, idx) in paginated('filteredCollections')">
                  <td>
                    <img
                      :src="item.poster_path || '/img/placeholder.png'"
                      alt="poster"
                      height="70"
                      width="50"
                      style="object-fit: cover; border-radius: 4px;"
                    />
                  </td>
                  <td><strong>{{ item.name }}</strong></td>
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
                :list="filteredCollections"
                :per="10"
                name="filteredCollections"
                tag="tbody"
                v-if="filteredCollections && filteredCollections.length"
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
              for="filteredCollections"
              v-if="filteredCollections && filteredCollections.length"
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
            <h4 class="card-title mb-0">{{ edit ? 'Edit Collection' : 'Add Collection' }}</h4>
            <button @click="back()" class="btn btn-secondary btn-sm">
              <em class="mdi mdi-arrow-left"></em> Back
            </button>
          </div>

          <form @submit.prevent="edit ? update() : store()">
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label for="name">Collection Name *</label>
                  <input
                    class="form-control"
                    id="name"
                    placeholder="e.g. Marvel Cinematic Universe, Harry Potter"
                    required
                    type="text"
                    v-model="form.name"
                  />
                </div>
              </div>

              <div class="col-md-6">
                <div class="form-group">
                  <label for="poster_path">Poster URL</label>
                  <input
                    class="form-control"
                    id="poster_path"
                    placeholder="https://..."
                    type="text"
                    v-model="form.poster_path"
                  />
                </div>
              </div>
            </div>

            <div class="row">
              <div class="col-md-12">
                <div class="form-group">
                  <label for="backdrop_path">Backdrop URL</label>
                  <input
                    class="form-control"
                    id="backdrop_path"
                    placeholder="https://..."
                    type="text"
                    v-model="form.backdrop_path"
                  />
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
      collections: [],
      search: "",
      paginate: ["filteredCollections"],
      form: {
        id: null,
        name: "",
        poster_path: "",
        backdrop_path: "",
      },
    };
  },
  async mounted() {
    this.loadData();
  },
  computed: {
    filteredCollections() {
      if (!this.search) return this.collections;
      return this.collections.filter((c) =>
        (c.name || "").toLowerCase().includes(this.search.toLowerCase())
      );
    },
  },
  methods: {
    async loadData() {
      try {
        const response = await axios.get(url + "/admin/collections/datawebcollections");
        this.collections = response.data || [];
      } catch (e) {
        this.showError();
      }
    },
    create() {
      this.form = { id: null, name: "", poster_path: "", backdrop_path: "" };
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
      this.index = false;
      this.add = false;
      this.edit = true;
    },
    async store() {
      try {
        const response = await axios.post(url + "/admin/collections/store", this.form);
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
          url + "/admin/collections/update/" + this.form.id,
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
          const response = await axios.delete(url + "/admin/collections/destroy/" + id);
          const cIndex = this.collections.findIndex((c) => c.id === id);
          if (cIndex !== -1) {
            this.collections.splice(cIndex, 1);
          }
          this.showSuccess(response.data.message || "Deleted successfully");
        } catch (error) {
          this.showError();
        }
      });
    },
  },
  mixins: [notifications, settings],
};
</script>
