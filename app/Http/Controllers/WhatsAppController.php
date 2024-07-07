<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Twilio\Rest\Client;
use App\Http\Controllers\UserController;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\App;

class WhatsAppController extends Controller
{
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

        switch($lokasiMenu) {
            case "userProfile": # show userProfile menu
                switch($input_message) {
                    case "1": # select 1 to edit profile
                        $message = $this->comingSoon($recipient_number);
                        break;
                    case "2": # select 2 to back to main menu
                        $message = $this->backToMainMenu($recipient_number);
                        break;
                    default:
                        $message = $this->showProfileMenu($recipient_number);
                }
                break;
            default: # show mainMenu by default
                switch($input_message) {
                    case "1": # menu to start/continue learning
                        $message = $this->comingSoon($recipient_number);
                        break;
                    case "2": # menu to show user profile
                        $message = $this->showProfileMenu($recipient_number);
                        break;
                    case "3": # menu to show about MicroLingo
                        $message = $this->comingSoon($recipient_number);
                        break;
                    default:
                        $message = $this->showMainMenu($recipient_number);
                }
        }

        return response()->json(['message' => 'Message was replied!', 'sid' => $message->sid, 'status' => $message->status]);
    }

    private function comingSoon($recipient_number)
    {
        $response = "Feature is still in development";
        $message = $this->replyUser($recipient_number, $response);
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
        $response = "Main Menu
        1. Mulai/Lanjut Pembelajaran
        2. Profil Anda
        3. Tentang MicroLingo";
        $message = $this->replyUser($recipient_number, $response);
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
            if (isset($user_data->user_name)) {
                $user_name = $user_data->user_name;
            }
            if (isset($user_data->user_job)) {
                $user_job = $user_data->user_job;
            }
        }

        $response = "Berikut merupakan profil Anda saat ini:
        - Nama: $user_name
        - Pekerjaan: $user_job
Pilih menu berikut untuk melanjutkan:
        1. Ubah profil
        2. Kembali ke Main Menu";
        $message = $this->replyUser($recipient_number, $response);
        return $message;
    }

    private function replyUser($recipient_number, $response)
    {
        $sid = env('TWILIO_ACCOUNT_SID');
        $token = env('TWILIO_AUTH_TOKEN');
        $twilio_whatsapp_number = env('TWILIO_WHATSAPP_NUMBER');

        $client = new Client($sid, $token);

        $message = $client->messages->create(
            $recipient_number,
            [
                'from' => $twilio_whatsapp_number,
                'body' => $response
            ]
        );

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
