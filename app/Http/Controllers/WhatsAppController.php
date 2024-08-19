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

    private function handleMenuLocation($menuLocationWithSubMenu, $inputMessage, $recipientNumber)
    {
        $menuLocationWithSubMenu = explode('-', $menuLocationWithSubMenu);
        $menuLocation = $menuLocationWithSubMenu[0]; # extract menu without submenu
        switch ($menuLocation) {
            case "userProfile":
                return $this->handleUserProfileMenu($inputMessage, $recipientNumber);
            case "userProfileSetName":
                return $this->handleUserProfileSetName($inputMessage, $recipientNumber);
            case "userProfileSetJob":
                return $this->handleUserProfileSetJob($inputMessage, $recipientNumber);
            case "levelPrompt":
                return $this->showLearningMenu($inputMessage, $recipientNumber);
            case "questionPrompt":
                $questionIndex = 0;
                if(isset($menuLocationWithSubMenu[1])) {
                    # set questionIndex with submenu if any
                    $questionIndex = intval($menuLocationWithSubMenu[1]);
                }
                return $this->giveUserQuestion($recipientNumber, $questionIndex, $inputMessage);
            case "questionEval":
                return $this->handleUserAnswer($inputMessage, $recipientNumber, intval($menuLocationWithSubMenu[1]));
            default:
                return $this->handleMainMenu($inputMessage, $recipientNumber);
        }
    }

    private function handleMainMenu($inputMessage, $recipientNumber)
    {
        switch ($inputMessage) {
            case "1":
                return $this->showLearningMenu($inputMessage, $recipientNumber);
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

    private function showLearningMenu($inputMessage, $recipientNumber)
    {
        if(strtolower($inputMessage) == "keluar") { # if user quit learning menu
            # change menu location to mainMenu
            return $this->backToMainMenu($recipientNumber);
        }

        $userNumber = $this->formatUserPhoneNumber($recipientNumber);

        # get user progress
        $userData = User::find($userNumber);
        $learningUnitId = 0;
        $levelId = 0;

        if (isset($userData->progress)) {
            $userProgress = $userData->progress;
            $userProgress = explode('-', $userProgress);
            $learningUnitId = (int)$userProgress[0];
            $levelId = (int)$userProgress[1];
        }

        // instantiate level and unit model
        $learningUnit = LearningUnit::findBy('sortId', $learningUnitId);
        $level = Level::where('unitId', $learningUnit->id)
                        ->where('sortId', $levelId)
                        ->first();

        $message = '';
        
        # generate prompt for the current level
        $prompt = $this->generateLevelPrompt($learningUnit, $level);
        $message .= $prompt . '|';
        
        if($level->videoLink != null) {
            Log::info("Video url is detected");
            Log::info("Retrieved video url: " . $level->videoLink);

            # prompt user to watch the video
            $message .= "Yuk, tonton video berikut untuk memperdalam pemahamanmu!|";
            $message .= $level->videoLink . '|';
        } else {
            Log::info("Failed to retrive the video");
        }

        $message .= "Jika sudah selesai menyimak materi, ketik apapun untuk menjawab pertanyaan atau 'Keluar' untuk kembali ke Main Menu! Tenang aja, progres belajarmu akan tersimpan kok!";

        # change menu location to questionPrompt
        $this->changeMenuLocation($userNumber, 'questionPrompt');

        # set the currentGrade of user to store the number of questions of this level
        $numberOfQuestions = $level->questions()->count();
        $userData->currentGrade = '0/' . strval($numberOfQuestions);
        $userData->save();

        return $message;
    }

    private function giveUserQuestion($recipientNumber, $questionIndex, $inputMessage = null) {
        if(strtolower($inputMessage) == "keluar") { # if user quit learning menu
            # change menu location to mainMenu
            return $this->backToMainMenu($recipientNumber);
        }
        
        $userNumber = $this->formatUserPhoneNumber($recipientNumber);

        # get user progress
        $userData = User::find($userNumber);
        $learningUnitId = 0;
        $levelId = 0;

        if (isset($userData->progress)) {
            $userProgress = $userData->progress;
            $userProgress = explode('-', $userProgress);
            $learningUnitId = (int)$userProgress[0];
            $levelId = (int)$userProgress[1];
        }

        // instantiate level and unit model
        $learningUnit = LearningUnit::findBy('sortId', $learningUnitId);
        $level = Level::where('unitId', $learningUnit->id)
                        ->where('sortId', $levelId)
                        ->first();

        $message = '';

        $questions = $level->questions()->orderBy('id', 'asc')->get();
        $question = $questions->get($questionIndex)->question ?? "Question not found"; # get question at requested index
        if($question != null) {
            if($questionIndex == 0) {
                $message .= "Jawab quiz berikut untuk mengevaluasi pemahaman Anda!!|";
            }

            $message .= "Quiz nomor " . strval($questionIndex+1) . ":\n" . $question;

            # update user current question
            $userData->currentQuestion = $question;
            $userData->save();

            # change menu location to questionEval-{questionIndex}
            $this->changeMenuLocation($userNumber, 'questionEval-' . strval($questionIndex));
        } else {
            Log::info("Failed to generate the question due to undetected topic or content");
        }

        return $message;
    }

    private function handleUserAnswer($inputMessage, $recipientNumber, $questionIndex = 0) {
        Log::info("Entering handleUserAnswer");
        $userNumber = $this->formatUserPhoneNumber($recipientNumber);

        # get user progress
        $userData = User::find($userNumber);
        $learningUnitId = 0;
        $levelId = 0;

        if (isset($userData->progress)) {
            $userProgress = $userData->progress;
            $userProgress = explode('-', $userProgress);
            $learningUnitId = (int)$userProgress[0];
            $levelId = (int)$userProgress[1];
        }

        // instantiate level and unit model
        $learningUnit = LearningUnit::findBy('sortId', $learningUnitId);
        $level = Level::where('unitId', $learningUnit->id)
                        ->where('sortId', $levelId)
                        ->first();

        $questions = $level->questions()->orderBy('id', 'asc')->get();
        $question = $questions->get($questionIndex) ?? null; # get question at requested index
        
        if($question != null) {

            $answer = $question->answer;
            if($answer != null) {
                $evaluation = false;

                if($question->type == 'Multiple Choice') {
                    # evaluating answer of multiple choice question
                    $answerOption = strtolower(explode('|', $answer)[0]);
                    $answerExact = strtolower(explode('|', $answer)[1]);
                    Log::info("Jawaban user: " . strtolower($inputMessage));
                    Log::info("Jawaban benar: " . $answerExact);

                    if(strtolower($inputMessage) == $answerOption || strtolower($inputMessage) == $answerExact) {
                        $evaluation = true;
                    }
                } else {
                    # evaluating answer of essay question
                    if(strtolower($inputMessage) == strtolower($answer)) {
                        $evaluation = true;
                    }
                }

                $message = "";

                $currentGrade = explode('/', $userData->currentGrade);

                if($evaluation) {
                    # increase the correct answer in currentGrade
                    $currentCorrectAnswers = intval($currentGrade[0]);
                    $currentCorrectAnswers++;
                    $userData->currentGrade = strval($currentCorrectAnswers) . '/' . $currentGrade[1];
                    $userData->save();

                    $message .= "Selamat, jawaban Anda benar.|";
                } else {
                    $message .= "Yah, jawaban Anda salah.|";
                }

                # increase questionIndex after evaluating the answer
                $questionIndex++;

                $numberOfQuestions = intval($currentGrade[1]);
                if($questionIndex < $numberOfQuestions) {
                    # send the next question if any
                    $message .= $this->giveUserQuestion($recipientNumber, $questionIndex);
                } else {
                    # grade all user answers in the current level
                    $currentGrade = explode('/', $userData->currentGrade);
                    $correctAnswers = intval($currentGrade[0]);
                    $score = ($correctAnswers / $numberOfQuestions) * 100; // return (float) score
                    $score = round($score); // round the score to integer

                    if($score >= 50) {
                        # increase the level
                        $userData->progress = strval($learningUnitId) . '-' . strval(++$levelId);
                        $userData->save();

                        # store the user score
                        $userData->levels()->attach($level->id, ['score' => $score]);

                        $message .= "Selamat, Anda telah berhasil menyelesaikan level ini dengan *nilai: " . strval($score) . "*";
                        $message .= "\n\n*ketik apapun untuk melanjutkan ke level berikutnya atau 'Keluar' untuk kembali ke Main Menu!* Tenang aja, progress kamu tidak akan hilang kok!";
                    } else {
                        $message .= "Maaf, *nilai Anda: " . strval($score) . "*; tidak lolos passing grade(50). Pelajarin lagi materi di level ini yuk! Semangat!";
                        $message .= "\n\n*ketik apapun untuk mengulang level ini atau 'Keluar' untuk kembali ke Main Menu!* Tenang aja, progress kamu tidak akan hilang kok!";
                    }

                    # change menu location to levelPrompt
                    $this->changeMenuLocation($userNumber, 'levelPrompt');
                }

                return $message;
            } else {
                return "Internal service error: topic and learning material not found";
            }
        } else {
            return "Internal service error: question not found in the database";
        }
    }

    private function generateLevelPrompt($learningUnit, $level)
    {
        // Base prompt for ChatGPT
        $basePrompt = "Selamat datang di unit: {$learningUnit->sortId} - {$learningUnit->topic}\n";
        $basePrompt .= "Saat ini, Anda berada pada level: {$level->sortId} - {$level->topic}\n\n";
        $basePrompt .= "Simak ringkasan materi berikut:\n";
        if ($level->content != null) {
            $basePrompt .= $level->content;
        } else {
            $basePrompt .= "Mohon maaf, materi untuk level ini belum dirilis";
        }

        return $basePrompt;
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
