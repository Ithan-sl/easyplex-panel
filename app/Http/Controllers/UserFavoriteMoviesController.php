<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\ContentQueryService;
use App\User;
use Illuminate\Support\Facades\Auth;

class UserFavoriteMoviesController extends Controller
{

    protected $queryService;

    public function __construct(ContentQueryService $queryService)
    {
        $this->queryService = $queryService;

    }

    public function getFavoriteMovies($userId)
    {
        // Check if the authenticated user's ID matches the requested user ID
        if (Auth::id() != $userId) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $user = User::findOrFail($userId);

        $favoriteMovies = $user->favorite_movies;

        return response()->json($favoriteMovies);
    }

    public function getFavoriteSeries($userId)
    {

        // Check if the authenticated user's ID matches the requested user ID
        if (Auth::id() != $userId) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $user = User::findOrFail($userId);
        $favoriteMovies = $user->favorite_series;

        return response()->json($favoriteMovies);
    }

    public function getFavoriteAnimes($userId)
    {

        // Check if the authenticated user's ID matches the requested user ID
        if (Auth::id() != $userId) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $user = User::findOrFail($userId);
        $favoriteMovies = $user->favorite_animes;

        return response()->json($favoriteMovies);
    }


    public function getFavoriteStreaming($userId)
    {

        // Check if the authenticated user's ID matches the requested user ID
        if (Auth::id() != $userId) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $user = User::findOrFail($userId);
        $favoriteMovies = $user->favorite_streaming;

        return response()->json($favoriteMovies);
    }
}
