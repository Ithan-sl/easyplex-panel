<template>
  <div class="row">
    <div class="col-lg-12 grid-margin stretch-card">
      <div class="card">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="card-title mb-0">User Comments</h4>
            <div style="width: 300px;">
              <input
                class="form-control"
                placeholder="Search comment or user..."
                type="text"
                v-model="search"
              />
            </div>
          </div>

          <div class="table-responsive">
            <table aria-describedby="Comments Table" class="table">
              <thead>
                <tr>
                  <th>User</th>
                  <th>Media Title</th>
                  <th>Type</th>
                  <th>Comment</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody>
                <tr :key="idx" v-for="(item, idx) in paginated('filteredComments')">
                  <td>
                    <div class="d-flex align-items-center">
                      <img
                        :src="item.user_image || '/img/avatar_default.png'"
                        alt="avatar"
                        class="rounded-circle mr-2"
                        height="35"
                        width="35"
                      />
                      <span>{{ item.user_name || 'Anonymous' }}</span>
                    </div>
                  </td>
                  <td><strong>{{ item.title }}</strong></td>
                  <td><span class="badge badge-info">{{ item.type }}</span></td>
                  <td style="max-width: 400px; white-space: normal;">{{ item.comment }}</td>
                  <td>
                    <button
                      @click="destroy(item.id, idx)"
                      class="btn btn-danger btn-sm"
                      type="button"
                    >
                      <em class="mdi mdi-delete"></em> Delete
                    </button>
                  </td>
                </tr>
              </tbody>

              <paginate
                :list="filteredComments"
                :per="10"
                name="filteredComments"
                tag="tbody"
                v-if="filteredComments && filteredComments.length"
              ></paginate>
            </table>

            <div v-if="!filteredComments.length" class="text-center py-4 text-muted">
              No comments found.
            </div>

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
              for="filteredComments"
              v-if="filteredComments && filteredComments.length"
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
      comments: [],
      search: "",
      paginate: ["filteredComments"],
    };
  },
  async mounted() {
    this.loadData();
  },
  computed: {
    filteredComments() {
      if (!this.search) return this.comments;
      const s = this.search.toLowerCase();
      return this.comments.filter(
        (c) =>
          (c.comment || "").toLowerCase().includes(s) ||
          (c.user_name || "").toLowerCase().includes(s) ||
          (c.title || "").toLowerCase().includes(s)
      );
    },
  },
  methods: {
    async loadData() {
      try {
        const response = await axios.get(url + "/admin/comments/allcomments");
        this.comments = (response.data && response.data.data) ? response.data.data : (response.data || []);
      } catch (e) {
        this.showError();
      }
    },
    destroy(id, index) {
      this.showConfirm(async () => {
        try {
          const response = await axios.delete(url + "/admin/media/delete/comments/" + id);
          const cIndex = this.comments.findIndex((c) => c.id === id);
          if (cIndex !== -1) {
            this.comments.splice(cIndex, 1);
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
