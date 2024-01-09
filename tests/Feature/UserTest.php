<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class UserTest extends TestCase
{
  /**
   * A basic feature test example.
   */
  public function testRegisterSuccess()
  {
    $this->post('/api/users', [
      "username" => "julian",
      "password" => "julian",
      "name" => "Julian Reza"
    ])->assertStatus(201)->assertJson([
      "data" => [
        "username" => "julian",
        "name" => "Julian Reza"
      ]
    ]);
  }

  public function testRegisterFailed()
  {
    $this->post('/api/users', [
      "username" => "",
      "password" => "",
      "name" => ""
    ])->assertStatus(400)->assertJson([
      "errors" => [
        "username" => [
          "The username field is required."
        ],
        "password" => [
          "The password field is required."
        ],
        "name" => [
          "The name field is required."
        ]
      ]
    ]);
  }

  public function testRegisterUsernameAlreadyExists()
  {
    $this->testRegisterSuccess();
    $this->post('/api/users', [
      "username" => "julian",
      "password" => "julian",
      "name" => "Julian Reza"
    ])->assertStatus(400)->assertJson([
      "errors" => [
        "username" => [
          "username already registered"
        ],
      ]
    ]);
  }

  public function testLoginSuccess()
  {
    $this->seed([UserSeeder::class]);
    $this->post('/api/users/login', [
      "username" => "test",
      "password" => "test",
    ])->assertStatus(200)->assertJson([
      "data" => [
        "username" => "test",
        "name" => "test"
      ]
    ]);

    $user = User::where('username', 'test')->first();
    self::assertNotNull($user->token);
  }

  public function testLoginFailedUsernameNotFound()
  {
    $this->post('/api/users/login', [
      "username" => "test",
      "password" => "test",
    ])->assertStatus(401)->assertJson([
      "errors" => [
        "message" => [
          "username or password wrong"
        ],
      ]
    ]);
  }

  public function testLoginFailedPasswordWrong()
  {
    $this->seed([UserSeeder::class]);
    $this->post('/api/users/login', [
      "username" => "test",
      "password" => "salah",
    ])->assertStatus(401)->assertJson([
      "errors" => [
        "message" => [
          "username or password wrong"
        ],
      ]
    ]);
  }

  public function testGetSuccess()
  {
    $this->seed([UserSeeder::class]);

    $this->get('/api/users/current', [
      'Authorization' => 'test'
    ])->assertStatus(200)->assertJson([
      'data' => [
        'username' => 'test',
        'name' => 'test'
      ]
    ]);
  }

  public function testGetUnauthorized()
  {
    $this->seed([UserSeeder::class]);

    $this->get('/api/users/current')->assertStatus(401)->assertJson([
      'errors' => [
        'message' => [
          'unauthorized'
        ]
      ]
    ]);
  }

  public function testGetInvalidToken()
  {
    $this->seed([UserSeeder::class]);

    $this->get('/api/users/current', [
      'Authorization' => 'salah'
    ])->assertStatus(401)->assertJson([
      'errors' => [
        'message' => [
          'unauthorized'
        ]
      ]
    ]);
  }

  public function testUpdateNameSuccess()
  {
    $this->seed([UserSeeder::class]);
    $oldUser = User::where('username', 'test')->first();

    $this->patch('/api/users/current', [
      'password' => 'baru'
    ], [
      'Authorization' => 'test'
    ])->assertStatus(200)->assertJson([
      'data' => [
        'username' => 'test',
        'name' => 'test'
      ]
    ]);

    $newUser = User::where('username', 'test')->first();
    self::assertNotEquals($oldUser->password, $newUser->password);
  }
  public function testUpdatePasswordSuccess()
  {
    $this->seed([UserSeeder::class]);
    $oldUser = User::where('username', 'test')->first();

    $this->patch('/api/users/current', [
      'name' => 'Julian'
    ], [
      'Authorization' => 'test'
    ])->assertStatus(200)->assertJson([
      'data' => [
        'username' => 'test',
        'name' => 'Julian'
      ]
    ]);

    $newUser = User::where('username', 'test')->first();
    self::assertNotEquals($oldUser->name, $newUser->name);
  }
  public function testUpdateFailed()
  {
    $this->seed([UserSeeder::class]);
    $this->patch('/api/users/current', [
      'name' => 'Lorem, ipsum dolor sit amet consectetur adipisicing elit. Quis totam dolorum suscipit saepe voluptatem vel ex maiores distinctio nihil et laboriosam quia nesciunt pariatur, modi laudantium? A, rem dicta non nulla accusamus distinctio, reprehenderit expedita possimus explicabo fugiat, molestiae architecto? A aliquam eligendi voluptate omnis nesciunt impedit aperiam optio, placeat possimus velit recusandae alias quaerat beatae dolor laborum blanditiis quibusdam maiores qui tempore deleniti dicta? Dolorum, unde blanditiis at fugiat ipsam tempore repudiandae sapiente laboriosam neque quidem dignissimos dolores ullam recusandae maxime veritatis dolor. Corrupti omnis minus quo? Modi ex sit libero iste id delectus incidunt blanditiis culpa maiores nisi.'
    ], [
      'Authorization' => 'test'
    ])->assertStatus(400)->assertJson([
      'errors' => [
        'name' => [
          'The name field must not be greater than 100 characters.'
        ]
      ]
    ]);
  }
}
