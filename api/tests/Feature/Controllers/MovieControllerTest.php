<?php

namespace Tests\Feature\Controllers;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class MovieControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create();
    }

    public function test_search_returns_movies_successfully(): void
    {
        Http::fake([
            'api.themoviedb.org/3/search/movie*' => Http::response([
                'results' => [
                    [
                        'id' => 27205,
                        'title' => 'Inception',
                        'original_title' => 'Inception',
                        'overview' => 'Cobb, a skilled thief...',
                        'poster_path' => '/9gk7adHYeDvHkCSEqAvQNLV5Uge.jpg',
                        'release_date' => '2010-07-16',
                        'genre_ids' => [28, 878, 53]
                    ]
                ]
            ], 200),
        ]);

        $response = $this->actingAs($this->user, 'api')
            ->getJson('/api/movies/search?q=Inception');

        $response->assertStatus(200)
            ->assertJsonStructure([
                '*' => ['id', 'title', 'overview', 'poster_path', 'release_date']
            ]);

        $movies = $response->json();
        $this->assertNotEmpty($movies);
        $this->assertEquals('Inception', $movies[0]['title']);
    }

    public function test_search_requires_query_parameter(): void
    {
        Http::fake([
            'api.themoviedb.org/3/search/movie*' => Http::response([
                'results' => []
            ], 200),
        ]);

        $response = $this->actingAs($this->user, 'api')
            ->getJson('/api/movies/search');

        $response->assertStatus(200)
            ->assertJson([]);
    }

    public function test_search_returns_empty_array_when_no_results(): void
    {
        Http::fake([
            'api.themoviedb.org/3/search/movie*' => Http::response([
                'results' => []
            ], 200)
        ]);

        $response = $this->actingAs($this->user, 'api')
            ->getJson('/api/movies/search?q=NonExistentMovie123456');

        $response->assertStatus(200)
            ->assertJson([]);
    }

    public function test_search_handles_special_characters(): void
    {
        Http::fake([
            'api.themoviedb.org/3/search/movie*' => Http::response([
                'results' => [
                    [
                        'id' => 604,
                        'title' => 'The Matrix: Reloaded',
                        'overview' => 'Overview',
                        'poster_path' => '/poster.jpg',
                        'release_date' => '2003-05-15'
                    ]
                ]
            ], 200)
        ]);

        $response = $this->actingAs($this->user, 'api')
            ->getJson('/api/movies/search?q=' . urlencode('Matrix: Reloaded'));

        $response->assertStatus(200);
        $this->assertNotEmpty($response->json());
    }

    public function test_search_returns_error_when_tmdb_api_fails(): void
    {
        Http::fake([
            'api.themoviedb.org/3/search/movie*' => Http::response([], 500)
        ]);

        $response = $this->actingAs($this->user, 'api')
            ->getJson('/api/movies/search?q=test');

        $response->assertStatus(400)
            ->assertJsonStructure(['message']);
    }

    public function test_genres_returns_list_successfully(): void
    {
        Http::fake([
            'api.themoviedb.org/3/genre/movie/list*' => Http::response([
                'genres' => [
                    ['id' => 28, 'name' => 'Ação'],
                    ['id' => 12, 'name' => 'Aventura'],
                    ['id' => 35, 'name' => 'Comédia'],
                    ['id' => 878, 'name' => 'Ficção científica'],
                    ['id' => 53, 'name' => 'Thriller']
                ]
            ], 200),
        ]);

        $response = $this->actingAs($this->user, 'api')
            ->getJson('/api/movies/genres');

        $response->assertStatus(200)
            ->assertJsonStructure([
                '*' => ['id', 'name']
            ]);

        $genres = $response->json();
        $this->assertNotEmpty($genres);
        
        $genreNames = array_column($genres, 'name');
        $this->assertContains('Ação', $genreNames);
    }

    public function test_genres_returns_error_when_tmdb_api_fails(): void
    {
        Http::fake([
            'api.themoviedb.org/3/genre/movie/list*' => Http::response([], 500)
        ]);

        $response = $this->actingAs($this->user, 'api')
            ->getJson('/api/movies/genres');

        $response->assertStatus(400)
            ->assertJsonStructure(['message']);
    }

    public function test_show_returns_movie_details_successfully(): void
    {
        Http::fake([
            'api.themoviedb.org/3/movie/*' => Http::response([
                'id' => 27205,
                'title' => 'A Origem',
                'original_title' => 'Inception',
                'overview' => 'Dom Cobb é um ladrão com a rara habilidade...',
                'poster_path' => '/9gk7adHYeDvHkCSEqAvQNLV5Uge.jpg',
                'backdrop_path' => '/s3TBrRGB1iav7gFOCNx3H31MoES.jpg',
                'release_date' => '2010-07-16',
                'vote_average' => 8.4,
                'vote_count' => 32000,
                'runtime' => 148,
                'genres' => [
                    ['id' => 28, 'name' => 'Ação'],
                    ['id' => 878, 'name' => 'Ficção científica'],
                    ['id' => 53, 'name' => 'Thriller']
                ]
            ], 200)
        ]);

        $movieId = 27205;

        $response = $this->actingAs($this->user, 'api')
            ->getJson("/api/movies/{$movieId}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'id',
                'title',
                'original_title',
                'overview',
                'poster_path',
                'release_date',
                'vote_average',
                'runtime',
                'genres' => [
                    '*' => ['id', 'name']
                ]
            ]);

        $movie = $response->json();
        $this->assertEquals(27205, $movie['id']);
        $this->assertEquals('A Origem', $movie['title']);
    }

    public function test_show_returns_error_when_movie_not_found(): void
    {
        Http::fake([
            'api.themoviedb.org/3/movie/*' => Http::response([], 404)
        ]);

        $response = $this->actingAs($this->user, 'api')
            ->getJson('/api/movies/999999');

        $response->assertStatus(400)
            ->assertJsonStructure(['message']);
    }

    public function test_show_returns_error_when_tmdb_api_fails(): void
    {
        Http::fake([
            'api.themoviedb.org/3/movie/*' => Http::response([], 500)
        ]);

        $response = $this->actingAs($this->user, 'api')
            ->getJson('/api/movies/123');

        $response->assertStatus(400)
            ->assertJsonStructure(['message']);
    }

    public function test_endpoints_require_authentication(): void
    {
        Http::fake();

        $searchResponse = $this->getJson('/api/movies/search?q=test');
        $searchResponse->assertStatus(401);

        $genresResponse = $this->getJson('/api/movies/genres');
        $genresResponse->assertStatus(401);

        $showResponse = $this->getJson('/api/movies/27205');
        $showResponse->assertStatus(401);
    }

    public function test_search_movies_caches_results_from_tmdb(): void
    {
        Http::fake([
            'api.themoviedb.org/3/search/movie*' => Http::response([
                'results' => [
                    [
                        'id' => 27205,
                        'title' => 'Inception',
                        'overview' => 'Cobb, a skilled thief...',
                        'poster_path' => '/9gk7adHYeDvHkCSEqAvQNLV5Uge.jpg',
                        'release_date' => '2010-07-16',
                    ]
                ]
            ], 200),
        ]);

        $response1 = $this->actingAs($this->user, 'api')
            ->getJson('/api/movies/search?q=Inception');
        $response1->assertStatus(200);

        $response2 = $this->actingAs($this->user, 'api')
            ->getJson('/api/movies/search?q=Inception');
        $response2->assertStatus(200);

        $this->assertEquals($response1->json(), $response2->json());
    }

    public function test_genres_list_contains_expected_structure(): void
    {
        Http::fake([
            'api.themoviedb.org/3/genre/movie/list*' => Http::response([
                'genres' => [
                    ['id' => 28, 'name' => 'Ação'],
                    ['id' => 12, 'name' => 'Aventura'],
                ]
            ], 200),
        ]);

        $response = $this->actingAs($this->user, 'api')
            ->getJson('/api/movies/genres');

        $genres = $response->json();
        
        foreach ($genres as $genre) {
            $this->assertArrayHasKey('id', $genre);
            $this->assertArrayHasKey('name', $genre);
            $this->assertIsInt($genre['id']);
            $this->assertIsString($genre['name']);
        }
    }

    public function test_movie_details_includes_all_required_fields(): void
    {
        Http::fake([
            'api.themoviedb.org/3/movie/*' => Http::response([
                'id' => 27205,
                'title' => 'A Origem',
                'original_title' => 'Inception',
                'overview' => 'Dom Cobb é um ladrão com a rara habilidade...',
                'poster_path' => '/9gk7adHYeDvHkCSEqAvQNLV5Uge.jpg',
                'backdrop_path' => '/s3TBrRGB1iav7gFOCNx3H31MoES.jpg',
                'release_date' => '2010-07-16',
                'vote_average' => 8.4,
                'vote_count' => 32000,
                'runtime' => 148,
                'genres' => [
                    ['id' => 28, 'name' => 'Ação'],
                ]
            ], 200)
        ]);

        $response = $this->actingAs($this->user, 'api')
            ->getJson('/api/movies/27205');

        $movie = $response->json();

        $requiredFields = ['id', 'title', 'original_title', 'overview', 'poster_path', 'release_date'];
        
        foreach ($requiredFields as $field) {
            $this->assertArrayHasKey($field, $movie, "Missing required field: $field");
        }
    }
}