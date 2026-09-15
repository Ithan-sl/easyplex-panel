<?php

namespace App;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Cashier\Billable;
use App\Notifications\PasswordReset;
use Laravel\Passport\HasApiTokens;
use BeyondCode\Comments\Contracts\Commentator;
use ChristianKuri\LaravelFavorite\Traits\Favoriteability;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Support\Carbon;
use Zorb\Promocodes\Traits\AppliesPromocode;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class User extends Authenticatable implements Commentator, MustVerifyEmail
{
    use Notifiable, HasApiTokens, Billable, HasFactory, Favoriteability, AppliesPromocode;

    private $settings;

    protected $with = ['profiles', 'devices'];



    public function __construct()
    {
        $this->settings = config('app_settings');
    }

    protected $fillable = [
        'name', 'email', 'password', 'avatar', 'premuim', 'manual_premuim', 'pack_name', 'pack_id', 'start_at', 'expired_in', 'role', 'email_verified_at',
        'type', 'provider_name', 'provider_id', 'phone', 'login_code'
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected $casts = [
        'premuim' => 'int',
        'active' => 'int',
        'verified' => 'int'
    ];

    protected $dates = [
        'email_verified_at' => 'datetime', 'trial_ends_at', 'subscription_ends_at', 'created_at'
    ];

    public function generateAndAssignLoginCode()
    {
        $lastGeneratedTime = Cache::get('last_login_code_generation_time_' . $this->id);

        if (!$lastGeneratedTime || now()->diffInMinutes($lastGeneratedTime) >= 1) {
            $randomCode = Str::random(8);
            $this->login_code = $randomCode;
            $this->save();

            Cache::put('last_login_code_generation_time_' . $this->id, now());
        }
    }

    public function getExpiredInAttribute($value)
    {
        return $this->asDateTime($value)->toDateString();
    }

    public function findFacebookUserForPassport($token) {
        // Your logic here using Socialite to push user data from Facebook generated token.
    }

    public function sendPasswordResetNotification($token)
    {
        $this->notify(new PasswordReset($token));
    }

    public function needsCommentApproval($model): bool
    {
        return false;    
    }

    public function profiles()
    {
        return $this->hasMany(Profile::class);
    }

    public function devices()
    {
        return $this->hasMany(Device::class);
    }

    public function getFavoriteMoviesAttribute()
    {
        return DB::table('favorites')
            ->join('movies', 'favorites.favoriteable_id', '=', 'movies.id')
            ->where('favorites.user_id', $this->id)
            ->where('favorites.favoriteable_type', Movie::class)
            ->select('id',
            'title',
            'poster_path',
            'backdrop_path',
            'backdrop_path_tv',
            'overview',
            'release_date',
            'views',
            DB::raw("'movie' AS type"))
            ->paginate(12);
    }

    public function getFavoriteSeriesAttribute()
    {
        return DB::table('favorites')
            ->join('series', 'favorites.favoriteable_id', '=', 'series.id')
            ->where('favorites.user_id', $this->id)
            ->where('favorites.favoriteable_type', Serie::class)
            ->select('id',
            'name',
            'poster_path',
            'backdrop_path',
            'backdrop_path_tv',
            'overview',
            'first_air_date AS release_date',
            'views',
            DB::raw("'serie' AS type"))
            ->paginate(12);
    }

    public function getFavoriteAnimesAttribute()
    {
        return DB::table('favorites')
            ->join('animes', 'favorites.favoriteable_id', '=', 'animes.id')
            ->where('favorites.user_id', $this->id)
            ->where('favorites.favoriteable_type', Anime::class)
            ->select('id',
            'name',
            'poster_path',
            'backdrop_path',
            'backdrop_path_tv',
            'overview',
            'first_air_date AS release_date',
            'views',
            DB::raw("'anime' AS type"))
            ->paginate(12);
    }

    public function getFavoriteStreamingAttribute()
    {
        return DB::table('favorites')
            ->join('livetvs', 'favorites.favoriteable_id', '=', 'livetvs.id')
            ->where('favorites.user_id', $this->id)
            ->where('favorites.favoriteable_type', Livetv::class)
            ->select('livetvs.id', 'livetvs.name', 'livetvs.poster_path', DB::raw("'streaming' AS type"))
            ->paginate(12);
    }


            public function isAdmin()
        {
            return $this->role === 'admin'; // Or however you're storing admin status
        }
}