<?php

namespace Tests\Mocks;

use App\Domain\Contracts\MovieProviderInterface;

class MovieProviderMock implements MovieProviderInterface
{
    private array $movies = [];
    private array $searchResults = [];
    private array $genres = [];

    public function setMovieDetails(int $movieId, array $details): void
    {
        $this->movies[$movieId] = $details;
    }

    public function setSearchResults(string $query, array $results): void
    {
        $this->searchResults[$query] = $results;
    }

    public function setGenres(array $genres): void
    {
        $this->genres = $genres;
    }

    public function getMovieDetails(int $movieId): array
    {
        if (!isset($this->movies[$movieId])) {
            throw new \Exception('Filme não encontrado.');
        }
        return $this->movies[$movieId];
    }

    public function searchMovies(string $query): array
    {
        return $this->searchResults[$query] ?? [];
    }

    public function getGenres(): array
    {
        return $this->genres;
    }
}
