<template>
  <div class="row">
    <div class="col-md-6 grid-margin stretch-card">
      <div class="card">
        <div class="card-body">
          <h4 class="card-title">Update Profile</h4>

          <form class="forms-sample">
            <div class="form-group">
              <div class="form-group">
                <label for="name">Name</label>
                <input
                  class="form-control"
                  id="name"
                  placeholder="Name"
                  type="text"
                  v-model="user.name"
                />
              </div>

              <div class="form-group">
                <label for="email">E-mail</label>
                <input
                  class="form-control"
                  id="email"
                  placeholder="Email"
                  type="email"
                  v-model="user.email"
                />
              </div>

              <div class="form-group">
                <label for="avatar">Avatar</label>
                <input
                  class="form-control"
                  id="avatar"
                  placeholder="avatar"
                  type="avatar"
                  v-model="user.avatar"
                />
              </div>

              <div class="row">
                <div class="col-md-8">
                  <div class="logo" v-if="avatar">
                    <img :src="avatar" alt="avatar" />
                  </div>
                </div>
              </div>

              <div class="form-group">
                <label>Avatar upload</label>
                <input class="file-upload-default" />
                <div class="input-group col-xs-12">
                  <input
                    @change="updateAvatar"
                    class="form-control file-upload-info"
                    id="avatar"
                    placeholder="Upload Image"
                    type="file"
                  />
                  <span class="input-group-append">
                    <button class="file-upload-browse btn btn-primary" type="button">Upload</button>
                  </span>
                </div>
              </div>

              <button
                :disabled="!user.name || !user.email"
                @click.prevent="update()"
                aria-pressed="true"
                class="btn btn-info"
                type="button"
              >Update</button>
            </div>

            <div class="row">
              <div class="col-md-12">
                <div class="form-group">
                  <label for="password">Password</label>
                  <input
                    class="form-control"
                    id="password"
                    placeholder="Password"
                    type="password"
                    v-model="password"
                  />
                </div>

                <div class="form-group">
                  <label for="password_confirmation">Confirm Password</label>
                  <input
                    class="form-control"
                    id="password_confirmation"
                    placeholder="Password"
                    type="password"
                    v-model="password_confirmation"
                  />
                </div>

                <button
                  :disabled="password.length < 6 || password_confirmation.length < 6  || password !== password_confirmation"
                  @click.prevent="change()"
                  aria-pressed="true"
                  class="btn btn-info"
                  type="button"
                >Update</button>
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

export default {
  data() {
    return {
      password: "",
      password_confirmation: "",
      user: {},

      avatar: url + "/api/image/users"
    };
  },
  async mounted() {
    try {
      const response = await axios.get(url + "/admin/account/data");
      this.user = response.data;
    } catch (error) {
      this.showError();
    }
  },
  methods: {


    // update the avatar in the storage
    async updateAvatar(event) {
      try {
        const data = new FormData();
        data.append("image", event.target.files[0]);

        const response = await axios.post(url + "/admin/update/avatar", data);
        this.logo = response.data.image_path;
        this.showSuccess(response.data.message);
      } catch (error) {
        this.showError(error.response);
      }
    },

    // update user data
    async update() {
      try {
        const response = await axios.put(
          url + "/admin/account/update",
          this.user
        );
        this.user = response.data.body;
        this.showSuccess(response.data.message);
      } catch (error) {
        this.showError(error.response);
      }
    },
    // update user password
    async change() {
      try {
        const response = await axios.put(
          url + "/admin/account/password/update",
          {
            password: this.password,
            password_confirmation: this.password_confirmation
          }
        );
        this.password = "";
        this.password_confirmation = "";
        this.showSuccess(response.data.message);
      } catch (error) {
        this.showError(error.response);
      }
    }
  },
  mixins: [notifications]
};
</script>

<style scoped></style>
