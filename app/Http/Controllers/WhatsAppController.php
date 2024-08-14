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
use App\Models\User;
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
        $twilioWhatsAppNumber = env('TWILIO_WHATSAPP_NUMBER');
        $recipientNumber = env('RECIPIENT_WHATSAPP_NUMBER'); // Change to the recipient's number

        $client = new Client($sid, $token);

        $message = $client->messages->create(
            $recipientNumber,
            [
                'from' => $twilioWhatsAppNumber,
                'body' => "Here's an audio file for you!"
            ]
        );

        return response()->json(['message' => 'Audio message sent!', 'sid' => $message->sid, 'status' => $message->status]);
    }

    public function receiveMessage(Request $request)
    {
        // Set the maximum execution time to 300 seconds (5 minutes)
        set_time_limit(300);

        $recipientNumber = $request->input('From'); // Change to the recipient's number
        $inputMessage = $request->input('Body'); // Message from user

        # Ensure that the user was registered
        $menuLocation = $this->checkUser($recipientNumber);

        $response = $this->handleMenuLocation($menuLocation, $inputMessage, $recipientNumber);

        return response()->json(
            [
                'recipient_number' => $recipientNumber,
                'response' => $response
            ]
        );
    }

    private function handleMenuLocation($menuLocation, $inputMessage, $recipientNumber)
    {
        $menuLocationWithSubMenu = $menuLocation;
        $menuLocation = explode('-', $menuLocation)[0];
        # examine lokasi menu without sub-menu
        switch ($menuLocation) {
            case "userProfile":
                return $this->handleUserProfileMenu($inputMessage, $recipientNumber);
            case "userProfileSetName":
                return $this->handleUserProfileSetName($inputMessage, $recipientNumber);
            case "userProfileSetJob":
                return $this->handleUserProfileSetJob($inputMessage, $recipientNumber);
            case "preLearning":
                # pass lokasi menu along with the sub menu to indetify current video index
                return $this->showLearningMenu($recipientNumber, $menuLocationWithSubMenu);
            case "learning":
                return $this->giveUserQuestion($inputMessage, $recipientNumber);
            case "learningQuestion":
                return $this->handleUserQuestion($inputMessage, $recipientNumber);
            default:
                return $this->handleMainMenu($inputMessage, $recipientNumber);
        }
    }

    private function handleMainMenu($inputMessage, $recipientNumber)
    {
        switch ($inputMessage) {
            case "1":
                return $this->showLearningMenu($recipientNumber);
            case "2":
                return $this->showProfileMenu($recipientNumber);
            case "3":
                return $this->showAboutUs();
            default:
                return $this->showMainMenu();
        }
    }

    private function handleUserProfileMenu($inputMessage, $recipientNumber)
    {
        switch ($inputMessage) {
            case "1":
                return $this->setProfile($recipientNumber);
            case "2":
                return $this->backToMainMenu($recipientNumber);
            default:
                return $this->showProfileMenu($recipientNumber);
        }
    }

    private function setProfile($recipientNumber)
    {
        $userNumber = $this->formatUserPhoneNumber($recipientNumber);

        # change menu location to userProfileSetName to ask user their name
        $this->changeMenuLocation($userNumber, 'userProfileSetName');

        return "Masukkan nama Anda:";
    }

    private function handleUserProfileSetName($inputMessage, $recipientNumber)
    {
        $userNumber = $this->formatUserPhoneNumber($recipientNumber);

        # set user name and change menu location to userProfileSetJob to ask user their job
        $user = User::find($userNumber);
        $user->name = $inputMessage;
        $user->menuLocation = 'userProfileSetJob';
        $user->save();

        return "Hallo, $inputMessage!! Apa pekerjaan Anda:";
    }

    private function handleUserProfileSetJob($inputMessage, $recipientNumber)
    {
        $userNumber = $this->formatUserPhoneNumber($recipientNumber);

        # set user job and change menu location back to userProfile
        $user = User::find($userNumber);
        $user->occupation = $inputMessage;
        $user->menuLocation = 'userProfile';
        $user->save();

        return "Profil Anda berhasil diatur, ketik dan kirim apapun untuk melihat profil Anda!";
    }

    private function comingSoon()
    {
        $message = "Feature is still in development";
        return $message;
    }

    private function changeMenuLocation($userNumber, $menu) {
        # change menu location to mainMenu
        $user = User::find($userNumber);
        $user->menuLocation = $menu;
        return $user->save(); # return true or false
    }

    private function showAboutUs()
    {
        $message = env('ABOUT_US_PROMPT');
        $message .= '|Ketik apapun untuk kembali ke main menu';
        return $message;
    }

    private function showMainMenu()
    {
        return env('MAIN_MENU_PROMPT');
    }

    private function backToMainMenu($recipientNumber)
    {
        $userNumber = $this->formatUserPhoneNumber($recipientNumber);

        # change menu location to mainMenu
        $result = $this->changeMenuLocation($userNumber, 'mainMenu');

        # check whether the user successfully back or not
        if($result) {
            Log::info("User successfully back to mainMenu");
            return $this->showMainMenu();
        } else {
            Log::info("User failed to back to mainMenu");
            return "Maaf, gagal kembali ke main menu, mohon dicoba kembali!";
        }
    }

    private function showProfileMenu($recipientNumber)
    {
        $userNumber = $this->formatUserPhoneNumber($recipientNumber);

        # change menu location to userProfile
        $this->changeMenuLocation($userNumber, 'userProfile');

        # get user data
        $userName = "belum diatur";
        $userOccupation = "belum diatur";
        $userData = User::find($userNumber);
        if ($userData != null) {
            if (isset($userData->name)) {
                $userName = $userData->name;
            }
            if (isset($userData->occupation)) {
                $userOccupation = $userData->occupation;
            }
        }

        $message = "Berikut merupakan profil Anda saat ini:
        - Nama: $userName
        - Pekerjaan: $userOccupation
Pilih menu berikut untuk melanjutkan:
        1. Ubah profil
        2. Kembali ke Main Menu";

        return $message;
    }

    private function showLearningMenu($recipientNumber, $menuLocation = 'preLearning')
    {
        $userNumber = $this->formatUserPhoneNumber($recipientNumber);

        # get user progress
        $learning_unit_id = null;
        $level_id = null;
        $user_query = App::call('App\Http\Controllers\UserController@showUserById', ['noWhatsapp' => $userNumber]);
        if ($user_query->getStatusCode() == 200) {
            // Decode the JSON user_query to get required data
            $userData = $user_query->getData();
            if (isset($userData->progress)) {
                $user_progress = $userData->progress;
                $user_progress = explode('-', $user_progress);
                $learning_unit_id = $user_progress[0];
                $level_id = $user_progress[1];
            }
        }

        // instantiate level and unit model
        $learningUnitDocument = $this->learningUnit->find($learning_unit_id);
        $level = new Level($learning_unit_id);
        $levelDocument = $level->find($level_id);

        $message = '';
        
        # generate prompt if user has just entered the learning menu
        if($menuLocation == "preLearning") {
            $prompt = $this->generateLevelPrompt($learningUnitDocument, $levelDocument);
            $message .= $prompt . '|';
        }

        $nextMenu = 'learning';
        
        if(is_array($levelDocument['videos'])) {
            $videos = $levelDocument['videos'];
            Log::info("Video url is detected");

            # indentify current video index based on user location
            $videoIndex = 0;
            if($menuLocation != "preLearning") {
                $videoIndex = (int)(explode('-', $menuLocation)[1]);
            }

            $videoUrl = env('NGROK_URL') . Storage::url($videos[$videoIndex]);
            Log::info("Retrieved video url: " . $videoUrl);
            $message .= $videoUrl;
            
            # prompt user to type next if the next video exist
            if(count($videos) > ($videoIndex+1)) {
                # change next menu to the next video instead of learning
                $nextMenu = 'preLearning-' . ($videoIndex+1);
                $message .= '|Ketik lanjutkan atau apapun untuk menonton video selanjutnya!';
            } else {
                $message .= "|Ketik apapun untuk menjawab pertanyaan atau '!exit' untuk kembali ke Main Menu!";
            }
        } else {
            Log::info("Failed to retrive the video");
        }

        # change menu location to the next menu
        $data = [
            'menu$menuLocation' => $nextMenu
        ];
        $request = Request::create('/users/$recipientNumber', 'PUT', $data);
        App::call('App\Http\Controllers\UserController@updateUser', ['request' => $request, 'noWhatsapp' => $userNumber]);

        return $message;
    }

    private function giveUserQuestion($inputMessage, $recipientNumber) {
        if($inputMessage == "!exit") { # if user quit learning menu
            # change menu location to mainMenu
            return $this->backToMainMenu($recipientNumber);
        }
        
        $userNumber = $this->formatUserPhoneNumber($recipientNumber);

        # get user progress
        $learning_unit_id = null;
        $level_id = null;
        $user_query = App::call('App\Http\Controllers\UserController@showUserById', ['noWhatsapp' => $userNumber]);
        if ($user_query->getStatusCode() == 200) {
            // Decode the JSON user_query to get required data
            $userData = $user_query->getData();
            if (isset($userData->progress)) {
                $user_progress = $userData->progress;
                $user_progress = explode('-', $user_progress);
                $learning_unit_id = $user_progress[0];
                $level_id = $user_progress[1];
            }
        }

        // instantiate level and unit model
        $learningUnitDocument = $this->learningUnit->find($learning_unit_id);
        $level = new Level($learning_unit_id);
        $levelDocument = $level->find($level_id);

        $message = '';

        if(isset($levelDocument['topic']) && isset($levelDocument['content'])) {
            $topic = $levelDocument['topic'];
            $content = $levelDocument['content'];
            $generatedQuestion = $this->generateQuestion($topic, $content);
            $message .= "Setelah menonton, jawab pertanyaan berikut:" . $generatedQuestion;

            # update user current question
            $data = [
                'currentQuestion' => $generatedQuestion
            ];
            $request = Request::create('/users/$recipientNumber', 'PUT', $data);
            App::call('App\Http\Controllers\UserController@updateUser', ['request' => $request, 'noWhatsapp' => $userNumber]);

            # change menu location to learningQuestion
            $data = [
                'menu$menuLocation' => 'learningQuestion'
            ];
            $request = Request::create('/users/$recipientNumber', 'PUT', $data);
            App::call('App\Http\Controllers\UserController@updateUser', ['request' => $request, 'noWhatsapp' => $userNumber]);
        } else {
            Log::info("Failed to generate the question due to undetected topic or content");
        }

        return $message;
    }

    private function handleUserQuestion($inputMessage, $recipientNumber) {
        Log::info("Entering handleUserQuestion");
        $userNumber = $this->formatUserPhoneNumber($recipientNumber);

        # get user progress
        $learning_unit_id = null;
        $level_id = null;
        $user_current_question = null;
        $user_query = App::call('App\Http\Controllers\UserController@showUserById', ['noWhatsapp' => $userNumber]);
        if ($user_query->getStatusCode() == 200) {
            // Decode the JSON user_query to get required data
            $userData = $user_query->getData();
            if (isset($userData->progress)) {
                $user_progress = $userData->progress;
                $user_progress = explode('-', $user_progress);
                $learning_unit_id = $user_progress[0];
                $level_id = $user_progress[1];
                $user_current_question = $userData->currentQuestion;
            }
        }

        // instantiate level and unit model
        $learningUnitDocument = $this->learningUnit->find($learning_unit_id);
        $level = new Level($learning_unit_id);
        $levelDocument = $level->find($level_id);
        try {
            if(isset($levelDocument['topic']) && isset($levelDocument['content'])) {
                $topic = $levelDocument['topic'];
                $content = $levelDocument['content'];
                $evaluation = $this->evaluateUserAnswer($topic, $content, $user_current_question, $inputMessage);
    
                $message = '';
                $grade = $evaluation['grade'];
                $gradeInt = (int)$grade;
                Log::info("The grade in integer: " . $grade);
                $feedback = $evaluation['feedback'];
                if($gradeInt >= 50) {
                    # increase the level
                    if($level->find($level_id + 1)) {
                        # go to the next level if any 
                        $level_id++;
                    } else {
                        # go to the next unit if all levels were completed
                        $learning_unit_id++;
                    }
                    $grade = "Congratulations, your grade is: " . $grade . "%. Thus, you have passed the unit's passing grade(50%)";
                } else {
                    $grade = "Unfortunatelly, your grade is: " . $grade . "%. Thus, you have failed to pass the unit's passing grade(50%)";
                }

                # change menu location to preLearning
                $data = [
                    'menu$menuLocation' => 'preLearning',
                    'progress' => $learning_unit_id . '-' . $level_id,
                ];
                $request = Request::create('/users/$userNumber', 'PUT', $data);
                App::call('App\Http\Controllers\UserController@updateUser', ['request' => $request, 'noWhatsapp' => $userNumber]);
    
                $message = $grade . "|" . $feedback . "|Ketik lanjutkan atau apapun untuk melanjutkan!";
    
                return $message;
            } else {
                return "Internal service error: topic and learning material not found";
            }
        } catch(\Exception $e) {
            return $e;
        }
    }

    private function generateLevelPrompt($learningUnitDocument, $levelDocument)
    {
        // Base prompt for ChatGPT
        $basePrompt = "Berikut adalah materi pembelajaran untuk topik: {$learningUnitDocument['topic']}. ";
        $basePrompt .= "Hari ini, kita akan membahas level: {$levelDocument['id']}. {$levelDocument['topic']}.";
        $basePrompt .= "Silakan tonton video-video berikut ini untuk memperdalam pemahamanmu:\n";

        $isMaxCharsValid = false;

        while (!$isMaxCharsValid) {
            // Humanize the prompt using ChatGPT
            $response = $this->openai->completions()->create([
                'model' => 'gpt-3.5-turbo-instruct',
                'prompt' => $basePrompt . "\nHumanize the above information into a friendly and engaging conversation as concise as possible with maximum 400 characters in Bahasa Indonesia without changing the meaning.",
                'max_tokens' => 250,
                'temperature' => 0.7,
            ]);
    
            $basePrompt = $response['choices'][0]['text'];

            if (strlen($basePrompt) <= 400) {
                $isMaxCharsValid = true;
            }
        }

        return $basePrompt;
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
        $prompt .= $answer . "\nEvaluate the answer and return grade only in number format with range from 0 to 100 and feedback in Bahasa Indonesia!";
        $prompt .= "The grade and feedback are seperated by '|' character to ease me to parse the return message in my program before shown to user.";
        $response = $this->openai->completions()->create([
            'model' => 'gpt-3.5-turbo-instruct',
            'prompt' => $prompt,
            'max_tokens' => 150,
            'temperature' => 0.7,
        ]);

        $evaluation = $response['choices'][0]['text'];

        $grade = explode('|', $evaluation)[0];
        $feedback = explode('|', $evaluation)[1];

        if(!ctype_digit($grade)) {
            $grade = $this->extractNumbers($grade);
            $grade = (string)$grade[0];
        }

        $result = array(
            "grade" => $grade,
            "feedback" => $feedback
        );

        return $result;
    }

    private function extractNumbers($string) {
        // This pattern matches sequences of digits
        $pattern = '/\d+/';
        // Find all sequences of digits in the string
        preg_match_all($pattern, $string, $matches);
        // Convert the matched sequences to integers
        $numbers = array_map('intval', $matches[0]);
        return $numbers;
    }

    private function checkUser($recipientNumber)
    {
        $userNumber = $this->formatUserPhoneNumber($recipientNumber);

        $userData = User::find($userNumber);
        $menuLocation = 'mainMenu';

        // Register new user if does not exist
        if ($userData == null) {
            Log::info("Creating new user with phone number: " . $userNumber);
            User::create([
                'phoneNumber' => $userNumber,
                'menuLocation' => $menuLocation,
                'progress' => '1-1',
            ]);
        }

        if (isset($userData->menuLocation)) {
            $menuLocation = $userData->menuLocation;
        }

        return $menuLocation;
    }

    private function formatUserPhoneNumber($recipientNumber)
    {
        // only include the phone number without 'whatsapp:' text behind it
        return explode(":", $recipientNumber)[1];
    }

    public function statusCallback()
    {
        return $this->twilioClient->$queues;
    }
}
