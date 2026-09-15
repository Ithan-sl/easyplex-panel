<template>
  <div class="main-content">
    <div class="section__content section__content--p30">
      <div class="container-fluid">
        <div class="row">
          <div class="col">
            <div class="au-card">
              <div class="fc-center">
                <h2>Users</h2>
              </div>

              <div class="fc fc-unthemed fc-ltr" id="calendar">
                <div class="fc-toolbar fc-header-toolbar">
                  <div class="fc-left">
                    <div class="fc-button-group">
                      <div class="row m-t-30">
                        <div class="col-md-12">
                          <!-- DATA TABLE-->
                          <div class="table-responsive m-b-40">
                            <table
                              aria-describedby="Episodes Links Table"
                              class="table table-borderless table-data3"
                              v-if="index"
                            >
                              <thead>
                                <tr>
                                  <th id="id">ID</th>
                                  <th id="ep_id">Ep isode ID</th>
                                  <th id="anime">anime</th>
                                  <th id="type">Type</th>
                                  <th id="actions">Actions</th>
                                </tr>
                              </thead>
                              <paginate
                                :list="filteredanimesVideos"
                                :per="5"
                                name="filteredanimesVideos"
                                tag="tbody"
                                v-if="filteredanimesVideos.length"
                              >
                                <tr
                                  :key="index"
                                  v-for="(animevideo, index) in paginated('filteredanimesVideos')"
                                >
                                  <td>{{animevideo.id}}</td>
                                  <td>{{animevideo.episode_id}}</td>
                                  <td>{{animevideo.server}}</td>
                                  <td>
                                    <span class="role admin">tttt</span>
                                  </td>

                                  <td>
                                    <button
                                      @click="destroy(animevideo.id, index)"
                                      class="btn btn-danger"
                                      type="button"
                                    >Delete</button>
                                  </td>
                                </tr>
                              </paginate>

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
                                for="filteredanimesVideos"
                                v-if="filteredanimesVideos.length"
                              ></paginate-links>
                            </table>
                          </div>
                          <!-- END DATA TABLE-->
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
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
      id: "",
      episode_id: "",
      server: "",
      animevideos: [],
      loading: false,
      paginate: ["filteredanimesVideos"],
    };
  },
  async mounted() {
    const response = await axios.get(url + "/admin/video/animes/allvideos");
    this.allvideosanimes = response.data;
  },

  methods: {},
  computed: {
    // filter the movies array with the search matches and return the filtered array
    filteredanimesVideos() {
      return this.animevideos.filter((animevideo) => {
        return animevideo.server.toLowerCase().match(this.search.toLowerCase());
      });
    },
  },
  mixins: [notifications, settings],
};
</script>
