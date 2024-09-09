<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\LearningUnit;
use App\Models\Level;
use App\Models\Question;
use App\Http\Middleware\CheckFirebaseRole;
use Illuminate\Support\Facades\Log;

class LevelControllerTest extends TestCase
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

    /** Test Cases for GET /updateLevel/{levelId} */
    public function testShowLevelByIdWithValidLevel()
    {
        // Get the first level created in the setup
        $level = Level::first();
    
        // Simulate a GET request to the showLevelById method
        $response = $this->withoutMiddleware(CheckFirebaseRole::class)
                         ->get(route('units.levels.show', ['levelId' => $level->id]));
    
        // Assert that the response status is 200 (OK)
        $response->assertStatus(200);
    
        // Assert that the correct view is returned
        $response->assertViewIs('admin.layouts.updateLevel');
    
        // Assert that the view has the correct level and associated questions
        $response->assertViewHas('level', $level);
        $response->assertViewHas('questions', $level->questions);
    }

    public function testShowLevelByIdWithNonExistentLevel()
    {
        // Simulate a GET request for a non-existent level
        $response = $this->withoutMiddleware(CheckFirebaseRole::class)
                         ->get(route('units.levels.show', ['levelId' => 999]));
    
        // Assert that the response status is 404 (Not Found)
        $response->assertStatus(404);
    
        // Assert that the response contains the correct JSON message
        $response->assertJson([
            'message' => 'Level not found',
        ]);
    }

    /** Test Cases for PUT /updateLevel/{levelId} */
    public function testUpdateLevelWithValidData()
    {
        $level = Level::first();

        // Prepare valid request data
        $data = [
            'topic' => 'Updated Topic',
            'content' => 'Updated Content',
            'videoLink' => 'https://www.example.com/video',
            'questionId1' => 1,
            'category1' => 'Essay',
            'editableQuestionEssay1' => 'Updated Essay Question',
            'editableAnswer1' => 'Updated Answer',
        ];

        // Simulate a PUT request
        $response = $this->withoutMiddleware()->put(route('units.levels.update', ['levelId' => $level->id]), $data);

        // Assert that the response is 200 OK
        $response->assertStatus(200);

        // Assert that the level was updated in the database
        $this->assertDatabaseHas('levels', [
            'id' => $level->id,
            'topic' => 'Updated Topic',
            'content' => 'Updated Content',
        ]);

        // Assert that the updated essay question was saved to the database
        $this->assertDatabaseHas('questions', [
            'question' => 'Updated Essay Question',
            'answer' => 'Updated Answer',
            'type' => 'Essay',
            'levelId' => $level->id,
        ]);

        // Check that the view contains the updated level and questions
        $response->assertViewIs('admin.layouts.updateLevel');
        $response->assertViewHas('level', function ($viewLevel) use ($level) {
            return $viewLevel->id === $level->id && $viewLevel->topic === 'Updated Topic';
        });
    }

    public function testUpdateLevelWithInvalidData()
    {
        $level = Level::first();

        // Prepare invalid request data
        $data = [
            'topic' => '', // Invalid because topic is required
            'content' => 'Updated Content',
            'videoLink' => 'not-a-valid-url', // Invalid URL
            'questionId1' => 1,
            'category1' => 'Essay',
        ];

        // Simulate a PUT request with invalid data
        $response = $this->withoutMiddleware()->put(route('units.levels.update', ['levelId' => $level->id]), $data);

        // Assert that the response redirects back to the update page with validation errors
        $response->assertStatus(302);
        $response->assertSessionHasErrors(['topic', 'videoLink']);

        // Assert that the level was not updated in the database
        $this->assertDatabaseMissing('levels', [
            'topic' => 'Updated Topic', // The topic should not have been updated
        ]);
    }
}
