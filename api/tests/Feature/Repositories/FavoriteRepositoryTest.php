<?php

namespace Tests\Feature\Repositories;

use App\Infra\Repositories\FavoriteRepository;
use App\Domain\Entities\Favorite;
use App\Models\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class FavoriteRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private FavoriteRepository $repository;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new FavoriteRepository();
        
        $this->user = User::factory()->create();
    }

    public function test_add_inserts_favorite_into_database(): void
    {
        $favorite = (new Favorite())
            ->setUserId($this->user->id)
            ->setMovieId(123)
            ->setMovieTitle('Test Movie')
            ->setOriginalTitle('Test Original')
            ->setOverview('A test movie overview')
            ->setPosterPath('/test.jpg')
            ->setReleaseDate('2023-01-01')
            ->setGenreIds([1, 2, 3]);

        $this->repository->add($favorite);

        $this->assertDatabaseHas('favorites', [
            'user_id' => $this->user->id,
            'movie_id' => 123,
            'movie_title' => 'Test Movie',
            'original_title' => 'Test Original',
            'overview' => 'A test movie overview',
            'poster_path' => '/test.jpg',
            'release_date' => '2023-01-01',
        ]);

        $favorite = \DB::table('favorites')
            ->where('user_id', $this->user->id)
            ->where('movie_id', 123)
            ->first();

        $this->assertNotNull($favorite);
        $this->assertEquals([1, 2, 3], json_decode($favorite->genre_ids, true));
    }

    public function test_exists_returns_true_when_favorite_exists(): void
    {
        $favorite = (new Favorite())
            ->setUserId($this->user->id)
            ->setMovieId(123)
            ->setMovieTitle('Test Movie')
            ->setOriginalTitle('Test Original')
            ->setOverview('Overview')
            ->setPosterPath('/poster.jpg')
            ->setReleaseDate('2023-01-01')
            ->setGenreIds([1, 2]);

        $this->repository->add($favorite);

        $result = $this->repository->exists($this->user->id, 123);

        $this->assertTrue($result);
    }

    public function test_exists_returns_false_when_favorite_does_not_exist(): void
    {
        $result = $this->repository->exists($this->user->id, 999);

        $this->assertFalse($result);
    }

    public function test_list_by_user_returns_all_favorites_without_filter(): void
    {
        $this->repository->add(
            (new Favorite())
                ->setUserId($this->user->id)
                ->setMovieId(123)
                ->setMovieTitle('Movie 1')
                ->setOriginalTitle('Original 1')
                ->setOverview('Overview 1')
                ->setPosterPath('/poster1.jpg')
                ->setReleaseDate('2023-01-01')
                ->setGenreIds([1, 2])
        );

        $this->repository->add(
            (new Favorite())
                ->setUserId($this->user->id)
                ->setMovieId(456)
                ->setMovieTitle('Movie 2')
                ->setOriginalTitle('Original 2')
                ->setOverview('Overview 2')
                ->setPosterPath('/poster2.jpg')
                ->setReleaseDate('2023-02-01')
                ->setGenreIds([3, 4])
        );

        $result = $this->repository->listByUser($this->user->id);

        $this->assertIsArray($result);
        $this->assertCount(2, $result);
        $this->assertInstanceOf(Favorite::class, $result[0]);
        $this->assertEquals('Movie 1', $result[0]->getMovieTitle());
        $this->assertEquals([1, 2], $result[0]->getGenreIds());
    }

    public function test_list_by_user_with_genre_filter(): void
    {
        $this->repository->add(
            (new Favorite())
                ->setUserId($this->user->id)
                ->setMovieId(101)
                ->setMovieTitle('Action Movie')
                ->setOriginalTitle('Action Movie')
                ->setOverview('Overview')
                ->setPosterPath('/poster.jpg')
                ->setReleaseDate('2023-01-01')
                ->setGenreIds([28, 12])
        );

        $this->repository->add(
            (new Favorite())
                ->setUserId($this->user->id)
                ->setMovieId(102)
                ->setMovieTitle('Comedy Movie')
                ->setOriginalTitle('Comedy Movie')
                ->setOverview('Overview')
                ->setPosterPath('/poster2.jpg')
                ->setReleaseDate('2023-02-01')
                ->setGenreIds([35])
        );

        $this->repository->add(
            (new Favorite())
                ->setUserId($this->user->id)
                ->setMovieId(103)
                ->setMovieTitle('Action Drama')
                ->setOriginalTitle('Action Drama')
                ->setOverview('Overview')
                ->setPosterPath('/poster3.jpg')
                ->setReleaseDate('2023-03-01')
                ->setGenreIds([28, 18])
        );

        $result = $this->repository->listByUser($this->user->id, 28);

        $this->assertIsArray($result);
        $this->assertCount(2, $result);
        
        $titles = array_map(fn($fav) => $fav->getMovieTitle(), $result);
        $this->assertContains('Action Movie', $titles);
        $this->assertContains('Action Drama', $titles);
        $this->assertNotContains('Comedy Movie', $titles);
    }

    public function test_remove_deletes_favorite_from_database(): void
    {
        $favorite = (new Favorite())
            ->setUserId($this->user->id)
            ->setMovieId(123)
            ->setMovieTitle('Test Movie')
            ->setOriginalTitle('Original')
            ->setOverview('Overview')
            ->setPosterPath('/poster.jpg')
            ->setReleaseDate('2023-01-01')
            ->setGenreIds([1]);

        $this->repository->add($favorite);
        
        $this->assertDatabaseHas('favorites', [
            'user_id' => $this->user->id,
            'movie_id' => 123,
        ]);

        $this->repository->remove($this->user->id, 123);

        $this->assertDatabaseMissing('favorites', [
            'user_id' => $this->user->id,
            'movie_id' => 123,
        ]);
    }

    public function test_repository_implements_interface(): void
    {
        $this->assertInstanceOf(\App\Domain\Repositories\FavoriteRepositoryInterface::class, $this->repository);
    }

    public function test_list_by_user_returns_empty_array_when_no_favorites(): void
    {
        $result = $this->repository->listByUser(999);

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    public function test_add_multiple_favorites_for_same_user(): void
    {
        $favorite1 = (new Favorite())
            ->setUserId($this->user->id)
            ->setMovieId(101)
            ->setMovieTitle('Movie 1')
            ->setOriginalTitle('Original 1')
            ->setOverview('Overview 1')
            ->setPosterPath('/poster1.jpg')
            ->setReleaseDate('2023-01-01')
            ->setGenreIds([1]);

        $favorite2 = (new Favorite())
            ->setUserId($this->user->id)
            ->setMovieId(102)
            ->setMovieTitle('Movie 2')
            ->setOriginalTitle('Original 2')
            ->setOverview('Overview 2')
            ->setPosterPath('/poster2.jpg')
            ->setReleaseDate('2023-02-01')
            ->setGenreIds([2]);

        $this->repository->add($favorite1);
        $this->repository->add($favorite2);

        $favorites = $this->repository->listByUser($this->user->id);

        $this->assertCount(2, $favorites);
    }

    public function test_remove_non_existent_favorite_does_not_throw_error(): void
    {
        $this->repository->remove(999, 999);

        $this->assertTrue(true);
    }
}
