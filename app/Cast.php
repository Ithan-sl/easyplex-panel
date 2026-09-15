<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Http\ClearsResponseCache;

class Cast extends Model
{
    use ClearsResponseCache;
    protected $guarded = [];

}
