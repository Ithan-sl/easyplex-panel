<template>
  <div class="row">
    <div class="col-12 grid-margin stretch-card">
      <div class="card">
        <div class="card-body">
          <div class="row">
            <div class="col-md-12">
              <ul class="nav nav-tabs" id="myTab" role="tablist">
                <li class="nav-item">
                  <a
                    aria-controls="brand"
                    aria-selected="true"
                    class="nav-link active"
                    data-toggle="tab"
                    href="#brand"
                    id="brand-tab"
                    role="tab"
                  >General</a>
                </li>

                <li class="nav-item">
                  <a
                    aria-controls="firebase"
                    aria-selected="false"
                    class="nav-link"
                    data-toggle="tab"
                    href="#firebase"
                    id="firebase-tab"
                    role="tab"
                  >Firebase</a>
                </li>

                <li class="nav-item">
                  <a
                    aria-controls="tmdb"
                    aria-selected="false"
                    class="nav-link"
                    data-toggle="tab"
                    href="#tmdb"
                    id="tmdb-tab"
                    role="tab"
                  >TMDB Api Key</a>
                </li>




                <li class="nav-item">
                  <a
                    aria-controls="ads"
                    aria-selected="false"
                    class="nav-link"
                    data-toggle="tab"
                    href="#ads"
                    id="ads-tab"
                    role="tab"
                  >Ads</a>
                </li>

                <li class="nav-item">
                  <a
                    aria-controls="appsettings"
                    aria-selected="false"
                    class="nav-link"
                    data-toggle="tab"
                    href="#appsettings"
                    id="appsettings-tab"
                    role="tab"
                  >App Settings</a>
                </li>

                <li class="nav-item">
                  <a
                    aria-controls="payments"
                    aria-selected="false"
                    class="nav-link"
                    data-toggle="tab"
                    href="#payments"
                    id="payments-tab"
                    role="tab"
                  >Payments</a>
                </li>



            <li class="nav-item">
                  <a
                    aria-controls="aws"
                    aria-selected="false"
                    class="nav-link"
                    data-toggle="tab"
                    href="#aws"
                    id="aws-tab"
                    role="tab"
                  >Amazon Web Services</a>
                </li>


              </ul>
              <div class="tab-content" id="myTabContent">
                <div
                  aria-labelledby="brand-tab"
                  class="tab-pane fade show active"
                  id="brand"
                  role="tabpanel"
                >
                  <div class="row">
                    <div class="col-md-12"></div>
                  </div>
                  <div class="row">
                    <div class="col-md-8">
                      <div class="logo" v-if="logo">
                        <img :src="logo" alt="logo" />
                      </div>
                    </div>
                    <div class="col-md-4">
                      <div class="logo" v-if="miniLogo">
                        <img :src="miniLogo" alt="mini logo" />
                      </div>
                    </div>
                  </div>

                  <div class="row">
                    <div class="col-md-6">
                      <div class="form-group">
                        <label>Logo upload</label>
                        <input class="file-upload-default" />
                        <div class="input-group col-xs-12">
                          <input
                            @change="updateLogo"
                            class="form-control file-upload-info"
                            id="logo"
                            placeholder="Upload Image"
                            type="file"
                          />
                          <span class="input-group-append">
                            <button class="file-upload-browse btn btn-primary" type="button">Upload</button>
                          </span>
                        </div>
                      </div>
                    </div>

                    <div class="col-md-6">
                      <div class="form-group">
                        <label>Mini Logo upload</label>
                        <input class="file-upload-default" />
                        <div class="input-group col-xs-12">
                          <input
                            @change="updateMiniLogo"
                            class="form-control file-upload-info"
                            id="miniLogo"
                            placeholder="Upload Image"
                            type="file"
                          />
                          <span class="input-group-append">
                            <button class="file-upload-browse btn btn-primary" type="button">Upload</button>
                          </span>
                        </div>
                      </div>
                    </div>
                  </div>

                  <div class="row">
                    <div class="col-md-6">
                      <div class="form-group">
                        <label for="app_name">App name</label>
                        <input
                          class="form-control"
                          id="app_name"
                          type="text"
                          v-model="settings.app_name"
                        />
                      </div>
                    </div>


                      <div class="col-md-6">
                      <div class="form-group">
                        <label for="app_name">App Version</label>
                        <input
                          class="form-control"
                          id="latestVersion"
                          type="text"
                          v-model="settings.latestVersion"
                        />
                      </div>
                    </div>
                  </div>

            
                  <div class="row">
                    <div class="col-md-6">
                      <div class="form-group">
                        <label for="update_title">Update Title</label>
                        <input
                          class="form-control"
                          id="update_title"
                          type="text"
                          v-model="settings.update_title"
                        />
                      </div>
                    </div>



                   <div class="col-md-6">
                      <div class="form-group">
                        <label for="app_name">App Update_link</label>
                        <input class="form-control" id="url" type="text" v-model="settings.url" />
                      </div>
                    </div>


                  </div>

      
                  <div class="row">

                <div class="col-md-12">
                      <div class="form-group">
                        <label for="releaseNotes">Update Release Notes</label>

                        <textarea
                          class="form-control pb-3"
                          id="releaseNotes"
                          rows="4"
                          type="text"
                          v-model="settings.releaseNotes"
                        ></textarea>
                      </div>
                    </div>
             
                  </div>

                  <div class="row">
                    <div class="col-md-12">
                      <div class="form-group">
                        <label for="title">Privacy Policy</label>
                        <textarea
                          class="form-control pb-3"
                          id="privacy_policy"
                          rows="10"
                          type="text"
                          v-model="settings.privacy_policy"
                        ></textarea>
                      </div>
                    </div>
                  </div>



      
                    <div class="form-group">
                  <div class="col-md-3">
                    <div class="form-check">
                      <div class="custom-control custom-checkbox">
                        <input
                          class="custom-control-input"
                          id="enable_custom_message"
                          type="checkbox"
                          v-model="settings.enable_custom_message"
                        />
                        <label class="custom-control-label" for="enable_custom_message">
                          Enable Custom Message (Home Page)
                        </label>
                      </div>
                    </div>
                  </div>
                </div>
        

                 <div class="row">
                    <div class="col-md-12">
                      <div class="form-group">
                        <label for="custom_message">Custom Message</label>
                        <textarea
                          class="form-control pb-3"
                          id="custom_message"
                          rows="10"
                          type="text"
                          v-model="settings.custom_message"
                        ></textarea>
                      </div>
                    </div>
                  </div>


                </div>

                <div
                  aria-labelledby="firebase-tab"
                  class="tab-pane fade"
                  id="firebase"
                  role="tabpanel"
                >


                 <div class="settings">
                  <div class="row">
                    <div class="col-md-8">
                      <div class="form-group">
                        <label for="app_name">Firebase Server Key</label>
                        <input
                          class="form-control"
                          id="authorization"
                          type="text"
                          v-model="settings.authorization"
                        />
                      </div>
                    </div>
                  </div>
                </div>
                  </div>

                <div aria-labelledby="ads-tab" class="tab-pane fade" id="ads" role="tabpanel">




                       <div class="settings">
                    <div class="form-group">
                  <div class="col-md-3">
                    <div class="form-check">
                      <div class="custom-control custom-checkbox">
                        <input
                          class="custom-control-input"
                          id="wach_ads_to_unlock"
                          type="checkbox"
                          v-model="settings.wach_ads_to_unlock"
                        />
                        <label class="custom-control-label" for="wach_ads_to_unlock">
                          Enable ( UNLOCK CONTENT)
                        </label>
                      </div>
                    </div>
                  </div>
                </div>
 </div>


    <div class="settings">
   <div class="col-md-6">
                  <div class="form-group">
                    <label for="lang">Default Rewarded Network</label>
                    <multiselect

                    :options="options"
                      
                      v-model="settings.default_network"
                    ></multiselect>
                  </div>
                </div>
     </div>


      <div class="settings">
                    <div class="form-group">
                  <div class="col-md-3">
                    <div class="form-check">
                      <div class="custom-control custom-checkbox">
                        <input
                          class="custom-control-input"
                          id="ad_interstitial"
                          type="checkbox"
                          v-model="settings.ad_interstitial"
                        />
                        <label class="custom-control-label" for="ad_interstitial">
                          Ad
                          interstitial
                        </label>
                      </div>
                    </div>
                  </div>
                </div>
 </div>


            <div class="form-group">
                     <div class="col-md-6">
                    <div class="form-check">
                      <div class="custom-control custom-checkbox">
                        <input
                          class="custom-control-input"
                          id="ad_banner"
                          type="checkbox"
                          v-model="settings.ad_banner"
                        />
                        <label class="custom-control-label" for="ad_banner">
                          Ad
                          Banner
                        </label>
                      </div>
                    </div>
                  </div>
                       </div>



          <div class="form-group">
                     <div class="col-md-6">
                    <div class="form-check">
                      <div class="custom-control custom-checkbox">
                        <input
                          class="custom-control-input"
                          id="ad_face_audience_interstitial"
                          type="checkbox"
                          v-model="settings.ad_face_audience_interstitial"
                        />
                        <label class="custom-control-label" for="ad_face_audience_interstitial">
                         Facebook Audience Interstitial
                        </label>
                      </div>
                    </div>
                  </div>
    </div>


                  

                       <div class="form-group">
                     <div class="col-md-6">
                    <div class="form-check">
                      <div class="custom-control custom-checkbox">
                        <input
                          class="custom-control-input"
                          id="ad_face_audience_banner"
                          type="checkbox"
                          v-model="settings.ad_face_audience_banner"
                        />
                        <label class="custom-control-label" for="ad_face_audience_banner">
                         Facebook Audience Banner
                        </label>
                      </div>
                    </div>
                  </div>
             </div>


                <div class="form-group">
                    <div class="col-md-6">
                      <div class="form-group">
                        <label for="ad_show_interstitial">Admob Interstitial Show</label>
                        <input
                          class="form-control"
                          id="ad_show_interstitial"
                          type="text"
                          v-model="settings.ad_show_interstitial"
                        />
                      </div>
                    </div>
                  </div>

                 <div class="form-group">
                    <div class="col-md-6">
                      <div class="form-group">
                        <label for="facebook_show_interstitial">Facebook Audience Interstitial Show</label>
                        <input
                          class="form-control"
                          id="facebook_show_interstitial"
                          type="text"
                          v-model="settings.facebook_show_interstitial"
                        />
                      </div>
                    </div>
                  </div>

                  <div class="form-group">
                    <div class="col-md-6">
                      <div class="form-group">
                        <label for="ad_unit_id_interstitial">Interstitial Unit ID</label>
                        <input
                          class="form-control"
                          id="ad_unit_id_interstitial"
                          type="text"
                          v-model="settings.ad_unit_id_interstitial"
                        />
                      </div>
                    </div>
                  </div>


                 <div class="form-group">
                    <div class="col-md-6">
                      <div class="form-group">
                        <label for="ad_unit_id_banner">Banner Unit ID</label>
                        <input
                          class="form-control"
                          id="ad_unit_id_banner"
                          type="text"
                          v-model="settings.ad_unit_id_banner"
                        />
                      </div>
                    </div>
                  </div>



                    <div class="form-group">
                    <div class="col-md-6">
                      <div class="form-group">
                        <label for="ad_unit_id_facebook_interstitial_audience">Facebook Audience Interstitial Unit Id</label>
                        <input
                          class="form-control"
                          id="ad_unit_id_facebook_interstitial_audience"
                          type="text"
                          v-model="settings.ad_unit_id_facebook_interstitial_audience"
                        />
                      </div>
                    </div>
                  </div>


                    <div class="form-group">
                    <div class="col-md-6">
                      <div class="form-group">
                        <label for="ad_unit_id_facebook_banner_audience">Facebook Audience Banner Unit Id</label>
                        <input
                          class="form-control"
                          id="ad_unit_id_facebook_banner_audience"
                          type="text"
                          v-model="settings.ad_unit_id_facebook_banner_audience"
                        />
                      </div>
                    </div>
                  </div>



                    <div class="form-group">
                    <div class="col-md-6">
                      <div class="form-group">
                        <label for="startapp_id">StartApp ID</label>
                        <input
                          class="form-control"
                          id="startapp_id"
                          type="text"
                          v-model="settings.startapp_id"
                        />
                      </div>
                    </div>
                  </div>


                   <div class="form-group">
                    <div class="col-md-6">
                      <div class="form-group">
                        <label for="ad_unit_id_rewarded">Admob Rewarded Unit Id</label>
                        <input
                          class="form-control"
                          id="ad_unit_id_rewarded"
                          type="text"
                          v-model="settings.ad_unit_id_rewarded"
                        />
                      </div>
                    </div>
                  </div>



                  <div class="form-group">
                    <div class="col-md-6">
                      <div class="form-group">
                        <label for="ad_unit_id__facebook_rewarded">Facebook Rewarded Unit Id</label>
                        <input
                          class="form-control"
                          id="ad_unit_id__facebook_rewarded"
                          type="text"
                          v-model="settings.ad_unit_id__facebook_rewarded"
                        />
                      </div>
                    </div>
                  </div>

                    <div class="form-group">
                    <div class="col-md-6">
                      <div class="form-group">
                        <label for="ad_unit_id__appodeal_rewarded">AppoDeal Rewarded Unit Id</label>
                        <input
                          class="form-control"
                          id="ad_unit_id__appodeal_rewarded"
                          type="text"
                          v-model="settings.ad_unit_id__appodeal_rewarded"
                        />
                      </div>
                    </div>
                  </div>




                   <div class="form-group">
                    <div class="col-md-6">
                      <div class="form-group">
                        <label for="unity_game_id">Unity Ads Game Id</label>
                        <input
                          class="form-control"
                          id="unity_game_id"
                          type="text"
                          v-model="settings.unity_game_id"
                        />
                      </div>
                    </div>
                  </div>


               

                </div>

                <div aria-labelledby="tmdb-tab" class="tab-pane fade" id="tmdb" role="tabpanel">

                     <div class="settings">
                  <div class="row">
                    <div class="col-md-8">
                      <div class="form-group">
                        <label for="tmdb_api_key">Api key</label>
                        <input
                          class="form-control"
                          id="tmdb_api_key"
                          type="text"
                          v-model="settings.tmdb_api_key"
                        />
                      </div>
                      <div class="form-group">
                        <label for="lang">Language</label>
                        <multiselect
                          :options="langs"
                          label="english_name"
                          track-by="iso_639_1"
                          v-model="settings.tmdb_lang"
                        ></multiselect>
                      </div>
                    </div>
                  </div>
                </div>
                      </div>

                <div aria-labelledby="code-tab" class="tab-pane fade" id="code" role="tabpanel">
                     <div class="settings">
                  <div class="row">
                    <div class="col-md-8">
                      <div class="form-group">
                        <label for="purchase_key">Purchase Code</label>
                        <input
                          class="form-control"
                          id="purchase_key"
                          type="text"
                          v-model="settings.purchase_key"
                        />
                      </div>
                    </div>
                  </div>
                </div>
                    </div>

                <div
                  aria-labelledby="appsettings-tab"
                  class="tab-pane fade"
                  id="appsettings"
                  role="tabpanel"
                >
                     <div class="settings">
                  <div class="form-group">
                    <div class="col-md-3">
                      <div class="form-check">
                        <div class="custom-control custom-checkbox">
                          <input
                            class="custom-control-input"
                            id="autosubstitles"
                            type="checkbox"
                            v-model="settings.autosubstitles"
                          />
                          <label class="custom-control-label" for="autosubstitles">Substitle</label>
                        </div>
                      </div>
                    </div>
                  </div>

                   <div class="form-group">
                    <div class="col-md-3">
                      <div class="form-check">
                        <div class="custom-control custom-checkbox">
                          <input
                            class="custom-control-input"
                            id="ads_player"
                            type="checkbox"
                            v-model="settings.ads_player"
                          />
                          <label class="custom-control-label" for="ads_player">Ads Player</label>
                        </div>
                      </div>
                    </div>
                  </div>

                  <div class="form-group">
                    <div class="col-md-3">
                      <div class="form-check">
                        <div class="custom-control custom-checkbox">
                          <input
                            class="custom-control-input"
                            id="anime"
                            type="checkbox"
                            v-model="settings.anime"
                          />
                          <label class="custom-control-label" for="anime">Anime</label>
                        </div>
                      </div>
                    </div>
                  </div>

                  <div class="row">
                    <div class="col-md-8">
                      <div class="form-group">
                        <div class="row">
                          <div class="col-md-8">
                            <div class="form-group">
                              <label for="imdb_cover_path">IMDB Cover Path</label>
                              <input
                                class="form-control"
                                id="imdb_cover_path"
                                type="text"
                                v-model="settings.imdb_cover_path"
                              />
                            </div>
                          </div>
                        </div>

                        <div class="row">
                          <div class="col-md-8">
                            <div class="form-group">
                              <label for="app_name">App Android Url</label>
                              <input
                                class="form-control"
                                id="app_url_android"
                                type="text"
                                v-model="settings.app_url_android"
                              />
                            </div>
                          </div>
                        </div>

                        <div class="row">
                          <div class="col-md-8">
                            <div class="form-group">
                              <label for="featured_home_numbers">
                                Featured Elements
                                Numbers
                              </label>
                              <input
                                class="form-control"
                                id="featured_home_numbers"
                                type="text"
                                v-model="settings.featured_home_numbers"
                              />
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                 </div>

                <div
                  aria-labelledby="payments-tab"
                  class="tab-pane fade"
                  id="payments"
                  role="tabpanel"
                >


                       <div class="settings">
                  <div class="row">
                    <div class="col-md-8">
                      <div class="form-group">
                        <label for="paypal_client_id">Paypal Client ID</label>
                        <input
                          class="form-control"
                          id="paypal_client_id"
                          type="text"
                          v-model="settings.paypal_client_id"
                        />
                      </div>

                      <div class="form-group">
                        <label for="stripe_publishable_key">Stripe Publishable key</label>
                        <input
                          class="form-control"
                          id="stripe_publishable_key"
                          type="text"
                          v-model="settings.stripe_publishable_key"
                        />
                      </div>


                        <div class="form-group">
                        <label for="stripe_secret_key">Stripe Secret key</label>
                        <input
                          class="form-control"
                          id="stripe_secret_key"
                          type="text"
                          v-model="settings.stripe_secret_key"
                        />
                      </div>
                    </div>
                  </div>
                </div>
                </div>





                    <div
                  aria-labelledby="aws-tab"
                  class="tab-pane fade"
                  id="aws"
                  role="tabpanel"
                >
             <div class="settings">
          
                    <div class="row mb-2">
                      <div class="col-md-3">
                        <div class="form-check">
                          <div class="custom-control custom-switch ml-2">
                            <input
                              v-model="settings.aws_s3_storage"
                              type="checkbox"
                              class="custom-control-input"
                              id="aws_s3_storage"
                            />
                            <label class="custom-control-label" for="aws_s3_storage">AWS S3 Storage</label>
                          </div>
                        </div>
                      </div>
                    </div>
                    
                    <div class="row">
                      <div class="col-md-12">
                        <div class="form-group">
                          <label for="aws_access_key_id">AWS access key ID</label>
                          <input
                            v-model="settings.aws_access_key_id"
                            id="aws_access_key_id"
                            type="text"
                            class="form-control"
                          />
                        </div>
                        </div>
                      <div class="col-md-12">
                        <div class="form-group">
                          <label for="aws_secret_access_key">AWS secret access key</label>
                          <input
                            v-model="settings.aws_secret_access_key"
                            id="aws_secret_access_key"
                            type="text"
                            class="form-control"
                          />
                        </div>
                        </div>
                      <div class="col-md-12">
                        <div class="form-group">
                          <label for="aws_default_region">AWS default region</label>
                          <input
                            v-model="settings.aws_default_region"
                            id="aws_default_region"
                            type="text"
                            class="form-control"
                          />
                        </div>
                        </div>
                      <div class="col-md-12">
                        <div class="form-group">
                          <label for="aws_bucket">AWS bucket</label>
                          <input
                            v-model="settings.aws_bucket"
                            id="aws_bucket"
                            type="text"
                            class="form-control"
                          />
                        </div>
                        
                      </div>
                  </div>
                </div>
                </div>








              </div>
            </div>
          </div>

          <button
            @click.prevent="update()"
            aria-pressed="true"
            class="btn btn-primary mr-2"
            type="button"
          >Update</button>
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
      logo: url + "/api/image/logo",
      miniLogo: url + "/api/image/minilogo",
      options: ['Auto','UnityAds', 'StartApp', 'Admob', 'Facebook','Appodeal'],
    };
  },
  methods: {
    // update the settings in the database
    async update() {
      try {
        const response = await axios.put(
          url + "/admin/settings/update/" + this.settings.id,
          this.settings
        );
        this.showSuccess(response.data.message);
      } catch (error) {
        this.showError(error.response);
      }
    },
    // update the logo in the storage
    async updateLogo(event) {
      try {
        const data = new FormData();
        data.append("image", event.target.files[0]);

        const response = await axios.post(url + "/admin/update/logo", data);
        this.logo = response.data.image_path;
        this.showSuccess(response.data.message);
      } catch (error) {
        this.showError(error.response);
      }
    },

    // update the mini logo in the storage
    async updateMiniLogo(event) {
      try {
        const data = new FormData();
        data.append("image", event.target.files[0]);

        const response = await axios.post(url + "/admin/update/minilogo", data);
        this.miniLogo = response.data.image_path;
        this.showSuccess(response.data.message);
      } catch (error) {
        this.showError(error.response);
      }
    },
  },
  mixins: [notifications, settings],
};
</script>


<style lang="scss" scoped>


.nav-tabs {
    border: none;
}



.settings {
    margin-top: 35px;
}

</style>