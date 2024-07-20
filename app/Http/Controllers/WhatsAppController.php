<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Twilio\Rest\Client;
use App\Http\Controllers\UserController;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\App;

class WhatsAppController extends Controller
{
    protected $twilioClient;
    protected $twilioWhatsAppNumber;

    public function __construct(Client $twilioClient)
    {
        $this->twilioClient = $twilioClient;
        $this->twilioWhatsAppNumber = env('TWILIO_WHATSAPP_NUMBER');
    }

    public function sendMessage()
    {
        $sid = env('TWILIO_ACCOUNT_SID');
        $token = env('TWILIO_AUTH_TOKEN');
        $twilio_whatsapp_number = env('TWILIO_WHATSAPP_NUMBER');
        $recipient_number = env('RECIPIENT_WHATSAPP_NUMBER'); // Change to the recipient's number

        $client = new Client($sid, $token);

        $message = $client->messages->create(
            $recipient_number,
            [
                'from' => $twilio_whatsapp_number,
                'body' => "Here's an audio file for you!"
            ]
        );

        return response()->json(['message' => 'Audio message sent!', 'sid' => $message->sid, 'status' => $message->status]);
    }

    public function receiveMessage(Request $request)
    {
        $recipient_number = $request->input('From'); // Change to the recipient's number
        $input_message = $request->input('Body'); // Message from user
        $message = "Feature is still in development";

        # Ensure that the user was registered
        $lokasiMenu = $this->checkUser($recipient_number);

        $message = $this->handleMenuLocation($lokasiMenu, $input_message, $recipient_number);

        $this->sendMessageToUser($recipient_number, $message);
    }

    private function sendMessageToUser($recipient_number, $message)
    {
        $this->twilioClient->messages->create(
            $recipient_number,
            [
                'from' => $this->twilioWhatsAppNumber,
                'body' => $message
            ]
        );
    }

    private function handleMenuLocation($lokasiMenu, $input_message, $recipient_number)
    {
        switch ($lokasiMenu) {
            case "userProfile":
                return $this->handleUserProfileMenu($input_message, $recipient_number);
            case "userProfileSetName":
                return $this->handleUserProfileSetName($input_message, $recipient_number);
            case "userProfileSetJob":
                return $this->handleUserProfileSetJob($input_message, $recipient_number);
            default:
                return $this->handleMainMenu($input_message, $recipient_number);
        }
    }

    private function handleMainMenu($input_message, $recipient_number)
    {
        switch ($input_message) {
            case "1":
                return $this->comingSoon();
            case "2":
                return $this->showProfileMenu($recipient_number);
            case "3":
                return $this->showAboutUs();
            default:
                return $this->showMainMenu($recipient_number);
        }
    }

    private function handleUserProfileMenu($input_message, $recipient_number)
    {
        switch ($input_message) {
            case "1":
                return $this->setProfile($recipient_number);
            case "2":
                return $this->backToMainMenu($recipient_number);
            default:
                return $this->showProfileMenu($recipient_number);
        }
    }

    private function setProfile($recipient_number)
    {
        $user_number = $this->formatUserPhoneNumber($recipient_number);
        # change menu location to userProfileSetName to ask user their name
        $data = [
            'lokasiMenu' => 'userProfileSetName'
        ];
        $request = Request::create('/users/$recipient_number', 'PUT', $data);
        App::call('App\Http\Controllers\UserController@updateUser', ['request' => $request, 'noWhatsapp' => $user_number]);
        $message = "Masukkan nama Anda:";
        return $message;
    }

    private function handleUserProfileSetName($input_message, $recipient_number)
    {
        $user_number = $this->formatUserPhoneNumber($recipient_number);
        # set user name and change menu location to userProfileSetJob to ask user their job
        $data = [
            'nama' => $input_message,
            'lokasiMenu' => 'userProfileSetJob'
        ];
        $request = Request::create('/users/$recipient_number', 'PUT', $data);
        App::call('App\Http\Controllers\UserController@updateUser', ['request' => $request, 'noWhatsapp' => $user_number]);
        $message = "Hallo, $input_message!! Apa pekerjaan Anda:";
        return $message;
    }

