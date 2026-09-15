<?php

namespace App\Http\Controllers;

use Illuminate\Validation\Rule;
use App\Http\Requests\AvatarRequest;
use App\Http\Requests\PasswordUpdateRequest;
use App\Http\Requests\PasswordAppRequest;
use App\Http\Requests\DeviceStoreRequest;
use App\Http\Requests\UserRequest;
use Illuminate\Http\Request;
use App\Http\Requests\UserRequestStore;
use App\Http\Requests\UserUpdateRequest;
use App\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Laravel\Passport\Client;
use App\Setting;
use App\Profile;
use App\Device;
use Illuminate\Support\Carbon;
use Sentinel;

class UserController extends Controller
{


    public function __construct()
    {
        $this->middleware('doNotCacheResponse', ['only' => ['checkExpirationDate']]);
    }


    const MESSAGE = "successfully updated";



    public function showUser($id)
    {

        $user = User::query()
        ->where('id', '=', $id)->select(['id','name'])->get();

        return response()->json($user);


    }

    public function devices (Request $request){


        $auth = Auth()->user();

        $user = Device::query()->where('user_id', '=', $auth->id)->get();


         return response()->json(['devices' => $user], 200);
     }

    public function createDevice (Request $request){


            $user = Auth()->user();


            $dev = Device::where('serial_number', $request->serial_number)->
            where('user_id', $user->id)->first();


            if (!$dev) {
            
            $this->validate($request, [
            'serial_number' => [
            'required',
            ],
            ]);


   
        $device = new Device();
        $device->name = $request->name;
        $device->model = $request->model;
        $device->serial_number = $request->serial_number;
        $device->user_id = $user->id;
        $user->devices()->save($device);

                
            }

        
            
        if ($device != null) {
        
            $data = ['status' => 200, 'message' => 'Device successfully Attached',];
        } else {
            $data = ['status' => 400, 'message' => 'Device Already Attached',];
        }

            return response()->json($data, 200);
       
     }



     
     public function deleteDevice ($id,Request $request){


        $user = Auth()->user();

        $deleteProfile = Device::find($id)->delete();


        if ($deleteProfile != null) {
        
            $data = ['status' => 200, 'message' => 'successfully removed',];
        } else {
            $data = ['status' => 400, 'message' => 'could not be deleted',];
        }

         return response()->json($data, 200);
     }


    public function createProfile (Request $request){

        $this->validate($request, [
            'name' => 'required']);


            if($request->avatar == null) {

                $user = Auth()->user();
                $profile = new Profile();
                $profile->name = $request->name;
                $profile->avatar = $request->root() . '/api/avatars/image/avatar_default.png';
                $user->profiles()->save($profile);

            }else {


                $user = Auth()->user();
                $profile = new Profile();
                $profile->name = $request->name;
                $profile->avatar = $request->avatar;
                $user->profiles()->save($profile);
            }



            
        if ($profile != null) {
        
            $data = ['status' => 200, 'message' => 'successfully Added',];
        } else {
            $data = ['status' => 400, 'message' => 'could not be deleted',];
        }

            return response()->json($data, 200);
       
     }
   


     public function showDefaultAvatar($filename)
    {
   
       $image = Storage::disk('avatars')->get($filename);
   
       $mime = Storage::disk('avatars')->mimeType($filename);
   
       return (new Response($image, 200))->header('Content-Type', $mime);
    }

     public function deleteProfile (Request $request){


        $user = Auth()->user();


        $deleteProfile = Profile::find($request->profile_id)->delete();


        if ($deleteProfile != null) {
        
            $data = ['status' => 200, 'message' => 'successfully removed',];
        } else {
            $data = ['status' => 400, 'message' => 'could not be deleted',];
        }

         return response()->json($data, 200);
     }
   


    public function QrAutoGenerate(Request $request)
	{	
		$result=0;
		if ($request->action = 'updateqr') {
			$user = Sentinel::getUser();
			if ($user) {
				$qrLogin=bcrypt($user->personal_number.$user->email.str_random(40));
		        $user->QRpassword= $qrLogin;
		        $user->update();
		        $result=1;
			}
		
		}
		
        return $result;
	}


    public function checkExpirationDate (Request $request){

        $user = Auth()->user();
        
        $s =  date('Y-m-d', strtotime($user->expired_in));

        if(Carbon::now()->startOfDay()->gte($s)){


         $data = ['status' => 200, 'subscription' => "expired"];

        }else{

           $data = ['status' => 200, 'subscription' => "notexpired"];
        }

        return response()->json($data, 200);
     }




    public function create(Request $request)
    {

        $this->validate($request, [
            'name' => 'required',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6'
        ]);

        $user = User::create([
            'name' => request('name'),
            'email' => request('email'),
            'premuim' => false,
            'password' => bcrypt(request('password'))


        ]);

        return $this->issueToken($request, 'password');

    }





    // returns the authenticated user for admin panel

    public function data()
    {

    
        $user = Auth()->user();
        return response()
        ->json( $user, 200);

    
    }




    public function allusers()
    {

        return response()->json(User::orderByDesc('created_at')
        ->paginate(12), 200);
    }


