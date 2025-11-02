<?php

namespace Tests\Unit\Application\UseCases\Favorite;

use App\Application\UseCases\Favorite\FavoriteRemoveUseCase;
use App\Domain\Entities\Favorite;
use Tests\Mocks\FavoriteRepositoryMock;
use Tests\TestCase;

class FavoriteRemoveUseCaseTest extends TestCase
{
  private FavoriteRepositoryMock $favoriteRepositoryMock;
  private FavoriteRemoveUseCase $favoriteRemoveUseCase;

  protected function setUp(): void
  {
    parent::setUp();
    
    $this->favoriteRepositoryMock = new FavoriteRepositoryMock();
    $this->favoriteRemoveUseCase = new FavoriteRemoveUseCase($this->favoriteRepositoryMock);
    
    $this->addSampleFavorites();
  }

  private function addSampleFavorites(): void
  {
    $favorites = [
      (new Favorite())
        ->setId(1)
        ->setUserId(1)
        ->setMovieId(101)
        ->setMovieTitle('Movie A')
        ->setGenreIds([1, 2]),
          
      (new Favorite())
        ->setId(2)
        ->setUserId(1)
        ->setMovieId(102)
        ->setMovieTitle('Movie B')
        ->setGenreIds([2, 3]),
          
      (new Favorite())
        ->setId(3)
        ->setUserId(2)
        ->setMovieId(101)
        ->setMovieTitle('Movie A')
        ->setGenreIds([1, 2])
    ];

    foreach ($favorites as $favorite) {
      $this->favoriteRepositoryMock->add($favorite);
    }
  }

  public function test_remove_existing_favorite(): void
  {
    $initialCount = count($this->favoriteRepositoryMock->getFavorites());
    
    $this->favoriteRemoveUseCase->execute(1, 101);
    
    $remainingFavorites = $this->favoriteRepositoryMock->getFavorites();
    $this->assertCount($initialCount - 1, $remainingFavorites);
    
    $removed = array_filter($remainingFavorites, fn($f) => $f->getMovieId() === 101 && $f->getUserId() === 1);
    $this->assertCount(0, $removed);
    
    $otherUserFavorite = array_filter($remainingFavorites, fn($f) => $f->getMovieId() === 101 && $f->getUserId() === 2);
    $this->assertCount(1, $otherUserFavorite);
  }

  public function test_remove_non_existent_favorite(): void
  {
    $nonExistentMovieId = 999;
    
    $this->expectException(\Exception::class);
    $this->expectExceptionMessage('Filme não está nos favoritos.');
    
    $this->favoriteRemoveUseCase->execute(1, $nonExistentMovieId);
  }

  public function test_remove_favorite_from_user_with_no_favorites(): void
  {
    $userIdWithNoFavorites = 3;
    
    $this->expectException(\Exception::class);
    $this->expectExceptionMessage('Filme não está nos favoritos.');
    
    $this->favoriteRemoveUseCase->execute($userIdWithNoFavorites, 101);
  }

  protected function tearDown(): void
  {
    $this->favoriteRepositoryMock->clear();
    parent::tearDown();
  }
}