<?php

namespace App\Http\Controllers;

use App\Http\Requests\FavoriteStoreRequest;
use App\Application\UseCases\Favorite\FavoriteAddUseCase;
use App\Application\UseCases\Favorite\FavoriteListUseCase;
use App\Application\UseCases\Favorite\FavoriteRemoveUseCase;
use Illuminate\Http\Request;
use Exception;

class FavoriteController extends Controller
{
  public function __construct(
    protected FavoriteAddUseCase $FavoriteAddUseCase,
    protected FavoriteListUseCase $FavoriteListUseCase,
    protected FavoriteRemoveUseCase $FavoriteRemoveUseCase,
  ) {}

  public function store(FavoriteStoreRequest $request)
  {
    try {
      $this->FavoriteAddUseCase->execute(
        auth()->id(),
        $request->movie_id
      );

      return response()->json(['message' => 'Filme adicionado aos favoritos!'], 201);
    } catch (Exception $e) {
      return response()->json(['message' => $e->getMessage()], 400);
    }
  }
  

  public function index(Request $request)
  {
    try {
      $userId = auth()->id();
      $genreFilter = $request->query('genre_id');

      $favorites = $this->FavoriteListUseCase->execute($userId, $genreFilter);

      return response()->json($favorites);
    } catch (Exception $e) {
      return response()->json(['message' => $e->getMessage()], 400);
    }
  }

  public function destroy(Request $request)
  {
    try {
      $userId = auth()->id();
      $movieId = $request->movie_id;

      $this->FavoriteRemoveUseCase->execute($userId, $movieId);

      return response()->json(['message' => 'Filme removido dos favoritos!']);
    } catch (Exception $e) {
      return response()->json(['message' => $e->getMessage()], 400);
    }
  }
}