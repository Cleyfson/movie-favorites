<?php

namespace Tests\Feature\Controllers;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class FavoriteControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create();
        
        Http::fake([
            'api.themoviedb.org/3/movie/*' => Http::response([
                'id' => 550,
                'title' => 'Fight Club',
                'original_title' => 'Fight Club',
                'genres' => [['id' => 18, 'name' => 'Drama']],
                'genre_ids' => [18],
                'overview' => 'A ticking-time-bomb insomniac...',
                'poster_path' => '/poster.jpg',
                'release_date' => '1999-10-15'
            ], 200),
            'api.themoviedb.org/3/search/movie*' => Http::response([
                'results' => []
            ], 200),
        ]);
    }

    public function test_user_can_add_favorite(): void
    {
        $response = $this->actingAs($this->user, 'api')
            ->postJson('/api/favorites', [
                'movie_id' => 550
            ]);

        $response->assertStatus(201)
            ->assertJson([
                'message' => 'Filme adicionado aos favoritos!'
            ]);

        $this->assertDatabaseHas('favorites', [
            'user_id' => $this->user->id,
            'movie_id' => 550,
            'movie_title' => 'Fight Club'
        ]);
    }

    public function test_user_cannot_add_duplicate_favorite(): void
    {
        $this->actingAs($this->user, 'api')
            ->postJson('/api/favorites', ['movie_id' => 550]);

        $response = $this->actingAs($this->user, 'api')
            ->postJson('/api/favorites', ['movie_id' => 550]);

        $response->assertStatus(400)
            ->assertJson([
                'message' => 'Filme já está nos favoritos.'
            ]);
    }

    public function test_user_can_list_favorites(): void
    {
        $this->actingAs($this->user, 'api')
            ->postJson('/api/favorites', ['movie_id' => 550]);

        $response = $this->actingAs($this->user, 'api')
            ->getJson('/api/favorites');

        $response->assertStatus(200)
            ->assertJsonStructure([
                '*' => ['movie_id', 'movie_title', 'genre_ids']
            ]);

        $favorites = $response->json();
        $this->assertCount(1, $favorites);
        $this->assertEquals(550, $favorites[0]['movie_id']);
    }

    public function test_user_can_remove_favorite(): void
    {
        $this->actingAs($this->user, 'api')
            ->postJson('/api/favorites', ['movie_id' => 550]);

        $this->assertDatabaseHas('favorites', [
            'user_id' => $this->user->id,
            'movie_id' => 550
        ]);

        $response = $this->actingAs($this->user, 'api')
            ->deleteJson('/api/favorites', ['movie_id' => 550]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Filme removido dos favoritos!'
            ]);

        $this->assertDatabaseMissing('favorites', [
            'user_id' => $this->user->id,
            'movie_id' => 550
        ]);
    }

    public function test_remove_non_existent_favorite_returns_error(): void
    {
        $response = $this->actingAs($this->user, 'api')
            ->deleteJson('/api/favorites', ['movie_id' => 999]);

        $response->assertStatus(400)
            ->assertJson([
                'message' => 'Filme não está nos favoritos.'
            ]);
    }

    public function test_unauthenticated_user_cannot_access_favorites(): void
    {
        $response = $this->getJson('/api/favorites');
        
        $response->assertStatus(401);
    }

    public function test_unauthenticated_user_cannot_add_favorite(): void
    {
        $response = $this->postJson('/api/favorites', ['movie_id' => 550]);
        
        $response->assertStatus(401);
    }

    public function test_unauthenticated_user_cannot_remove_favorite(): void
    {
        $response = $this->deleteJson('/api/favorites', ['movie_id' => 550]);
        
        $response->assertStatus(401);
    }

    public function test_user_only_sees_their_own_favorites(): void
    {
        $otherUser = User::factory()->create();

        $this->actingAs($this->user, 'api')
            ->postJson('/api/favorites', ['movie_id' => 550]);

        Http::fake([
            'api.themoviedb.org/3/movie/27205' => Http::response([
                'id' => 27205,
                'title' => 'Inception',
                'genre_ids' => [28],
                'genres' => [['id' => 28, 'name' => 'Action']],
                'overview' => 'Overview',
                'poster_path' => '/poster.jpg',
                'release_date' => '2010-07-16'
            ], 200),
        ]);

        $this->actingAs($otherUser, 'api')
            ->postJson('/api/favorites', ['movie_id' => 27205]);

        $response = $this->actingAs($this->user, 'api')
            ->getJson('/api/favorites');

        $response->assertStatus(200);
        $favorites = $response->json();
        $this->assertCount(1, $favorites);
        $this->assertEquals(550, $favorites[0]['movie_id']);
    }
}