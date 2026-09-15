<template>
  <div class="row">
    <div class="col-lg-12 grid-margin stretch-card">
      <div class="card">
        <div class="card-body">
          <div class="row">
            <div class="col-12">
              <div class="form-group">
                <label for="genre">Genre</label>
                <input
                  class="form-control"
                  id="genre"
                  placeholder="Genre name"
                  type="text"
                  v-model="genre"
                />
              </div>

              <button
                :disabled="!genre"
                @click="store()"
                class="btn btn-info"
                type="submit"
                v-if="!edit"
              >Add</button> 

              <button
                :disabled="!genre"
                @click="updateSubmit()"
                class="btn btn-info"
                type="button"
                v-if="edit"
              >Update</button>

              <button
                :disabled="!genre"
                @click="cancel()"
                class="btn btn-light"
                type="button"
                v-if="edit"
              >Cancel</button>
            </div>
          </div>

          <div class="table-responsive">
            <table aria-describedby="Genres Table" class="table">
              <thead>
                <tr>
                  <th id="name">Names</th>
                  <th id="Action">Actions</th>
                </tr>
              </thead>
              <tbody>
                <tr :key="index" v-for="(genre, index) in paginated('genres')">
                  <td>{{genre.name}}</td>
                  <td>
                    <div class="list-icons">
                      <a
                        @click="update(genre, index)"
                        class="list-icons-item mr-2"
                        data-original-title="Edit"
                        rel="tooltip"
                        title
                      >
                        <em class="mdi mdi-pencil fa-lg" style="color:#4d83ff"></em>
                      </a>
                      <a
                        @click="destroy(genre.id, index)"
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
                :list="genres"
                :per="10"
                name="genres"
                tag="tbody"
                v-if="genres && genres.length"
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
                for="genres"
              ></paginate-links>
          </div>
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
      genres: [],
      genre: "",
      paginate: ["genres"],
      edit: false,
      editing: {},
    };
  },
  async mounted() {
    const response = await axios.get(url + "/admin/categories/data");
    this.genres = response.data;
  },
  methods: {
    // create a genre in database
    async store() {
      try {
        const response = await axios.post(url + "/admin/categories/store", {
          name: this.genre,
        });
        this.genres.unshift(response.data.body);
        this.genre = "";
        this.showSuccess(response.data.message);
      } catch (error) {
        this.showError(error.response);
      }
    },
    // delete a genre from database
    destroy(id, index) {
      this.showConfirm(async () => {
        try {
          const response = await axios.delete(
            url + "/admin/categories/destroy/" + id
          );
          const genreIndex = this.genres.findIndex((genre) => genre.id === id);
          this.genres.splice(genreIndex, 1);
          this.paginate.genres.list.splice(index, 1);
          this.showSuccess(response.data.message);
        } catch (error) {
          this.showError();
        }
      });
    },
    update(genre, index) {
      this.edit = true;
      this.genre = genre.name;
      this.editing = genre;
      this.editing.index = index;
    },
    cancel() {
      this.edit = false;
      this.genre = "";
      this.editing = {};
    },
    // update a genre from database
    async updateSubmit() {
      try {
        const response = await axios.put(
          url + "/admin/categories/update/" + this.editing.id,
          { name: this.genre, id: this.editing.id }
        );
        this.genres[this.editing.index] = response.data.body;
        this.paginate.genres.list[this.editing.index] = response.data.body;
        this.edit = false;
        this.genre = "";
        this.editing = {};
        this.showSuccess(response.data.message);
      } catch (error) {
        this.showError(error.response);
      }
    },
  },
  mixins: [notifications, settings],
};
</script>
