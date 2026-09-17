<template>
  <div class="row">
    <!-- Headers Section -->
    <div class="col-lg-6 grid-margin stretch-card">
      <div class="card">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="card-title mb-0">HTTP Headers</h4>
            <button @click="openHeaderModal()" class="btn btn-primary btn-sm">
              <em class="mdi mdi-plus"></em> Add Header
            </button>
          </div>

          <div class="table-responsive">
            <table aria-describedby="Headers Table" class="table table-sm">
              <thead>
                <tr>
                  <th>Header Key</th>
                  <th>Value</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                <tr :key="h.id" v-for="(h, idx) in headers">
                  <td><code>{{ h.key || h.name }}</code></td>
                  <td>{{ h.value }}</td>
                  <td>
                    <div class="list-icons">
                      <a @click="editHeader(h)" class="list-icons-item mr-2" title="Edit" style="cursor: pointer;">
                        <em class="mdi mdi-pencil" style="color: #4d83ff"></em>
                      </a>
                      <a @click="deleteHeader(h.id, idx)" class="list-icons-item text-danger" title="Delete" style="cursor: pointer;">
                        <em class="mdi mdi-delete" style="color: red"></em>
                      </a>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
            <div v-if="!headers.length" class="text-center py-3 text-muted">No custom headers</div>
          </div>
        </div>
      </div>
    </div>

    <!-- User Agents Section -->
    <div class="col-lg-6 grid-margin stretch-card">
      <div class="card">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="card-title mb-0">User Agents</h4>
            <button @click="openUAModal()" class="btn btn-primary btn-sm">
              <em class="mdi mdi-plus"></em> Add User Agent
            </button>
          </div>

          <div class="table-responsive">
            <table aria-describedby="User Agents Table" class="table table-sm">
              <thead>
                <tr>
                  <th>Title / Name</th>
                  <th>User Agent String</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                <tr :key="ua.id" v-for="(ua, idx) in userAgents">
                  <td><strong>{{ ua.title || ua.name }}</strong></td>
                  <td style="max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                    {{ ua.useragent || ua.value }}
                  </td>
                  <td>
                    <div class="list-icons">
                      <a @click="editUA(ua)" class="list-icons-item mr-2" title="Edit" style="cursor: pointer;">
                        <em class="mdi mdi-pencil" style="color: #4d83ff"></em>
                      </a>
                      <a @click="deleteUA(ua.id, idx)" class="list-icons-item text-danger" title="Delete" style="cursor: pointer;">
                        <em class="mdi mdi-delete" style="color: red"></em>
                      </a>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
            <div v-if="!userAgents.length" class="text-center py-3 text-muted">No user agents</div>
          </div>
        </div>
      </div>
    </div>

    <!-- Header Form Modal -->
    <b-modal id="header-modal" :title="headerEdit ? 'Edit Header' : 'Add Header'" hide-footer>
      <form @submit.prevent="saveHeader()">
        <div class="form-group">
          <label>Header Key (e.g. Referer, Origin, User-Agent)</label>
          <input class="form-control" required type="text" v-model="headerForm.key" />
        </div>
        <div class="form-group">
          <label>Value</label>
          <input class="form-control" required type="text" v-model="headerForm.value" />
        </div>
        <div class="text-right">
          <button @click="$bvModal.hide('header-modal')" class="btn btn-light" type="button">Cancel</button>
          <button class="btn btn-primary" type="submit">Save</button>
        </div>
      </form>
    </b-modal>

    <!-- User Agent Form Modal -->
    <b-modal id="ua-modal" :title="uaEdit ? 'Edit User Agent' : 'Add User Agent'" hide-footer>
      <form @submit.prevent="saveUA()">
        <div class="form-group">
          <label>Name / Identifier (e.g. Chrome Windows, VLC Player)</label>
          <input class="form-control" required type="text" v-model="uaForm.title" />
        </div>
        <div class="form-group">
          <label>User Agent String</label>
          <textarea class="form-control" required rows="3" v-model="uaForm.useragent"></textarea>
        </div>
        <div class="text-right">
          <button @click="$bvModal.hide('ua-modal')" class="btn btn-light" type="button">Cancel</button>
          <button class="btn btn-primary" type="submit">Save</button>
        </div>
      </form>
    </b-modal>
  </div>
</template>

<script>
import { notifications } from "../mixins/notifications";
import { settings } from "../mixins/settings";

export default {
  data() {
    return {
      headers: [],
      userAgents: [],
      headerEdit: false,
      headerForm: { id: null, key: "", value: "" },
      uaEdit: false,
      uaForm: { id: null, title: "", useragent: "" },
    };
  },
  async mounted() {
    this.loadHeaders();
    this.loadUserAgents();
  },
  methods: {
    async loadHeaders() {
      try {
        const res = await axios.get(url + "/admin/headers/dataheaders");
        this.headers = res.data || [];
      } catch (e) {
        this.showError();
      }
    },
    async loadUserAgents() {
      try {
        const res = await axios.get(url + "/admin/useragents/datausersagent");
        this.userAgents = (res.data && res.data.data) ? res.data.data : (res.data || []);
      } catch (e) {
        this.showError();
      }
    },
    openHeaderModal() {
      this.headerEdit = false;
      this.headerForm = { id: null, key: "", value: "" };
      this.$bvModal.show("header-modal");
    },
    editHeader(h) {
      this.headerEdit = true;
      this.headerForm = { id: h.id, key: h.key || h.name || "", value: h.value || "" };
      this.$bvModal.show("header-modal");
    },
    async saveHeader() {
      try {
        if (this.headerEdit) {
          await axios.put(url + "/admin/headers/update/" + this.headerForm.id, this.headerForm);
        } else {
          await axios.post(url + "/admin/headers/store", this.headerForm);
        }
        this.$bvModal.hide("header-modal");
        this.loadHeaders();
        this.showSuccess();
      } catch (e) {
        this.showError(e.response);
      }
    },
    deleteHeader(id, index) {
      this.showConfirm(async () => {
        try {
          await axios.delete(url + "/admin/headers/destroy/" + id);
          this.headers.splice(index, 1);
          this.showSuccess();
        } catch (e) {
          this.showError();
        }
      });
    },
    openUAModal() {
      this.uaEdit = false;
      this.uaForm = { id: null, title: "", useragent: "" };
      this.$bvModal.show("ua-modal");
    },
    editUA(ua) {
      this.uaEdit = true;
      this.uaForm = { id: ua.id, title: ua.title || ua.name || "", useragent: ua.useragent || ua.value || "" };
      this.$bvModal.show("ua-modal");
    },
    async saveUA() {
      try {
        if (this.uaEdit) {
          await axios.put(url + "/admin/useragents/update/" + this.uaForm.id, this.uaForm);
        } else {
          await axios.post(url + "/admin/useragents/store", this.uaForm);
        }
        this.$bvModal.hide("ua-modal");
        this.loadUserAgents();
        this.showSuccess();
      } catch (e) {
        this.showError(e.response);
      }
    },
    deleteUA(id, index) {
      this.showConfirm(async () => {
        try {
          await axios.delete(url + "/admin/useragents/destroy/" + id);
          this.userAgents.splice(index, 1);
          this.showSuccess();
        } catch (e) {
          this.showError();
        }
      });
    },
  },
  mixins: [notifications, settings],
};
</script>
