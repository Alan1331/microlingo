<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\App;
use App\Models\LearningUnit;
use App\Models\Level;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class WhatsAppController extends Controller
{
    public function receiveMessage(Request $request)
    {
        // Set the maximum execution time to 300 seconds (5 minutes)
        set_time_limit(300);

        $recipientNumber = $request->input('From'); // Change to the recipient's number
        $inputMessage = $request->input('Body'); // Message from user
        $userNumber = $this->formatUserPhoneNumber($recipientNumber);

        # Ensure that the user was registered
        $menuLocation = $this->checkUser($userNumber);

        # Handle edge case when user sent sticker or unknown object that lead to null input message
        $response = 'Maaf, chatbot tidak mengenali input dari Anda';
        if($inputMessage != null) {
            Log::info("[" . $userNumber . "] Message received: " . $inputMessage);
            $response = $this->handleMenuLocation($menuLocation, $inputMessage, $userNumber);
        } else {
            Log::info("[" . $userNumber . "] Message received: user sent unknown object like sticker");
        }

        return response()->json(
            [
                'recipient_number' => $recipientNumber,
                'response' => $response
            ]
        );
    }

    private function handleMenuLocation($menuLocationWithSubMenu, $inputMessage, $userNumber)
    {
        $menuLocationWithSubMenu = explode('-', $menuLocationWithSubMenu);
        $menuLocation = $menuLocationWithSubMenu[0]; # extract menu without submenu
        switch ($menuLocation) {
            case "pitchingMenuAskName":
                return $this->pitchingMenuAskName($inputMessage, $userNumber);
            case "pitchingSession":
                return $this->pitchingSession($inputMessage, $userNumber);
            case "userProfile":
                return $this->handleUserProfileMenu($inputMessage, $userNumber);
            case "userProfileSetName":
                return $this->handleUserProfileSetName($inputMessage, $userNumber);
            case "userProfileSetJob":
                return $this->handleUserProfileSetJob($inputMessage, $userNumber);
            case "levelPrompt":
                return $this->showLearningMenu($inputMessage, $userNumber);
            case "questionPrompt":
                $questionIndex = 0;
                if(isset($menuLocationWithSubMenu[1])) {
                    # set questionIndex with submenu if any
                    $questionIndex = intval($menuLocationWithSubMenu[1]);
                }
                return $this->giveUserQuestion($userNumber, $questionIndex, $inputMessage);
            case "questionEval":
                return $this->handleUserAnswer($inputMessage, $userNumber, intval($menuLocationWithSubMenu[1]));
            case "promptResetProgress":
                return $this->promptResetProgress($inputMessage, $userNumber);
            default:
                return $this->handleMainMenu($inputMessage, $userNumber);
        }
    }

    private function handleMainMenu($inputMessage, $userNumber)
    {
        switch ($inputMessage) {
            case "1":
                return $this->showLearningMenu($inputMessage, $userNumber);
            case "2":
                return $this->showPitchingMenu($userNumber);
            case "3":
                return $this->showProfileMenu($userNumber);
            case "4":
                Log::info("[" . $userNumber . "] Display About Us");
                return $this->showAboutUs();
            default:
                Log::info("[" . $userNumber . "] Display Main Menu");
                return $this->showMainMenu();
        }
    }

    private function handleUserProfileMenu($inputMessage, $userNumber)
    {
        switch ($inputMessage) {
            case "1":
                return $this->setProfile($userNumber);
            case "2":
                return $this->backToMainMenu($userNumber);
            default:
                return $this->showProfileMenu($userNumber);
        }
    }

    private function showPitchingMenu($userNumber)
    {
        $userName = User::find($userNumber)->name;

        if(isset($userName)) {
            return $this->enterPitchingSession($userNumber, $userName);
        } else {
            Log::info("[" . $userNumber . "] Asking user name before entering pitching session");
            $this->changeMenuLocation($userNumber, 'pitchingMenuAskName');
            return "Siapa nama Anda?";
        }
    }

    private function pitchingMenuAskName($inputMessage, $userNumber) {
        $userName = $inputMessage;
        $user = User::find($userNumber);
        $user->name = $userName;
        $user->save();

        return $this->enterPitchingSession($userNumber, $userName);
    }

    private function enterPitchingSession($userNumber, $userName)
    {
        Log::info("[" . $userNumber . "] Enter pitching session");
        $this->changeMenuLocation($userNumber, 'pitchingSession');
        $message = "Anda telah masuk ke sesi pitching. Pada sesi ini, Anda dapat mengetik dan mengirim kata kunci berikut:\n";
        $message .= "- *keluar*: untuk keluar dari sesi pitching dan kembali ke Main Menu\n";
        $message .= "- *terjemahkan*: untuk menerjemahkan pesan sebelumnya jika Anda belum mengerti\n";
        $message .= "- *bedah kosakata*: untuk melihat penjelasan dari kosakata sulit pada pesan sebelumnya";
        $message .= "|Bayangkan Anda sedang bertemu dengan calon partner bisnis Anda dari luar negeri. ";
        $message .= "Tugas Anda adalah untuk meyakinkan calon partner bisnis Anda untuk bergabung dalam bisnis Anda! ";
        $message .= "Manfaatkan semua ilmu yang sudah Anda pelajari pada MicroLingo untuk deal dengan partner Anda! ";
        $message .= "Good luck!!|";

        $helloMessage = "Hello, I am " . $userName;
        $message .= $this->pitchingSession($helloMessage, $userNumber);

        return $message;
    }

    private function pitchingSession($inputMessage, $userNumber)
    {
        // Data for the API request
        $data = [
            'session_id' => $userNumber,
            'input' => $inputMessage,
        ];

        Log::info("[" . $userNumber . "] Send received user message to pitching API");
        // Send a POST request using Laravel's Http client with 7 attempt and 3s delay between retry
        $response = Http::retry(7,3000)->post(env('PITCHING_SERVICE_ENDPOINT'), $data);

        // Check for errors and return the response
        if ($response->successful()) {
            Log::info("[" . $userNumber . "] Response from pitching API was retrieved");
            $responseData = $response->json();
            $message = $responseData['message'];
            $missionStatus = $responseData['mission_status'];

            if($missionStatus != "ongoing") {
                # return back to main menu if conversation ends
                $this->backToMainMenu($userNumber);
            }

            if($missionStatus == "success") {
                $message .= "|Congratulations, you have *successfully* finished the mission. ";
                $message .= "Ketik *Yes* untuk kembali ke Main Menu.";
            }

            if($missionStatus == "failed") {
                $message .= "|Unfortunatelly, you have *failed* the mission. Don't worry, try again next!! ";
                $message .= "Ketik *Semangat* untuk kembali ke Main Menu.";
            }

            if($missionStatus == "quit") {
                $message .= "|" . $this->showMainMenu();
            }

            Log::info("[" . $userNumber . "] Sending response from pitching API to user: " . $message);
            return $message;
        } else {
            Log::info("[" . $userNumber . "] Failed to retrieve response from pitching API");
            return 'Error internal chatbot: gagal memulai sesi pitching.';
        }
    }

    private function setProfile($userNumber)
    {
        # change menu location to userProfileSetName to ask user their name
        $this->changeMenuLocation($userNumber, 'userProfileSetName');

        Log::info("[" . $userNumber . "] Ask user name");
        return "Masukkan nama Anda:";
    }

    private function handleUserProfileSetName($inputMessage, $userNumber)
    {
        # set user name and change menu location to userProfileSetJob to ask user their job
        $user = User::find($userNumber);
        $user->name = $inputMessage;
        $user->menuLocation = 'userProfileSetJob';
        $user->save();

        Log::info("[" . $userNumber . "] Ask user occupation");
        return "Hallo, $inputMessage!! Apa pekerjaan Anda:";
    }

    private function handleUserProfileSetJob($inputMessage, $userNumber)
    {
        Log::info("[" . $userNumber . "] Updating user profile");
        # set user job and change menu location back to userProfile
        $user = User::find($userNumber);
        $user->occupation = $inputMessage;
        $user->menuLocation = 'userProfile';
        $user->save();

        Log::info("[" . $userNumber . "] User profile was updated");

        return "Profil berhasil diperbaharui.|" . $this->showProfileMenu($userNumber);
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
        $message .= '|Ketik *Kembali* untuk kembali ke main menu';
        return $message;
    }

    private function showMainMenu()
    {
        return env('MAIN_MENU_PROMPT');
    }

    private function backToMainMenu($userNumber)
    {
        # change menu location to mainMenu
        $result = $this->changeMenuLocation($userNumber, 'mainMenu');

        # check whether the user successfully back or not
        if($result) {
            Log::info("[" . $userNumber . "] Back to Main Menu");
            return $this->showMainMenu();
        } else {
            Log::info("User failed to back to mainMenu");
            return "Maaf, gagal kembali ke main menu, mohon dicoba kembali!";
        }
    }

    private function showProfileMenu($userNumber)
    {
        # change menu location to userProfile
        Log::info("[" . $userNumber . "] Enter profile menu");
        $this->changeMenuLocation($userNumber, 'userProfile');

        # get user data
        Log::info("[" . $userNumber . "] Query user profile");
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

        Log::info("[" . $userNumber . "] Display user profile");
        return $message;
    }

    private function promptResetProgress($inputMessage, $userNumber) {
        if(strval($inputMessage) == 1) {
            $user = User::find($userNumber);
            $user->progress = '1-1';
            $user->menuLocation = 'levelPrompt';
            $user->save();
        } elseif(strval($inputMessage) == 2) {
            return $this->backToMainMenu($userNumber);
        }

        return $this->showLearningMenu('lanjut', $userNumber);
    }

    private function showLearningMenu($inputMessage, $userNumber)
    {
        Log::info("[" . $userNumber . "] Enter Learning Menu");
        if(strtolower($inputMessage) == "keluar") { # if user quit learning menu
            # change menu location to mainMenu
            return $this->backToMainMenu($userNumber);
        }

        # get user progress
        Log::info("[" . $userNumber . "] Query level based on user progress");
        $userData = User::find($userNumber);
        $learningUnitId = 0;
        $levelId = 0;

        if (isset($userData->progress)) {
            $userProgress = $userData->progress;

            # send user to main menu or prompt for reset progress if they have completed all levels
            $isCompleted = $this->isProgressCompleted($userProgress, $userNumber);
            if($isCompleted) {
                return $isCompleted;
            }

            $userProgress = explode('-', $userProgress);
            $learningUnitId = (int)$userProgress[0];
            $levelId = (int)$userProgress[1];
        } else {
            Log::info("[" . $userNumber . "] Failed to retrieve user progress");
            return "Error internal chatbot, gagal mendapatkan data pengguna dari database";
        }

        // instantiate level and unit model
        $learningUnit = LearningUnit::findBy('sortId', $learningUnitId);
        $level = Level::where('unitId', $learningUnit->id)
                        ->where('sortId', $levelId)
                        ->first();

        Log::info("[" . $userNumber . "] Level retrieved");

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

        $message .= "Jika sudah selesai menyimak materi, ketik *Lanjut* untuk menjawab pertanyaan atau *Keluar* untuk kembali ke Main Menu! Tenang aja, progres belajarmu akan tersimpan kok!";

        # change menu location to questionPrompt
        $this->changeMenuLocation($userNumber, 'questionPrompt');

        # set the currentGrade of user to store the number of questions of this level
        $numberOfQuestions = $level->questions()->count();
        $userData->currentGrade = '0/' . strval($numberOfQuestions);
        $userData->save();

        Log::info("[" . $userNumber . "] Send level materials");

        return $message;
    }

    private function giveUserQuestion($userNumber, $questionIndex, $inputMessage = null) {
        if(strtolower($inputMessage) == "keluar") { # if user quit learning menu
            # change menu location to mainMenu
            return $this->backToMainMenu($userNumber);
        }

        # get user progress
        Log::info("[" . $userNumber . "] Query level based on user progress");
        $userData = User::find($userNumber);
        $learningUnitId = 0;
        $levelId = 0;

        if (isset($userData->progress)) {
            $userProgress = $userData->progress;

            $isCompleted = $this->isProgressCompleted($userProgress, $userNumber);
            if($isCompleted) {
                return $isCompleted;
            }

            $userProgress = explode('-', $userProgress);
            $learningUnitId = (int)$userProgress[0];
            $levelId = (int)$userProgress[1];
        }

        // instantiate level and unit model
        $learningUnit = LearningUnit::findBy('sortId', $learningUnitId);
        $level = Level::where('unitId', $learningUnit->id)
                        ->where('sortId', $levelId)
                        ->first();

        Log::info("[" . $userNumber . "] Level retrieved");

        $message = '';

        Log::info("[" . $userNumber . "] Query question " . strval($questionIndex+1));
        $questions = $level->questions()->orderBy('id', 'asc')->get();
        $question = $questions->get($questionIndex) ?? null; # get question at requested index
        if($question != null) {
            Log::info("[" . $userNumber . "] Question " . strval($questionIndex+1) . " was retrieved");
            if($questionIndex == 0) {
                $message .= "Jawab quiz berikut untuk mengevaluasi pemahaman Anda!!|";
            }

            # form a question message
            $question_msg = "Quiz nomor " . strval($questionIndex+1) . ":\n" . $question->question;
            # add options to question message if the type multiple choice
            if($question->type == 'Multiple Choice') {
                $question_msg .= "\nA. " . $question->optionA;
                $question_msg .= "\nB. " . $question->optionB;
                $question_msg .= "\nC. " . $question->optionC;
                $question_msg .= "\n\nJawab dengan 'A', 'B', atau 'C'!";
            } else {
                $question_msg .= "\n\nJawab pertanyaan di atas!";
            }

            # attach question to the message and log
            $message .= $question_msg;
            Log::info("[" . $userNumber . "] Send question to user: \n" . $question_msg);

            # change menu location to questionEval-{questionIndex}
            $this->changeMenuLocation($userNumber, 'questionEval-' . strval($questionIndex));
        } else {
            Log::info("[" . $userNumber . "] Failed to retrived the question from DB");
        }

        return $message;
    }

    private function handleUserAnswer($inputMessage, $userNumber, $questionIndex = 0) {

        # get user progress
        Log::info("[" . $userNumber . "] Query level based on user progress");
        $userData = User::find($userNumber);
        $learningUnitId = 0;
        $levelId = 0;

        if (isset($userData->progress)) {
            $userProgress = $userData->progress;

            $isCompleted = $this->isProgressCompleted($userProgress, $userNumber);
            if($isCompleted) {
                return $isCompleted;
            }

            $userProgress = explode('-', $userProgress);
            $learningUnitId = (int)$userProgress[0];
            $levelId = (int)$userProgress[1];
        }

        // instantiate level and unit model
        $learningUnit = LearningUnit::findBy('sortId', $learningUnitId);
        $level = Level::where('unitId', $learningUnit->id)
                        ->where('sortId', $levelId)
                        ->first();
        
        Log::info("[" . $userNumber . "] Level retrieved");

        Log::info("[" . $userNumber . "] Query answer of question " . strval($questionIndex+1));
        $questions = $level->questions()->orderBy('id', 'asc')->get();
        $question = $questions->get($questionIndex) ?? null; # get question at requested index
        
        if($question != null) {

            Log::info("[" . $userNumber . "] Answer of question " . strval($questionIndex+1) . " was retrieved");

            $answer = $question->answer;
            if($answer != null) {
                $answer = strtolower($answer);
                $evaluation = false;
                $userAnswer = strtolower($inputMessage);

                if($question->type == 'Multiple Choice') {
                    # evaluating answer of multiple choice question
                    $answerOption = $answer;
                    $answerExact = '';

                    switch($answerOption) {
                        case "a":
                            $answerExact = $question->optionA;
                            break;
                        case "b":
                            $answerExact = $question->optionB;
                            break;
                        case "c":
                            $answerExact = $question->optionC;
                            break;
                        default:
                            $answerExact = '';
                    }

                    Log::info("[" . $userNumber . "] User's answer: " . $userAnswer);
                    Log::info("[" . $userNumber . "] Correct answer: " . $answerOption . ". " . $answerExact);

                    if($userAnswer == $answerOption || $userAnswer == strtolower($answerExact)) {
                        $evaluation = true;
                    }
                } else {
                    # evaluating answer of essay question
                    if($userAnswer == $answer) {
                        $evaluation = true;
                    }
                }

                $message = "";

                $currentGrade = explode('/', $userData->currentGrade);

                $evaluation_msg = '';
                if($evaluation) {
                    # increase the correct answer in currentGrade
                    $currentCorrectAnswers = intval($currentGrade[0]);
                    $currentCorrectAnswers++;
                    $userData->currentGrade = strval($currentCorrectAnswers) . '/' . $currentGrade[1];
                    $userData->save();

                    $evaluation_msg = "Selamat, jawaban Anda *benar*.";
                } else {
                    $evaluation_msg = "Yah, jawaban Anda *salah*.";
                }

                Log::info("[" . $userNumber . "] Sending evaluation message: \n" . $evaluation_msg);
                $message .= $evaluation_msg . "|";

                # increase questionIndex after evaluating the answer
                $questionIndex++;

                $numberOfQuestions = intval($currentGrade[1]);
                if($questionIndex < $numberOfQuestions) {
                    # send the next question if any
                    Log::info("[" . $userNumber . "] Proceed to the next question");
                    $message .= $this->giveUserQuestion($userNumber, $questionIndex);
                } else {
                    # grade all user answers in the current level
                    Log::info("[" . $userNumber . "] Grade user answers for unit " . strval($learningUnitId) . " - level " . strval($levelId));
                    $currentGrade = explode('/', $userData->currentGrade);
                    $correctAnswers = intval($currentGrade[0]);
                    $score = ($correctAnswers / $numberOfQuestions) * 100; // return (float) score
                    $score = round($score); // round the score to integer

                    if($score >= 50) {
                        # increase the level
                        Log::info("[" . $userNumber . "] User has passed the level");
                        $userData->progress = $this->findNextActiveLevel($learningUnitId, $levelId);
                        $userData->save();

                        # upsert the user score
                        DB::table('user_grade')->upsert(
                            [
                                'user_phoneNumber' => $userData->phoneNumber,
                                'level_id' => $level->id,
                                'score' => $score,
                            ],
                            ['user_phoneNumber', 'level_id'],  // Composite unique key
                            ['score']  // Columns to update if a record with the unique keys already exists
                        );

                        $message .= "Selamat, Anda telah berhasil menyelesaikan level ini dengan *nilai: " . strval($score) . "*";
                        $message .= "\n\nketik *Lanjut* untuk melanjutkan ke level berikutnya atau *Keluar* untuk kembali ke Main Menu! Tenang aja, progress kamu tidak akan hilang kok!";
                    } else {
                        Log::info("[" . $userNumber . "] User failed to pass the level");
                        $message .= "Maaf, *nilai Anda: " . strval($score) . "*; tidak lolos passing grade(*50*). Pelajarin lagi materi di level ini yuk! Semangat!";
                        $message .= "\n\nketik *Semangat* untuk mengulang level ini atau *Keluar* untuk kembali ke Main Menu! Tenang aja, progress kamu tidak akan hilang kok!";
                    }

                    Log::info("[" . $userNumber . "] Sending user grade");

                    # change menu location to levelPrompt
                    $this->changeMenuLocation($userNumber, 'levelPrompt');
                }

                return $message;
            } else {
                $message = "Error layanan internal chatbot: topik dan materi tidak ditemukan";
                Log::info("[" . $userNumber . "] " . $message);
                return $message;
            }
        } else {
            $message = "Error layanan internal chatbot: pertanyaan tidak tersedia";
            Log::info("[" . $userNumber . "] " . $message);
            return $message;
        }
    }

    private function findNextActiveLevel($currentUnitNumber, $currentLevelNumber)
    {
        $nextLevels = DB::table('levels')
                        ->join('learning_units', 'levels.unitId', '=', 'learning_units.id')
                        ->where('learning_units.sortId', '>=', $currentUnitNumber)
                        ->select('levels.*', 'levels.sortId as levelNumber', 'learning_units.sortId as unitNumber')
                        ->orderBy('unitNumber')->orderBy('levelNumber')
                        ->get();

        # find next level
        foreach ($nextLevels as $level) {
            if($level->isActive && ($level->levelNumber > $currentLevelNumber || $level->unitNumber > $currentUnitNumber)) {
                return $level->unitNumber . '-' . $level->levelNumber;
            }
        }

        # if next level was not found
        return 'completed';
    }

    private function generateLevelPrompt($learningUnit, $level)
    {
        // Base prompt for ChatGPT
        $basePrompt = "Selamat datang di *unit: {$learningUnit->sortId} - {$learningUnit->topic}*\n";
        $basePrompt .= "Saat ini, Anda berada pada *level: {$level->sortId} - {$level->topic}*\n\n";
        $basePrompt .= "Simak ringkasan materi berikut:|";
        if ($level->content != null) {
            $basePrompt .= $level->content;
        } else {
            $basePrompt .= "Mohon maaf, materi untuk level ini belum dirilis";
        }

        return $basePrompt;
    }

    private function checkUser($userNumber)
    {
        $userData = User::find($userNumber);
        $menuLocation = 'mainMenu';

        // Register new user if does not exist
        if ($userData == null) {
            Log::info("[" . $userNumber . "] Register new user");
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

    private function isProgressCompleted($userProgress, $userNumber)
    {
        if(strtolower($userProgress) == 'completed') {
            $this->changeMenuLocation($userNumber, 'promptResetProgress');
            Log::info("[" . $userNumber . "] User has completed all levels");
            $message = "Anda telah menyelesaikan semua materi pembelajaran.|";
            $message .= "Pilih opsi berikut untuk melanjutkan:\n";
            $message .= "1. Ulang materi dari awal (unit 1 - level 1)\n";
            $message .= "2. Kembali ke Main Menu";
            return $message;
        } else {
            return false;
        }
    }

    private function formatUserPhoneNumber($recipientNumber)
    {
        // only include the phone number without 'whatsapp:' text behind it
        return explode(":", $recipientNumber)[1];
    }
}
