<template>
  <div class="row">
    <div class="col-lg-12 grid-margin stretch-card" v-if="index">
      <div class="card">
        <div class="card-body">
          <div class="table-responsive">
            <table aria-describedby="Report Table" class="table">
              <thead>
                <tr>
                  <th class="text-center" id="id">ID</th>
                  <th class="text-center" id="movie_name">Movie Name</th>
                  <th class="text-center" id="message">Message</th>
                  <th class="text-center" id="actions">Actions</th>
                </tr>
              </thead>
              <tbody>
                <tr :key="index" v-for="(report, index) in paginated('filteredReports')">
                  <td class="text-center">{{report.id}}</td>
                  <td class="text-center">{{report.title}}</td>
                  <td class="text-center">{{report.message}}</td>
                  <td class="text-center">
                    <button
                      @click="destroy(report.id, index)"
                      class="btn btn-danger btn-sm"
                      type="button"
                    >Dismiss</button>
                  </td>
                </tr>
              </tbody>

              <paginate
                :list="filteredReports"
                :per="5"
                name="filteredReports"
                tag="tbody"
                v-if="filteredReports.length"
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
                for="filteredReports"
                v-if="filteredReports.length"
              ></paginate-links>
            </table>
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
      index: true,
      search: "",
      form: {
        report: {
          name: "",
          message: "",
        },
        notification: false,
      },
      reports: [],
      options: [],
      loading: false,
      paginate: ["filteredReports"],
    };
  },
  async mounted() {
    const response = await axios.get(url + "/admin/reports/data");
    this.reports = response.data;
  },

  methods: {
    back() {
      this.form.report = "";
      this.add = false;
      this.edit = false;
      this.index = true;
    },


    // delete a record (Report) in the database
    destroy(id, index) {
      this.showConfirm(async () => {
        try {
          const response = await axios.delete(
            url + "/admin/reports/destroy/" + id
          );
          const reportIndex = this.reports.findIndex(
            (report) => report.id === id
          );
          this.reports.splice(reportIndex, 1);
          this.paginate.filteredReports.list.splice(index, 1);
          this.showSuccess(response.data.message);
        } catch (error) {
          this.showError();
        }
      });
    },
  },
  computed: {
    
    filteredReports() {
      return this.reports.filter((report) => {
        return report.title.toLowerCase().match(this.search.toLowerCase());
      });
    },
  },
  mixins: [notifications, settings],
};
</script>
