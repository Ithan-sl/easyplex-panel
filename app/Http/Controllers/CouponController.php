<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Zorb\Promocodes\Facades\Promocodes;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class CouponController extends Controller
{
    public function create(Request $request)
    {



        $this->validate($request, [
            'planName' => 'required',
            'planDays' => 'required'
        ]);

        $planName = $request->input('planName');

        $planDays = $request->input('planDays');



        $user = Auth()->user();

        $couponSettings = [
            'mask' => '****-****-****',
            'characters' => 'ABCDEFGHIJKLMNOPQRSTUVWYZ123456789',
            'expiration' => now()->addYear(),
            'details' => [
            'name' => $planName,
            'days' => $planDays],
        ];
    
        // Get the user's input for count, usages, unlimited, and multiUse from the request
        $count = $request->input('count', 1);
        $usages = $request->input('usages_left', 1);
        $unlimited = $request->input('unlimited', false);
        $multiUse = $request->input('multi_use', false);
    
        // Set the values in the couponSettings array based on user input
        $couponSettings['count'] = $count;
        $couponSettings['usages'] = $usages;
        $couponSettings['unlimited'] = $unlimited;
        $couponSettings['multiUse'] = $multiUse;
    
        // Create the coupon using the Promocodes library with the configured settings
        $coupon = Promocodes::mask($couponSettings['mask'])
            ->characters($couponSettings['characters'])
            ->multiUse($couponSettings['multiUse'])
            ->unlimited($couponSettings['unlimited'])
            ->count($couponSettings['count'])
            ->usages($couponSettings['usages'])
            ->expiration($couponSettings['expiration'])
            ->details($couponSettings['details'])
            ->create();
    
        return response()->json(['coupon' => $coupon], 201);
    }



    public function update(Request $request, $couponId)
{


    // Retrieve the coupon based on the provided $couponId
    $coupon = DB::table('promocodes')->where('id', $couponId)->first();

    // Check if the coupon exists
    if (!$coupon) {
        return response()->json(['error' => 'Coupon not found'], 404);
    }

    // Update the coupon settings based on the request input
    DB::table('promocodes')
        ->where('id', $couponId)
        ->update([
            'usages_left' => $request->input('usages_left', 1),
            'multi_use' => $request->input('multi_use', true),
        ]);

    // Fetch the updated coupon
    $updatedCoupon = DB::table('promocodes')->where('id', $couponId)->first();

    return response()->json(['coupon' => $updatedCoupon], 200);
}


    
    public function applyPromoToAuser(Request $request)
    {



        $this->validate($request, [
            'coupon_code' => 'required',
            'user_id' => 'required'
        ]);


     



        try {
   

            $coupon = Promocodes::code($request->coupon_code)
            ->user(User::find($request->user_id)) // default: null
            ->apply();


            $daysValue = $coupon->details['days'];
            $nameValue = $coupon->details['name'];

            $data = [
                'status' => 200,
                'message' => 'the coupon has been successfully Applied.',
                'duration' => $coupon->details,
            ];



            DB::table('users')
            ->where('id', $request->user_id)
            ->update(

                array( 
                    "premuim" => true,
                    "pack_name" => $nameValue,
                    "type" => 'coupon',
                    "expired_in" => Carbon::now()->addDays($daysValue)));


        } catch (\Zorb\Promocodes\Exceptions\PromocodeNoUsagesLeftException $e) {
            $errorMessage = $e->getMessage();
            // You can now use $errorMessage to access the message
            // For example, you can log it or return it as a response.


            $data = [
                'status' => 400,
                'message' => $errorMessage,
            ];
        }catch (\Zorb\Promocodes\Exceptions\PromocodeAlreadyUsedByUserException $e) {

            $errorMessage = "the coupon has already been used by this account !";
            // You can now use $errorMessage to access the message
            // For example, you can log it or return it as a response.


            $data = [
                'status' => 400,
                'message' => $errorMessage,
            ];
        }
    
    
        return response()->json($data, $data['status']);
    }


    public function data()
    {
        $coupon = DB::table(function ($query) use ($q) {
            $query->from('promocodes');
        })->orderByDesc('created_at')
        ->get();

        return response()->json($coupon, 201);
    }


    public function destroy($id)
    {
       
        $table = 'promocodes'; // Replace 'your_table_name' with the actual table name

        $deletedRows = DB::table($table)->where('id', '=', $id)->delete();
    
        if ($deletedRows > 0) {
            $data = [
                'status' => 200,
                'message' => 'Successfully deleted.',
            ];
        } else {
            $data = [
                'status' => 400,
                'message' => 'Record not found or could not be deleted.',
            ];
        }
    
        return response()->json($data, $data['status']);
    }
    
}
