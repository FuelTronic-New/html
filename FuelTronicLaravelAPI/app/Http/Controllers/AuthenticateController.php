<?php namespace App\Http\Controllers;
use JWTAuth;
use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Http\Request;
use Session;
use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Support\Arr;
use Illuminate\Auth\Passwords\TokenRepositoryInterface;
use Illuminate\Support\Facades\Password;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use App\Repositories\Frontend\User\UserContract;
use Illuminate\Contracts\Auth\PasswordBroker;
use Illuminate\Foundation\Auth\ResetsPasswords;

class AuthenticateController extends Controller
{
    use ResetsPasswords;
    
    public function __construct(Request $request, TokenRepositoryInterface $tokens, PasswordBroker $passwords) {
        $this->request = $request;
        $this->tokens = $tokens;
        $this->passwords = $passwords;
    }

    public function authenticateUser(Request $request)
    {
		
        // grab credentials from the request
        $credentials = $request->only('email', 'password');

        try {
            // attempt to verify the credentials and create a token for the user
            if (! $token = JWTAuth::attempt($credentials)) {
                return response()->json(['error' => 'Invalid Credentials'], 401);
            }
        } catch (JWTException $e) {
            // something went wrong whilst attempting to encode the token
            return response()->json(['error' => 'Login Failed, try again later'], 500);
        }

        $user = JWTAuth::toUser($token);

        //if(!in_array($user->IsActive, array(100, 200))){
        //    return response()->json(['error' => 'Inactive Account'], 401);
        //}

        // all good so return the token
        return response()->json(compact('token','user'));
    }

    public function register() {

        $validator = \Validator::make($this->request->all(), [
            'name'    => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json(['validation_errors'=>$validator->errors()], 400);
        }

        $userData = [
            'name' => $this->request->get("name"),
            "email" => $this->request->get("email"),
            'password' => \Hash::make($this->request->get("password")),
        ];

        $user = User::create($userData);
        return response()->json($user);
    }

    public function postEmail(Request $request)
    {
        //Make sure user is confirmed before resetting password.
        $user = User::where('email', $request->get('email'))->first();
        if (!$user) {
            return response()->json(["message" => "There is no user with that e-mail address."], 404);
        }

        $this->validate($request, ['email' => 'required|email']);
        $response = $this->sendResetLink($request->only('email'), function (Message $message) {
            $message->subject($this->getEmailSubject());
        });

        switch ($response) {
            case 'passwords.sent':
                return response()->json(["message" => "Done"], 200);
            case 'passwords.user':
                return response()->json(["message" => "Error"], 500);
        }
    }

    public function sendResetLink(array $credentials, \Closure $callback = null)
    {
        $user = $this->getUser($credentials);
        if (is_null($user)) {
            return 'passwords.user';;
        }
        $token = $this->tokens->create($user);
        $this->emailResetLink($user, $token, $callback);
        return 'passwords.sent';
    }

    public function emailResetLink($user, $token, \Closure $callback = null)
    {
        $link = env('APP_FRONTEND_URL').'/#/access/reset/password?token='.$token;
//        $link = url('password/reset/' . $token);// for laravel app
        \Mail::send('emails.password', ['link' => $link], function ($m) use ($user) {
            $m->to($user->email, $user->FirstName)->subject('Reset Password');
        });
        return true;
    }

    public function getUser(array $credentials)
    {
        $credentials = Arr::except($credentials, ['token']);
        return User::where($credentials)->first();
    }

    public function postReset()
    {
        $validator = \Validator::make($this->request->all(), [
            'token'    => 'required',
            'email'    => 'required',
            'password' => 'required|confirmed|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json(['validation_errors'=> $validator->errors()], 400);
        }

        $user = User::where('email',$this->request->get('email'))->first();
        if(!isset($user->email)) {
            return response()->json(['error'], 400);
        }
        $credentials = $this->request->only('password', 'password_confirmation', 'token');
        $credentials['email'] = $user->email;
        $response = Password::reset($credentials, function ($user, $password) {
            $this->resetPasswordCustom($user, $password);
        });
        switch ($response) {
            case 'passwords.reset':
                return response()->json(["message" => "Done"], 200);
            default:
                return response()->json(["message" => "Error"], 400);
        }
    }

    private function resetPasswordCustom($user, $password){
        $userObj['password'] = \Hash::make($password);
        User::where('ID', $user->id)->update($userObj);
        return 'passwords.reset';
    }

    public function logOut()
    {
        if(auth()->check()){
            auth()->logout();
        }
        Session::flush();
        return response()->json(["status" => 'true', "message" => "Logged out successfully"], 200);
    }

}