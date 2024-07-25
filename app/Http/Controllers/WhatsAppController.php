<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Twilio\Rest\Client;
use App\Http\Controllers\UserController;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\App;
use App\Models\LearningUnit;
use App\Models\Level;
use OpenAI;
use Illuminate\Support\Facades\Storage;

class WhatsAppController extends Controller
{
    protected $twilioClient;
    protected $twilioWhatsAppNumber;
    protected $learningUnit;
    protected $openai;

    public function __construct(Client $twilioClient, LearningUnit $learningUnit)
    {
        $this->twilioClient = $twilioClient;
        $this->twilioClient->setLogLevel('debug');
        $this->twilioWhatsAppNumber = env('TWILIO_WHATSAPP_NUMBER');
        $this->learningUnit = $learningUnit;
        $this->openai = OpenAI::client(env('OPENAI_API_KEY'));
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
        // Set the maximum execution time to 300 seconds (5 minutes)
        set_time_limit(300);

        $recipient_number = $request->input('From'); // Change to the recipient's number
        $input_message = $request->input('Body'); // Message from user
        $messages = "Feature is still in development";

        # Ensure that the user was registered
        $lokasiMenu = $this->checkUser($recipient_number);

        $response = $this->handleMenuLocation($lokasiMenu, $input_message, $recipient_number);

        $messages = explode('|', $response);
        
        foreach ($messages as $message) {
            Log::info("Sending message: " . $message);
            if(str_contains($message, '/storage/videos/')) {
                $videos = [$message];
                // send video if contains typical video url
                $this->sendMessageToUser($recipient_number, 'Sending video', $videos);
            } else {
                // send message instead
                $this->sendMessageToUser($recipient_number, $message);
            }
        }
    }

