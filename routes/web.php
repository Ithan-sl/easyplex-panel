<?php

use App\Http\Controllers\MovieController;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/


Auth::routes();

Route::get('/', function () {
  return redirect('/home');
});


// Admin Routes
Route::group(['prefix' => 'admin', 'middleware' => 'admin'], function () {



  Route::get('/firebase/getAccessToken', 'SettingController@sendPushNotification');
  Route::post('/firebase/store', 'SettingController@storeFirebaseJson');
  Route::get('/firebase/{filename}', 'SettingController@getFirebase');

  Route::post('/m3u/store', 'SettingController@storeM3u');


  Route::get('/cacheClear', function() {
    $exitCode2 = Artisan::call('optimize');
    $exitCode = Artisan::call('config:clear');
    return 'Routes cache cleared';
});
 
    Route::get('/updateMailSettings', 'AdminController@updateMailSettings');


    Route::get('/certification', 'AdminController@certification')->name('admin.certification');

    Route::get('/', 'AdminController@index')->name('admin');
    Route::get('/movies', 'AdminController@movies')->name('admin.movies');
    Route::get('/series', 'AdminController@series')->name('admin.series');
    Route::get('/users', 'AdminController@users')->name('admin.users');
    Route::get('/streaming', 'AdminController@streaming')->name('admin.streaming');
    Route::get('/qualities', 'AdminController@servers')->name('admin.server');
    Route::get('/headers', 'AdminController@headers')->name('admin.headers');
    Route::get('/genres', 'AdminController@genres')->name('admin.genres');
    Route::get('/networks', 'AdminController@networks')->name('admin.networks');
    Route::get('/casters', 'AdminController@casters')->name('admin.casters');
    Route::get('/notifications', 'AdminController@notifications')->name('admin.notifications');
    Route::get('/settings', 'AdminController@settings')->name('admin.settings');
    Route::get('/account', 'AdminController@account')->name('admin.account');
    Route::get('/news', 'AdminController@articles')->name('admin.articles');
    Route::get('/substitles', 'AdminController@substitles')->name('admin.substitles');
    Route::get('/reports', 'AdminController@reports')->name('admin.reports');
    Route::get('/suggestions', 'AdminController@suggestions')->name('admin.suggestions');
    Route::get('/animes', 'AdminController@animes')->name('admin.animes');
    Route::get('/ads', 'AdminController@ads')->name('admin.ads');
    Route::get('/upcoming', 'AdminController@upcomings')->name('admin.upcomings');
    Route::get('/plans', 'AdminController@plans')->name('admin.plans');
    Route::get('/categories', 'AdminController@categories')->name('admin.categories');
    Route::get('/previews', 'AdminController@previews')->name('admin.previews');
    Route::get('/featured', 'AdminController@featured')->name('admin.featured');
    Route::get('/comments', 'AdminController@comments')->name('admin.comments');
    Route::get('/certifications', 'AdminController@certifications')->name('admin.certifications');
    Route::get('/languages', 'AdminController@languages')->name('admin.languages');
    Route::get('/collections', 'AdminController@collections')->name('admin.collections');


    Route::get('/moviesCount', 'AdminController@moviesCount');
    Route::get('/moviesInactiveCount', 'AdminController@moviesInactiveCount');

    Route::get('/moviesCountViews', 'AdminController@moviesCountViews');
   
    Route::post('/create', 'Api\Auth\RegisterController@register');




    // Dashboard
    Route::get('/Allfeatured', 'AdminController@featured');
    Route::get('/topmovies', 'AdminController@topmovies');
    Route::get('/topanimes', 'AdminController@topanimes');
    Route::get('/topepisodes', 'AdminController@topepisodes');
    Route::get('/toplivetvs', 'AdminController@toplivetvs');
    Route::get('/topcontentmovies', 'AdminController@topcontentmovies');
    Route::get('/topcontentseries', 'AdminController@topcontentseries');
    Route::get('/topcontentanimes', 'AdminController@topcontentanimes');
    Route::get('/topcontent', 'AdminController@topcontent');


    // Settings
    Route::put('/settings/updatepanel', 'AdminController@updateWebPanel');
    Route::post('/settings/update', 'SettingController@updateSetting');

    Route::get('/settings/data', 'SettingController@data');

    Route::get('/settingsSecurity/data', 'SettingController@settingsSecurity');


    Route::put('/settings/update/{setting}', 'SettingController@update');
    Route::post('/update/logo', 'SettingController@updateLogo');
    Route::post('/update/episode', 'SettingController@updateEpisode');
    Route::post('/update/mediahome', 'SettingController@mediahome');
    Route::post('/update/minilogo', 'SettingController@updateMiniLogo');
    Route::post('/update/notificationicon', 'SettingController@updateNotificationIcon');
    Route::post('/update/customBanner', 'SettingController@customBanner');
    Route::post('/update/splash', 'SettingController@updateSplash');

    // Account
    Route::get('/account/data', 'UserController@data');
    Route::put('/account/update', 'UserController@update');
    Route::put('/account/password/update', 'UserController@passwordUpdate');
    Route::put('/account/passwordApp/update', 'UserController@passwordUpdateApp');
    Route::put('/account/password/updateUser', 'UserController@updateUserPassword');
    Route::post('/update/avatar', 'UserController@updateAvatar');


    // Users
    Route::get('/users/data', 'UserController@data');
    Route::get('/users/allusers', 'UserController@allusers');
    Route::delete('/users/destroy/{user}', 'UserController@destroy');
    Route::put('/users/update/{user}', 'UserController@updateUser');
    Route::post('/users/store', 'UserController@store');
    Route::put('/user/ban/{id}', 'UserController@banUser');
    Route::put('/user/unban/{id}', 'UserController@unbanUser');


    // Comments

    Route::get('/comments/allcomments', 'CommentController@allcomments');
    Route::delete('/media/delete/comments/{movieid}', 'MovieController@deletecommentweb');

 
    // Featureds

    Route::post('/featureds/image/store', 'FeaturedController@storeImg');


    // Search Admin Panel
    Route::get('/search_langs', 'SearchController@searchLangs');
    Route::get('/search_comments', 'SearchController@searchComments');
    Route::get('/searchwebcasts', 'CastController@searchCast');
    Route::get('/search_featured', 'SearchController@searchFeatured');
    Route::get('/search_casts', 'SearchController@searchCasts');
    Route::get('/search_certifications', 'SearchController@searchCertifications');
    Route::get('/search_movies', 'SearchController@searchMovies');
    Route::get('/search_series', 'SearchController@searchSeries');
    Route::get('/search_animes', 'SearchController@searchAnimes');
    Route::get('/search_streaming', 'SearchController@searchStreaming');
    Route::get('/search_users', 'SearchController@searchUsers');
    Route::get('/movies/dataweb', 'MovieController@web');
    Route::post('/moviesmedia/storemovie', 'MovieController@store');
    Route::delete('/movies/destroy/{movie}', 'MovieController@destroy');
    Route::post('/movies/image/store', 'MovieController@storeImg');
    Route::put('/moviesmedia/updatemovie/{movie}', 'MovieController@update');
    Route::put('/settings/set', 'AdminController@set');
    //Route::put('/moviesmedia/updatemovie/{movie}', [MovieController::class, 'update']);
    Route::post('/movies/videodelete/destroy/{movie}', 'MovieController@deteleVideoFromServer');
    Route::delete('/movies/downloads/destroy/{moviedownload}', 'MovieController@downloadDestroy');
    Route::delete('/movies/videos/destroy/{movievideo}', 'MovieController@videoDestroy');
    Route::delete('/movies/substitles/destroy/{moviesubstitle}', 'MovieController@substitleDestroy');
    Route::delete('/movies/genres/destroy/{moviegenre}', 'MovieController@destroyGenre');
    Route::get('/movies/videos/{movie}', 'MovieController@videos');
    Route::get('/movies/productions/{movie}', 'MovieController@productions');
    Route::get('/movies/downloads/{movie}', 'MovieController@downloads');
    Route::get('/movies/substitles/{movie}', 'MovieController@substitles');
    Route::get('/movies/casters/{movie}', 'MovieController@casters');
    Route::delete('/movies/casts/destroy/{cast}', 'MovieController@destroyCast');
    Route::delete('/series/casts/destroy/{cast}', 'SerieController@destroyCast');
    Route::delete('/animes/casts/destroy/{cast}', 'AnimeController@destroyCast');
    Route::delete('/movies/networks/destroy/{cast}', 'MovieController@destroyNetworks');
    Route::delete('/series/networks/destroy/{cast}', 'SerieController@destroyNetworks');
    Route::delete('/animes/networks/destroy/{cast}', 'AnimeController@destroyNetworks');



    //Series
    Route::get('/series/data', 'SerieController@data');
    Route::post('/series/store', 'SerieController@store');
    Route::delete('/series/destroy/{serie}', 'SerieController@destroy');
    Route::put('/series/update/{serie}', 'SerieController@update');
    Route::post('/series/image/store', 'SerieController@storeImg');
    Route::delete('/series/genres/destroy/{seriegenre}', 'SerieController@destroyGenre');

    // Seasons And Episodes
    Route::delete('/series/seasons/destroy/{season}', 'SeasonController@destroy');
    Route::delete('/series/episodes/destroy/{episode}', 'EpisodeController@destroy');
    Route::delete('/animes/episodes/destroy/{episode}', 'EpisodeController@destroyAnime');
    Route::delete('/series/videos/destroy/{serievideo}', 'EpisodeController@destroyVideo');
    Route::delete('/animes/videos/destroy/{serievideo}', 'EpisodeController@destroyVideoAnime');
    Route::delete('/animes/downloads/destroy/{serievideo}', 'EpisodeController@destroyDownloadAnime');
    Route::delete('/series/downloads/destroy/{serievideo}', 'EpisodeController@destroyDownloadSerie');
    Route::delete('/series/substitles/destroy/{seriesubstitle}', 'EpisodeController@destroySubstitles');
    Route::delete('/animes/substitles/destroy/{seriesubstitle}', 'EpisodeController@destroyAnimeSubstitles');


    // Livetv
    Route::get('/livetv/data', 'LivetvController@data');
    Route::post('/livetv/store', 'LivetvController@store');
    Route::delete('/livetv/destroy/{livetv}', 'LivetvController@destroy');
    Route::put('/livetv/update/{livetv}', 'LivetvController@update');
    Route::post('/livetv/image/store', 'LivetvController@storeImg');
    Route::delete('/livetv/genres/destroy/{livetvgenre}', 'LivetvController@destroyGenre');
    Route::get('/livetvs/videos/{livetv}', 'LivetvController@videos');
    Route::delete('/livetvs/videos/destroy/{livetv}', 'LivetvController@videoDestroy');

    // Upcoming
    Route::get('/upcoming/data', 'UpcomingController@data');
    Route::post('/upcoming/store', 'UpcomingController@store');
    Route::delete('/upcoming/destroy/{upcoming}', 'UpcomingController@destroy');
    Route::put('/upcoming/update/{upcoming}', 'UpcomingController@update');
    Route::post('/upcoming/image/store', 'UpcomingController@storeImg');


    Route::get('/featured/data', 'FeaturedController@data');
    Route::post('/featured/store', 'FeaturedController@store');
    Route::delete('/featured/destroy/{upcoming}', 'FeaturedController@destroy');
    Route::put('/featured/update/{featured}', 'FeaturedController@update');
    Route::post('/featured/image/store', 'FeaturedController@storeImg');



       // Previews
       Route::get('/preview/data', 'PreviewController@data');
       Route::post('/preview/store', 'PreviewController@store');
       Route::delete('/preview/destroy/{preview}', 'PreviewController@destroy');
       Route::put('/preview/update/{preview}', 'PreviewController@update');
       Route::post('/preview/image/store', 'PreviewController@storeImg');
   


    // Animes
    Route::get('/animes/data', 'AnimeController@data');
    Route::post('/animes/store', 'AnimeController@store');
    Route::delete('/animes/destroy/{anime}', 'AnimeController@destroy');
    Route::put('/animes/update/{anime}', 'AnimeController@update');
    Route::post('/animes/image/store', 'AnimeController@storeImg');
    Route::delete('/animes/genres/destroy/{animegenre}', 'AnimeController@destroyGenre');
    Route::delete('/animes/seasons/destroy/{season}', 'AnimeSeasonController@destroy');



    // Servers
    Route::get('/servers/dataservers', 'ServerController@serversdata');
    Route::get('/drmservers/drmdataservers', 'ServerController@drmdataservers');
    Route::get('/drmservers/drmdataserversweb', 'ServerController@drmdataserversweb');



    Route::post('/servers/store', 'ServerController@store');
    Route::put('/servers/update/{server}', 'ServerController@update');
    Route::delete('/servers/destroy/{server}', 'ServerController@destroy');




        
    Route::post('/drmservers/store', 'ServerController@storedrm');
    Route::put('/drmservers/update/{server}', 'ServerController@updatedrm');
    Route::delete('/drmservers/destroy/{server}', 'ServerController@destroydrm');



       // Headers
       Route::get('/headers/dataheaders', 'HeaderController@headersdata');
       Route::post('/headers/store', 'HeaderController@store');
       Route::put('/headers/update/{header}', 'HeaderController@update');
       Route::delete('/headers/destroy/{header}', 'HeaderController@destroy');

       
       // UserAgents
       Route::get('/useragents/datausersagent', 'HeaderController@userAgentweb');
       Route::get('/useragents/datausersagentoptions', 'HeaderController@datausersagentoptions');
       Route::post('/useragents/store', 'HeaderController@useragentsstore');
       Route::put('/useragents/update/{useragent}', 'HeaderController@useragentsupdate');
       Route::delete('/useragents/destroy/{header}', 'HeaderController@useragentsdestroy');




  // Spoken Langs

  Route::get('/spokenlangs/datawebspokenlangs', 'SpokenLanguagesController@datawebspokenlangs');
  Route::post('/spokenlangs/store', 'SpokenLanguagesController@store');
  Route::put('/spokenlangs/update/{network}', 'SpokenLanguagesController@update');


  Route::delete('/movies/spokenlangs/destroy/{movievideo}', 'SpokenLanguagesController@destroyLangs');


  // Production Companies

  Route::get('/productioncompanies/datawebproductioncompanies', 'ProductionCompanyController@datawebproductioncompanies');
  Route::post('/productioncompanies/store', 'NetworkController@store');
  Route::delete('/productioncompanies/destroy/{network}', 'NetworkController@destroy');
  Route::put('/productioncompanies/update/{network}', 'NetworkController@update');


  // Networks

  Route::get('/networks/datanetworks', 'NetworkController@datawebnetworks');
  Route::post('/networks/store', 'NetworkController@store');
  Route::delete('/networks/destroy/{network}', 'NetworkController@destroy');
  Route::put('/networks/update/{network}', 'NetworkController@update');



  
  // Collections

  Route::get('/collections/datawebcollections', 'CollectionController@datawebcollections');
  Route::post('/collections/store', 'CollectionController@store');
  Route::delete('/collections/destroy/{collection}', 'CollectionController@destroyCollection');
  Route::put('/collections/update/{collection}', 'CollectionController@update');
  Route::delete('/movie/collection/destroy/{collection}', 'MovieController@destroyCollection');
  Route::delete('/serie/collection/destroy/{collection}', 'SerieController@destroyCollection');
  Route::delete('/anime/collection/destroy/{collection}', 'AnimeController@destroyCollection');


  // Certifications
  Route::delete('/movie/certification/destroy/{certification}', 'MovieController@destroyCertification');
  Route::delete('/serie/certification/destroy/{certification}', 'SerieController@destroyCertification');
  Route::delete('/anime/certification/destroy/{certification}', 'AnimeController@destroyCertification');
  Route::delete('/certification/delete-all-records', 'CertificationController@deleteAllRecords');


  Route::post('/certification/store', 'CertificationController@store');
  Route::put('/certifications/update/{certification}', 'CertificationController@update');
  Route::delete('/certifications/destroy/{certification}', 'CertificationController@destroy');


  // Langs

  Route::get('/languages/data', 'LanguagesController@data');
  Route::post('/languages/store', 'LanguagesController@store');
  Route::delete('/languages/destroy/{langs}', 'LanguagesController@destroy');
  Route::put('/languages/update/{lang}', 'LanguagesController@update');
  Route::post('/languages/fetch', 'LanguagesController@fetch');
  Route::post('/languages/image/store', 'LanguagesController@storeImg');



  Route::delete('/movies/languages/destroy/{moviegenre}', 'LanguagesController@destroyMovieLanguage');
  Route::delete('/series/languages/destroy/{moviegenre}', 'LanguagesController@destroySerieLanguage');
  Route::delete('/animes/languages/destroy/{moviegenre}', 'LanguagesController@destroyAnimeLanguage');


    // Genres
    Route::get('/genres/datagenres', 'GenreController@datagenres');
    Route::post('/genres/store', 'GenreController@store');
    Route::post('/genres/fetch', 'GenreController@fetch');
    Route::delete('/genres/destroy/{genre}', 'GenreController@destroy');
    Route::put('/genres/update/{genre}', 'GenreController@update');




    Route::get('/certifications/datagenres', 'CertificationController@datagenres');
    Route::post('/certifications/store', 'CertificationController@store');
    Route::post('/certifications/fetch', 'CertificationController@fetch');
    Route::delete('/certifications/destroy/{genre}', 'CertificationController@destroy');
    Route::put('/certifications/update/{genre}', 'CertificationController@update');


    Route::get('/networks/datawebnetworks', 'NetworkController@datawebnetworks');

    Route::put('/casters/updatecast/{cast}', 'CastController@update');
    Route::get('/casters/datawebcaster', 'CastController@datawebcaster');
    Route::get('/casters/datacasters', 'CastController@datacasters');
      Route::get('/casts/data', 'CastController@data');
      Route::post('/casts/store', 'CastController@store');
      Route::post('/casts/fetch', 'CastController@fetch');
      Route::delete('/casts/destroy/{genre}', 'CastController@destroy');
      Route::put('/casts/update/{cast}', 'CastController@update');
      Route::post('/casts/image/store', 'CastController@storeImg');

   
       Route::get('/categories/data', 'CategoryController@data');
       Route::post('/categories/store', 'CategoryController@store');
       Route::delete('/categories/destroy/{genre}', 'CategoryController@destroy');
       Route::put('/categories/update/{genre}', 'CategoryController@update');



    // Videos
    Route::post('/video/store', 'VideoController@store');
    Route::post('/video/anime/store', 'VideoController@store');
    Route::post('/streaming/store', 'VideoController@Streamingstore');


    // Substitles
    Route::get('/substitles/data', 'SubstitleController@data');
    Route::post('/substitle/store', 'SubstitleController@store');
    Route::put('/substitles/update/{substitle}', 'SubstitleController@update');


    // Reports
    Route::post('/reports/send', 'ReportController@sendReport');
    Route::get('/reports/data', 'ReportController@data');
    Route::delete('/reports/destroy/{report}', 'ReportController@destroy');



    Route::post('/suggestions/send', 'SuggestionsController@sendReport');
    Route::get('/suggestions/data', 'SuggestionsController@data');
    Route::delete('/suggestions/destroy/{report}', 'SuggestionsController@destroy');


    // Ads
    Route::get('/ads/data', 'AdsController@data');
    Route::delete('/ads/destroy/{ads}', 'AdsController@destroy');
    Route::post('/ads/store', 'AdsController@store');
    Route::put('/ads/update/{ads}', 'AdsController@update');


     // Plans
     Route::get('/plans/data', 'PlanController@data');
     Route::get('/subscriptions/all', 'PlanController@all');
     Route::delete('/plans/destroy/{plan}', 'PlanController@destroy');
     Route::post('/plans/store', 'PlanController@store');
     Route::put('/plans/update/{plan}', 'PlanController@update');
     Route::get('/subscriptions/data', 'PlanController@all');
     Route::get('/subscriptions/paypal', 'PlanController@paypal');
     Route::get('/subscriptions/find', 'PlanController@find');


     Route::post('/settings/image/store', 'SettingController@storeImg');


     Route::get('/animes/show/{anime}', 'AnimeController@show');
     Route::get('/media/detail/{movie}', 'MovieController@show');
     Route::get('/series/show/{serie}', 'SerieController@show');
     Route::get('/stream/show/{livetv}', 'LivetvController@show');


     Route::get('/languages/data/', 'LanguagesController@datamobile');
     Route::get('/languages/admindata/', 'LanguagesController@data');



        // Coupon
        Route::get('/coupons/data', 'CouponController@data');
        Route::post('/coupon/store', 'CouponController@create');
        Route::delete('/coupon/destroy/{plan}', 'CouponController@destroy');
        Route::put('/coupons/{couponId}', 'CouponController@update');


        Route::get('certification/data', 'CertificationController@data'); 
        Route::get('certification/datacertifications', 'CertificationController@datacertifications'); 
        Route::post('certification/fetch', 'CertificationController@fetch'); 

});


Auth::routes();

Route::get('/home', 'AdminController@index')->name('home');




