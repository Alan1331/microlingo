<?php

namespace App\Http\Middleware;

use Closure;
use Kreait\Firebase\Auth as FirebaseAuth;
use Kreait\Firebase\Exception\AuthException;
use Illuminate\Http\Request;
use Google_Client;

class CheckFirebaseRole
{
    protected $auth;
    protected $googleClient;

    public function __construct(FirebaseAuth $auth)
    {
        $this->auth = $auth;
        $this->googleClient = new Google_Client();
        $this->googleClient->setClientId(config('services.google.client_id'));
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  $role
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        try {
            // Retrieve the Firebase ID token from the session
            $idToken = $request->session()->get('firebase_id_token');
            if (!$idToken) {
                return redirect('/loginAdmin');;
            }

            // Verify the ID token using Firebase Auth
            $verifiedIdToken = $this->auth->verifyIdToken($idToken);

            $claims = $verifiedIdToken->claims();
            $uid = $claims->get('sub'); // Extract UID from the verified ID token

            $admin = $this->auth->getUser($uid);

            // Add admin data to request
            $request->attributes->set('admin', $admin);

            // Add the $admin variable to the request, making it accessible in all routes
            view()->share('admin', $admin);

            return $next($request);
        } catch (AuthException $e) {
            return redirect('/loginAdmin');
        } catch (\Exception $e) {
            return redirect('/loginAdmin');
        }
    }
}