    private function sendMessageToUser($recipient_number, $message, $mediaUrls=null)
    {
        if($mediaUrls != null) {
            Log::info("mediaUrls type: ". gettype($mediaUrls));
            
            $this->twilioClient->messages->create(
                $recipient_number,
                [
                    'mediaUrl' => $mediaUrls,
                    'from' => $this->twilioWhatsAppNumber,
                    'body' => $message,
                ]
            );
        } else {
            $this->twilioClient->messages->create(
                $recipient_number,
                [
                    'from' => $this->twilioWhatsAppNumber,
                    'body' => $message,
                ]
            );
        }
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
            case "learningQuestion":
                return $this->handleUserQuestion($input_message, $recipient_number);
            default:
                return $this->handleMainMenu($input_message, $recipient_number);
        }
    }

    private function handleMainMenu($input_message, $recipient_number)
    {
        switch ($input_message) {
            case "1":
                return $this->showLearningMenu($recipient_number);
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

    private function showLearningMenu($recipient_number)
    {
        $user_number = $this->formatUserPhoneNumber($recipient_number);

        # get user progress
        $learning_unit_id = null;
        $level_id = null;
        $user_query = App::call('App\Http\Controllers\UserController@showUserById', ['noWhatsapp' => $user_number]);
        if ($user_query->getStatusCode() == 200) {
            // Decode the JSON user_query to get required data
            $user_data = $user_query->getData();
            if (isset($user_data->progress)) {
                $user_progress = $user_data->progress;
                $user_progress = explode('-', $user_progress);
                $learning_unit_id = $user_progress[0];
                $level_id = $user_progress[1];
            }
        }

        // instantiate level and unit model
        $learningUnitDocument = $this->learningUnit->find($learning_unit_id);
        $level = new Level($learning_unit_id);
        $levelDocument = $level->find($level_id);

        $prompt = $this->generateLevelPrompt($learningUnitDocument, $levelDocument);
        $message = $prompt;
        $message .= "\nKetik '!exit' dan kirim untuk kembali ke Main Menu!";
        if(is_array($levelDocument['videos'])) {
            $videos = $levelDocument['videos'];
            Log::info("Video url is detected");
            foreach($videos as $video) {
                $videoUrl = env('NGROK_URL') . Storage::url($video);
                Log::info("Retrieved video url: " . $videoUrl);
                $message .= '|' . $videoUrl;
            }
        } else {
            Log::info("Failed to retrive the video");
        }
        
        if(isset($levelDocument['topic']) && isset($levelDocument['content'])) {
            $topic = $levelDocument['topic'];
            $content = $levelDocument['content'];
            $generatedQuestion = $this->generateQuestion($topic, $content);
            $message .= '|' . "Setelah menonton, jawab pertanyaan berikut:" . $generatedQuestion;

            # update user current question
            $data = [
                'currentQuestion' => $generatedQuestion
            ];
            $request = Request::create('/users/$recipient_number', 'PUT', $data);
            App::call('App\Http\Controllers\UserController@updateUser', ['request' => $request, 'noWhatsapp' => $user_number]);

            # change menu location to learningQuestion
            $data = [
                'lokasiMenu' => 'learningQuestion'
            ];
            $request = Request::create('/users/$recipient_number', 'PUT', $data);
            App::call('App\Http\Controllers\UserController@updateUser', ['request' => $request, 'noWhatsapp' => $user_number]);
        } else {
            Log::info("Failed to generate the question due to undetected topic or content");
        }

        return $message;
    }

    private function handleUserQuestion($input_message, $recipient_number) {
        $user_number = $this->formatUserPhoneNumber($recipient_number);

        # get user progress
        $learning_unit_id = null;
        $level_id = null;
        $user_current_question = null;
        $user_query = App::call('App\Http\Controllers\UserController@showUserById', ['noWhatsapp' => $user_number]);
        if ($user_query->getStatusCode() == 200) {
            // Decode the JSON user_query to get required data
            $user_data = $user_query->getData();
            if (isset($user_data->progress)) {
                $user_progress = $user_data->progress;
                $user_progress = explode('-', $user_progress);
                $learning_unit_id = $user_progress[0];
                $level_id = $user_progress[1];
                $user_current_question = $user_data->currentQuestion;
            }
        }

        // instantiate level and unit model
        $learningUnitDocument = $this->learningUnit->find($learning_unit_id);
        $level = new Level($learning_unit_id);
        $levelDocument = $level->find($level_id);
        if(isset($levelDocument['topic']) && isset($levelDocument['content'])) {
            $topic = $levelDocument['topic'];
            $content = $levelDocument['content'];
            $evaluation = $this->evaluateUserAnswer($topic, $content, $user_current_question, $input_message);
            return $evaluation;
        } else {
            return "Internal service error: topic and learning material not found";
        }
    }

    private function generateLevelPrompt($learningUnitDocument, $levelDocument)
    {
        // Base prompt for ChatGPT
        $basePrompt = "Berikut adalah materi pembelajaran untuk topik: {$learningUnitDocument['topic']}. ";
        $basePrompt .= "Hari ini, kita akan membahas level: {$levelDocument['id']}. {$levelDocument['topic']}.";
        $basePrompt .= "Silakan tonton video-video berikut ini untuk memperdalam pemahamanmu:\n";

        // Humanize the prompt using ChatGPT
        $response = $this->openai->completions()->create([
            'model' => 'gpt-3.5-turbo-instruct',
            'prompt' => $basePrompt . "\nHumanize the above information into a friendly and engaging conversation in brief in Bahasa Indonesia without changing the meaning.",
            'max_tokens' => 250,
            'temperature' => 0.7,
        ]);

        return $response['choices'][0]['text'];
    }

    private function generateQuestion($topic, $content)
    {
        $response = $this->openai->completions()->create([
            'model' => 'gpt-3.5-turbo-instruct',
            'prompt' => "Analyze the following learning materials, then generate a question about " . $topic . " for user in english:" . "\n" . $content,
            'max_tokens' => 150,
            'temperature' => 0.7,
        ]);

        return $response['choices'][0]['text'];
    }

    private function evaluateUserAnswer($topic, $content, $question, $answer)
    {
        $prompt = "Analyze the following learning material: " . "\n" . $content . "\n";
        $prompt .= "From the given material, user has answered the following question about " . $topic . ":\n";
        $prompt .= $question . "\nAnd, the user answer:\n";
        $prompt .= $answer . "\nEvaluate the answer and return grade in percentage (0%-100%) and feedback in Bahasa Indonesia!";
        $prompt .= "The grade and feedback are seperated by '|' character to ease me to parse the return message in my program before shown to user.";
        $response = $this->openai->completions()->create([
            'model' => 'gpt-3.5-turbo-instruct',
            'prompt' => $prompt,
            'max_tokens' => 150,
            'temperature' => 0.7,
        ]);

        return $response['choices'][0]['text'];
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

    public function statusCallback()
    {
        return $this->twilioClient->$queues;
    }
}
