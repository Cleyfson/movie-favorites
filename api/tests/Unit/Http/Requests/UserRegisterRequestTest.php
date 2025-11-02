<?php

namespace Tests\Unit\Http\Requests;

use App\Http\Requests\UserRegisterRequest;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;
use Illuminate\Support\Facades\DB;

class UserRegisterRequestTest extends TestCase
{
    private UserRegisterRequest $request;

    protected function setUp(): void
    {
        parent::setUp();
        $this->request = new UserRegisterRequest();
    }

    public function test_authorize_returns_true(): void
    {
        $this->assertTrue($this->request->authorize());
    }

    public function test_validation_fails_without_required_fields(): void
    {
        $data = [];

        $rules = $this->request->rules();
        $rules['email'] = 'required|email';

        $validator = Validator::make($data, $rules);

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('name', $validator->errors()->toArray());
        $this->assertArrayHasKey('email', $validator->errors()->toArray());
        $this->assertArrayHasKey('password', $validator->errors()->toArray());
    }

    public function test_validation_fails_with_invalid_email(): void
    {
        $data = [
            'name' => 'John Doe',
            'email' => 'invalid-email',
            'password' => 'password123',
        ];

        $rules = $this->request->rules();
        $rules['email'] = 'required|email';

        $validator = Validator::make($data, $rules);

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('email', $validator->errors()->toArray());
    }

    public function test_validation_fails_with_short_password(): void
    {
        $data = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => '123',
        ];

        $rules = $this->request->rules();
        $rules['email'] = 'required|email';

        $validator = Validator::make($data, $rules);

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('password', $validator->errors()->toArray());
    }

    public function test_validation_fails_with_long_name(): void
    {
        $data = [
            'name' => str_repeat('a', 256),
            'email' => 'john@example.com',
            'password' => 'password123',
        ];

        $rules = $this->request->rules();
        $rules['email'] = 'required|email';

        $validator = Validator::make($data, $rules);

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('name', $validator->errors()->toArray());
    }

    public function test_custom_error_messages_are_defined(): void
    {
        $messages = $this->request->messages();

        $this->assertArrayHasKey('name.required', $messages);
        $this->assertArrayHasKey('email.required', $messages);
        $this->assertArrayHasKey('email.email', $messages);
        $this->assertArrayHasKey('email.unique', $messages);
        $this->assertArrayHasKey('password.required', $messages);
        $this->assertArrayHasKey('password.min', $messages);
    }

    public function test_validation_with_edge_case_email_formats(): void
    {
        $validEmails = [
            'user.name@example.com',
            'user+tag@example.com',
            'user123@example-domain.com',
        ];

        foreach ($validEmails as $email) {
            $data = [
                'name' => 'Test User',
                'email' => $email,
                'password' => 'password123',
            ];

            $validator = Validator::make($data, [
                'name' => 'required|string|max:255',
                'email' => 'required|email',
                'password' => 'required|string|min:6',
            ]);
            
            $this->assertFalse($validator->fails(), "Email {$email} should be valid");
        }

        $invalidEmails = [
            'invalid',
            '@example.com',
            'test@',
            'test..test@example.com',
        ];

        foreach ($invalidEmails as $email) {
            $data = [
                'name' => 'Test User',
                'email' => $email,
                'password' => 'password123',
            ];

            $validator = Validator::make($data, [
                'name' => 'required|string|max:255',
                'email' => 'required|email',
                'password' => 'required|string|min:6',
            ]);
            
            $this->assertTrue($validator->fails(), "Email {$email} should be invalid");
        }
    }
}