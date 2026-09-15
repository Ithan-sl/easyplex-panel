<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Http\ClearsResponseCache;

class Language extends Model
{

    protected $fillable = ['iso_639_1', 'english_name', 'name','logo_path','featured'];

}
