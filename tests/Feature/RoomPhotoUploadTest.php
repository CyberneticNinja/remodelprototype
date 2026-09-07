<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Feature\Concerns\CreatesTestData;
use Tests\TestCase;

class RoomPhotoUploadTest extends TestCase
{
    use RefreshDatabase, CreatesTestData;

    private function beforePhoto(): UploadedFile
    {
        return new UploadedFile(
            base_path('tests/Fixtures/bedroom-bland.jpeg'), 'bedroom-bland.jpeg', 'image/jpeg', null, true
        );
    }

    private function afterPhoto(): UploadedFile
    {
        return new UploadedFile(
            base_path('tests/Fixtures/bedroom-modern.jpeg'), 'bedroom-modern.jpeg', 'image/jpeg', null, true
        );
    }

    public function test_contractor_can_upload_a_before_photo_while_unlocked(): void
    {
        Storage::fake('public');
        $contractor = $this->makeContractor();
        $client = $this->makeClient($contractor);
        $project = $this->makeProject($contractor, $client);
        $room = $this->makeRoom($project);

        $response = $this->actingAs($contractor)->post(
            route('rooms.photos.store', [$project, $room]),
            ['type' => 'before', 'photo' => $this->beforePhoto()]
        );

        $response->assertRedirect(route('rooms.show', [$project, $room]));
        $this->assertEquals(1, $room->beforePhotos()->count());
        Storage::disk('public')->assertExists($room->beforePhotos()->first()->path);
    }

    public function test_before_photo_upload_is_blocked_once_locked(): void
    {
        Storage::fake('public');
        $contractor = $this->makeContractor();
        $client = $this->makeClient($contractor);
        $project = $this->makeProject($contractor, $client);
        $room = $this->makeRoom($project);

        $this->signWorkAgreed($room, $contractor, $client);
        $this->assertTrue($room->fresh()->isEstimateLocked());

        $this->actingAs($contractor)->post(
            route('rooms.photos.store', [$project, $room]),
            ['type' => 'before', 'photo' => $this->beforePhoto()]
        )->assertForbidden();

        $this->assertEquals(0, $room->beforePhotos()->count());
    }

    public function test_after_photo_upload_is_blocked_before_work_agreed_is_complete(): void
    {
        Storage::fake('public');
        $contractor = $this->makeContractor();
        $client = $this->makeClient($contractor);
        $project = $this->makeProject($contractor, $client);
        $room = $this->makeRoom($project);

        $this->actingAs($contractor)->post(
            route('rooms.photos.store', [$project, $room]),
            ['type' => 'after', 'photo' => $this->afterPhoto()]
        )->assertForbidden();

        $this->assertEquals(0, $room->afterPhotos()->count());
    }

    public function test_after_photo_upload_is_allowed_once_work_agreed_is_complete(): void
    {
        Storage::fake('public');
        $contractor = $this->makeContractor();
        $client = $this->makeClient($contractor);
        $project = $this->makeProject($contractor, $client);
        $room = $this->makeRoom($project);

        $this->signWorkAgreed($room, $contractor, $client);

        $response = $this->actingAs($contractor)->post(
            route('rooms.photos.store', [$project, $room]),
            ['type' => 'after', 'photo' => $this->afterPhoto()]
        );

        $response->assertRedirect(route('rooms.show', [$project, $room]));
        $this->assertEquals(1, $room->afterPhotos()->count());
    }

    public function test_after_photo_upload_is_blocked_once_room_is_fully_complete(): void
    {
        Storage::fake('public');
        $contractor = $this->makeContractor();
        $client = $this->makeClient($contractor);
        $project = $this->makeProject($contractor, $client);
        $room = $this->makeRoom($project);

        $this->signWorkAgreed($room, $contractor, $client);
        $this->signCompleted($room, $contractor, $client);
        $this->assertTrue($room->fresh()->is_complete);

        $this->actingAs($contractor)->post(
            route('rooms.photos.store', [$project, $room]),
            ['type' => 'after', 'photo' => $this->afterPhoto()]
        )->assertForbidden();
    }

    public function test_client_cannot_upload_photos(): void
    {
        Storage::fake('public');
        $contractor = $this->makeContractor();
        $client = $this->makeClient($contractor);
        $project = $this->makeProject($contractor, $client);
        $room = $this->makeRoom($project);

        $this->actingAs($client)->post(
            route('rooms.photos.store', [$project, $room]),
            ['type' => 'before', 'photo' => $this->beforePhoto()]
        )->assertForbidden();
    }
}