    private function handleUserProfileSetJob($input_message, $recipient_number)
    {
        $user_number = $this->formatUserPhoneNumber($recipient_number);
        # set user job and change menu location back to userProfile
        $data = [
            'pekerjaan' => $input_message,
            'lokasiMenu' => 'userProfile'
        ];
        $request = Request::create('/users/$recipient_number', 'PUT', $data);
        App::call('App\Http\Controllers\UserController@updateUser', ['request' => $request, 'noWhatsapp' => $user_number]);
        $message = "Profil Anda berhasil diatur, ketik dan kirim apapun untuk melihat profil Anda!";
        return $message;
    }

    private function comingSoon()
    {
        $message = "Feature is still in development";
        return $message;
    }

    private function showAboutUs()
    {
        $message = env('ABOUT_US_PROMPT');
        return $message;
    }

    private function backToMainMenu($recipient_number)
    {
        $user_number = $this->formatUserPhoneNumber($recipient_number);
        # change menu location to mainMenu
        $data = [
            'lokasiMenu' => 'mainMenu'
        ];
        $request = Request::create('/users/$recipient_number', 'PUT', $data);
        App::call('App\Http\Controllers\UserController@updateUser', ['request' => $request, 'noWhatsapp' => $user_number]);   
        return $this->showMainMenu($recipient_number);
    }

    private function showMainMenu($recipient_number)
    {
        $message = env('MAIN_MENU_PROMPT');
        return $message;
    }

    private function showProfileMenu($recipient_number)
    {
        $user_number = $this->formatUserPhoneNumber($recipient_number);

        # change menu location to userProfile
        $data = [
            'lokasiMenu' => 'userProfile'
        ];
        $request = Request::create('/users/$recipient_number', 'PUT', $data);
        App::call('App\Http\Controllers\UserController@updateUser', ['request' => $request, 'noWhatsapp' => $user_number]);

        # get user data
        $user_name = "belum diatur";
        $user_job = "belum diatur";
        $user_query = App::call('App\Http\Controllers\UserController@showUserById', ['noWhatsapp' => $user_number]);
        if ($user_query->getStatusCode() == 200) {
            // Decode the JSON user_query to get required data
            $user_data = $user_query->getData();
            if (isset($user_data->nama)) {
                $user_name = $user_data->nama;
            }
            if (isset($user_data->pekerjaan)) {
                $user_job = $user_data->pekerjaan;
            }
        }

        $message = "Berikut merupakan profil Anda saat ini:
        - Nama: $user_name
        - Pekerjaan: $user_job
Pilih menu berikut untuk melanjutkan:
        1. Ubah profil
        2. Kembali ke Main Menu";
        return $message;
    }


    private function checkUser($recipient_number)
    {
        $user_number = $this->formatUserPhoneNumber($recipient_number);

        $check_user = App::call('App\Http\Controllers\UserController@showUserById', ['noWhatsapp' => $user_number]);
        $statusCode = $check_user->getStatusCode();
        $lokasiMenu = 'notSet';

        if ($statusCode == 200) {
            // Decode the JSON response to get the lokasiMenu
            $user_data = $check_user->getData();
            if (isset($user_data->lokasiMenu)) {
                $lokasiMenu = $user_data->lokasiMenu;
            }
        }

        // Register new user if does not exist
        if ($statusCode == 404) {
            $data = [
                'noWhatsapp' => $user_number
            ];
            $request = Request::create('/users', 'POST', $data);
            App::call('App\Http\Controllers\UserController@createUser', ['request' => $request]);
            $lokasiMenu = 'mainMenu';
        }

        return $lokasiMenu;
    }

    private function formatUserPhoneNumber($recipient_number)
    {
        // only include the phone number without 'whatsapp:' text behind it
        return explode(":", $recipient_number)[1];
    }
}
