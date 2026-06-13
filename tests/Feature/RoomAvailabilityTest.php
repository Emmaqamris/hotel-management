<?php
namespace Tests\Feature;
use Tests\TestCase;
class RoomAvailabilityTest extends TestCase
{
    public function test_example(): void
    {
        $response = $this->get('/');
        $response->assertRedirect();
    }
}
