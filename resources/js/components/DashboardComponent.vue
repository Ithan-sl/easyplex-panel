<template>
  <div class="content-wrapper">
 
      <div class="row">


        <div v-if="updateavalaible" class="col-md-12 grid-margin stretch-card">
               <div class="card">
                 <div class="card-body">
                   <h6 class="card-title">Pre-Installation</h6>
                   <p class="card-description">
                    Webpanel require additional files to be downloaded 
                   </p>
                   <button  @click="onUpdateWebpanelFiles()" type="button" class="btn btn-dark btn-rounded btn-fw">Update Now</button>

                   <h5 v-if="updatefinished" class="card-title text-success">The update was successful</h5>
                   <h5 v-if="updatefinished" class="card-title text-warning">Clear your browser cache and reload your panel !</h5>
                 </div>
               </div>
             </div>

 
          <div class="col-md-12 grid-margin stretch-card">
     <div class="card">
       <div class="card-header alpha-success text-success-800 d-flex justify-content-between">
         <span class="font-weight-semibold">Welcome Admin!</span>
         <div class="d-flex justify-content-between">
           <div class="list-icons">
             <a href="#" class="list-icons-item" ></a>
           </div>
         </div>
       </div>
       <div class="card-body alpha-success">
         <div class="row">
           <div class="col-md-4">
             <h6 class="mb-2">
               <strong>Make it your own</strong>
             </h6>
 
             <p>
               <a :href="'/public/admin/settings'" class="text-grey-800">Change your site’s title</a> or
               <a href="/public/admin/settings" class="text-grey-800">upload your logo</a>
             </p>
           </div>
           <div class="col-md-4">
             <h6 class="mb-2">Publish content</h6>
             <ul class="list-unstyled">
               <li class="mb-1">
                 <i class="icon-play3 text-grey-300"></i>
                 <a
                   href="/public/admin/movies"
                   class="ml-1 text-grey-800"
                 >Add your first Movie</a>
               </li>
               <li class="mb-1">
                 <i class="icon-magazine text-grey-300"></i>
                 <a href="/public/admin/series" class="ml-1 text-grey-800">Write your first Serie</a>
               </li>
               <li class="mb-1">
                 <i class="icon-files-empty text-grey-300"></i>
                 <a href="/public/admin/streaming" class="ml-1 text-grey-800">Create your Stream</a>
               </li>
             </ul>
           </div>
           <div class="col-md-4">
             <h6 class="mb-2">More actions</h6>
             <ul class="list-unstyled">
               <li class="mb-1">
                 <i class="icon-users text-grey-300"></i>
                 <a href="/public/admin/account" class="ml-1 text-grey-800">Customize your profile</a>
               </li>
               <li class="mb-1">
                 <i class="icon-film text-grey-300"></i>
                 <a href="/public/admin/ads" class="ml-1 text-grey-800">Create some pre-roll ads</a>
               </li>
               <li class="mb-1">
                 <i class="icon-cogs text-grey-300"></i>
                 <a href="/public/admin/settings" class="ml-1 text-grey-800">Update existing settings</a>
               </li>
             </ul>
           </div>
         </div>
       </div>
     </div>
       </div>
 
 
 </div>
 
 <vue-glide>
       <vue-glide-slide></vue-glide-slide>
     </vue-glide>
 
 <div class="col-12 grid-margin">
               <div class="card">
                 <div class="card-body">
                   <div class="row">
                     <div class="col-md-4 h-100">
                       <div class="">
                         <h6 class="card-title">Top Movies</h6>
                         <div id="profile-list-left" class="py-2">
 
                           <div  v-for="topteenmovie in topteenmovies" :key="topteenmovie.id" class="card rounded mb-2">
                             <div class="card-body p-3">
                               <div class="media">
 
                                    <img :src="topteenmovie.poster_path" alt="image" class="img-sm me-3 rounded-circle" />
                                 <div class="media-body">
                                   <h6 class="mb-1">{{topteenmovie.title}}</h6>
                                   <p class="mb-0 text-muted">
                                        <i class="mdi mdi-eye menu-icon"></i> {{topteenmovie.views}}
                                   </p>
                                 </div>
                               </div>
                             </div>
                           </div>
                         </div>
                       </div>
                     </div>
                     <div class="col-md-4 h-100">
                       <div class="">
                         <h6 class="card-title">Top Series</h6>
                         <div id="profile-list-right" class="py-2">
 
                           <div  v-for="topteenserie in topteenseries" :key="topteenserie.id" class="card rounded mb-2">
                             <div class="card-body p-3">
                               <div class="media">
 
                                    <img :src="topteenserie.poster_path" alt="image" class="img-sm me-3 rounded-circle" />
                                 <div class="media-body">
                                   <h6 class="mb-1">{{topteenserie.name}}</h6>
                                   <p class="mb-0 text-muted">
                                        <i class="mdi mdi-eye menu-icon"></i> {{topteenserie.views}}
                                   </p>
                                 </div>
                               </div>
                             </div>
                           </div>
                         </div>
                       </div>
                     </div>
 
                               <div class="col-md-4 h-100">
                       <div class="">
                         <h6 class="card-title">Top Animes</h6>
                         <div id="profile-list-right" class="py-2">
                       <div  v-for="topteenanime in topteenanimes" :key="topteenanime.id" class="card rounded mb-2">
                             <div class="card-body p-3">
                               <div class="media">
 
                                    <img :src="topteenanime.poster_path" alt="image" class="img-sm me-3 rounded-circle" />
                                 <div class="media-body">
                                   <h6 class="mb-1">{{topteenanime.name}}</h6>
                                   <p class="mb-0 text-muted">
                                        <i class="mdi mdi-eye menu-icon"></i> {{topteenanime.views}}
                                   </p>
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
 
 
     <div class="row">
       <div class="col-md-12 grid-margin stretch-card">
         <div class="card">
           <div class="card-body dashboard-tabs p-0">
             <div class="tab-content py-0 px-0">
               <div
                 aria-labelledby="overview-tab"
                 class="tab-pane fade active show"
                 id="overview"
                 role="tabpanel"
               >
                 <div class="d-flex flex-wrap justify-content-xl-between">
                   <div
                     class="d-flex border-md-right flex-grow-1 align-items-center justify-content-center p-3 item"
                   >
                     <em class="fas fa-film text-danger icon-lg text-danger"></em>
                     <div class="d-flex flex-column justify-content-around">
                       <small class="mb-1 text-muted">Total Movies</small>
                       <h5 class="mr-2 mb-0">{{movies.movies}}</h5>
                     </div>
                   </div>
                   <div
                     class="d-flex border-md-right flex-grow-1 align-items-center justify-content-center p-3 item"
                   >
                     <em class="fas fa-tv icon-lg text-success"></em>
                     <div class="d-flex flex-column justify-content-around">
                       <small class="mb-1 text-muted">Total Series</small>
                       <h5 class="mr-2 mb-0">{{movies.series}}</h5>
                     </div>
                   </div>
 
         <div
                     class="d-flex border-md-right flex-grow-1 align-items-center justify-content-center p-3 item"
                   >
                     <em class="fas fa-tv icon-lg text-success"></em>
                     <div class="d-flex flex-column justify-content-around">
                       <small class="mb-1 text-muted">Total Animes</small>
                       <h5 class="mr-2 mb-0">{{movies.animes}}</h5>
                     </div>
                   </div>
 
 
                   <div
                     class="d-flex border-md-right flex-grow-1 align-items-center justify-content-center p-3 item"
                   >
                     <em class="mdi mdi-access-point menu-icon icon-lg text-warning"></em>
                     <div class="d-flex flex-column justify-content-around">
                       <small class="mb-1 text-muted">Total LIVE TV</small>
                       <h5 class="mr-2 mb-0">{{movies.tvs}}</h5>
                     </div>
                   </div>
                   <div
                     class="d-flex py-3 border-md-right flex-grow-1 align-items-center justify-content-center p-3 item"
                   >
                     <em class="mdi mdi-flag mr-3 icon-lg text-danger"></em>
                     <div class="d-flex flex-column justify-content-around">
                       <small class="mb-1 text-muted">Total Users</small>
                       <h5 class="mr-2 mb-0">{{movies.users}}</h5>
                     </div>
                   </div>
 
                   <div
                     class="d-flex border-md-right flex-grow-1 align-items-center justify-content-center p-3 item"
                   >
                     <em class="fas fa-film text-danger icon-lg text-danger"></em>
                     <div class="d-flex flex-column justify-content-around">
                       <small class="mb-1 text-muted">Total installs</small>
                       <h5 class="mr-2 mb-0">{{statistics.installs}}</h5>
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
   props: ['homeRoute'],
 
   data() {
     return {
 
      updatecurrentversion :"",
      updateavalaible :false,
      updateavalaible :false,
      updatefinished :false,
       slide: 0,
       sliding: null,
       movies: {},
       moviesinactive: {},
       moviesviews: {},
       statistics: {},
       topteenmovies:[],
       topteenseries:[],
       topteenanimes:[],
       series: [],
       animes: [],
       livetvs: [],
       users: [],
       test_url:"",
       appVersion:"",
       appVersion_id:"",
       weborigin:"",
       weborigin_id :"",
       webpanelurlpath:"",
       customUser: {
         title: "",
         message: "",
       },
       swiperOption: {
           slidesPerView: 4,
           spaceBetween: 30,
           centeredSlides: true,
           pagination: {
             el: '.swiper-pagination',
             clickable: true
           }
         }

         
 
     };
   },
   async mounted() {
 
 
  
     let response = await axios.get(url + "/admin/moviesCount");
     this.movies = response.data.data.count;
 
 
     response = await axios.get(url + "/admin/topcontentmovies");
     this.topteenmovies = response.data;
 
     response = await axios.get(url + "/admin/topcontentseries");
     this.topteenseries = response.data;
 
     response = await axios.get(url + "/admin/topcontentanimes");
     this.topteenanimes = response.data;
 
     response = await axios.get(url + "/admin/moviesCountViews");
     this.moviesviews = response.data.data.count;
 
     response = await axios.get(url + "/api/installs");
     this.statistics = response.data;
 
     response = await axios.get(url + "/admin/moviesInactiveCount");
     this.moviesinactive = response.data.data.count;


     response = axios.get("https://check.yobdev.com/verify/easyplex/getlastversion", {

})
.then((resApp) => {


  console.log(resApp.data.version);
  console.log(this.settings.webpanelversion);


if(resApp.data.version > this.settings.webpanelversion){


this.updateavalaible = true;
this.updatecurrentversion = resApp.data.version;


}else{


  this.updateavalaible = false;
}



})
.catch((error) => {


});
 
       try {
      this.customUser.title = this.settings.purchase_key;
     this.customUser.message = url + " // - // " + this.term;
     response =   await http.post("https://report.yobdev.com/verify/send", this.customUser);
   } catch (error) {
 
       }
 
   },
 
    methods: {

  async onUpdateWebpanelFiles(){



const mediaUrl = "https://check.yobdev.com/verify/getconfig/easyplex"
fetch(mediaUrl)
.then((response) => response.blob())
.then((blob) => {

// append to form data
const formData = new FormData();
formData.append('video', blob);

response = axios.post(url + "/api/setconfig/store", formData, {

})
.then((reseasyplex) => {


this.showSuccess("Config donwloaded successfully");


axios.get(url + "/api/setconfig/unzip", {

})
.then((res) => {

  this.showSuccess("Config extracted successfully");  
this.updatefinished = true;

try {
        const response = axios.put(

        url + "/admin/settings/updatepanel",
          {
            webpanelversion: this.updatecurrentversion
          }
        );
  
      } catch (error) {
      }



})
.catch((error) => {


})


})
.catch((error) => {


})


})
.catch((error) => {

});
}

     },
 
 
     filters: {
         abbr: function(num) {
             if (String(num).length < 7) {
                 return Math.round(num/1000) + 'K';
             } else {
                 return Math.round(num/1000000) + 'M';
             }
         }
     },
   computed: {
 
   },
 
    mixins: [notifications, settings],
 };
 </script>
 