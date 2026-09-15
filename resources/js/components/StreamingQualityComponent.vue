<template>
  <div class="row">
    <div class="col-lg-12 grid-margin stretch-card">
      <div class="card">
        <div class="card-body">
          <div class="row">
            <div class="col-12">
              <div class="form-group">
                <label for="qualities">Qualities</label>
                <input
                  class="form-control"
                  placeholder="Server name"
                  type="text"
                  v-model="form.name"
                />
              </div>

              <button
                :disabled="!form.name"
                @click="store()"
                class="btn btn-info"
                type="submit"
                v-if="!edit"
              >Add</button>

              <button
                :disabled="!form.name"
                @click="updateSubmit()"
                class="btn btn-info"
                type="button"
                v-if="edit"
              >Update</button>

              <button @click="cancel()" class="btn btn-light" type="button" v-if="edit">Cancel</button>
            </div>
          </div>

          <div class="table-responsive">
            <table aria-describedby="Streaming Links Table" class="table">
              <thead>
                <tr>
                  <th id="actions">Name</th>
                  <th id="names">Actions</th>
                </tr>
              </thead>
              <tbody>
                <tr :key="index" v-for="(server, index) in paginated('servers')">
                  <td>{{server.name}}</td>
                  <td>


                     <div class="list-icons">
                      <a
                        @click="update(server, index)"
                        class="list-icons-item mr-2"
                        data-original-title="Edit"
                        rel="tooltip"
                        title
                      >
                        <em class="mdi mdi-pencil fa-lg" style="color:#4d83ff"></em>
                      </a>
                      <a
                        @click="destroy(server.id, index)"
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
                :list="servers"
                :per="5"
                name="servers"
                tag="tbody"
                v-if="servers && servers.length"
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
                for="servers"
              ></paginate-links>
          </div>
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
      form: {
        name: "",
        id: "",
      },
      servers: [],
      paginate: ["servers"],
      edit: false,
      editing: {
        server: "",
        index: "",
      },
    };
  },
  async mounted() {
    const response = await axios.get(url + "/admin/servers/data");
    this.servers = response.data;
  },
  methods: {
    // create a new server in database
    async store() {
      try {
        const response = await axios.post(
          url + "/admin/servers/store",
          this.form
        );
        this.servers.unshift(response.data.body);
        this.form.name = "";
        this.showSuccess(response.data.message);
      } catch (error) {
        this.showError(error.response);
      }
    },
    // delete a server from database
    destroy(id, index) {
      this.showConfirm(async () => {
        try {
          const response = await axios.delete(
            url + "/admin/servers/destroy/" + id
          );
          this.servers.splice(index, 1);
          this.showSuccess(response.data.message);
        } catch (error) {
          this.showError();
        }
      });
    },
    update(server, index) {
      this.form.name = server.name;
      this.edit = true;
      this.editing.server = server;
      this.editing.index = index;
    },
    // update a server from database
    async updateSubmit() {
      try {
        this.form.id = this.editing.server.id;
        const response = await axios.put(
          url + "/admin/servers/update/" + this.editing.server.id,
          this.form
        );
        this.servers[this.editing.index] = response.data.body;
        this.paginate.servers.list[this.editing.index] = response.data.body;
        this.form.name = "";
        this.edit = false;
        this.showSuccess(response.data.message);
      } catch (error) {
        this.showError(error.response);
      }
    },
    cancel() {
      this.edit = false;
      this.form.name = "";
    },
  },
  mixins: [notifications],
};
</script>


<style scoped>
</style>
