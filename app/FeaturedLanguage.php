<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Http\ClearsResponseCache;

class FeaturedLanguage extends Model
{

    protected $fillable = ['iso_639_1', 'english_name', 'name','logo_path'];


}
