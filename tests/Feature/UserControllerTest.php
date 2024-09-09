<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;

class UserControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Create sample users for testing
        User::factory()->create([
            'phoneNumber' => '123-456-7890',
            'name' => 'User 1',
            'menuLocation' => 'mainMenu',
            'progress' => '1-1',
        ]);
        User::factory()->create([
            'phoneNumber' => '987-654-3210',
            'name' => 'User 2',
            'menuLocation' => 'mainMenu',
            'progress' => '1-1',
        ]);
        User::factory()->create([
            'phoneNumber' => '555-555-5555',
            'name' => 'User 3',
            'menuLocation' => 'mainMenu',
            'progress' => '1-1',
        ]);
    }

    /** Test Cases for GET /kelolaPengguna */
    public function testShowUsers()
    {
        // Send a GET request to the showUsers route
        $response = $this->withoutMiddleware()->get(route('kelolaPengguna'));

        // Assert that the response status is 200 (OK)
        $response->assertStatus(200);

        // Assert that the correct view is returned
        $response->assertViewIs('admin.layouts.kelolaPengguna');

        // Assert that the view receives the correct data (all users)
        $response->assertViewHas('users', function ($users) {
            return $users->count() === 3;
        });

        // Check that the users are displayed in the response
        $response->assertSeeText('User 1');
        $response->assertSeeText('123-456-7890');

        $response->assertSeeText('User 2');
        $response->assertSeeText('987-654-3210');

        $response->assertSeeText('User 3');
        $response->assertSeeText('555-555-5555');
    }
}
