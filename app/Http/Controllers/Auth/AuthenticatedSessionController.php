<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Tools\ApiResponseTools;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

/**
 * @OA\Post(
 * path="/register",
 * tags={"Authentication"},
 * summary="Enregistre un nouvel utilisateur",
 * @OA\RequestBody(
 * required=true,
 * @OA\JsonContent(
 * required={"name", "email", "password", "password_confirmation"},
 * @OA\Property(property="name", type="string", example="John Doe"),
 * @OA\Property(property="email", type="string", format="email", example="john@example.com"),
 * @OA\Property(property="password", type="string", format="password", example="password"),
 * @OA\Property(property="password_confirmation", type="string", format="password", example="password")
 * )
 * ),
 * @OA\Response(
 * response=201,
 * description="Utilisateur enregistré avec succès. (Retourne généralement le cookie de session Sanctum et un statut 201)."
 * ),
 * @OA\Response(response=422, description="Erreur de validation")
 * )
 * * @OA\Post(
 * path="/login",
 * tags={"Authentication"},
 * summary="Connecte l'utilisateur et établit la session Sanctum (SPA)",
 * description="Doit être précédé d'un appel GET à /sanctum/csrf-cookie.",
 * @OA\RequestBody(
 * required=true,
 * @OA\JsonContent(
 * required={"email", "password"},
 * @OA\Property(property="email", type="string", format="email", example="john@example.com"),
 * @OA\Property(property="password", type="string", format="password", example="password"),
 * @OA\Property(property="remember", type="boolean", example="true")
 * )
 * ),
 * @OA\Response(response=204, description="Connexion réussie."),
 * @OA\Response(response=422, description="Erreur de validation ou identifiants incorrects")
 * )
 * * @OA\Get(
 * path="/user",
 * tags={"Authentication"},
 * summary="Récupère les informations de l'utilisateur authentifié",
 * security={{"sanctum": {}}},
 * @OA\Response(
 * response=200,
 * description="Informations de l'utilisateur",
 * @OA\JsonContent(
 * @OA\Property(property="id", type="integer", example=1),
 * @OA\Property(property="name", type="string", example="John Doe"),
 * @OA\Property(property="email", type="string", format="email", example="john@example.com")
 * )
 * ),
 * @OA\Response(response=401, description="Non authentifié")
 * )
 * * @OA\Post(
 * path="/logout",
 * tags={"Authentication"},
 * summary="Déconnecte l'utilisateur (invalide la session Sanctum)",
 * security={{"sanctum": {}}},
 * @OA\Response(response=204, description="Déconnexion réussie."),
 * @OA\Response(response=401, description="Non authentifié")
 * )
 * * @OA\Get(
 * path="/sanctum/csrf-cookie",
 * tags={"Authentication"},
 * summary="Initialise le cookie CSRF pour l'authentification SPA",
 * description="Endpoint obligatoire à appeler par le frontend avant toute requête d'authentification (login, register).",
 * @OA\Response(response=204, description="Cookie CSRF défini."),
 * )
 */

class AuthenticatedSessionController extends Controller
{
    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): Response
    {
        $request->authenticate();

        $request->session()->regenerate();
        $user = Auth::user(); 
        $token = null;
        $data = [$user,$token ];
        return ApiResponseTools::format('connexion effectué avec succès ', $data);
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): Response
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return response()->noContent();
    }
}
