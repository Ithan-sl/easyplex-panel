<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Http\ClearsResponseCache;

class Collection extends Model
{

    protected $fillable = ['id', 'name', 'poster_path', 'backdrop_path'];


    


}
