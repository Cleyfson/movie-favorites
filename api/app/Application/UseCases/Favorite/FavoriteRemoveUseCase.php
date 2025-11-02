<?php

namespace App\Application\UseCases\Favorite;

use App\Domain\Repositories\FavoriteRepositoryInterface;
use Exception;

class FavoriteRemoveUseCase
{
    public function __construct(
        private FavoriteRepositoryInterface $repository
    ) {}

    public function execute(int $userId, int $movieId): void
    {
        if (!$this->repository->exists($userId, $movieId)) {
            throw new Exception('Filme não está nos favoritos.');
        }

        $this->repository->remove($userId, $movieId);
    }
}
