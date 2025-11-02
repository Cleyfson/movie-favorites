<?php

namespace Tests\Unit\Http\Requests;

use App\Http\Requests\FavoriteStoreRequest;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class FavoriteStoreRequestTest extends TestCase
{
    private FavoriteStoreRequest $request;

    protected function setUp(): void
    {
        parent::setUp();
        $this->request = new FavoriteStoreRequest();
    }

    public function test_authorize_returns_true(): void
    {
        $this->assertTrue($this->request->authorize());
    }

    public function test_validation_passes_with_valid_movie_id(): void
    {
        $data = [
            'movie_id' => 123,
        ];

        $validator = Validator::make($data, $this->request->rules());

        $this->assertFalse($validator->fails());
    }

    public function test_validation_fails_without_movie_id(): void
    {
        $data = [];

        $validator = Validator::make($data, $this->request->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('movie_id', $validator->errors()->toArray());
    }

    public function test_validation_passes_with_string_numeric_movie_id(): void
    {
        $data = [
            'movie_id' => '123',
        ];

        $validator = Validator::make($data, $this->request->rules());

        $this->assertFalse($validator->fails());
    }

    public function test_validation_with_negative_movie_id(): void
    {
        $data = [
            'movie_id' => -1,
        ];

        $validator = Validator::make($data, $this->request->rules());

        $this->assertFalse($validator->fails());
    }

    public function test_validation_with_zero_movie_id(): void
    {
        $data = [
            'movie_id' => 0,
        ];

        $validator = Validator::make($data, $this->request->rules());

        $this->assertFalse($validator->fails());
    }

    public function test_custom_error_messages_are_defined(): void
    {
        $messages = $this->request->messages();

        $this->assertArrayHasKey('movie_id.required', $messages);
        $this->assertArrayHasKey('movie_id.integer', $messages);

        $this->assertEquals('O ID do filme é obrigatório.', $messages['movie_id.required']);
        $this->assertEquals('O ID do filme deve ser um número inteiro.', $messages['movie_id.integer']);
    }

    public function test_validation_with_large_movie_id(): void
    {
        $data = [
            'movie_id' => 999999999,
        ];

        $validator = Validator::make($data, $this->request->rules());

        $this->assertFalse($validator->fails());
    }

    public function test_validation_with_empty_string_movie_id(): void
    {
        $data = [
            'movie_id' => '',
        ];

        $validator = Validator::make($data, $this->request->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('movie_id', $validator->errors()->toArray());
    }

    public function test_validation_fails_with_null_movie_id(): void
    {
        $data = [
            'movie_id' => null,
        ];

        $validator = Validator::make($data, $this->request->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('movie_id', $validator->errors()->toArray());
    }

    public function test_validation_fails_with_array_movie_id(): void
    {
        $data = [
            'movie_id' => [],
        ];

        $validator = Validator::make($data, $this->request->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('movie_id', $validator->errors()->toArray());
    }
}