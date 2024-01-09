<?php

namespace Tests\Feature;

use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ContactTest extends TestCase
{
  /**
   * A basic feature test example.
   */
  public function testCreateSuccess()
  {
    $this->seed([UserSeeder::class]);
    $this->post('/api/contacts', [
      'first_name' => 'Julian',
      'last_name' => 'Reza',
      'email' => 'julianreza@gmail.com',
      'phone' => '6280123456789',
    ], ['Authorization' => 'test'])->assertStatus(201)->assertJson([
      'data' => [
        'first_name' => 'Julian',
        'last_name' => 'Reza',
        'email' => 'julianreza@gmail.com',
        'phone' => '6280123456789',
      ]
    ]);
  }
  public function testCreateFailed()
  {
    $this->seed([UserSeeder::class]);
    $this->post('/api/contacts', [
      'first_name' => '',
      'last_name' => 'Reza',
      'email' => 'julianreza',
      'phone' => '6280123456789',
    ], ['Authorization' => 'test'])->assertStatus(400)->assertJson([
      'errors' => [
        'first_name' => ['The first name field is required.'],
        'email' => ['The email field must be a valid email address.'],
      ]
    ]);
  }

  public function testCreateUnauthorized()
  {
    $this->seed([UserSeeder::class]);
    $this->post('/api/contacts', [
      'first_name' => '',
      'last_name' => 'Reza',
      'email' => 'julianreza',
      'phone' => '6280123456789',
    ], ['Authorization' => 'salah'])->assertStatus(401)->assertJson([
      'errors' => [
        'message' => ['unauthorized'],
      ]
    ]);
  }
}
