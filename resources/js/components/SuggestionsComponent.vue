<template>
  <div class="row">
    <div class="col-lg-12 grid-margin stretch-card">
      <div class="card">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="card-title mb-0">User Suggestions</h4>
            <div style="width: 300px;">
              <input
                class="form-control"
                placeholder="Search suggestions..."
                type="text"
                v-model="search"
              />
            </div>
          </div>

          <div class="table-responsive">
            <table aria-describedby="Suggestions Table" class="table">
              <thead>
                <tr>
                  <th class="text-center">ID</th>
                  <th>Title</th>
                  <th>Message / Suggestion</th>
                  <th class="text-center">Actions</th>
                </tr>
              </thead>
              <tbody>
                <tr :key="item.id" v-for="(item, idx) in paginated('filteredSuggestions')">
                  <td class="text-center">{{ item.id }}</td>
                  <td><strong>{{ item.title }}</strong></td>
                  <td style="max-width: 500px; white-space: normal;">{{ item.message }}</td>
                  <td class="text-center">
                    <button
                      @click="destroy(item.id, idx)"
                      class="btn btn-danger btn-sm"
                      type="button"
                    >
                      <em class="mdi mdi-delete"></em> Dismiss
                    </button>
                  </td>
                </tr>
              </tbody>

              <paginate
                :list="filteredSuggestions"
                :per="10"
                name="filteredSuggestions"
                tag="tbody"
                v-if="filteredSuggestions && filteredSuggestions.length"
              ></paginate>
            </table>

            <div v-if="!filteredSuggestions.length" class="text-center py-4 text-muted">
              No suggestions found.
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
              for="filteredSuggestions"
              v-if="filteredSuggestions && filteredSuggestions.length"
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
      suggestions: [],
      search: "",
      paginate: ["filteredSuggestions"],
    };
  },
  async mounted() {
    this.loadData();
  },
  computed: {
    filteredSuggestions() {
      if (!this.search) return this.suggestions;
      const s = this.search.toLowerCase();
      return this.suggestions.filter(
        (item) =>
          (item.title || "").toLowerCase().includes(s) ||
          (item.message || "").toLowerCase().includes(s)
      );
    },
  },
  methods: {
    async loadData() {
      try {
        const response = await axios.get(url + "/admin/suggestions/data");
        this.suggestions = response.data || [];
      } catch (e) {
        this.showError();
      }
    },
    destroy(id, index) {
      this.showConfirm(async () => {
        try {
          const response = await axios.delete(url + "/admin/suggestions/destroy/" + id);
          const sIndex = this.suggestions.findIndex((s) => s.id === id);
          if (sIndex !== -1) {
            this.suggestions.splice(sIndex, 1);
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
