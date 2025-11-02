<?php

namespace Tests\Unit\Movie;

use App\Application\UseCases\Movie\MovieGetDetailsUseCase;
use Tests\Mocks\MovieProviderMock;
use Tests\TestCase;

class MovieGetDetailsUseCaseTest extends TestCase
{
    private MovieProviderMock $movieProviderMock;
    private MovieGetDetailsUseCase $movieGetDetailsUseCase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->movieProviderMock = new MovieProviderMock();
        $this->movieGetDetailsUseCase = new MovieGetDetailsUseCase($this->movieProviderMock);
    }

    public function test_get_movie_details_with_valid_id(): void
    {
        $movieId = 27205;
        $expectedDetails = [
            'id' => 27205,
            'title' => 'Inception',
            'original_title' => 'Inception',
            'overview' => 'A thief who steals corporate secrets through the use of dream-sharing technology.',
            'poster_path' => '/9gk7adHYeDvHkCSEqAvQNLV5Uge.jpg',
            'release_date' => '2010-07-16',
            'genres' => [
                ['id' => 28, 'name' => 'Action'],
                ['id' => 878, 'name' => 'Science Fiction'],
                ['id' => 53, 'name' => 'Thriller']
            ]
        ];

        $this->movieProviderMock->setMovieDetails($movieId, $expectedDetails);

        $result = $this->movieGetDetailsUseCase->execute($movieId);

        $this->assertEquals($expectedDetails, $result);
        $this->assertEquals('Inception', $result['title']);
        $this->assertCount(3, $result['genres']);
    }

    public function test_get_movie_details_with_invalid_id_throws_exception(): void
    {
        $invalidMovieId = 999999;

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Filme não encontrado.');

        $this->movieGetDetailsUseCase->execute($invalidMovieId);
    }
}