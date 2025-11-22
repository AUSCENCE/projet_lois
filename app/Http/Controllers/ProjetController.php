<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProjetRequest;
use App\Http\Requests\UpdateProjetRequest;
use App\Http\Tools\ApiResponseTools;
use App\Http\Tools\CrudTools;
use App\Http\Tools\CrudToolsProjet;
use App\Http\Tools\DefaultMessageTools;
use App\Http\Tools\FunctionTools;
use App\Models\Projet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProjetController extends Controller
{
     /**
     * @OA\Get(
     *     path="/api/projet",
     *     operationId="listProjets",
     *     tags={"Projets"},
     *     summary="Récupère la liste des projets",
     *     description="Retourne une liste paginée de projets, incluant les relations 'users' et 'organisme'.",
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Numéro de page pour la pagination",
     *         required=false,
     *         @OA\Schema(type="integer", default=1)
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Nombre d'éléments par page",
     *         required=false,
     *         @OA\Schema(type="integer", default=15)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Liste des projets récupérée avec succès",
     *         @OA\JsonContent(ref="#/components/schemas/Projets")
     *     ),
     *     @OA\Response(response=401, description="Non autorisé"),
     *     @OA\Response(response=403, description="Accès refusé"),
     *     @OA\Response(response=500, description="Erreur serveur"),
     *    security={{"bearerAuth":{}},}
     * )
     */
    public function index()
    {
        return CrudToolsProjet::list(Projet::class,['organisme']);
    }

   
    /**
     * @OA\Get(
     *     path="/api/projet/promuleguer",
     *     operationId="listProjetPromuleguer",
     *     tags={"Projets"},
     *     summary="Récupère les projets promulgués",
     *     description="Retourne la liste des projets ayant le statut promulgué.",
     *     @OA\Response(
     *         response=200,
     *         description="Liste des projets promulgués",
     *         @OA\JsonContent(ref="#/components/schemas/Projets")
     *     ),
     *     @OA\Response(response=401, description="Non autorisé"),
     *     @OA\Response(response=500, description="Erreur serveur"),
     *     security={{"bearerAuth":{}}}
     * )
     */
   
    public function promulegue()
    {
        CrudToolsProjet::projetPromulguer();
    }
     /**
     * @OA\Get(
     *     path="/api/projet/nonPromulegue",
     *     operationId="listProjetNonPromulegue",
     *     tags={"Projets"},
     *     summary="Récupère les projets non promulgués",
     *     description="Retourne la liste des projets n'ayant pas le statut promulgué.",
     *     @OA\Response(
     *         response=200,
     *         description="Liste des projets non promulgués",
     *         @OA\JsonContent(ref="#/components/schemas/Projets")
     *     ),
     *     @OA\Response(response=401, description="Non autorisé"),
     *     @OA\Response(response=500, description="Erreur serveur"),
     *     security={{"bearerAuth":{}}}
     * )
     */
    public function nonPromulegue()
    {
        CrudToolsProjet::projetNonPromulegue();
    }
    
    /**
     * @OA\Get(
     *     path="/api/projet/avoter",
     *     operationId="listProjetAvoter",
     *     tags={"Projets"},
     *     summary="Récupère les projets à voter",
     *     description="Retourne la liste des projets en attente de vote.",
     *     @OA\Response(
     *         response=200,
     *         description="Liste des projets à voter",
     *         @OA\JsonContent(ref="#/components/schemas/Projets")
     *     ),
     *     @OA\Response(response=401, description="Non autorisé"),
     *     @OA\Response(response=500, description="Erreur serveur"),
     *     security={{"bearerAuth":{}}}
     * )
     */
    public function Avoter()
    {
        CrudToolsProjet::projetAvoter();
    }

    /**
    * @OA\Post(
    *     path="/api/projet/voter/{projet_id}",
    *     operationId="voterProjet",
    *     tags={"Projets"},
    *     summary="Vote pour ou contre un projet",
    *     description="Enregistre le vote d'un utilisateur sur un projet de lois.",
    *     @OA\Parameter(ref="#/components/parameters/projet--id"),
    *     @OA\RequestBody(
    *         description="Données du vote",
    *         required=true,
    *         @OA\JsonContent(
    *             type="object",
    *             required={"vote"},
    *             @OA\Property(property="vote", type="boolean", description="Vote de l'utilisateur (true=pour, false=contre)", example=true),
    *             @OA\Property(property="commentaire", type="string", description="Commentaire optionnel du vote", example="En accord avec le projet")
    *         )
    *     ),
    *     @OA\Response(
    *         response=200,
    *         description="Vote enregistré avec succès",
    *         @OA\JsonContent(
    *             type="object",
    *             @OA\Property(property="message", type="string", example="Vote enregistré avec succès"),
    *             @OA\Property(property="vote", type="object",
    *                 @OA\Property(property="user_id", type="integer", example=1),
    *                 @OA\Property(property="projet_id", type="integer", example=1),
    *                 @OA\Property(property="vote", type="boolean", example=true),
    *                 @OA\Property(property="commentaire", type="string", example="En accord avec le projet")
    *             )
    *         )
    *     ),
    *     @OA\Response(response=400, description="Données de vote invalides"),
    *     @OA\Response(response=404, description="Projet non trouvé"),
    *     @OA\Response(response=401, description="Non autorisé"),
    *     @OA\Response(response=500, description="Erreur serveur"),
    *     security={{"bearerAuth":{}}}
    * )
    */
    public function voter(Projet $projet, Request $request)
    {
        $data = $request->all();
        CrudToolsProjet::voter($projet, $data );
    }

    
    /**
     * @OA\Post(
     *     path="/api/projet/store",
     *     operationId="storeProjet",
     *     tags={"Projets"},
     *     summary="Crée un nouvel projet",
     *     description="Crée et enregistre un nouveau projet de lois en base de données.",
     *     @OA\RequestBody(
     *         description="Données du projet à créer",
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/Projet")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Projet créé avec succès",
     *         @OA\JsonContent(ref="#/components/schemas/Projet")
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Les données envoyées sont incorrectes",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Projet créé avec succès"),
     *             @OA\Property(property="data", ref="#/components/schemas/Projet")
     *         )
     *     ),
     *     security={{"bearerAuth":{}}}
     * )
    */
    public function store(Request $request)
    {
         /*  return ApiResponseTools::format(
            'Projet créé avec succès !',
            [$request->all(), $request->file('filePath')],
            true
        );
         */
        $validator = Validator::make($request->all(), [
            'title'        => 'required|string|unique:projets,title',
            'filePath' => 'nullable|file|mimes:pdf|max:50240',
            'organisme_id' => 'required|exists:organismes,id'
        ]);

        if ($validator->fails()) {
            return ApiResponseTools::format(
                DefaultMessageTools::fieldValidation(),
                (array) $validator->errors()->messages(),
                false
            );
        }

        // 📂 Préparation des données
        $data = $request->except('filePath');

        // 📁 Traitement du fichier PDF
        if ($request->hasFile('filePath')) {
            $data['filePath'] = FunctionTools::copyFileToStorage(
                $request->file('filePath'),
                'Projet',
                $request->title
            );
        }

        // 💾 Enregistrement en base
        $projet = CrudToolsProjet::saveOrUpdate($data, null, Projet::class);

        return ApiResponseTools::format(
            'Projet créé avec succès !',
            $projet,
            true
        );
    }


   
    /**
     * @OA\Get(
     *     path="/api/projet/show/{projet_id}",
     *     operationId="showProjet",
     *     tags={"Projets"},
     *     summary="Affiche les détails d'un projet",
     *     description="Retourne les informations détaillées d'un projet spécifique, incluant ses utilisateurs et son organisme.",
     *     @OA\Parameter(ref="#/components/parameters/projet--id"),
     *     @OA\Response(
     *         response=200,
     *         description="Détails du projet",
     *         @OA\JsonContent(ref="#/components/schemas/Projet")
     *     ),
     *     @OA\Response(response=404, description="Projet non trouvé"),
     *     @OA\Response(response=401, description="Non autorisé"),
     *     @OA\Response(response=500, description="Erreur serveur"),
     *     security={{"bearerAuth":{}}}
     * )
     */
    public function show(Projet $projet)
    {
        CrudToolsProjet::show($projet,Projet::class,['organisme']);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Projet $projet)
    {
        //
    }

   
    /**
     * @OA\Put(
     *     path="/api/projet/update/{projet_id}",
     *     operationId="updateProjet",
     *     tags={"Projets"},
     *     summary="Met à jour un projet",
     *     description="Met à jour les informations d'un projet existant.",
     *     @OA\Parameter(ref="#/components/parameters/projet--id"),
     *     @OA\RequestBody(
     *         description="Données du projet à mettre à jour",
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/Projet")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Projet mis à jour avec succès",
     *         @OA\JsonContent(ref="#/components/schemas/Projet")
     *     ),
     *     @OA\Response(response=400, description="Les données envoyées sont incorrectes"),
     *     @OA\Response(response=404, description="Projet non trouvé"),
     *     @OA\Response(response=401, description="Non autorisé"),
     *     @OA\Response(response=500, description="Erreur serveur"),
     *     security={{"bearerAuth":{}}}
     * )
     */
     public function update(UpdateProjetRequest $request, Projet $projet)
    {
        CrudToolsProjet::saveOrUpdate($request->all(),$projet,Projet::class);

    }

     /**
     * @OA\Delete(
     *     path="/api/projet/delete/{projet_id}",
     *     operationId="destroyProjet",
     *     tags={"Projets"},
     *     summary="Supprime un projet",
     *     description="Supprime un projet de lois de la base de données.",
     *     @OA\Parameter(ref="#/components/parameters/projet--id"),
     *     @OA\Response(
     *         response=204,
     *         description="Projet supprimé avec succès"
     *     ),
     *     @OA\Response(response=404, description="Projet non trouvé"),
     *     @OA\Response(response=401, description="Non autorisé"),
     *     @OA\Response(response=403, description="Accès refusé"),
     *     @OA\Response(response=500, description="Erreur serveur"),
     *     security={{"bearerAuth":{}}}
     * )
     */

    public function destroy(Projet $projet)
    {
        CrudToolsProjet::deleteItem(Projet::class, $projet->id);
    }
}
