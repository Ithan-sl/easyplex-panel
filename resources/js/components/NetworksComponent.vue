<template>
  <div class="row">
    <div class="col-lg-12 grid-margin stretch-card" v-if="index">
      <div class="card">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
              <button @click="create()" class="btn btn-primary">
                <em class="mdi mdi-plus"></em> Add Network
              </button>
            </div>
            <div style="width: 300px;">
              <input
                class="form-control"
                placeholder="Search network..."
                type="text"
                v-model="search"
              />
            </div>
          </div>

          <div class="table-responsive">
            <table aria-describedby="Networks Table" class="table">
              <thead>
                <tr>
                  <th>Logo</th>
                  <th>Name</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                <tr :key="item.id" v-for="(item, idx) in paginated('filteredNetworks')">
                  <td>
                    <img
                      :src="item.logo_path || '/img/placeholder.png'"
                      alt="logo"
                      height="40"
                      style="object-fit: contain; max-width: 100px; background: #222; padding: 4px; border-radius: 4px;"
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
                :list="filteredNetworks"
                :per="10"
                name="filteredNetworks"
                tag="tbody"
                v-if="filteredNetworks && filteredNetworks.length"
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
              for="filteredNetworks"
              v-if="filteredNetworks && filteredNetworks.length"
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
            <h4 class="card-title mb-0">{{ edit ? 'Edit Network' : 'Add Network' }}</h4>
            <button @click="back()" class="btn btn-secondary btn-sm">
              <em class="mdi mdi-arrow-left"></em> Back
            </button>
          </div>

          <form @submit.prevent="edit ? update() : store()">
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label for="name">Network Name *</label>
                  <input
                    class="form-control"
                    id="name"
                    placeholder="e.g. Netflix, HBO Max, Disney+, Apple TV+"
                    required
                    type="text"
                    v-model="form.name"
                  />
                </div>
              </div>

              <div class="col-md-6">
                <div class="form-group">
                  <label for="logo_path">Logo URL</label>
                  <input
                    class="form-control"
                    id="logo_path"
                    placeholder="https://..."
                    type="text"
                    v-model="form.logo_path"
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
      networks: [],
      search: "",
      paginate: ["filteredNetworks"],
      form: {
        id: null,
        name: "",
        logo_path: "",
      },
    };
  },
  async mounted() {
    this.loadData();
  },
  computed: {
    filteredNetworks() {
      if (!this.search) return this.networks;
      return this.networks.filter((n) =>
        (n.name || "").toLowerCase().includes(this.search.toLowerCase())
      );
    },
  },
  methods: {
    async loadData() {
      try {
        const response = await axios.get(url + "/admin/networks/datanetworks");
        this.networks = response.data || [];
      } catch (e) {
        this.showError();
      }
    },
    create() {
      this.form = { id: null, name: "", logo_path: "" };
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
        const response = await axios.post(url + "/admin/networks/store", this.form);
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
          url + "/admin/networks/update/" + this.form.id,
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
          const response = await axios.delete(url + "/admin/networks/destroy/" + id);
          const nIndex = this.networks.findIndex((n) => n.id === id);
          if (nIndex !== -1) {
            this.networks.splice(nIndex, 1);
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
