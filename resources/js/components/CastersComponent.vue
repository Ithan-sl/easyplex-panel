<template>
  <div class="row">
    <div class="col-lg-12 grid-margin stretch-card" v-if="index">
      <div class="card">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
              <button @click="create()" class="btn btn-primary">
                <em class="mdi mdi-plus"></em> Add Cast / Actor
              </button>
            </div>
            <div style="width: 300px;">
              <input
                class="form-control"
                placeholder="Search actor / director..."
                type="text"
                v-model="search"
              />
            </div>
          </div>

          <div class="table-responsive">
            <table aria-describedby="Casters Table" class="table">
              <thead>
                <tr>
                  <th>Photo</th>
                  <th>Name</th>
                  <th>Gender</th>
                  <th>Views</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                <tr :key="item.id" v-for="(item, idx) in paginated('filteredCasters')">
                  <td>
                    <img
                      :src="item.profile_path || '/img/placeholder.png'"
                      alt="profile"
                      height="50"
                      width="50"
                      style="object-fit: cover; border-radius: 50%;"
                    />
                  </td>
                  <td><strong>{{ item.name }}</strong></td>
                  <td>{{ item.gender === 1 ? 'Female' : (item.gender === 2 ? 'Male' : 'Other') }}</td>
                  <td>{{ item.views || 0 }}</td>
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
                :list="filteredCasters"
                :per="10"
                name="filteredCasters"
                tag="tbody"
                v-if="filteredCasters && filteredCasters.length"
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
              for="filteredCasters"
              v-if="filteredCasters && filteredCasters.length"
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
            <h4 class="card-title mb-0">{{ edit ? 'Edit Cast' : 'Add Cast' }}</h4>
            <button @click="back()" class="btn btn-secondary btn-sm">
              <em class="mdi mdi-arrow-left"></em> Back
            </button>
          </div>

          <form @submit.prevent="edit ? update() : store()">
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label for="name">Name *</label>
                  <input
                    class="form-control"
                    id="name"
                    placeholder="Full Name"
                    required
                    type="text"
                    v-model="form.name"
                  />
                </div>
              </div>

              <div class="col-md-3">
                <div class="form-group">
                  <label for="gender">Gender</label>
                  <select class="form-control" id="gender" v-model="form.gender">
                    <option :value="2">Male</option>
                    <option :value="1">Female</option>
                    <option :value="0">Other</option>
                  </select>
                </div>
              </div>

              <div class="col-md-3">
                <div class="form-group">
                  <label for="active">Status</label>
                  <select class="form-control" id="active" v-model="form.active">
                    <option :value="1">Active</option>
                    <option :value="0">Inactive</option>
                  </select>
                </div>
              </div>
            </div>

            <div class="row">
              <div class="col-md-12">
                <div class="form-group">
                  <label for="profile_path">Photo / Profile Image URL</label>
                  <input
                    class="form-control"
                    id="profile_path"
                    placeholder="https://image.tmdb.org/t/p/w500/..."
                    type="text"
                    v-model="form.profile_path"
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
      casters: [],
      search: "",
      paginate: ["filteredCasters"],
      form: {
        id: null,
        name: "",
        gender: 2,
        active: 1,
        profile_path: "",
      },
    };
  },
  async mounted() {
    this.loadData();
  },
  computed: {
    filteredCasters() {
      if (!this.search) return this.casters;
      return this.casters.filter((c) =>
        (c.name || "").toLowerCase().includes(this.search.toLowerCase())
      );
    },
  },
  methods: {
    async loadData() {
      try {
        const response = await axios.get(url + "/admin/casters/datacasters");
        this.casters = (response.data && response.data.data) ? response.data.data : (response.data || []);
      } catch (e) {
        this.showError();
      }
    },
    create() {
      this.form = { id: null, name: "", gender: 2, active: 1, profile_path: "" };
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
        const response = await axios.post(url + "/admin/casts/store", this.form);
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
          url + "/admin/casts/update/" + this.form.id,
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
          const response = await axios.delete(url + "/admin/casts/destroy/" + id);
          const cIndex = this.casters.findIndex((c) => c.id === id);
          if (cIndex !== -1) {
            this.casters.splice(cIndex, 1);
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
