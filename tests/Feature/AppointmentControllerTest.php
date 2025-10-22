<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Enums\AppointmentStatus;
use App\Repositories\AppointmentRepository;
use Mockery;
use PHPUnit\Framework\Attributes\Test;

class AppointmentControllerTest extends TestCase
{
    protected $mockRepo;

    protected function setUp(): void
    {
        parent::setUp();

        // Mock AppointmentRepository
        $this->mockRepo = Mockery::mock(AppointmentRepository::class);
        $this->app->instance(AppointmentRepository::class, $this->mockRepo);
    }

    #[Test]
    public function can_create_appointment()
    {
        $data = [
            'visitor_name' => 'John Doe',
            'nric_passport' => 'S1234567D',
            'phone_number' => '0123456789',
            'email' => 'john@example.com',
            'purpose' => 'Meeting',
            'personal_in_charge' => 'Staff A',
            'date' => '2025-10-25',
            'start_time' => '10:00',
            'end_time' => '11:00',
        ];

        $appointmentMock = (object) array_merge($data, [
            'id' => 1,
            'status' => AppointmentStatus::Pending
        ]);

        $this->mockRepo
             ->shouldReceive('create')
             ->once()
             ->with($data)
             ->andReturn($appointmentMock);

        $response = $this->postJson('/api/appointments', $data);

        $response->assertStatus(201)
                 ->assertJsonFragment(['visitor_name' => 'John Doe']);
    }

    #[Test]
    public function can_show_appointment()
    {
        $appointmentMock = (object)[
            'id' => 1,
            'visitor_name' => 'John Doe',
            'status' => AppointmentStatus::Pending
        ];

        $this->mockRepo
             ->shouldReceive('find')
             ->once()
             ->with(1)
             ->andReturn($appointmentMock);

        $response = $this->getJson('/api/appointments/1');

        $response->assertStatus(200)
                 ->assertJsonFragment(['visitor_name' => 'John Doe']);
    }

    #[Test]
    public function can_update_appointment()
    {
        $data = [
            'visitor_name' => 'John Doe Updated',
            'phone_number' => '0987654321'
        ];

        $appointmentMock = (object) array_merge($data, [
            'id' => 1,
            'status' => AppointmentStatus::Pending
        ]);

        $this->mockRepo
             ->shouldReceive('update')
             ->once()
             ->with(1, $data)
             ->andReturn($appointmentMock);

        $response = $this->putJson('/api/appointments/1', $data);

        $response->assertStatus(200)
                 ->assertJsonFragment(['visitor_name' => 'John Doe Updated']);
    }

    #[Test]
    public function can_cancel_appointment()
    {
        $appointmentMock = (object)[
            'id' => 1,
            'visitor_name' => 'John Doe',
            'status' => AppointmentStatus::Pending
        ];

        // 模拟 find 返回预约对象
        $this->mockRepo
             ->shouldReceive('find')
             ->once()
             ->with(1)
             ->andReturn($appointmentMock);

        // 模拟 update 改变状态
        $this->mockRepo
             ->shouldReceive('update')
             ->once()
             ->with(1, ['status' => AppointmentStatus::Cancelled])
             ->andReturn((object) array_merge((array)$appointmentMock, ['status' => AppointmentStatus::Cancelled]));

        $response = $this->patchJson('/api/appointments/1/cancel');

        $response->assertStatus(200)
                 ->assertJsonFragment(['status' => AppointmentStatus::Cancelled]);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
