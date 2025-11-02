<?php

namespace Tests\Unit\Entities;

use App\Domain\Entities\Favorite;
use Tests\TestCase;

class FavoriteTest extends TestCase
{
    public function test_favorite_entity_creation_and_getters(): void
    {
        $favorite = new Favorite();
        $favorite->setId(1)
                 ->setUserId(10)
                 ->setMovieId(123)
                 ->setMovieTitle('Test Movie')
                 ->setGenreIds([1, 2, 3]);

        $this->assertEquals(1, $favorite->getId());
        $this->assertEquals(10, $favorite->getUserId());
        $this->assertEquals(123, $favorite->getMovieId());
        $this->assertEquals('Test Movie', $favorite->getMovieTitle());
        $this->assertEquals([1, 2, 3], $favorite->getGenreIds());
    }

    public function test_favorite_entity_method_chaining(): void
    {
        $favorite = (new Favorite())
            ->setId(5)
            ->setUserId(20)
            ->setMovieId(456)
            ->setMovieTitle('Another Movie')
            ->setGenreIds([4, 5]);

        $this->assertInstanceOf(Favorite::class, $favorite);
        $this->assertEquals(5, $favorite->getId());
        $this->assertEquals('Another Movie', $favorite->getMovieTitle());
    }

    public function test_favorite_entity_with_empty_genre_ids(): void
    {
        $favorite = (new Favorite())
            ->setUserId(1)
            ->setMovieId(789)
            ->setMovieTitle('No Genre Movie')
            ->setGenreIds([]);

        $this->assertEquals([], $favorite->getGenreIds());
        $this->assertIsArray($favorite->getGenreIds());
    }
}