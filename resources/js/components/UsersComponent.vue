<template>
  <div class="row">


    <div v-if="index" class="col-md-6 grid-margin">
   <ul class="navbar-nav mr-lg-4 w-100">
          <li class="nav-item nav-search d-none d-lg-block w-100">
            <div class="input-group">
              <div class="input-group-prepend">
                <span class="input-group-text" id="search">
                  <i class="mdi mdi-magnify"></i>
                </span>
              </div>
              <input v-model="search" type="text" class="form-control" placeholder="Search now" aria-label="search" aria-describedby="search">
            </div>
          </li>
        </ul>
    </div>


    <div class="col-lg-12 grid-margin stretch-card" v-if="index">
      <div class="card">
        <div class="card-body">
          <div class="table-responsive">
            <table aria-describedby="Users Table" class="table">
              <thead>
                <tr>
                  <th class="text-center" id="id">ID</th>
                  <th class="text-center" id="email">E-mail</th>
                  <th class="text-center" id="name">Name</th>
                  <th class="text-center" id="type">Type</th>
                  <th class="text-center" id="premuim">Premuim</th>
                  <th class="text-center" id="actions">Actions</th>
                </tr>
              </thead>
              <tbody>
                <tr :key="index" v-for="(user, index) in paginated('filteredUsers')">
                  <td class="text-center">{{user.id}}</td>
                  <td class="text-center">{{user.email}}</td>
                  <td class="text-center">{{user.name}}</td>
                  <td class="text-center" v-if="user.role === 'admin'">
                    <span class="role admin">{{user.role}}</span>
                  </td>
                  <td class="text-center" v-else>
                    <span class="role user">{{user.role}}</span>
                  </td>

                  <td class="text-center" v-if="user.premuim === 1 ">
                    <em class="mdi mdi-check"></em>
                  </td>
                  <td class="text-center" v-else>
                    <em class="mdi mdi-close"></em>
                  </td>

                  <td class="text-center" v-if="user.role === 'admin'"></td>
                  <td class="text-center" v-else>


                        <div class="list-icons">
                      <a
                       @click="editing(user)"
                        class="list-icons-item mr-2"
                        data-original-title="Edit"
                        rel="tooltip"
                        title
                      >
                        <em class="mdi mdi-pencil fa-lg" style="color:#4d83ff"></em>
                      </a>
                      <a
                      @click="destroy(user.id, index)"
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
                :list="filteredUsers"
                :per="5"
                name="filteredUsers"
                tag="tbody"
                v-if="filteredUsers.length"
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
                for="filteredUsers"
                v-if="filteredUsers.length"
              ></paginate-links>
          </div>
        </div>
      </div>
    </div>

    <div class="col-12 grid-margin stretch-card" v-if="edit">
      <div class="card">
        <div class="card-body">
          <form class="forms-sample">



        <div class="form-group">
              <div class="custom-control custom-checkbox">
                <input
                  class="custom-control-input"
                  id="premuim"
                  type="checkbox"
                  v-model="form.user.premuim"
                />
                <label class="custom-control-label" for="premuim">Premuim</label>
              </div>
            </div>

            <div class="form-group">
              <label for="name">Title</label>
              <input
                class="form-control"
                id="title"
                placeholder="Name"
                type="text"
                v-model="form.user.name"
              />
            </div>

            <div class="form-group">
              <label for="name">E-mail</label>
              <input
                class="form-control"
                id="title"
                placeholder="Name"
                type="text"
                v-model="form.user.email"
              />
            </div>


            
            <div class="form-group">
              <label for="pack_name">Pack Name</label>
              <input
                class="form-control"
                id="pack_name"
                placeholder="pack_name"
                type="text"
                v-model="form.user.pack_name"
              />
            </div>


              <div class="form-group">
              <label for="expired_in">Ends At</label>
              <input
                class="form-control"
                id="expired_in"
                placeholder="expired_in"
                type="text"
                v-model="form.user.expired_in"
              />
            </div>

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
import { settings } from "../mixins/settings";

export default {
  data() {
    return {
      index: true,
      add: false,
      edit: false,
      search: "",
      form: {
        user: {
          name: "",
          email: "",
          premuim: "",
        },
        notification: false,
      },
      users: [],
      plans: [],
      loading: false,
      paginate: ["filteredUsers"],
    };
  },
  async mounted() {
    let response = await axios.get(url + "/admin/users/allusers");
    this.users = response.data;

    response = await axios.get(url + "/admin/plans/data");
    this.plans = response.data;
  },

  methods: {
    editing(user) {
      this.index = false;
      this.edit = true;
      this.form.user = user;
    },

    back() {
      this.form.user = "";
      this.add = false;
      this.edit = false;
      this.index = true;
    },

    update() {
      axios
        .put(url + "/admin/users/update/" + this.form.user.id, this.form)
        .then((response) => {
          this.edit = false;
          this.index = true;
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
          const response = await axios.delete(
            url + "/admin/users/destroy/" + id
          );
          const userIndex = this.users.findIndex((user) => user.id === id);
          this.users.splice(userIndex, 1);
          this.paginate.filteredUsers.list.splice(index, 1);
          this.showSuccess(response.data.message);
        } catch (error) {
          this.showError();
        }
      });
    },
  },
  computed: {
    // filter the movies array with the search matches and return the filtered array
    filteredUsers() {
      return this.users.filter((user) => {
        return user.name.toLowerCase().match(this.search.toLowerCase());
      });
    },
  },
  mixins: [notifications, settings],
};
</script>