    // return the logo checking the format
    public function showAvatar()

    {
        if (Storage::disk('public')->exists('users/users.jpg')) {
            $image = Storage::disk('public')->get('users/users.jpg');
            $mime = Storage::disk('public')->mimeType('/users/users.jpg');
            $type = 'jpg';
        } else {
            $image = Storage::disk('public')->get('users/users.png');
            $mime = Storage::disk('public')->mimeType('users/users.png');
            $type = 'png';
        }
        return (new Response($image, 200))
            ->header('Content-Type', $mime)->header('type', $type);
    }


    public function updateAvatar(AvatarRequest $request)
    {
        if ($request->hasFile('image')) {
            Storage::disk('public')->deleteDirectory('users');
            $extension = $request->image->getClientOriginalExtension();
            $filename = Storage::disk('public')->putFileAs('users', $request->image, "users.$extension");
            $data = [
                'status' => 200,
                'image_path' => $request->root() . '/api/image/users?' . time(),
            ];
        } else {
            $data = [
                'status' => 400,
            ];
        }

        return response()->json($data, $data['status']);
    }



      // update user data in the database
      public function phoneUpdate(Request $request
)
      {
        $this->validate($request, [
            'id' => 'required']);
        
       
        $user = Auth::user();


        DB::table('users')
            ->where('id',$request->id)
            ->update(
                array( 
                    "verified" => 1));

      return response()->noContent();

      }


    // update user data in the database
    public function update(UserRequest $request)
    {
        $user = Auth()->user();
        
        if ($request->get('password') == '') {
            $user->update($request->except('password'));
        } else {
            $user->update($request->all());
        }  
        $user->save();

        
        $data = [
            'status' => 200,
            self::MESSAGE,
            'body' => $user
        ];

        return response()->json($data, $data['status']);
    }


    public function isSubscribed(Request $request)
    {

     $user = Auth()->user();

     
        
      return response()->json($data = [
        'active' => $user->subscriptions()->active()->count()]);



    }




    public function unbanUser($id)
    {

        DB::table('users')
            ->where('id', $id)
            ->update(

                array( 
                    "active" => true)

            );


            $response = [
                'status' => 200,
                'message' => 'the user has been unbanned successfully',
            ];
            
        return response()->json($response, 200);

    }

    public function banUser($id)
    {

        DB::table('users')
            ->where('id', $id)
            ->update(

                array( 
                    "active" => false)

            );


            $response = [
                'status' => 200,
                'message' => 'the user has been banned successfully',
            ];
            
        return response()->json($response, 200);

    }


    public function store(Request $request){


        $this->validate($request, [
            'name' => 'required',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6'
        ]);

        $user = User::create([
            'name' => request('name'),
            'email' => request('email'),
            'premuim' => false,
            'password' => bcrypt(request('password'))


        ]);

    }


    public function addUser(Request $request)
    {

        //validation for inputs
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|email'
        ]);
        $return['message'] = 'Please enter valid inputs';
        if ($validator->fails()) {
            return $return;
        }

        //Add user
        $user = new User();
        $user->name = $request->name;
        $user->email = $request->email;
        $user->role = $request->role;
        $user->password = Hash::make(rand(10000, 99999));
        $user->save();
        return $user;
    }


    public function updateUser(UserUpdateRequest $request, User $user)


    {

        if ($user != null) {

            $user->fill($request->user);
            $user->save();

            $data = [
                'status' => 200,
                self::MESSAGE,
            ];
        } else {
            $data = [
                'status' => 400,
                'message' => 'Error',
            ];
        }

        return response()->json($data, $data['status']);
    }


    public function destroy(User $user)
    {
        if ($user != null) {
            $user->delete();

            $data = [
                'status' => 200,
                'message' => 'successfully removed',
            ];
        } else {
            $data = [
                'status' => 400,
                'message' => 'could not be deleted',
            ];
        }

        return response()->json($data, $data['status']);
    }

    // update user password in the database
    public function passwordUpdate(PasswordUpdateRequest $request)
    {
        $user = Auth()->user();
        $user->password = bcrypt($request->password);
        $user->save();
        $data = [
            'status' => 200,
            self::MESSAGE,
        ];

        return response()->json($data, $data['status']);
    }




    public function passwordUpdateApp(PasswordAppRequest $request)
    {

        $settings = Setting::first();
        $settings->password = bcrypt($request->password);
        $settings->save();
        $data = [
            'status' => 200,
            self::MESSAGE,
        ];

        return response()->json($data, $data['status']);
    }


 // update user password in the database
 public function updateUserPassword (PasswordUpdateRequest $request)
 {
     $user = Auth()->user();
     $user->password = bcrypt($request->password);
     $user->save();
     $data = [
         'status' => 200,
         self::MESSAGE,
     ];

     return response()->json($data, $data['status']);
 }




 public function show($filename)
 {

    $image = Storage::disk('avatars')->get($filename);

    $mime = Storage::disk('avatars')->mimeType($filename);

    return (new Response($image, 200))->header('Content-Type', $mime);
 }

    
}
