<?php

namespace App\Infra\Repositories;

use App\Domain\Entities\Favorite;
use App\Domain\Repositories\FavoriteRepositoryInterface;
use Illuminate\Support\Facades\DB;

class FavoriteRepository implements FavoriteRepositoryInterface
{
  public function add(Favorite $favorite): void
  {
    DB::table('favorites')->insert([
      'user_id' => $favorite->getUserId(),
      'movie_id' => $favorite->getMovieId(),
      'movie_title' => $favorite->getMovieTitle(),
      'original_title' => $favorite->getOriginalTitle(),
      'overview' => $favorite->getOverview(),
      'poster_path' => $favorite->getPosterPath(),
      'release_date' => $favorite->getReleaseDate(),
      'genre_ids' => json_encode($favorite->getGenreIds()),
      'created_at' => now(),
      'updated_at' => now(),
    ]);
  }
  

  public function exists(int $userId, int $movieId): bool
  {
    return DB::table('favorites')
      ->where('user_id', $userId)
      ->where('movie_id', $movieId)
      ->exists();
  }

  public function listByUser(int $userId, ?int $genreFilter = null): array
  {
    $query = DB::table('favorites')
        ->where('user_id', $userId);

    $results = $query->get()->map(function ($record) {
        return (new Favorite())
            ->setId($record->id)
            ->setUserId($record->user_id)
            ->setMovieId($record->movie_id)
            ->setMovieTitle($record->movie_title)
            ->setOriginalTitle($record->original_title)
            ->setOverview($record->overview)
            ->setPosterPath($record->poster_path)
            ->setReleaseDate($record->release_date)
            ->setGenreIds(json_decode($record->genre_ids, true));
    });

    if ($genreFilter !== null) {
        $results = $results->filter(function ($favorite) use ($genreFilter) {
            return in_array($genreFilter, $favorite->getGenreIds());
        });
    }

    return $results->values()->all();
  }

  public function remove(int $userId, int $movieId): void
  {
    DB::table('favorites')
      ->where('user_id', $userId)
      ->where('movie_id', $movieId)
      ->delete();
  }
}
