<template>
  <div class="row">
    <div class="col-md-12 grid-margin">
      <div class="d-flex justify-content-between flex-wrap">
        <div class="d-flex align-items-end flex-wrap">
          <div class="d-flex"></div>
        </div>
        <div class="d-flex justify-content-between align-items-end flex-wrap">
          <button @click="create()" class="btn btn-primary mt-2 mt-xl-0" v-if="index">Add Ads</button>
          <button @click="back()" class="btn btn-primary mt-2 mt-xl-0" v-if="!index">Back</button>
        </div>
      </div>
    </div>

    <div class="col-lg-12 grid-margin stretch-card" v-if="index">
      <div class="card">
        <div class="card-body">
          <div class="table-responsive">
            <table aria-describedby="Ads Table" class="table">
              <thead>
                <tr>
                  <th class="text-center" id="id">ID</th>
                  <th class="text-center" id="title">Title</th>
                  <th class="text-center" id="ads link">Ads Url</th>
                  <th class="text-center" id="options">Options</th>
                </tr>
              </thead>
              <tbody>
                <tr :key="index" v-for="(ads, index) in paginated('filteredAllads')">
                  <td class="text-center">{{ads.id}}</td>
                  <td class="text-center">{{ads.title}}</td>
                  <td class="text-center">{{ads.clickThroughUrl}}</td>
                  <td class="text-center">
                    <div class="list-icons">
                      <a
                        @click="editing(ads)"
                        class="list-icons-item mr-2"
                        data-original-title="Edit"
                        rel="tooltip"
                        title
                      >
                        <em class="mdi mdi-pencil fa-lg" style="color:#4d83ff"></em>
                      </a>
                      <a
                        @click="destroy(ads.id, index)"
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
                :list="filteredAllads"
                :per="5"
                name="filteredAllads"
                tag="tbody"
                v-if="filteredAllads.length"
              ></paginate>

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
                for="filteredAllads"
                v-if="filteredAllads.length"
              ></paginate-links>
            </table>
          </div>
        </div>
      </div>
    </div>

    <div class="col-12 grid-margin stretch-card" v-if="add || edit">
      <div class="card">
        <div class="card-body">
          <form class="forms-sample">
            <div class="form-group">
              <label for="name">Title</label>
              <input
                class="form-control"
                id="title"
                placeholder="Title"
                type="text"
                v-model="form.ads.title"
              />
            </div>

            <div class="form-group">
              <label for="name">Link</label>
              <input
                class="form-control"
                id="link"
                placeholder="Google Ad Manager, the Google AdSense network, or any VAST-compliant ad server."
                type="text"
                v-model="form.ads.link"
              />
            </div>

            <div class="form-group">
              <label for="name">clickThroughUrl</label>
              <input
                class="form-control"
                id="clickThroughUrl"
                placeholder="clickThroughUrl"
                type="text"
                v-model="form.ads.clickThroughUrl"
              />
            </div>

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
          </form>
        </div>
      </div>
    </div>
  </div>
</template>
<script>
import { notifications } from "../mixins/notifications";

export default {
  data() {
    return {
      index: true,
      add: false,
      edit: false,
      search: "",
      form: {
        ads: {
          title: "",
          link: "",
          clickThroughUrl: "",
        },
      },
      allads: [],
      paginate: ["filteredAllads"],
    };
  },
  async mounted() {
    const response = await axios.get(url + "/admin/ads/data");
    this.allads = response.data;
  },

  methods: {
    create() {
      this.index = false;
      this.edit = false;
      this.add = true;
    },

    editing(ads) {
      this.index = false;
      this.edit = true;
      this.form.ads = ads;
      this.form.allads = "";
    },

    back() {
      this.form.ads = "";
      this.add = false;
      this.edit = false;
      this.index = true;
    },

    store() {
      axios
        .post(url + "/admin/ads/store", this.form)
        .then((response) => {
          this.add = false;
          this.edit = false;
          this.index = true;
          this.form.ads = {};
          this.allads.unshift(response.data.body);
          this.showSuccess(response.data.message);
        })
        .catch((error) => {
          this.showError(error.response);
        });
    },

    update() {
      axios
        .put(url + "/admin/ads/update/" + this.form.ads.id, this.form)
        .then((response) => {
          this.edit = false;
          this.index = true;
          this.form.allads = [];
          this.showSuccess(response.data.message);
        })
        .catch((error) => {
          this.showError(error.response);
        });
    },

    // delete a record (user) in the database
    destroy(id, index) {
      this.showConfirm(async () => {
        try {
          const response = await axios.delete(url + "/admin/ads/destroy/" + id);
          const adsIndex = this.allads.findIndex((ads) => ads.id === id);
          this.allads.splice(adsIndex, 1);
          this.paginate.filteredAllads.list.splice(index, 1);
          this.showSuccess(response.data.message);
        } catch (error) {
          this.showError();
        }
      });
    },
  },
  computed: {
    // filter the movies array with the search matches and return the filtered array
    filteredAllads() {
      return this.allads.filter((ads) => {
        return ads.title.toLowerCase().match(this.search.toLowerCase());
      });
    },
  },

  mixins: [notifications],
};
</script>