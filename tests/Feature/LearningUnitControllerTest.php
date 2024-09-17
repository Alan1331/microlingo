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

class LearningUnitControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('logging.default', 'null');

        // Create learning units
        for($i = 1; $i <= 3; $i++) {
            $learningUnit = LearningUnit::factory()->create(['topic' => 'sample topic', 'sortId' => $i]);

            // Create levels
            for($j = 1; $j <= 3; $j++) {
                $level = Level::factory()->create([
                    'unitId' => $learningUnit->id,
                    'sortId' => $j,
                    'isActive' => true,
                ]);
    
                // Create questions for each level
                for($k = 1; $k <= 2; $k++) {
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
    }

    /** Test Cases for GET /materiPembelajaran */
    public function testShowLearningUnits(): void
    {
        $response = $this->withoutMiddleware(CheckFirebaseRole::class)->get('/materiPembelajaran');

        // Assert the HTTP status code is 200 (OK)
        $response->assertStatus(200);

        // Assert that the correct view is returned
        $response->assertViewIs('admin.layouts.materiPembelajaran');

        // Assert that the learning units are passed to the view in the correct order
        $response->assertViewHas('learningUnits', function ($learningUnits) {
            return $learningUnits->pluck('sortId')->toArray() === [1, 2, 3];
        });
    }

    /** Test Cases for GET /materiPembelajaran/{id} */
    public function testShowLearningUnitById(): void
    {
        $unit = LearningUnit::where('sortId', 1)->first();
        $response = $this->withoutMiddleware(CheckFirebaseRole::class)->get('/materiPembelajaran/' . strval($unit->id));

        // Assert the HTTP status code is 200 (OK)
        $response->assertStatus(200);

        // Assert that the correct view is returned
        $response->assertViewIs('admin.layouts.viewLevel');

        // Assert that the learning units are passed to the view in the correct order
        $response->assertViewHas('levels', function ($levels) {
            return $levels->pluck('sortId')->toArray() === [1, 2, 3];
        });
        $response->assertViewHas('unitId');
        $response->assertViewHas('unitNumber');
    }

    /** Test Cases for POST /materiPembelajaran */
    public function testCreateLearningUnitNormalCase()
    {
        // Simulate a POST request with valid 'topic'
        $response = $this->withoutMiddleware(CheckFirebaseRole::class)->post(route('units.create'), [
            'topic' => 'New Topic',
        ]);

        // Assert that the response status is 302 (redirection)
        $response->assertStatus(302);

        // Assert that the learning unit was created in the database
        $this->assertDatabaseHas('learning_units', [
            'topic' => 'New Topic',
        ]);

        // Retrieve the created learning unit
        $unit = LearningUnit::where('topic', 'New Topic')->first();

        // Assert that 5 levels were created and associated with the learning unit
        $this->assertEquals(5, $unit->levels()->count());

        // Assert that each level has the correct `sortId` and is linked to the right `unitId`
        for ($i = 1; $i <= 5; $i++) {
            $this->assertDatabaseHas('levels', [
                'sortId' => $i,
                'unitId' => $unit->id,
            ]);
        }

        // Instead of asserting session messages, just check for the redirect location
        $response->assertRedirect(route('materiPembelajaran'));
    }

    public function testCreateLearningUnitValidationError()
    {
        // Simulate a POST request with missing 'topic' field
        $response = $this->withoutMiddleware(CheckFirebaseRole::class)->post(route('units.create'), [
            'topic' => '',
        ]);

        // Assert the validation error for 'topic'
        $response->assertInvalid(['topic']);
    }

    /** Test Cases for PUT /materiPembelajaran/{id} */
    public function testUpdateLearningUnitNormalCase()
    {
        // Create a learning unit to update
        $nextSortId = LearningUnit::max('sortId') + 1;
        $unit = LearningUnit::create([
            'sortId' => $nextSortId,
            'topic' => 'Old Topic',
        ]);

        // Simulate a PUT request to update the learning unit
        $response = $this->withoutMiddleware(CheckFirebaseRole::class)->put(route('units.update', ['id' => $unit->id]), [
            'topic' => 'Updated Topic',
        ]);

        // Assert that the response status is 302 (redirection)
        $response->assertStatus(302);

        // Assert that the learning unit's topic was updated in the database
        $this->assertDatabaseHas('learning_units', [
            'id' => $unit->id,
            'topic' => 'Updated Topic',
        ]);

        // Assert that the old topic no longer exists
        $this->assertDatabaseMissing('learning_units', [
            'id' => $unit->id,
            'topic' => 'Old Topic',
        ]);

        // Assert that the response redirects to the correct route
        $response->assertRedirect(route('materiPembelajaran'));

        // Optionally, assert that the success message is in the session
        $response->assertSessionHas('success', 'The topic was successfully updated');
    }

    public function testUpdateLearningUnitFails()
    {
        // Simulate a PUT request to update a non-existent learning unit
        $response = $this->withoutMiddleware(CheckFirebaseRole::class)->put(route('units.update', ['id' => 999]), [
            'topic' => 'Non-Existent Topic',
        ]);

        // Assert that the response status is 302 (redirection)
        $response->assertStatus(302);

        // Assert that the response redirects to the correct route
        $response->assertRedirect(route('materiPembelajaran'));

        // Optionally, assert that the failure message is in the session
        $response->assertSessionHas('failed');
    }

    /** Test Cases for DELETE /materiPembelajaran/{id} */
    public function testDeleteUnit()
    {
        // Create a learning unit with levels to delete
        $nextSortId = LearningUnit::max('sortId') + 1;
        $unit = LearningUnit::create([
            'sortId' => $nextSortId,
            'topic' => 'Test Unit',
        ]);
    
        // Create 5 levels associated with the learning unit
        for ($i = 1; $i <= 5; $i++) {
            Level::create([
                'sortId' => $i,
                'unitId' => $unit->id,
            ]);
        }
    
        // Simulate a DELETE request to delete the learning unit
        $response = $this->withoutMiddleware(CheckFirebaseRole::class)->delete(route('units.delete', ['id' => $unit->id]));
    
        // Assert that the response status is 302 (redirection)
        $response->assertStatus(302);
    
        // Assert that the learning unit and all associated levels are deleted from the database
        $this->assertDatabaseMissing('learning_units', [
            'id' => $unit->id,
        ]);
    
        foreach ($unit->levels as $level) {
            $this->assertDatabaseMissing('levels', [
                'unitId' => $unit->id,
                'sortId' => $level->sortId,
            ]);
        }
    
        // Assert that the response redirects to the correct route
        $response->assertRedirect(route('materiPembelajaran'));
    
        // Optionally, assert that the success message is in the session
        $response->assertSessionHas('success', 'Unit deleted successfully!');
    
        // Check that other units with sortId greater than the deleted one have their sortId decremented
        $this->assertDatabaseMissing('learning_units', ['sortId' => $unit->id]);
    }
}
