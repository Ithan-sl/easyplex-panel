<template>
  <div class="row">
    <div class="col-md-12 grid-margin">
      <div class="d-flex justify-content-between flex-wrap">
        <div class="d-flex align-items-end flex-wrap">
          <div class="d-flex"></div>
        </div>
        <div class="d-flex justify-content-between align-items-end flex-wrap">
          <button @click="create()" class="btn btn-primary mt-2 mt-xl-0" v-if="index">Add Plan</button>
          <button @click="back()" class="btn btn-primary mt-2 mt-xl-0" v-if="!index">Back</button>
        </div>
      </div>
    </div>

    <div class="col-lg-12 grid-margin stretch-card" v-if="index">
      <div class="card">
        <div class="card-body">
          <div class="table-responsive">
            <table aria-describedby="Ads Table" class="table">
              <thead>
                <tr>
                  <th class="text-center" id="title">Title</th>
                  <th class="text-center" id="ads link">Price</th>
                  <th class="text-center" id="options">Description</th>
                      <th class="text-center" id="options">Options</th>
                </tr>
              </thead>
              <tbody>
                <tr :key="index" v-for="(plan, index) in paginated('filteredPlans')">
                  <td class="text-center">{{plan.name}}</td>
                <td class="text-center">{{plan.price}}</td>
                 <td class="text-center">{{plan.description}}</td>
                  <td class="text-center">
                    <div class="list-icons">
                      <a
                        @click="editing(plan)"
                        class="list-icons-item mr-2"
                        data-original-title="Edit"
                        rel="tooltip"
                        title
                      >
                        <em class="mdi mdi-pencil fa-lg" style="color:#4d83ff"></em>
                      </a>
                      <a
                        @click="destroy(plan.id, index)"
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
                :list="filteredPlans"
                :per="5"
                name="filteredPlans"
                tag="tbody"
                v-if="filteredPlans.length"
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
                for="filteredPlans"
                v-if="filteredPlans.length"
              ></paginate-links>
            </table>
          </div>
        </div>
      </div>
    </div>












 <div class="col-lg-12 grid-margin stretch-card" v-if="index">
      <div class="card">
        <div class="card-body">


<p class="card-title">Stripe Active Subscriptions</p>


          <div class="table-responsive">
            <table aria-describedby="Ads Table" class="table">
              <thead>
                <tr>
                  <th class="text-center" id="title">ID</th>
                  <th class="text-center" id="ads link">User id</th>
                  <th class="text-center" id="options">Stripe Status</th>
                  <th class="text-center" id="options">Stripe Plan</th>
                </tr>
              </thead>
              <tbody>
                <tr :key="index" v-for="(subscription, index) in paginated('filteredSubscriptions')">
                  <td class="text-center">{{subscription.id}}</td>
                <td class="text-center">{{subscription.user_id}}</td>
                 <td class="text-center">{{subscription.stripe_status}}</td>
                      <td class="text-center">{{subscription.stripe_plan}}</td>
                </tr>
              </tbody>

              <paginate
                :list="filteredSubscriptions"
                :per="5"
                name="filteredSubscriptions"
                tag="tbody"
                v-if="filteredSubscriptions.length"
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
                for="filteredSubscriptions"
                v-if="filteredSubscriptions.length"
              ></paginate-links>
            </table>
          </div>
        </div>
      </div>
    </div>







<div class="col-lg-12 grid-margin stretch-card" v-if="index">
      <div class="card">
        <div class="card-body">


