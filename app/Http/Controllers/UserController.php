<?php

namespace App\Http\Controllers;

use App\Http\Tools\ApiResponseTools;
use App\Http\Tools\CrudTools;
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

    /**
     * @OA\Post(
     *     path="/api/user/logout",
     *     tags={"Authentication"},
     *     summary="Déconnexion de l'utilisateur",
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Déconnexion réussie",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Logged out")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Non authentifié")
     * )
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            "message" => "Logged out"
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/user/me",
     *     tags={"Authentication"},
     *     summary="Récupérer l'utilisateur connecté",
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Informations de l'utilisateur connecté",
     *         @OA\JsonContent(ref="#/components/schemas/User")
     *     ),
     *     @OA\Response(response=401, description="Non authentifié")
     * )
     */
    public function user()
    {
        return  ApiResponseTools::format('Utilisateur connecté !',Auth::user());
    }

    /**
     * @OA\Post(
     *     path="/api/user/refresh",
     *     tags={"Authentication"},
     *     summary="Rafraîchir le token d'authentification",
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Token rafraîchi avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="token", type="string", example="1|laravel_sanctum_token..."),
     *             @OA\Property(property="user", ref="#/components/schemas/User")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Non authentifié")
     * )
     */
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
    /**
     * @OA\Get(
     *     path="/api/user",
     *     tags={"User Management"},
     *     summary="Liste des utilisateurs",
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Liste des utilisateurs récupérée avec succès",
     *     ),
     *     @OA\Response(response=401, description="Non authentifié")
     * )
     */
    public function index()
    {
        return CrudTools::list(User::class);
    }

    /**
     * @OA\Post(
     *     path="/api/user",
     *     tags={"User Management"},
     *     summary="Créer un nouvel utilisateur",
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "email", "password", "role"},
     *             @OA\Property(property="name", type="string", example="Jane Doe"),
     *             @OA\Property(property="email", type="string", format="email", example="jane@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="password"),
     *             @OA\Property(property="role", type="string", example="user")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Utilisateur créé avec succès",
     *         @OA\JsonContent(ref="#/components/schemas/User")
     *     ),
     *     @OA\Response(response=401, description="Non authentifié"),
     *     @OA\Response(response=422, description="Erreur de validation")
     * )
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:users,email',
            'password' => 'required',
            'role' => 'required|string'
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
        ]);

        return ApiResponseTools::format(DefaultMessageTools::successSave(), [
            'user' => $user,
            'password' => $request->password
        ], true, 201);
    }

    /**
     * @OA\Get(
     *     path="/api/user/{user}",
     *     tags={"User Management"},
     *     summary="Afficher un utilisateur spécifique",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="user",
     *         in="path",
     *         required=true,
     *         description="ID de l'utilisateur",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Détails de l'utilisateur",
     *         @OA\JsonContent(ref="#/components/schemas/User")
     *     ),
     *     @OA\Response(response=401, description="Non authentifié"),
     *     @OA\Response(response=404, description="Utilisateur non trouvé")
     * )
     */
    public function show(User $user)
    {
        return ApiResponseTools::format(DefaultMessageTools::succesRecherche(), $user);
    }

    /**
     * @OA\Put(
     *     path="/api/user/{user}/role",
     *     tags={"User Management"},
     *     summary="Mettre à jour le rôle d'un utilisateur",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="user",
     *         in="path",
     *         required=true,
     *         description="ID de l'utilisateur",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"role"},
     *             @OA\Property(property="role", type="string", example="admin")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Rôle mis à jour avec succès",
     *         @OA\JsonContent(ref="#/components/schemas/User")
     *     ),
     *     @OA\Response(response=401, description="Non authentifié"),
     *     @OA\Response(response=422, description="Erreur de validation")
     * )
     */
    public function updateRole(Request $request, User $user)
    {
        $request->validate([
            'role' => 'required|string'
        ]);

        $user->update(['role' => $request->role]);

        return ApiResponseTools::format(DefaultMessageTools::successUpdate(), $user);
    }

    /**
     * @OA\Delete(
     *     path="/api/user/{user}",
     *     tags={"User Management"},
     *     summary="Supprimer un utilisateur",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="user",
     *         in="path",
     *         required=true,
     *         description="ID de l'utilisateur",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Utilisateur supprimé avec succès"
     *     ),
     *     @OA\Response(response=401, description="Non authentifié"),
     *     @OA\Response(response=404, description="Utilisateur non trouvé")
     * )
     */
    public function destroy(User $user)
    {
        return CrudTools::deleteItem(User::class, $user->id);
    }
}
