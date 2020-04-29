<?php

namespace Component\LaravelPassport\Http\Controllers\Api\Auth;
 
use App\Http\Controllers\Controller;
use Laravel\Passport\ClientRepository;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;
use Component\LaravelPassport\Models\User;
use Validator;


class AuthController extends Controller
{ 
    protected $defaultSetting; 
    protected $clients;
    protected $defaultUserData = [
        'username', 'lastname', 'firsname', 'email', 'password', 'displayname'
    ];

    function __construct(ClientRepository $clients)
    {
        $this->clients = $clients;  

        $default = config('laravel-passport.default_auth', [
            /*'username', 'password', 'email', */'mobile' 
        ]);

        $this->defaultSetting = option('laravel-passport-auth', $default); 
    } 

    /**
     * Handle a registration request for the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function reset(Request $request)
    {     
        if($user = $this->attemptLogin($request, false)) { 
            return $this->verifyResponse($user, true); 
        }   
        
        return response(
            ['error' => armin_trans('armin::auth.invalid_user')], 404
        );  
    }

    /**
     * Handle a registration request for the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function login(Request $request)
    {   
        $validator = $this->validator($request);

        if($validator->fails()) {
            return $this->failedCredentionals($validator->errors());
        } 

        if($user = $this->retriveByCredentional($request->mobile ?? $request->username)) { 

            $user->verification()->updateOrCreate(['credentials' => $user->mobile], [
                'token' => bcrypt($code = rand(99999, 999999)) 
            ]);

            \Qasedak::message(
                __("Your verification code is :code", compact('code')), $user->mobile
            );


            return response()->json([
                'verify_path' => route('user-api.verify', $user),
                'result' => 1,
            ]);

            return $this->makeTokenResponse($user); 
        }   
        
        return response(
            ['error' => __('Failed Login: Invalid User')], 404
        );  
    }

    public function attemptLogin(Request $request, $password = true)
    {
        $credentials = $this->credentials($request); 
        $user = $this->retriveByCredentional(
            array_except($credentials, 'password')
        ); 

        if(! $this->needPassword($user)) {
            return $user;
        } 
        $hashEqual = $this->hashEqual(
            optional($user)->password, array_get($credentials, 'password')
        ); 

        return ($hashEqual || false === $password) ? $user : null; 
    } 

    /**
     * Handle a registration request for the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function register(Request $request)
    {  
        $validator = $this->validator($request, true);

        if($validator->fails()) {
            return $this->failedCredentionals($validator->errors());
        } 

        $user = $this->create($this->fetchDataFromRequest($request));

        event(new \Illuminate\Auth\Events\Registered($user));

        return $this->makeTokenResponse($user);  
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return User
     */
    protected function create(array $data)
    {       
        $user = User::updateOrCreate(
            ['username'  => $data['username']], array_except($data, 'metas')
        );
        
        collect($data['metas'])->each(function($value , $key) use ($user) {
            $user->setMeta($key, $value);
        });

        $user->save(); 

        return $user;
    } 

     /**
     * Handle a registration request for the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function verify(Request $request, $id)
    {
        $user = User::with('verification')->findOrFail($id);  

        $verified = $this->hashEqual(
            optional($user->verification)->token, $request->input('verify_code')
        ); 

        if(! $verified) {  
            return $this->failedVerificationResponse();  
        }

        $user->verification()->update(['verified' => true]);  

        return $this->makeTokenResponse($user);  
    }


    /**
     * Get the needed authorization credentials from the request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    protected function credentials(Request $request)
    {
        $credentials[$this->username()] = $request->get($this->username()); 

        if(1 || $this->needPassword()) {  
            $credentials['password'] = $request->get('password');
        }

        return $credentials;
    }

    /**
     * Check if user login need password for login.
     * 
     * @return boolean
     */
    public function needPassword($user = null)
    {
        return false;
    }

    protected function fetchDataFromRequest($request)
    {
        $username = $request->get('username') ?? $request->get('mobile') ?? time();

        $data = [ 
            'username'  => $username,
            'password'  => bcrypt($request->get('password') ?? $username),
            'email'     => $request->get('email') ?? time() .'example@mail.com',
            'firstname' => $request->get('firstname'), 
            'lastname'  => $request->get('lastname'), 
            'displayname'  => $request->get('displayname')
        ]; 

        $data['metas'] = [
            'mobile' => $request->mobile ?? $request->username
        ]; 

        return $data; 
    }