<p class="card-title">Paypal Subscriptions</p>


          <div class="table-responsive">
            <table aria-describedby="Ads Table" class="table">
              <thead>
                <tr>
                  <th class="text-center" id="title">ID</th>
                  <th class="text-center" id="ads link">E-Email</th>
                  <th class="text-center" id="options">Package Name</th>
                  <th class="text-center" id="premuim">Premuim Status</th>
                </tr>
              </thead>
              <tbody>
                <tr :key="index" v-for="(paypal, index) in paginated('filteredPaypal')">
                  <td class="text-center">{{paypal.id}}</td>
                <td class="text-center">{{paypal.email}}</td>
                 <td class="text-center">{{paypal.pack_name}}</td>
                 <td class="text-center" v-if="paypal.premuim === 1 ">
                    <em class="mdi mdi-check"></em>
                  </td>
                  <td class="text-center" v-else>
                    <em class="mdi mdi-close"></em>
                  </td>
                </tr>
              </tbody>

              <paginate
                :list="filteredPaypal"
                :per="5"
                name="filteredPaypal"
                tag="tbody"
                v-if="filteredPaypal.length"
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
                for="filteredPaypal"
                v-if="filteredPaypal.length"
              ></paginate-links>
            </table>
          </div>
        </div>
      </div>
    </div>




    <div class="col-12 grid-margin stretch-card" v-if="add || edit">
      <div class="card">
        <div class="card-body">
          <form class="forms-sample">
            <div class="form-group">
              <label for="name">Title</label>
              <input
                class="form-control"
                id="name"
                placeholder="Title"
                type="text"
                v-model="form.plan.name"
              />
            </div>

            <div class="form-group">
              <label for="Description">Description</label>
              <textarea
                class="form-control"
                id="Description"
                placeholder="Description"
                type="text"
                rows="4"
                v-model="form.plan.description"
              />
            </div>


            <div class="form-group">
              <label for="price">Price</label>
              <input
                class="form-control"
                id="price"
                placeholder="price"
                type="text"
                v-model="form.plan.price"
              />
            </div>



              <div class="form-group">
              <label for="stripe_plan_id">Stripe Product Id</label>
              <input
                class="form-control"
                id="stripe_plan_id"
                placeholder="stripe_plan_id"
                type="text"
                v-model="form.plan.stripe_plan_id"
              />
            </div>



               <div class="form-group">
              <label for="stripe_price_id">Stripe Price Id</label>
              <input
                class="form-control"
                id="stripe_price_id"
                placeholder="stripe_price_id"
                type="text"
                v-model="form.plan.stripe_price_id"
              />
            </div>


               <div class="form-group">
              <label for="name">Pack Duration ( Billing period )</label>
              <input
                class="form-control"
                id="pack_duration"
                placeholder="pack_duration"
                type="number"
                v-model="form.plan.pack_duration"
              />
            </div>


            <button
              @click.prevent="store()"
              class="btn btn-primary mr-2"
              type="submit"
              v-if="add"
            >Save</button>
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

export default {
  data() {
    return {
      index: true,
      add: false,
      edit: false,
      search: "",
      form: {
        plan: {
          name: "",
          price: "",
          description: "",
          is_active: "",
          currency: "",
        },
      },
      plans: [],
      subscriptions: [],
      paypal: [],
      paginate: ["filteredPlans","filteredSubscriptions","filteredPaypal"],
    };
  },
  async mounted() {


    let response = await axios.get(url + "/admin/plans/data");
    this.plans = response.data;


    response = await axios.get(url + "/admin/subscriptions/data");
    this.subscriptions = response.data;


     response = await axios.get(url + "/admin/subscriptions/paypal");
    this.paypal = response.data;

  },

  methods: {
    create() {
      this.index = false;
      this.edit = false;
      this.add = true;
    },

    editing(plan) {
      this.index = false;
      this.edit = true;
      this.form.plan = plan;
      this.form.plans = "";
    },

    back() {
      this.form.plan = "";
      this.add = false;
      this.edit = false;
      this.index = true;
    },

    store() {
      axios
        .post(url + "/admin/plans/store", this.form)
        .then((response) => {
          this.add = false;
          this.edit = false;
          this.index = true;
          this.form.plan = {};
          this.plans.unshift(response.data.body);
          this.showSuccess(response.data.message);
        })
        .catch((error) => {
          this.showError(error.response);
        });
    },

    update() {
      axios
        .put(url + "/admin/plans/update/" + this.form.plan.id, this.form)
        .then((response) => {
          this.edit = false;
          this.index = true;
          this.form.plans = [];
          this.showSuccess(response.data.message);
        })
        .catch((error) => {
          this.showError(error.response);
        });
    },

    // delete a record (Plan) in the database
    destroy(id, index) {
      this.showConfirm(async () => {
        try {
          const response = await axios.delete(url + "/admin/plans/destroy/" + id);
          const planIndex = this.plans.findIndex((plan) => plan.id === id);
          this.plans.splice(planIndex, 1);
          this.paginate.filteredPlans.list.splice(index, 1);
          this.showSuccess(response.data.message);
        } catch (error) {
          this.showError();
        }
      });
    },
  },
  computed: {
    // filter the Plans array with the search matches and return the filtered array
    filteredPlans() {
      return this.plans.filter((plan) => {
        return plan.name.toLowerCase().match(this.search.toLowerCase());
      });
    },
    filteredSubscriptions() {
      return _.orderBy(this.subscriptions, "created_at").reverse().splice(0, 10);
    },

    filteredPaypal() {
      return _.orderBy(this.paypal, "created_at").reverse().splice(0, 10);
    },
  },

  mixins: [notifications],
};
</script>