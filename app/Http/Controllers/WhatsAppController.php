<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\App;
use App\Models\LearningUnit;
use App\Models\Level;
use App\Models\User;
use App\Models\UserGrade;
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

    public function handleMenuLocation($menuLocationWithSubMenu, $inputMessage, $userNumber)
    {
        $menuLocationWithSubMenu = explode('-', $menuLocationWithSubMenu);
        $menuLocation = $menuLocationWithSubMenu[0]; # extract menu without submenu
        switch ($menuLocation) {
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
            default:
                return $this->handleMainMenu($inputMessage, $userNumber);
        }
    }

    public function handleMainMenu($inputMessage, $userNumber)
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

    public function handleUserProfileMenu($inputMessage, $userNumber)
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

    public function showPitchingMenu($userNumber)
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

    public function pitchingMenuAskName($inputMessage, $userNumber) {
        $userName = $inputMessage;
        $user = User::find($userNumber);
        $user->name = $userName;
        $user->save();

        return $this->enterPitchingSession($userNumber, $userName);
    }

    public function enterPitchingSession($userNumber, $userName)
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

    public function pitchingSession($inputMessage, $userNumber)
    {
        // Data for the API request
        $data = [
            'session_id' => $userNumber,
            'input' => $inputMessage,
        ];

        Log::info("[" . $userNumber . "] Send received user message to pitching API");

        // Send a POST request using Laravel's Http client with 7 attempt and 3s delay between retry
        try {
            $response = Http::retry(7,3000)->post(config('endpoints.pitching_service'), $data);
        } catch (\Exception $e) {
            Log::info("[" . $userNumber . "] Failed to retrieve response from pitching API");
            Log::info($e);
            return 'Error internal chatbot: gagal memulai sesi pitching.';
        }

        // Check for errors and return the response
        if ($response->successful()) {
            Log::info("[" . $userNumber . "] Response from pitching API was retrieved");
            $responseData = $response->json();
            $message = "🗣️: " . $responseData['message'];
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
                $message .= "|Anda telah meninggalkan percakapan!";
                $message .= "|" . $this->showMainMenu();
            }

            Log::info("[" . $userNumber . "] Sending response from pitching API to user: " . $message);
            return $message;
        }
    }

    public function setProfile($userNumber)
    {
        # change menu location to userProfileSetName to ask user their name
        $this->changeMenuLocation($userNumber, 'userProfileSetName');

        Log::info("[" . $userNumber . "] Ask user name");
        return "Masukkan nama Anda:";
    }

    public function handleUserProfileSetName($inputMessage, $userNumber)
    {
        # set user name and change menu location to userProfileSetJob to ask user their job
        $user = User::find($userNumber);
        $user->name = $inputMessage;
        $user->menuLocation = 'userProfileSetJob';
        $user->save();

        Log::info("[" . $userNumber . "] Ask user occupation");
        return "Hallo, $inputMessage!! Apa pekerjaan Anda:";
    }

    public function handleUserProfileSetJob($inputMessage, $userNumber)
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

    public function changeMenuLocation($userNumber, $menu) {
        # change menu location to mainMenu
        $user = User::find($userNumber);
        $user->menuLocation = $menu;
        return $user->save(); # return true or false
    }

    public function showAboutUs()
    {
        $message = config('prompts.about_us');

        $message .= '|' . $this->showTableOfContents();
        
        $message .= '|Ketik *Kembali* untuk kembali ke main menu';
        return $message;
    }

    public function showTableOfContents()
    {
        # Get learning units and levels to show table of contents
        $message = "*Daftar Isi Materi:*\n";
        $units = LearningUnit::all();
        foreach ($units as $unit) {
            $unitNumber = strval($unit->sortId);
            $message .= "*Unit $unitNumber*: $unit->topic\n";
            
            $levels = $unit->levels;
            $activeLevelFound = false;
            foreach ($levels as $level) {
                # Show only active levels
                if($level->isActive) {
                    $activeLevelFound = true;
                    $levelNumber = strval($level->sortId);
                    $message .= "- *Level $levelNumber*: $level->topic\n";
                }
            }
            # Give explanation if the unit has no active levels
            if(!$activeLevelFound) {
                $message .= "\t- Materi pada unit ini belum dirilis\n";
            }

            # Add space between units
            $message .= "\n";
        }

        return $message;
    }

    public function showMainMenu()
    {
        return config('prompts.main_menu');
    }

    public function backToMainMenu($userNumber)
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

    public function showProfileMenu($userNumber)
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

    public function promptResetProgress($inputMessage, $userNumber) {
        if(strval($inputMessage) == 1) {
            $user = User::find($userNumber);
            $user->progress = '1-1';
            $user->progressPercentage = 0;
            $user->menuLocation = 'levelPrompt';
            $user->save();
        } elseif(strval($inputMessage) == 2) {
            return $this->backToMainMenu($userNumber);
        }

        return $this->showLearningMenu('lanjut', $userNumber);
    }

    public function showLearningMenu($inputMessage, $userNumber)
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

        # Show table of contents for user in progress: 1-1
        if($learningUnitId == 0 && $levelId == 0) {
            # Format messages
            $message = "Selamat datang di sesi pembelejaran, di sesi ini, Anda akan mempelajari hal-hal berikut:\n";
            $message .= '|' . $this->showTableOfContents();
            $message .= "|Ketik *Lanjut* untuk memulai pembelajaran atau *Keluar* untuk kembali ke Main Menu!";
            
            # Set user progress to the first unit & level and menu location to learning menu
            $userData->progress = "1-1";
            $userData->menuLocation = "levelPrompt";
            $userData->save();

            return $message;
        }

        // instantiate level and unit model
        $learningUnit = LearningUnit::findBy('sortId', $learningUnitId);
        $level = Level::where('unitId', $learningUnit->id)
                        ->where('sortId', $levelId)
                        ->first();

        Log::info("[" . $userNumber . "] Level retrieved");

        $message = '';
        
        # generate prompt for the current level
        $prompt = $this->generateLevelPrompt($learningUnit, $level, $userData->progressPercentage);
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

    public function giveUserQuestion($userNumber, $questionIndex, $inputMessage = null) {
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

    public function handleUserAnswer($inputMessage, $userNumber, $questionIndex = 0) {

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

                    # Give chance for user if they input unvailable option
                    $multipleChoiceOptions = ['a', 'b', 'c'];
                    if(strlen($userAnswer) == 1 && (!in_array($userAnswer, $multipleChoiceOptions))) {
                        return "Opsi tidak tersedia, jawab dengan 'A', 'B', atau 'C'";
                    }

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

                    // insert the score
                    UserGrade::create([
                        'user_phoneNumber' => $userData->phoneNumber,
                        'level_id' => $level->id,
                        'score' => $score,
                    ]);

                    if($score >= 50) {
                        # increase the level
                        Log::info("[" . $userNumber . "] User has passed the level");
                        $updatedProgress = $this->findNextActiveLevel($learningUnitId, $levelId);
                        $userData->progress = $updatedProgress;
                        $userData->progressPercentage = $this->calculateProgressInPercentage($updatedProgress);
                        $userData->save();

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

    public function findNextActiveLevel($currentUnitNumber, $currentLevelNumber)
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

    public function calculateProgressInPercentage($progress)
    {
        if($progress == 'completed') {
            return 100;
        }

        # Extract current unit and level
        $progress_arr = explode('-', $progress);
        $currentUnit = intval($progress_arr[0]);
        $currentLevel = intval($progress_arr[1]);

        # Count completed levels
        $completedLevels = 0;
        for($unit = 1; $unit < $currentUnit; $unit++) {
            $unitId = LearningUnit::where('sortId', $unit)->value('id');

            if ($unitId) {
                $completedLevels += Level::where('unitId', $unitId)
                                        ->where('isActive', true)
                                        ->count();
            }
        }

        Log::info("Completed levels from previous units: " . strval($completedLevels));

        $unitId = LearningUnit::where('sortId', $currentUnit)->value('id');

        if ($unitId) {
            $completedLevels += Level::where('unitId', $unitId)
                                    ->where('isActive', true)
                                    ->where('sortId', '<', $currentLevel)
                                    ->count();
        }

        Log::info("Total of completed levels: " . strval($completedLevels));
        
        $totalLevels = Level::where('isActive', true)->count();
        $result = round(($completedLevels / $totalLevels) * 100);
        Log::info("New progress percentage: " . strval($result));

        return $result;
    }

    public function generateLevelPrompt($learningUnit, $level, $progressPercentage = -1)
    {
        // Base prompt for ChatGPT
        $basePrompt = "Selamat datang di:\n";
        $basePrompt .= "- *Unit: {$learningUnit->sortId} - {$learningUnit->topic}*\n";
        $basePrompt .= "- *Level: {$level->sortId} - {$level->topic}*\n";
        if($progressPercentage != -1) {
            $basePrompt .= "- *Progress Pembelajaran: {$progressPercentage}%*\n";
        }
        $basePrompt .= "\nSimak ringkasan materi berikut:|";
        if ($level->content != null) {
            $basePrompt .= $level->content;
        } else {
            $basePrompt .= "Mohon maaf, materi untuk level ini belum dirilis";
        }

        return $basePrompt;
    }

    public function checkUser($userNumber)
    {
        $userData = User::find($userNumber);
        $menuLocation = 'mainMenu';

        // Register new user if does not exist
        if ($userData == null) {
            Log::info("[" . $userNumber . "] Register new user");
            User::create([
                'phoneNumber' => $userNumber,
                'menuLocation' => $menuLocation,
                'progress' => '0-0',
                'progressPercentage' => '0',
            ]);
        }

        if (isset($userData->menuLocation)) {
            $menuLocation = $userData->menuLocation;
        }

        return $menuLocation;
    }

    public function isProgressCompleted($userProgress, $userNumber)
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

    public function formatUserPhoneNumber($recipientNumber)
    {
        // only include the phone number without 'whatsapp:' text behind it
        return explode(":", $recipientNumber)[1];
    }
}
