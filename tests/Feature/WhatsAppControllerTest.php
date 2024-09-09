<?php

namespace Tests\Feature;

use Tests\TestCase;
use Mockery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use App\Http\Controllers\WhatsAppController;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\App;
use App\Models\LearningUnit;
use App\Models\Level;
use App\Models\User;
use App\Models\Question;
use Illuminate\Support\Facades\DB;

class WhatsAppControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('logging.default', 'null');

        // Create a learning unit and level
        $learningUnit = LearningUnit::factory()->create(['topic' => 'sample topic', 'sortId' => 1]);

        // Create levels
        for($i = 1; $i <= 3; $i++) {
            $level = Level::factory()->create([
                'unitId' => $learningUnit->id,
                'sortId' => $i,
                'isActive' => true,
            ]);

            // Create questions for each level
            for($j = 1; $j <= 2; $j++) {
                // Multiple Choice
                Question::factory()->create([
                    'question' => 'sample question',
                    'answer' => 'A',
                    'type' => 'Multiple Choice',
                    'optionA' => 'correct answer',
                    'optionB' => 'incorrect answer 1',
                    'optionC' => 'incorrect answer 2',
                    'levelId' => $level->id,
                ]);
            }
            // Essay
            Question::factory()->create([
                'question' => 'sample question',
                'answer' => 'correct answer',
                'type' => 'Multiple Choice',
                'levelId' => $level->id,
            ]);
        }
    }

    # Test Cases for Menu 1: showLearningMenu
    public function testShowLearningMenuNormalCase()
    {
        $userNumber = '+1 12345';
        $inputMessage = 'some message';

        // Create a user using factory with progress
        User::factory()->create([
            'phoneNumber' => $userNumber,  // assuming this is the user number you're using
            'progress' => '1-1',  // progress format: learningUnitId-levelId
            'menuLocation' => 'mainMenu',
        ]);

        // Spy on the controller's methods (if needed)
        $controller = Mockery::mock(WhatsappController::class)->makePartial();
        $controller->shouldReceive('isProgressCompleted')->andReturn(false);  // For this test, we assume progress is not complete

        // Directly call the controller method with arguments
        $result = $controller->showLearningMenu($inputMessage, $userNumber);

        // Assertions
        $this->assertNotEmpty($result);
        $this->assertStringContainsString('Jika sudah selesai menyimak materi', $result);

        // Assert that the user's progress and grade were updated
        $updatedUser = User::find($userNumber);
        $this->assertEquals('0/3', $updatedUser->currentGrade);
    }

    public function testShowLearningMenuUserQuits()
    {
        $userNumber = '+2 12345';
        $inputMessage = 'Keluar';

        // Mock the WhatsappController class partially
        $controllerMock = Mockery::mock(WhatsappController::class)->makePartial();

        // Mock the backToMainMenu method to simulate user quitting
        $controllerMock->shouldReceive('backToMainMenu')
            ->with($userNumber)
            ->once()
            ->andReturn('Returning to main menu');

        // Log should be available since this is a feature test
        Log::shouldReceive('info')->once();  // Optional: Mock logging

        // Call the showLearningMenu method with 'keluar' input
        $result = $controllerMock->showLearningMenu($inputMessage, $userNumber);

        // Assert the result matches the expected behavior
        $this->assertEquals('Returning to main menu', $result);
    }

    public function testShowLearningMenuCompleted()
    {
        $userNumber = '+3 12345';
        $inputMessage = 'some message';

        // Create a user using factory with progress
        User::factory()->create([
            'phoneNumber' => $userNumber,  // assuming this is the user number you're using
            'progress' => 'completed',  // all levels were already completed
            'menuLocation' => 'mainMenu',
        ]);

        // Spy on the controller's methods (if needed)
        $controller = Mockery::mock(WhatsappController::class)->makePartial();
        $controller->shouldReceive('isProgressCompleted')->andReturn('Anda telah menyelesaikan semua materi pembelajaran.');

        // Directly call the controller method with arguments
        $result = $controller->showLearningMenu($inputMessage, $userNumber);

        // Assertions
        $this->assertNotEmpty($result);
        $this->assertStringContainsString('Anda telah menyelesaikan semua materi pembelajaran.', $result);
    }

    # Test Cases for Menu 1: giveUserQuestion
    public function testGiveUserQuestionNormalCase()
    {
        $userNumber = '+4 12345';
        $inputMessage = 'Lanjut';

        // Create a user using factory with progress
        User::factory()->create([
            'phoneNumber' => $userNumber,  // assuming this is the user number you're using
            'progress' => '1-1',  // progress format: learningUnitId-levelId
            'menuLocation' => 'questionPrompt',
        ]);

        for($questionIndex = 0; $questionIndex < 3; $questionIndex++) {
    
            // Spy on the controller's methods (if needed)
            $controller = Mockery::mock(WhatsappController::class)->makePartial();
            $controller->shouldReceive('isProgressCompleted')->andReturn(false);  // For this test, we assume progress is not complete
    
            // Directly call the controller method with arguments
            $result = $controller->giveUserQuestion($userNumber, $questionIndex, $inputMessage);
    
            // Assertions
            $this->assertNotEmpty($result);
            $this->assertStringContainsString(('Quiz nomor ' . strval($questionIndex+1)), $result);
    
            // Assert that the user's menu location was updated
            $updatedUser = User::find($userNumber);
            $this->assertEquals(('questionEval-' . strval($questionIndex)), $updatedUser->menuLocation);
        }
    }

    public function testGiveUserQuestionUserQuits()
    {
        $userNumber = '+5 12345';
        $inputMessage = 'Keluar';
        $questionIndex = 1;

        // Create a user using factory with progress
        User::factory()->create([
            'phoneNumber' => $userNumber,  // assuming this is the user number you're using
            'progress' => '1-1',  // progress format: learningUnitId-levelId
            'menuLocation' => 'questionPrompt',
        ]);

        // Mock the WhatsappController class partially
        $controllerMock = Mockery::mock(WhatsappController::class)->makePartial();

        // Mock the backToMainMenu method to simulate user quitting
        $controllerMock->shouldReceive('backToMainMenu')
            ->with($userNumber)
            ->once()
            ->andReturn('Returning to main menu');

        // Call the showLearningMenu method with 'keluar' input
        $result = $controllerMock->giveUserQuestion($userNumber, $questionIndex, $inputMessage);

        // Assert the result matches the expected behavior
        $this->assertEquals('Returning to main menu', $result);
    }

    public function testGiveUserQuestionCompleted()
    {
        $userNumber = '+6 12345';
        $inputMessage = 'some message';
        $questionIndex = 0;

        // Create a user using factory with progress
        User::factory()->create([
            'phoneNumber' => $userNumber,  // assuming this is the user number you're using
            'progress' => 'completed',  // all levels were already completed
            'menuLocation' => 'questionPrompt',
        ]);

        // Spy on the controller's methods (if needed)
        $controller = Mockery::mock(WhatsappController::class)->makePartial();
        $controller->shouldReceive('isProgressCompleted')->andReturn('Anda telah menyelesaikan semua materi pembelajaran.');

        // Directly call the controller method with arguments
        $result = $controller->giveUserQuestion($userNumber, $questionIndex, $inputMessage);

        // Assertions
        $this->assertNotEmpty($result);
        $this->assertStringContainsString('Anda telah menyelesaikan semua materi pembelajaran.', $result);
    }

    # Test Cases for Menu 1: handleUserAnswer
    public function testHandleUserAnswerNormalCase()
    {
        for($i = 0; $i < 2; $i++) {
            # i = 0 = evaluate correct answers
            # i = 1 = evaluate incorrect answers
            for($questionIndex = 0; $questionIndex < 3; $questionIndex++) {
                // Create a user using factory with progress
                $userNumber = '+7 12345' . strval($i) . strval($questionIndex);
                User::factory()->create([
                    'phoneNumber' => $userNumber,
                    'progress' => '1-1',
                    'menuLocation' => 'questionEval-' . strval($questionIndex),
                    'currentGrade' => '0/3',
                ]);
    
                $inputMessage = 'A';

                if($i == 1) {
                    // set inputMessage to mimic incorrect answers
                    $inputMessage = 'C';
                }
    
                if($i == 0 && $questionIndex == 2) {
                    // for correct answers evaluation
                    // change input to correct answer for essay question (index 2)
                    $inputMessage = 'correct answer';
                }
    
                // Spy on the controller's methods (if needed)
                $controller = Mockery::mock(WhatsappController::class)->makePartial();
                $controller->shouldReceive('isProgressCompleted')->andReturn(false);
    
                // Directly call the controller method with arguments
                $result = $controller->handleUserAnswer($inputMessage, $userNumber, $questionIndex);

                if($i == 0) {
                    // Assertions of correct answers
                    $this->assertNotEmpty($result);
                    $this->assertStringContainsString('Selamat, jawaban Anda *benar*', $result);
                    $updatedUser = User::find($userNumber);
                    
                    // The user's grade should be increased by 1
                    $this->assertEquals('1/3', $updatedUser->currentGrade);
                } else {
                    // Assertions of incorrect answers
                    $this->assertNotEmpty($result);
                    $this->assertStringContainsString('Yah, jawaban Anda *salah*', $result);
                    $updatedUser = User::find($userNumber);

                    // The user's grade should not be changed
                    $this->assertEquals('0/3', $updatedUser->currentGrade);
                }
            }
        }
    }

    public function testHandleUserAnswerLevelUp()
    {
        // Create a user using factory with progress
        $userNumber = '+8 12345';
        $questionIndex = 2;
        $inputMessage = 'correct answer'; // answer for last question
        User::factory()->create([
            'phoneNumber' => $userNumber,
            'progress' => '1-1',
            'menuLocation' => 'questionEval-' . strval($questionIndex),
            'currentGrade' => '1/3', // current score: 33%
        ]);

        // Spy on the controller's methods (if needed)
        $controller = Mockery::mock(WhatsappController::class)->makePartial();
        $controller->shouldReceive('isProgressCompleted')->andReturn(false);

        // Directly call the controller method with arguments
        $result = $controller->handleUserAnswer($inputMessage, $userNumber, $questionIndex);

        // Assertions of level up
        $this->assertNotEmpty($result);
        $this->assertStringContainsString('Selamat, Anda telah berhasil menyelesaikan level ini', $result);
    }

    public function testHandleUserAnswerFailedToLevelUp()
    {
        // Create a user using factory with progress
        $userNumber = '+9 12345';
        $questionIndex = 2;
        $inputMessage = 'incorrect answer'; // incorrect answer for last question
        User::factory()->create([
            'phoneNumber' => $userNumber,
            'progress' => '1-1',
            'menuLocation' => 'questionEval-' . strval($questionIndex),
            'currentGrade' => '1/3', // current score: 33%
        ]);

        // Spy on the controller's methods (if needed)
        $controller = Mockery::mock(WhatsappController::class)->makePartial();
        $controller->shouldReceive('isProgressCompleted')->andReturn(false);

        // Directly call the controller method with arguments
        $result = $controller->handleUserAnswer($inputMessage, $userNumber, $questionIndex);

        // Assertions of failed to level up
        $this->assertNotEmpty($result);
        $this->assertStringContainsString('tidak lolos passing grade(*50*)', $result);
    }

    # Test Cases for Menu 2: showPitchingMenu
    public function testShowPitchingMenuNameAvailable()
    {
        $userNumber = '+10 12345';
        $user = User::factory()->create([
            'phoneNumber' => $userNumber,
            'progress' => '1-1',
            'menuLocation' => 'mainMenu',
            'name' => 'Alice',
        ]);

        // Mock the WhatsappController class partially
        $controllerMock = Mockery::mock(WhatsappController::class)->makePartial();

        // Mock the enterPitchingSession method
        $controllerMock->shouldReceive('enterPitchingSession')
            ->with($userNumber, $user->name)
            ->once()
            ->andReturn('Entering pitching session');

        // Call the showPitchingMenu method
        $result = $controllerMock->showPitchingMenu($userNumber);

        // Assert the result matches the expected behavior
        $this->assertEquals('Entering pitching session', $result);
    }

    public function testShowPitchingMenuNameUnavailable()
    {
        $userNumber = '+11 12345';
        User::factory()->create([
            'phoneNumber' => $userNumber,
            'progress' => '1-1',
            'menuLocation' => 'mainMenu',
            'name' => null,
        ]);

        // Mock the WhatsappController class partially
        $controllerMock = Mockery::mock(WhatsappController::class)->makePartial();

        // Mock the changeMenuLocation method
        $controllerMock->shouldReceive('changeMenuLocation')
            ->with($userNumber, 'pitchingMenuAskName')
            ->once();

        // Call the showPitchingMenu method
        $result = $controllerMock->showPitchingMenu($userNumber);

        // Assert the result matches the expected behavior
        $this->assertStringContainsString('Siapa nama Anda?', $result);
    }

    # Test Cases for Menu 2: pitchingMenuAskName
    public function testPitchingMenuAskName()
    {
        $userNumber = '+12 12345';
        User::factory()->create([
            'phoneNumber' => $userNumber,
            'progress' => '1-1',
            'menuLocation' => 'mainMenu',
            'name' => null,
        ]);

        $inputMessage = 'Alice';

        // Mock the WhatsappController class partially
        $controllerMock = Mockery::mock(WhatsappController::class)->makePartial();

        // Call the pitchingMenuAskName method
        $result = $controllerMock->pitchingMenuAskName($inputMessage, $userNumber);

        // Assert the result matches the expected behavior
        $this->assertNotEmpty($result);
        $this->assertStringContainsString('Anda telah masuk ke sesi pitching', $result);
        
        // The user's name should not be changed
        $updatedUser = User::find($userNumber);
        $this->assertEquals($inputMessage, $updatedUser->name);
    }

    # Test Cases for Menu 2: pitchingSession
    public function testPitchingSessionOnProgress()
    {
        $mockResponse = 'What kind of business do you have?';
        
        // Input values
        $inputMessage = 'User input message';
        $userNumber = '+13 12345';

        // Mock the HTTP request
        Http::fake([
            config('endpoints.pitching_service') => Http::response([
                'message' => $mockResponse,
                'mission_status' => 'ongoing',
            ], 200),
        ]);

        // Mock all logs
        Log::shouldReceive('info')->with("[" . $userNumber . "] Send received user message to pitching API")->once();
        Log::shouldReceive('info')->with("[" . $userNumber . "] Response from pitching API was retrieved")->once();
        Log::shouldReceive('info')->with("[" . $userNumber . "] Sending response from pitching API to user: " . $mockResponse)->once();

        // Call the method
        $response = (new WhatsAppController)->pitchingSession($inputMessage, $userNumber);

        // Assert the expected response
        $this->assertStringContainsString($mockResponse, $response);
    }

    public function testPitchingSessionOnSuccess()
    {
        $mockResponse = "Okay, deal! I am interested in your business";
        
        // Input values
        $inputMessage = "Let's make the deal!!";
        $userNumber = '+14 12345';

        // Output value
        $expectedResponse = $mockResponse;
        $expectedResponse .= "|Congratulations, you have *successfully* finished the mission. ";
        $expectedResponse .= "Ketik *Yes* untuk kembali ke Main Menu.";

        // Mock the HTTP request
        Http::fake([
            config('endpoints.pitching_service') => Http::response([
                'message' => $mockResponse,
                'mission_status' => 'success',
            ], 200),
        ]);

        // Mock all logs
        Log::shouldReceive('info')->with("[" . $userNumber . "] Send received user message to pitching API")->once();
        Log::shouldReceive('info')->with("[" . $userNumber . "] Response from pitching API was retrieved")->once();
        Log::shouldReceive('info')->with("[" . $userNumber . "] Sending response from pitching API to user: " . $expectedResponse)->once();

        // Mock the WhatsappController class partially
        $controllerMock = Mockery::mock(WhatsappController::class)->makePartial();

        // Mock the backToMainMenu method to simulate user quitting
        $controllerMock->shouldReceive('backToMainMenu')
            ->with($userNumber)
            ->once()
            ->andReturn('Returning to main menu');

        // Call the method
        $response = $controllerMock->pitchingSession($inputMessage, $userNumber);

        // Assert the expected response
        $this->assertStringContainsString($expectedResponse, $response);
    }

    public function testPitchingSessionOnFailed()
    {
        $mockResponse = "Sorry, I am not interested in your business";
        
        // Input values
        $inputMessage = "I am still don't know what to do";
        $userNumber = '+15 12345';

        // Output value
        $expectedResponse = $mockResponse;
        $expectedResponse .= "|Unfortunatelly, you have *failed* the mission. Don't worry, try again next!! ";
        $expectedResponse .= "Ketik *Semangat* untuk kembali ke Main Menu.";

        // Mock the HTTP request
        Http::fake([
            config('endpoints.pitching_service') => Http::response([
                'message' => $mockResponse,
                'mission_status' => 'failed',
            ], 200),
        ]);

        // Mock all logs
        Log::shouldReceive('info')->with("[" . $userNumber . "] Send received user message to pitching API")->once();
        Log::shouldReceive('info')->with("[" . $userNumber . "] Response from pitching API was retrieved")->once();
        Log::shouldReceive('info')->with("[" . $userNumber . "] Sending response from pitching API to user: " . $expectedResponse)->once();

        // Mock the WhatsappController class partially
        $controllerMock = Mockery::mock(WhatsappController::class)->makePartial();

        // Mock the backToMainMenu method to simulate user quitting
        $controllerMock->shouldReceive('backToMainMenu')
            ->with($userNumber)
            ->once()
            ->andReturn('Returning to main menu');

        // Call the method
        $response = $controllerMock->pitchingSession($inputMessage, $userNumber);

        // Assert the expected response
        $this->assertStringContainsString($expectedResponse, $response);
    }

    public function testPitchingSessionUserQuits()
    {
        $mockResponse = "Anda telah meninggalkan percakapan!";
        
        // Input values
        $inputMessage = "Keluar";
        $userNumber = '+16 12345';
        $mainMenuMessage = "Returning to main menu";

        // Output value
        $expectedResponse = $mockResponse;
        $expectedResponse .= "|" . $mainMenuMessage;

        // Mock the HTTP request
        Http::fake([
            config('endpoints.pitching_service') => Http::response([
                'message' => $mockResponse,
                'mission_status' => 'quit',
            ], 200),
        ]);

        // Mock all logs
        Log::shouldReceive('info')->with("[" . $userNumber . "] Send received user message to pitching API")->once();
        Log::shouldReceive('info')->with("[" . $userNumber . "] Response from pitching API was retrieved")->once();
        Log::shouldReceive('info')->with("[" . $userNumber . "] Sending response from pitching API to user: " . $expectedResponse)->once();

        // Mock the WhatsappController class partially
        $controllerMock = Mockery::mock(WhatsappController::class)->makePartial();

        // Mock the backToMainMenu method to simulate user quitting
        $controllerMock->shouldReceive('backToMainMenu')
            ->with($userNumber)
            ->once()
            ->andReturn($mainMenuMessage);

        // Mock the showMainMenu method to simulate user quitting
        $controllerMock->shouldReceive('showMainMenu')
            ->once()
            ->andReturn($mainMenuMessage);

        // Call the method
        $response = $controllerMock->pitchingSession($inputMessage, $userNumber);

        // Assert the expected response
        $this->assertStringContainsString($expectedResponse, $response);
    }

    public function testPitchingSessionApiError()
    {
        // Input values
        $inputMessage = "Hello";
        $userNumber = '+17 12345';

        $expectedResponse = "Error internal chatbot: gagal memulai sesi pitching.";

        // Mock the HTTP request with a failed response
        Http::fake([
            config('endpoints.pitching_service') => Http::response([], 500),
        ]);

        // Call the method
        $response = (new WhatsAppController)->pitchingSession($inputMessage, $userNumber);

        // Assert the expected response
        $this->assertEquals($expectedResponse, $response);
    }

    # Test Cases for Menu 3: showProfileMenu
    public function testShowProfileMenu()
    {
        $userNumber = '+18 12345';
        $user = User::factory()->create([
            'phoneNumber' => $userNumber,
            'progress' => '1-1',
            'menuLocation' => 'mainMenu',
            'name' => 'Alice',
            'occupation' => 'Business Owner',
        ]);

        // Mock the WhatsappController class partially
        $controllerMock = Mockery::mock(WhatsappController::class)->makePartial();

        // Mock the changeMenuLocation method
        $controllerMock->shouldReceive('changeMenuLocation')
            ->with($userNumber, 'userProfile')
            ->once();

        // Call the showProfileMenu method
        $result = $controllerMock->showProfileMenu($userNumber);

        // Assert the result matches the expected behavior
        $this->assertStringContainsString($user->name, $result);
        $this->assertStringContainsString($user->occupation, $result);
    }

    # Test Cases for Menu 3: setProfile
    public function testSetProfile()
    {
        $userNumber = '+19 12345';
        User::factory()->create([
            'phoneNumber' => $userNumber,
            'progress' => '1-1',
            'menuLocation' => 'userProfile',
            'name' => 'Alice',
            'occupation' => 'Business Owner',
        ]);

        // Mock the WhatsappController class partially
        $controllerMock = Mockery::mock(WhatsappController::class)->makePartial();

        // Call the setProfile method
        $result = $controllerMock->setProfile($userNumber);

        // Assert the result matches the expected behavior
        $this->assertStringContainsString('Masukkan nama Anda:', $result);

        // Assert that the user's menu location was updated
        $updatedUser = User::find($userNumber);
        $this->assertEquals('userProfileSetName', $updatedUser->menuLocation);
    }

    # Test Cases for Menu 3: handleUserProfileSetName
    public function testHandleUserProfileSetName()
    {
        $inputMessage = 'Bob';

        $userNumber = '+20 12345';
        User::factory()->create([
            'phoneNumber' => $userNumber,
            'progress' => '1-1',
            'menuLocation' => 'userProfileSetName',
            'name' => 'Alice',
            'occupation' => 'Business Owner',
        ]);

        // Mock the WhatsappController class partially
        $controllerMock = Mockery::mock(WhatsappController::class)->makePartial();

        // Call the handleUserProfileSetName method
        $result = $controllerMock->handleUserProfileSetName($inputMessage, $userNumber);

        // Assert the result matches the expected behavior
        $this->assertStringContainsString("Hallo, $inputMessage!! Apa pekerjaan Anda:", $result);

        // Assert that the user's menu location and name were updated
        $updatedUser = User::find($userNumber);
        $this->assertEquals('userProfileSetJob', $updatedUser->menuLocation);
        $this->assertEquals($inputMessage, $updatedUser->name);
    }

    # Test Cases for Menu 3: handleUserProfileSetJob
    public function testHandleUserProfileSetJob()
    {
        $inputMessage = 'Student';

        $userNumber = '+21 12345';
        User::factory()->create([
            'phoneNumber' => $userNumber,
            'progress' => '1-1',
            'menuLocation' => 'userProfileSetJob',
            'name' => 'Bob',
            'occupation' => 'Business Owner',
        ]);

        // Mock the WhatsappController class partially
        $controllerMock = Mockery::mock(WhatsappController::class)->makePartial();

        // Call the handleUserProfileSetJob method
        $result = $controllerMock->handleUserProfileSetJob($inputMessage, $userNumber);

        // Assert the result matches the expected behavior
        $this->assertStringContainsString("Profil berhasil diperbaharui", $result);

        // Assert that the user's menu location and occupation were updated
        $updatedUser = User::find($userNumber);
        $this->assertEquals('userProfile', $updatedUser->menuLocation);
        $this->assertEquals($inputMessage, $updatedUser->occupation);
    }

    # Test Cases for Menu 4: showAboutUs
    public function testShowAboutUs()
    {
        // Mock the WhatsappController class partially
        $controllerMock = Mockery::mock(WhatsappController::class)->makePartial();

        // Call the showAboutUs method
        $result = $controllerMock->showAboutUs();

        // Assert the result matches the expected behavior
        $this->assertStringContainsString(config('prompts.about_us'), $result);
    }
}
