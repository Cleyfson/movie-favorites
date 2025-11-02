<?php

namespace Tests\Unit\Http\Requests;

use App\Http\Requests\UserLoginRequest;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class UserLoginRequestTest extends TestCase
{
    private UserLoginRequest $request;

    protected function setUp(): void
    {
        parent::setUp();
        $this->request = new UserLoginRequest();
    }

    public function test_authorize_returns_true(): void
    {
        $this->assertTrue($this->request->authorize());
    }

    public function test_validation_passes_with_valid_data(): void
    {
        $data = [
            'email' => 'john@example.com',
            'password' => 'password123',
        ];

        $validator = Validator::make($data, $this->request->rules());

        $this->assertFalse($validator->fails());
    }

    public function test_validation_fails_without_email(): void
    {
        $data = [
            'password' => 'password123',
        ];

        $validator = Validator::make($data, $this->request->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('email', $validator->errors()->toArray());
    }

    public function test_validation_fails_without_password(): void
    {
        $data = [
            'email' => 'john@example.com',
        ];

        $validator = Validator::make($data, $this->request->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('password', $validator->errors()->toArray());
    }

    public function test_validation_fails_with_invalid_email_format(): void
    {
        $data = [
            'email' => 'invalid-email',
            'password' => 'password123',
        ];

        $validator = Validator::make($data, $this->request->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('email', $validator->errors()->toArray());
    }

    public function test_validation_fails_with_short_password(): void
    {
        $data = [
            'email' => 'john@example.com',
            'password' => '12345',
        ];

        $validator = Validator::make($data, $this->request->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('password', $validator->errors()->toArray());
    }

    public function test_custom_error_messages_are_defined(): void
    {
        $messages = $this->request->messages();

        $this->assertArrayHasKey('email.required', $messages);
        $this->assertArrayHasKey('email.email', $messages);
        $this->assertArrayHasKey('password.required', $messages);
        $this->assertArrayHasKey('password.min', $messages);

        $this->assertEquals('O e-mail é obrigatório.', $messages['email.required']);
        $this->assertEquals('O e-mail deve ser válido.', $messages['email.email']);
        $this->assertEquals('A senha é obrigatória.', $messages['password.required']);
        $this->assertEquals('A senha deve ter pelo menos 6 caracteres.', $messages['password.min']);
    }

    public function test_validation_with_empty_strings(): void
    {
        $data = [
            'email' => '',
            'password' => '',
        ];

        $validator = Validator::make($data, $this->request->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('email', $validator->errors()->toArray());
        $this->assertArrayHasKey('password', $validator->errors()->toArray());
    }

    public function test_validation_with_whitespace_only(): void
    {
        $data = [
            'email' => '   ',
            'password' => '   ',
        ];

        $validator = Validator::make($data, $this->request->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('email', $validator->errors()->toArray());
        $this->assertArrayHasKey('password', $validator->errors()->toArray());
    }
}