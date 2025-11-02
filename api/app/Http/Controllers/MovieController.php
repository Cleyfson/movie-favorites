<?php

namespace App\Http\Controllers;

use App\Application\UseCases\Movie\MovieSearchUseCase;
use App\Application\UseCases\Movie\MovieGetGenresUseCase;
use App\Application\UseCases\Movie\MovieGetDetailsUseCase;
use Illuminate\Http\Request;
use Exception;

class MovieController extends Controller
{
  public function __construct(
    protected MovieSearchUseCase $MovieSearchUseCase,
    protected MovieGetGenresUseCase $MovieGetGenresUseCase,
    protected MovieGetDetailsUseCase $MovieGetDetailsUseCase,
  ) {}

  public function search(Request $request)
  {
    try {
      $query = $request->query('q');
  
      $movies = $this->MovieSearchUseCase->execute($query);
      return response()->json($movies);
    } catch (Exception $e) {
      return response()->json(['message' => $e->getMessage()], 400);
    }
  }

  public function genres()
  {
    try {
      $genres = $this->MovieGetGenresUseCase->execute();
      return response()->json($genres);
    } catch (Exception $e) {
      return response()->json(['message' => $e->getMessage()], 400);
    }
  }

  public function show(int $id)
  {
    try {
      $movie = $this->MovieGetDetailsUseCase->execute($id);
      return response()->json($movie);
    } catch (Exception $e) {
      return response()->json(['message' => $e->getMessage()], 400);
    }
  }
}
