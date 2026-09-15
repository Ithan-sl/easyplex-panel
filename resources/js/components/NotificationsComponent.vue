<template>
  <div class="row">
    <div class="col-md-12 grid-margin stretch-card">
      <div class="card">
        <div class="card-body">
          <h4 class="card-title">Notifications</h4>

          <form class="forms-sample">
            <div class="form-group">
              <label for="title">Title</label>
              <input
                class="form-control"
                id="title"
                placeholder="Title"
                type="text"
                v-model="title"
              />
            </div>

            <div class="form-group">
              <label for="content">Content</label>
              <textarea class="form-control" cols="30" id="content" rows="10" v-model="body"></textarea>
            </div>

            <div class="form-group">
              <label for="image">Image</label>
              <input
                class="form-control"
                id="image"
                placeholder="URL link here (Optional)"
                type="text"
                v-model="image"
              />
            </div>
            <button
              :disabled="!settings.authorization || !title || !body"
              @click.prevent="send()"
              class="btn btn-primary mr-2"
              type="button"
            >Submit</button>
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
      title: "",
      body: "",
      image: "",
    };
  },
  mounted() {
    // check if the FCM server key exists in the settings
    setTimeout(() => {
      if (this.settings.authorization == null) {
        this.showAlert("you must configure your FCM server key in settings");
      }
    }, 1000);
  },
  methods: {
    // send a new push notification using the firebase rest service
    async send() {
      try {
        const config = {
          headers: {
            Authorization: "key=" + this.settings.authorization,
          },
        };

        const form = {
          to: "/topics/all",
          notification: {
            title: this.title,
            body: this.body,
            image: this.image,
            click_action: "EASYPLEX",
          },
        };

        await http.post("https://fcm.googleapis.com/fcm/send", form, config);

        this.showSuccess();
        this.title = "";
        this.body = "";
        this.image = "";
      } catch (error) {
        this.showError();
      }
    },
  },
  mixins: [notifications, settings],
};
</script>
