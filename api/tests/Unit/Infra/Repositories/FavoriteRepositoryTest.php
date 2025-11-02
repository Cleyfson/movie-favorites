<?php

namespace Tests\Unit\Infra\Repositories;

use App\Infra\Repositories\FavoriteRepository;
use App\Domain\Entities\Favorite;
use Tests\TestCase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use Mockery;

class FavoriteRepositoryTest extends TestCase
{
    private FavoriteRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new FavoriteRepository();
    }

    public function test_add_inserts_favorite_into_database(): void
    {
        $favorite = (new Favorite())
            ->setUserId(1)
            ->setMovieId(123)
            ->setMovieTitle('Test Movie')
            ->setOriginalTitle('Test Original')
            ->setOverview('A test movie overview')
            ->setPosterPath('/test.jpg')
            ->setReleaseDate('2023-01-01')
            ->setGenreIds([1, 2, 3]);

        DB::shouldReceive('table')->with('favorites')->once()->andReturnSelf();
        DB::shouldReceive('insert')
            ->once()
            ->with(Mockery::on(function ($data) {
                return $data['user_id'] === 1 &&
                       $data['movie_id'] === 123 &&
                       $data['movie_title'] === 'Test Movie' &&
                       $data['original_title'] === 'Test Original' &&
                       $data['overview'] === 'A test movie overview' &&
                       $data['poster_path'] === '/test.jpg' &&
                       $data['release_date'] === '2023-01-01' &&
                       $data['genre_ids'] === json_encode([1, 2, 3]) &&
                       isset($data['created_at']) &&
                       isset($data['updated_at']);
            }));

        $this->repository->add($favorite);

        $this->assertTrue(true);
    }

    public function test_exists_returns_true_when_favorite_exists(): void
    {
        $userId = 1;
        $movieId = 123;

        DB::shouldReceive('table')->with('favorites')->once()->andReturnSelf();
        DB::shouldReceive('where')->with('user_id', $userId)->once()->andReturnSelf();
        DB::shouldReceive('where')->with('movie_id', $movieId)->once()->andReturnSelf();
        DB::shouldReceive('exists')->once()->andReturn(true);

        $result = $this->repository->exists($userId, $movieId);

        $this->assertTrue($result);
    }

    public function test_exists_returns_false_when_favorite_does_not_exist(): void
    {
        $userId = 1;
        $movieId = 999;

        DB::shouldReceive('table')->with('favorites')->once()->andReturnSelf();
        DB::shouldReceive('where')->with('user_id', $userId)->once()->andReturnSelf();
        DB::shouldReceive('where')->with('movie_id', $movieId)->once()->andReturnSelf();
        DB::shouldReceive('exists')->once()->andReturn(false);

        $result = $this->repository->exists($userId, $movieId);

        $this->assertFalse($result);
    }

    public function test_list_by_user_returns_all_favorites_without_filter(): void
    {
        $userId = 1;
        $mockData = collect([
            (object) [
                'id' => 1,
                'user_id' => 1,
                'movie_id' => 123,
                'movie_title' => 'Movie 1',
                'original_title' => 'Original 1',
                'overview' => 'Overview 1',
                'poster_path' => '/poster1.jpg',
                'release_date' => '2023-01-01',
                'genre_ids' => json_encode([1, 2])
            ],
            (object) [
                'id' => 2,
                'user_id' => 1,
                'movie_id' => 456,
                'movie_title' => 'Movie 2',
                'original_title' => 'Original 2',
                'overview' => 'Overview 2',
                'poster_path' => '/poster2.jpg',
                'release_date' => '2023-02-01',
                'genre_ids' => json_encode([3, 4])
            ]
        ]);

        DB::shouldReceive('table')->with('favorites')->once()->andReturnSelf();
        DB::shouldReceive('where')->with('user_id', $userId)->once()->andReturnSelf();
        DB::shouldReceive('get')->once()->andReturn($mockData);

        $result = $this->repository->listByUser($userId);

        $this->assertIsArray($result);
        $this->assertCount(2, $result);
        $this->assertInstanceOf(Favorite::class, $result[0]);
        $this->assertEquals('Movie 1', $result[0]->getMovieTitle());
        $this->assertEquals([1, 2], $result[0]->getGenreIds());
    }

    public function test_remove_deletes_favorite_from_database(): void
    {
        $userId = 1;
        $movieId = 123;

        DB::shouldReceive('table')->with('favorites')->once()->andReturnSelf();
        DB::shouldReceive('where')->with('user_id', $userId)->once()->andReturnSelf();
        DB::shouldReceive('where')->with('movie_id', $movieId)->once()->andReturnSelf();
        DB::shouldReceive('delete')->once();

        $this->repository->remove($userId, $movieId);

        $this->assertTrue(true);
    }

    public function test_repository_implements_interface(): void
    {
        $this->assertInstanceOf(\App\Domain\Repositories\FavoriteRepositoryInterface::class, $this->repository);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}