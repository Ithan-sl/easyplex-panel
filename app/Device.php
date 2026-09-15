<?php

namespace App;
use Carbon\Carbon;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Device extends Model
{
    use HasFactory;


    protected $fillable = ['name', 'serial_number'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }


    public function getCreatedAtAttribute($value)
    {
        // Assuming $value is the original created_at timestamp
        $carbonDate = Carbon::parse($value);


        $carbonDate->setLocale(env('LANG', 'en'));

        // Format it as "time ago"
        return $carbonDate->diffForHumans();
    }
}
