<?php

namespace Tests\Unit\Favorite;

use App\Application\UseCases\Favorite\FavoriteListUseCase;
use App\Domain\Entities\Favorite;
use Tests\Mocks\FavoriteRepositoryMock;
use Tests\TestCase;

class FavoriteListUseCaseEdgeCasesTest extends TestCase
{
    private FavoriteRepositoryMock $favoriteRepositoryMock;
    private FavoriteListUseCase $favoriteListUseCase;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->favoriteRepositoryMock = new FavoriteRepositoryMock();
        $this->favoriteListUseCase = new FavoriteListUseCase($this->favoriteRepositoryMock);
    }

    public function test_list_favorites_with_multiple_genre_matches(): void
    {
        $favorite = (new Favorite())
            ->setId(1)
            ->setUserId(1)
            ->setMovieId(101)
            ->setMovieTitle('Multi-Genre Movie')
            ->setGenreIds([1, 2, 3, 4, 5]);

        $this->favoriteRepositoryMock->add($favorite);

        $result1 = $this->favoriteListUseCase->execute(1, 1);
        $result3 = $this->favoriteListUseCase->execute(1, 3);
        $result5 = $this->favoriteListUseCase->execute(1, 5);

        $this->assertCount(1, $result1);
        $this->assertCount(1, $result3);
        $this->assertCount(1, $result5);
    }

    public function test_list_favorites_with_empty_genre_ids(): void
    {
        $favorite = (new Favorite())
            ->setId(1)
            ->setUserId(1)
            ->setMovieId(101)
            ->setMovieTitle('Movie Without Genres')
            ->setGenreIds([]);

        $this->favoriteRepositoryMock->add($favorite);

        $resultAll = $this->favoriteListUseCase->execute(1);
        $this->assertCount(1, $resultAll);

        $resultFiltered = $this->favoriteListUseCase->execute(1, 1);
        $this->assertCount(0, $resultFiltered);
    }

    public function test_list_favorites_with_large_user_id(): void
    {
        $largeUserId = 999999999;
        
        $favorite = (new Favorite())
            ->setId(1)
            ->setUserId($largeUserId)
            ->setMovieId(101)
            ->setMovieTitle('Test Movie')
            ->setGenreIds([1]);

        $this->favoriteRepositoryMock->add($favorite);

        $result = $this->favoriteListUseCase->execute($largeUserId);
        $this->assertCount(1, $result);
        $this->assertEquals($largeUserId, $result[0]->getUserId());
    }

    public function test_list_favorites_preserves_order(): void
    {
        $favorites = [
            (new Favorite())->setId(3)->setUserId(1)->setMovieId(103)->setMovieTitle('Movie C')->setGenreIds([1]),
            (new Favorite())->setId(1)->setUserId(1)->setMovieId(101)->setMovieTitle('Movie A')->setGenreIds([1]),
            (new Favorite())->setId(2)->setUserId(1)->setMovieId(102)->setMovieTitle('Movie B')->setGenreIds([1]),
        ];

        foreach ($favorites as $favorite) {
            $this->favoriteRepositoryMock->add($favorite);
        }

        $result = $this->favoriteListUseCase->execute(1);
        
        $this->assertEquals('Movie C', $result[0]->getMovieTitle());
        $this->assertEquals('Movie A', $result[1]->getMovieTitle());
        $this->assertEquals('Movie B', $result[2]->getMovieTitle());
    }

    protected function tearDown(): void
    {
        $this->favoriteRepositoryMock->clear();
        parent::tearDown();
    }
}