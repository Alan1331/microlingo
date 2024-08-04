<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Kreait\Firebase\Auth as FirebaseAuth;
use GuzzleHttp\Client;
use Google_Client;
use Google_Service_Oauth2;
use App\Models\Admin;

class FirebaseLoginController extends Controller
{
    protected $auth;
    protected $admin;

    public function __construct(FirebaseAuth $auth, Admin $admin)
    {
        $this->auth = $auth;
        $this->admin = $admin;
    }

    public function redirectToGoogle()
    {
        $client = new Google_Client();
        $client->setClientId(config('services.google.client_id'));
        $client->setClientSecret(config('services.google.client_secret'));
        $client->setRedirectUri(route('login.google.callback'));
        $client->addScope('email');
        $client->addScope('profile');

        return redirect($client->createAuthUrl());
    }

    public function handleGoogleCallback(Request $request)
    {
        $client = new Google_Client();
        $client->setClientId(config('services.google.client_id'));
        $client->setClientSecret(config('services.google.client_secret'));
        $client->setRedirectUri(route('login.google.callback'));

        $token = $client->fetchAccessTokenWithAuthCode($request->code);
        $client->setAccessToken($token['access_token']);

        $oauth = new Google_Service_Oauth2($client);
        $googleUser = $oauth->userinfo->get();
        
        // prevent user login for non-admin
        $adminDocument = $this->admin->find($googleUser->email);
        if (!$adminDocument) {
            return view('admin.layouts.unauthorizedAccess');
        }
        Log::info("User picture: " . $googleUser->picture);

        $firebaseUser = $this->auth->signInWithGoogleIdToken($token['id_token']);

        // Store ID token in session
        $request->session()->put('firebase_id_token', $firebaseUser->idToken());

        return redirect('/admin-page');
    }

    public function logout(Request $request)
    {
        // Get the Firebase ID token from the session
        $idToken = $request->session()->get('firebase_id_token');

        // Clear the session
        $request->session()->forget('firebase_id_token');

        // revoke the token
        if ($idToken) {
            try {
                $this->auth->revokeRefreshTokens($idToken);
            } catch (\Exception $e) {
                // Handle the exception as needed
            }
        }

        // Redirect to login page or any other page
        return redirect('/loginAdmin');
    }
}