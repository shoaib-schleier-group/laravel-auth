<?php

namespace App\Http\Controllers\Auth;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\OtpNumbers;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use PHPUnit\TextUI\Help;

class ApiAuthController extends Controller
{
    //


    public function register (Request $request) {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
            'contact' => 'required'
        ]);

        if ($validator->fails())
        {
            return self::failure($validator->errors()->first());
        }

        $data = $request->all();

        // $names = Helper::split_name($data['name']);
        $data['first_name'] = $data['first_name'];
        $data['last_name'] = $data['last_name'];
        $data['password'] = bcrypt($data['password']);
        $data['remember_token'] = Str::random(10);

        $user = new User($data);
        $user->save();
        $user_id = $user->id;

        // find the phone number in otp_numbers and append user_id there
        OtpNumbers::updateOrCreate(
            ['phone_number' => $data['contact'] ],
            ['verified' => 1, 'user_id' => $user_id]
        );



        $token = $user->createToken('Laravel Password Grant Client')->accessToken;
        $response = ['token' => [
            'access_token' => $token
        ]];
        return self::success("Login Successful", $response);
    }

    public function createsuperlogin (Request $request) {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
        ]);
        if ($validator->fails()){
            return self::failure($validator->errors()->first());
        }

        $names = Helper::split_name($request['name']);
        $request['first_name'] = $names['first_name'];
        $request['last_name'] = $names['last_name'];
        $request['password']=Hash::make($request['password']);
        $request['remember_token'] = Str::random(10);
        $user = User::create($request->toArray());
        $token = $user->createToken('Laravel Password Grant Client')->accessToken;
        $response = ['token' => [
            'access_token' => $token
        ]];

        $user = User::where('email', $request['email'])->get();
        $user->assignRole('SuperAdmin');



        return self::success("Login Successful", $response);
    }

    public function login (Request $request) {

        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email|max:255',
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()){
            return self::failure($validator->errors()->first()); //response(['errors'=>$validator->errors()->all()], 422);
        }
        $user = User::where('email', $request->email)->first();
        if ($user) {
            if (Hash::check($request->password, $user->password)) {
                $token = $user->createToken('Laravel Password Grant Client')->accessToken;
                $response = ['token' => $token];
                return self::success("Login Successful", $response);
            } else {
                return self::failure("Password mismatch");
            }
        } else {
            return self::failure('User does not exist');
        }
    }

    public function logout (Request $request) {

        $user = \Auth::user()->token();
        $user->revoke();

//        if (\Auth::check()) {
//            \Auth::user()->AauthAcessToken()->delete();
//        }


        return self::success('You have been successfully logged out!');
    }

    public function forgot_password(Request $request)
    {
        $input = $request->all();
        $rules = array(
            'email' => "required|email",
        );
        $validator = Validator::make($input, $rules);
        if ($validator->fails()) {
            return self::failure($validator->errors()->first(), [], 400);
        } else {
            try {
                $response = Password::sendResetLink($request->only('email'), function (Message $message) {
                    $message->subject($this->getEmailSubject());
                });
                switch ($response) {
                    case Password::RESET_LINK_SENT:
                        return self::success("Reset Password Email Sent, Please check your email");
                    case Password::INVALID_USER:
                        return self::failure("Email doesn't exist in system");
                }
            } catch (\Swift_TransportException $ex) {
                return self::failure($ex->getMessage());
            } catch (\Exception $ex) {
                return self::failure($ex->getMessage());
            }
        }



    }

    public function sociallogin(Request $request){


        $validator = Validator::make($request->all(), [
            'display_name' => 'required|string|max:255',
            'email' => 'required|string|email',
            'social' => 'required',
        ]);

        if ($validator->fails()) {
            return self::failure($validator->errors()->first());
        }

        $data = $request->all();

        $user = User::where('email', $data['email'])->first();
        $otpVerified = false;
        if($user) {

            $otp = OtpNumbers::where('user_id', $user->id)->first();
            if($otp){
                $otpVerified = $otp->is_verified == 1;
            }

            $token = $user->createToken('Laravel Password Grant Client')->accessToken;

            $response = [
                'token' => $token,
                'otp_verified' => $otpVerified
            ];

            return self::success('Login Successful', $response);

        }else{

            $names = Helper::split_name($data['display_name']);
            $data['first_name'] = $names['first_name'];
            $data['last_name'] = $names['last_name'];

            // find the phone number in otp_numbers and append user_id there
            $data['password'] = bcrypt('SOCIAL123@');
            $data['remember_token'] = Str::random(10);
            $user = new User($data);
            $user->save();
            $user_id = $user->id;


            OtpNumbers::updateOrCreate(
                ['email' => $data['email'] ],
                ['verified' => 0, 'user_id' => $user_id]
            );

            $token = $user->createToken('Laravel Password Grant Client')->accessToken;
            $response = [
                'token' => $token,
                'otp_verified' => $otpVerified
            ];

            return self::success("Login Successful", $response);


        }

    }

}
