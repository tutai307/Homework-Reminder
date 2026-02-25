<?php

namespace Tests\Feature;

use App\Models\ClassModel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AITimetableTest extends TestCase
{
    /**
     * Test that the AI import route is protected by auth.
     */
    public function test_ai_import_route_requires_auth()
    {
        $class = ClassModel::factory()->create();
        $response = $this->postJson(route('teacher.timetables.import-image', $class), [
            'image' => 'data:image/png;base64,mock'
        ]);

        $response->assertStatus(401);
    }

    /**
     * Test that a teacher can access their class AI import.
     */
    public function test_teacher_can_access_ai_import()
    {
        $teacher = User::factory()->create(['role' => 'teacher']);
        $class = ClassModel::factory()->create();
        $teacher->classes()->attach($class);

        $this->actingAs($teacher);

        $response = $this->postJson(route('teacher.timetables.import-image', $class), [
            'image' => 'data:image/png;base64,mock'
        ]);

        // It might be 422 because the mock image is invalid for AI, but not 403
        $this->assertNotEquals(403, $response->getStatusCode());
    }
}