    protected function failedCredentionals($errors)
    { 
        $errors = array_flatten($errors->toArray());

        return response()->json(compact('errors') + ['result' => 0], 203); 
    } 

    protected function username()
    {
        return 'mobile';
    }

    protected function retriveByCredentional($mobile)
    {
        
        $user = $this->createModel()->whereMeta('mobile', $mobile)->first();

        if(is_null($user)) {
            $user = $this->createModel()->firstOrCreate([
                'username' => request('username', $mobile),
                'firstname' => request('firstname'),
                'lastname' => request('lastname'),
                'displayname' => request('displayname'), 
                'email' => request('email', $mobile.'@example.com'), 
            ], ['password' => bcrypt($mobile)]);

            $user->setMeta('mobile', $mobile);
            $user->save();
        }

        return $user;
    }

    public function createModel()
    {
        return \Auth::guard('user')->getProvider()->createModel();
    }

    protected function hashEqual($hash, $password)
    {
        return app('hash')->driver('bcrypt')->check($password, $hash);
    } 
   
    protected function failedVerificationResponse()
    {
        return response()->json([
            'result'    => 0,
            'message'   => 'Verification Failed.'
        ]);
    }

    protected function makeTokenResponse($user)
    {  
        if($this->needVerification($user)) {
            return $this->verifyResponse($user);
        }

        if($client = $this->clients->forUser($user->id)->first()) {
            $client = $this->clients->regenerateSecret($client); 
        } else {
            $client =  $this->clients->createPasswordGrantClient(
                $user->id, $user->username,'http://localhost'
            ); 
        }  

        return response()->json([
            'token_path'=> url('api/user/oauth/token'),
            'grant_type'=> 'password', 
            'client_id' => $client->id, 
            'client_secret' => $client->secret, 
            'scope'     => '*',
            'user'      => [
                'id' => $user->id,
                'firstname' => $user->firstname,
                'lastname'  => $user->lastname, 
                'avatar'    => empty($user->avatar) ? null :
                                        \Storage::disk('armin.image')->url($user->avatar), 
            ],
            'result'    => 1
        ]);
    }


    protected function needVerification($user = null)
    {
        if($this->isVerified($user)) { 
            return false;
        } 

        return !$this->needPassword(); 
    }

    public function isVerified($user)
    {
        $user->load('verification'); 

        return (bool) optional($user->verification)->verified; 
    }
    protected function verifyResponse($user, $resetPassword = false)
    {  
        $password = bcrypt($this->makeVerification($user)); 

        if(true === $resetPassword) {
            $user->update(compact('password')); 
        }

        return response()->json([
            'verify_path' => route('user-api.verify', $user),
            'result' => 1,
        ]);  
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(Request $request, $register = false)
    { 
        return \Validator::make($this->credentials($request), $this->rules($register)); 
    } 

    public function rules($register = false)
    { 
        $rules[$this->username()] = 'required'. ($register? "|unique:users" : '');

        if($this->needPassword()) {
            $rules['password'] = 'required';
        }

        return $rules;
    }
    

    protected function makeVerification($user)
    {
        return tap($this->verifyCode(), function($verifyCode) use ($user) {
            $user->verification()->firstOrCreate([
                'credentials' => $user->mobile
            ])->update([
                'token' =>  bcrypt($verifyCode)
            ]);  

            $this->sendVerificationCode($verifyCode, $user);
        });  
    }

    protected function verifyCode()
    {
        return rand(99999, 999999);
    }

    protected function sendVerificationCode($code, $user)
    {   
        $setting = option('_component_passport_setting');

        if($verifier = array_get($setting, 'verifier')) {
            \Config::set('user.verifier.default', $verifier);
        }

        $message = array_get($setting, 'verify_message', 'Your Verification Code Is: #');
        $appKey  = array_get($setting, 'app_key');

        if(false !== strpos($message, '#')) {
            $message = str_replace('#', $code, $message);
        } else {
            $message .= " {$code}";
        }

        if(! empty($appKey)) {
            $message = "<#> {$message} \r\n {$appKey}";
        }
         
        return app('verifier.sender')->send($message, $user);
    } 
}
