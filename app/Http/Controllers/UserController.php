<?php

namespace App\Http\Controllers;

use App\Http\Tools\ApiResponseTools;
use App\Http\Tools\DefaultMessageTools;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class UserController extends Controller
{
    //

/**
     * @OA\Post(
     * path="/api/user/login",
     * tags={"Authentication"},
     * summary="Connexion d'un utilisateur",
     * @OA\RequestBody(
     * required=true,
     * @OA\JsonContent(
     * required={"email", "password"},
     * @OA\Property(property="email", type="string", format="email", example="john@example.com"),
     * @OA\Property(property="password", type="string", format="password", example="password"),
     * )
     * ),
     * @OA\Response(
     * response=201,
     * description="Connexion effectué avec succès. "
     * ),
     * @OA\Response(response=422, description="Erreur de validation")
     * )
     */
    public function login(Request $request)
    {
        try {
            $user = User::where('email', $request->email)->first();

            if (! $user || ! Hash::check($request->password, $user->password)) {
                return ApiResponseTools::format('Email ou mot de Passe incorrect.', null,false, 401);
            }
            

            $token = $user->createToken('api-token')->plainTextToken;

            return  ApiResponseTools::format('Connexion Réussit !',['token' => $token,'user' => $user,]);
            
        } catch (\Exception $e) {
            return ApiResponseTools::devFormat(true,'Erreur', $e);
        }    
    }
    /**
     * @OA\Post(
     * path="/api/user/register",
     * tags={"Authentication"},
     * summary="Enregistre un nouvel utilisateur",
     * @OA\RequestBody(
     * required=true,
     * @OA\JsonContent(
     * required={"name", "email", "password", "password_confirmation"},
     * @OA\Property(property="name", type="string", example="John Doe"),
     * @OA\Property(property="email", type="string", format="email", example="john@example.com"),
     * @OA\Property(property="role", type="string", example="Depute"),
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
     */
    public function register(Request $request) 
    {
        
        $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:users,email',
            'password' =>['required', 'confirmed', Rules\Password::defaults()]
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

         $user->tokens()->delete();

        $token = $user->createToken("api-token")->plainTextToken;
            return  ApiResponseTools::format('Connexion Réussit !',['token' => $token,'user' => $user,]);

    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            "message" => "Logged out"
        ]);
    }

    public function user()
    {
        return  ApiResponseTools::format('Utilisateur connecté !',Auth::user());
    }

    public function refresh_token(Request $request)
    {
        try {
            $user = $request->user();

            if (! $user) {
                return ApiResponseTools::format('Non authentifié.', null, false, 401);
            }

            // Supprime le token courant (rotation)
            $current = $request->user()->currentAccessToken();
            if ($current) {
                $current->delete();
            }

            // Crée et renvoie un nouveau token
            $token = $user->createToken('api-token')->plainTextToken;

            return ApiResponseTools::format('Token rafraîchi !', ['token' => $token, 'user' => $user]);

        } catch (\Exception $e) {
            return ApiResponseTools::devFormat(true,'Erreur', $e);
        }
    }

    
    

}
