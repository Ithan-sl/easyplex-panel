<template>
  <div class="row">
    <!-- List View -->
    <div class="col-lg-12 grid-margin stretch-card" v-if="index">
      <div class="card">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
              <button @click="create()" class="btn btn-primary">
                <em class="mdi mdi-plus"></em> Add Preview
              </button>
            </div>
            <div style="width: 300px;">
              <input
                class="form-control"
                placeholder="Search previews..."
                type="text"
                v-model="search"
              />
            </div>
          </div>

          <div class="table-responsive">
            <table aria-describedby="Previews Table" class="table">
              <thead>
                <tr>
                  <th>Cover</th>
                  <th>Title</th>
                  <th>Link / Stream</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                <tr :key="item.id" v-for="(item, idx) in paginated('filteredPreviews')">
                  <td>
                    <img
                      :src="item.minicover || item.backdrop_path || '/img/placeholder.png'"
                      alt="cover"
                      height="60"
                      width="60"
                      style="object-fit: cover; border-radius: 4px;"
                    />
                  </td>
                  <td><strong>{{ item.title }}</strong></td>
                  <td style="max-width: 300px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                    {{ item.link }}
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
                :list="filteredPreviews"
                :per="10"
                name="filteredPreviews"
                tag="tbody"
                v-if="filteredPreviews && filteredPreviews.length"
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
              for="filteredPreviews"
              v-if="filteredPreviews && filteredPreviews.length"
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
            <h4 class="card-title mb-0">{{ edit ? 'Edit Preview' : 'Add Preview' }}</h4>
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
                    placeholder="Preview Title"
                    required
                    type="text"
                    v-model="form.preview.title"
                  />
                </div>
              </div>

              <div class="col-md-6">
                <div class="form-group">
                  <label for="link">Video / Stream Link *</label>
                  <input
                    class="form-control"
                    id="link"
                    placeholder="https://..."
                    required
                    type="text"
                    v-model="form.preview.link"
                  />
                </div>
              </div>
            </div>

            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label for="minicover">Mini Cover URL</label>
                  <input
                    class="form-control"
                    id="minicover"
                    placeholder="https://..."
                    type="text"
                    v-model="form.preview.minicover"
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
                    v-model="form.preview.backdrop_path"
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
      previews: [],
      search: "",
      paginate: ["filteredPreviews"],
      form: {
        preview: {
          title: "",
          link: "",
          minicover: "",
          backdrop_path: "",
        },
      },
    };
  },
  async mounted() {
    this.loadData();
  },
  computed: {
    filteredPreviews() {
      if (!this.search) return this.previews;
      return this.previews.filter((p) =>
        (p.title || "").toLowerCase().includes(this.search.toLowerCase())
      );
    },
  },
  methods: {
    async loadData() {
      try {
        const response = await axios.get(url + "/admin/preview/data");
        this.previews = response.data || [];
      } catch (e) {
        this.showError();
      }
    },
    create() {
      this.form.preview = { title: "", link: "", minicover: "", backdrop_path: "" };
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
      this.form.preview = Object.assign({}, item);
      this.index = false;
      this.add = false;
      this.edit = true;
    },
    async store() {
      try {
        const response = await axios.post(url + "/admin/preview/store", this.form);
        this.previews.unshift(response.data.body);
        this.back();
        this.showSuccess(response.data.message || "Created successfully");
      } catch (error) {
        this.showError(error.response);
      }
    },
    async update() {
      try {
        const response = await axios.put(
          url + "/admin/preview/update/" + this.form.preview.id,
          this.form
        );
        const idx = this.previews.findIndex((p) => p.id === this.form.preview.id);
        if (idx !== -1) {
          this.$set(this.previews, idx, response.data.body);
        }
        this.back();
        this.showSuccess(response.data.message || "Updated successfully");
      } catch (error) {
        this.showError(error.response);
      }
    },
    destroy(id, index) {
      this.showConfirm(async () => {
        try {
          const response = await axios.delete(url + "/admin/preview/destroy/" + id);
          const pIndex = this.previews.findIndex((p) => p.id === id);
          if (pIndex !== -1) {
            this.previews.splice(pIndex, 1);
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
