<?php

namespace App\Http\Controllers;

use App\Application\UseCases\Auth\UserRegisterUseCase;
use App\Application\UseCases\Auth\UserLoginUseCase;
use App\Application\UseCases\Auth\UserLogoutUseCase;

use App\Http\Requests\UserRegisterRequest;
use App\Http\Requests\UserLoginRequest;

use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Http\Request;
use Exception;

class AuthController extends Controller
{
  public function __construct(
    protected UserRegisterUseCase $UserRegisterUseCase,
    protected UserLoginUseCase $UserLoginUseCase,
    protected UserLogoutUseCase $UserLogoutUseCase
  ) {}

  public function register(UserRegisterRequest $request)
  {
    try {
      $user = $this->UserRegisterUseCase->execute(
        $request->name,
        $request->email,
        $request->password
      );

      return response()->json([
        'message' => 'Usuário registrado com sucesso!',
        'user' => [
          'id' => $user->getId(),
          'name' => $user->getName(),
          'email' => $user->getEmail()
        ]
      ], 201);
    } catch (Exception $e) {
      return response()->json(['message' => $e->getMessage()], 400);
    }
  }

  public function login(UserLoginRequest $request)
  {
    try {
      $data = $this->UserLoginUseCase->execute(
        $request->email, 
        $request->password
      );
      
      return response()->json([
          'access_token' => $data,
          'token_type' => 'bearer',
          'expires_in' => auth()->factory()->getTTL() * 60
      ]);
        
    } catch (Exception $e) {
      return response()->json(['message' => $e->getMessage()], 401);
    }
  }

  public function logout(Request $request)
  {
    try {
      $this->UserLogoutUseCase->execute();

      return response()->json([
          'message' => 'Logout realizado com sucesso.'
      ]);
    } catch (Exception $e) {
      return response()->json([
          'message' => 'Erro ao tentar fazer logout.',
          'details' => $e->getMessage()
      ], 500);
    }
  }
}
