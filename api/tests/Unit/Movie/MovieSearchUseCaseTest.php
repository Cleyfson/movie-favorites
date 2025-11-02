<?php

namespace Tests\Unit\Movie;

use App\Application\UseCases\Movie\MovieSearchUseCase;
use Tests\Mocks\MovieProviderMock;
use Tests\TestCase;

class MovieSearchUseCaseTest extends TestCase
{
    private MovieProviderMock $movieProviderMock;
    private MovieSearchUseCase $movieSearchUseCase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->movieProviderMock = new MovieProviderMock();
        $this->movieSearchUseCase = new MovieSearchUseCase($this->movieProviderMock);
    }

    public function test_search_movies_with_valid_query(): void
    {
        $query = 'Inception';
        $expectedMovies = [
            ['id' => 27205, 'title' => 'Inception', 'release_date' => '2010-07-16'],
            ['id' => 12345, 'title' => 'Inception: The Dream', 'release_date' => '2020-01-01']
        ];

        $this->movieProviderMock->setSearchResults($query, $expectedMovies);

        $result = $this->movieSearchUseCase->execute($query);

        $this->assertEquals($expectedMovies, $result);
    }

    public function test_search_movies_with_empty_query(): void
    {
        $result = $this->movieSearchUseCase->execute('');

        $this->assertEquals([], $result);
    }

    public function test_search_movies_with_no_results(): void
    {
        $query = 'NonExistentMovie123';
        $this->movieProviderMock->setSearchResults($query, []);

        $result = $this->movieSearchUseCase->execute($query);

        $this->assertEquals([], $result);
    }
}