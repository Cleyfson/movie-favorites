<?php

namespace Tests\Unit\Movie;

use App\Application\UseCases\Movie\MovieGetGenresUseCase;
use Tests\Mocks\MovieProviderMock;
use Tests\TestCase;

class MovieGetGenresUseCaseTest extends TestCase
{
    private MovieProviderMock $movieProviderMock;
    private MovieGetGenresUseCase $movieGetGenresUseCase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->movieProviderMock = new MovieProviderMock();
        $this->movieGetGenresUseCase = new MovieGetGenresUseCase($this->movieProviderMock);
    }

    public function test_get_genres_returns_all_available_genres(): void
    {
        $expectedGenres = [
            ['id' => 28, 'name' => 'Action'],
            ['id' => 12, 'name' => 'Adventure'],
            ['id' => 16, 'name' => 'Animation'],
            ['id' => 35, 'name' => 'Comedy'],
            ['id' => 18, 'name' => 'Drama']
        ];

        $this->movieProviderMock->setGenres($expectedGenres);

        $result = $this->movieGetGenresUseCase->execute();

        $this->assertEquals($expectedGenres, $result);
        $this->assertCount(5, $result);
    }

    public function test_get_genres_returns_empty_when_no_genres_available(): void
    {
        $this->movieProviderMock->setGenres([]);

        $result = $this->movieGetGenresUseCase->execute();

        $this->assertEquals([], $result);
    }
}