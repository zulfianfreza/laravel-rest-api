<?php

namespace Tests\Feature;

use App\Models\Contact;
use Database\Seeders\ContactSeeder;
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

  public function testGetSuccess()
  {
    $this->seed([UserSeeder::class, ContactSeeder::class]);

    $contact = Contact::query()->limit(1)->first();

    $this->get('/api/contacts/' . $contact->id, [
      'Authorization' => 'test'
    ])->assertStatus(200)
      ->assertJson([
        'data' => [
          'first_name' => 'test',
          'last_name' => 'test',
          'email' => 'test@julianreza.com',
          'phone' => '6281234567890',
        ]
      ]);
  }

  public function testGetNotFound()
  {
    $this->seed([UserSeeder::class, ContactSeeder::class]);

    $contact = Contact::query()->limit(1)->first();

    $this->get('/api/contacts/' . ($contact->id + 1), [
      'Authorization' => 'test'
    ])->assertStatus(404)
      ->assertJson([
        'errors' => [
          'message' => [
            'not found'
          ]
        ]
      ]);
  }
  public function testGetOtherUserContact()
  {
    $this->seed([UserSeeder::class, ContactSeeder::class]);

    $contact = Contact::query()->limit(1)->first();

    $this->get('/api/contacts/' . $contact->id, [
      'Authorization' => 'test2'
    ])->assertStatus(404)
      ->assertJson([
        'errors' => [
          'message' => [
            'not found'
          ]
        ]
      ]);
  }

  public function testUpdateSuccess()
  {
    $this->seed([UserSeeder::class, ContactSeeder::class]);
    $contact = Contact::query()->limit(1)->first();

    $this->put('/api/contacts/' . $contact->id, [
      'first_name' => 'test2',
      'last_name' => 'test2',
      'email' => 'test2@julianreza.com',
      'phone' => '6281234567890',
    ], [
      'Authorization' => 'test'
    ])->assertStatus(200)
      ->assertJson([
        'data' => [
          'first_name' => 'test2',
          'last_name' => 'test2',
          'email' => 'test2@julianreza.com',
          'phone' => '6281234567890',
        ]
      ]);
  }

  public function testUpdateValidationError()
  {
    $this->seed([UserSeeder::class, ContactSeeder::class]);
    $contact = Contact::query()->limit(1)->first();

    $this->put('/api/contacts/' . $contact->id, [
      'first_name' => '',
      'last_name' => 'test2',
      'email' => 'test2@julianreza.com',
      'phone' => '6281234567890',
    ], [
      'Authorization' => 'test'
    ])->assertStatus(400)
      ->assertJson([
        'errors' => [
          'first_name' => [
            'The first name field is required.'
          ],
        ]
      ]);
  }
}
