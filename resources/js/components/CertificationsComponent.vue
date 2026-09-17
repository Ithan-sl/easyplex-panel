<template>
  <div class="row">
    <div class="col-lg-12 grid-margin stretch-card" v-if="index">
      <div class="card">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
              <button @click="create()" class="btn btn-primary mr-2">
                <em class="mdi mdi-plus"></em> Add Certification
              </button>
            </div>
            <div style="width: 300px;">
              <input
                class="form-control"
                placeholder="Search certification or country..."
                type="text"
                v-model="search"
              />
            </div>
          </div>

          <div class="table-responsive">
            <table aria-describedby="Certifications Table" class="table">
              <thead>
                <tr>
                  <th>Country Code</th>
                  <th>Certification</th>
                  <th>Meaning</th>
                  <th>Order</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                <tr :key="item.id" v-for="(item, idx) in paginated('filteredCertifications')">
                  <td><code>{{ item.country_code }}</code></td>
                  <td><span class="badge badge-info">{{ item.certification }}</span></td>
                  <td>{{ item.meaning }}</td>
                  <td>{{ item.order || 0 }}</td>
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
                :list="filteredCertifications"
                :per="10"
                name="filteredCertifications"
                tag="tbody"
                v-if="filteredCertifications && filteredCertifications.length"
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
              for="filteredCertifications"
              v-if="filteredCertifications && filteredCertifications.length"
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
            <h4 class="card-title mb-0">{{ edit ? 'Edit Certification' : 'Add Certification' }}</h4>
            <button @click="back()" class="btn btn-secondary btn-sm">
              <em class="mdi mdi-arrow-left"></em> Back
            </button>
          </div>

          <form @submit.prevent="edit ? update() : store()">
            <div class="row">
              <div class="col-md-4">
                <div class="form-group">
                  <label for="country_code">Country Code (e.g. US, BR, ES) *</label>
                  <input
                    class="form-control"
                    id="country_code"
                    placeholder="e.g. US, BR"
                    required
                    type="text"
                    v-model="form.country_code"
                  />
                </div>
              </div>

              <div class="col-md-4">
                <div class="form-group">
                  <label for="certification">Certification *</label>
                  <input
                    class="form-control"
                    id="certification"
                    placeholder="e.g. PG-13, 16, L"
                    required
                    type="text"
                    v-model="form.certification"
                  />
                </div>
              </div>

              <div class="col-md-4">
                <div class="form-group">
                  <label for="order">Display Order</label>
                  <input
                    class="form-control"
                    id="order"
                    placeholder="0"
                    type="number"
                    v-model="form.order"
                  />
                </div>
              </div>
            </div>

            <div class="row">
              <div class="col-md-12">
                <div class="form-group">
                  <label for="meaning">Meaning / Description</label>
                  <textarea
                    class="form-control"
                    id="meaning"
                    placeholder="Appropriate for age 16 and older..."
                    rows="3"
                    v-model="form.meaning"
                  ></textarea>
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
      certifications: [],
      search: "",
      paginate: ["filteredCertifications"],
      form: {
        id: null,
        country_code: "",
        certification: "",
        meaning: "",
        order: 0,
      },
    };
  },
  async mounted() {
    this.loadData();
  },
  computed: {
    filteredCertifications() {
      if (!this.search) return this.certifications;
      const s = this.search.toLowerCase();
      return this.certifications.filter(
        (c) =>
          (c.certification || "").toLowerCase().includes(s) ||
          (c.country_code || "").toLowerCase().includes(s) ||
          (c.meaning || "").toLowerCase().includes(s)
      );
    },
  },
  methods: {
    async loadData() {
      try {
        const response = await axios.get(url + "/admin/certification/datacertifications");
        this.certifications = response.data || [];
      } catch (e) {
        this.showError();
      }
    },
    create() {
      this.form = { id: null, country_code: "", certification: "", meaning: "", order: 0 };
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
        const response = await axios.post(url + "/admin/certification/store", this.form);
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
          url + "/admin/certifications/update/" + this.form.id,
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
          const response = await axios.delete(url + "/admin/certifications/destroy/" + id);
          const cIndex = this.certifications.findIndex((c) => c.id === id);
          if (cIndex !== -1) {
            this.certifications.splice(cIndex, 1);
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
