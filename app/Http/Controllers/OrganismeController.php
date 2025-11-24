<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreOrganismeRequest;
use App\Http\Requests\UpdateOrganismeRequest;
use App\Http\Tools\ApiResponseTools;
use App\Http\Tools\CrudTools;
use App\Http\Tools\DefaultMessageTools;
use App\Models\Organisme;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class OrganismeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(){
        //
        return CrudTools::list(Organisme::class, ['user']);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    
    /**
     * @OA\Post(
     *     path="/api/organisme/store",
     *     tags={"Organisme"},
     *     summary="Crée un nouvel Organisme",
     *
     *
     *     @OA\RequestBody(
     *         description="Organisme à créer",
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/Organisme")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Si( Success = true) Organisme créé avec succès   ",
     *         @OA\JsonContent(ref="#/components/schemas/Organisme")
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Les données envoyées sont incorrectes"
     *     )
     * ),
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'name'=>"required|string|unique:organismes,name"
        ]);
        if ($validator->fails()) {
            return ApiResponseTools::format(
                DefaultMessageTools::fieldValidation(),
                (array) $validator->errors()->messages(),
                false
            );
        }
        $data = $request->all();
        return CrudTools::saveOrUpdate($data,null,Organisme::class);
    }

    
    /**
     * @OA\Get(
     *     path="/api/organisme/show/{organisme}",
     *     tags={"Organisme"},
     *     summary="Récupère un organisme",
     *     description="Retourne l'organisme identifié par son ID.",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="organisme",
     *         in="path",
     *         required=true,
     *         description="Identifiant de l'organisme",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Organisme retourné",
     *         @OA\JsonContent(ref="#/components/schemas/Organisme")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Organisme non trouvé",
     *     )
     * )
     */
    public function show(Organisme $organisme)
    {
        //
        return ApiResponseTools::format('Organisme trouvé',$organisme);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Organisme $organisme)
    {
        //
        
    }

    /**
     * @OA\Put(
     *   path="/api/organisme/update/{organisme}",
     *   operationId="updateOrganisme",
     *   summary="Met à jour un organisme existant",
     *   tags={"Organisme"},
     *   description="Met à jour un organisme identifié par son ID.",
     *   security={{"sanctum":{}}},
     *   @OA\Parameter(ref="#/components/parameters/organisme--id"),
     *   @OA\RequestBody(
     *     description="Données de l'organisme à mettre à jour",
     *     required=true,
     *     @OA\JsonContent(ref="#/components/schemas/Organisme")
     *   ),
     *   @OA\Response(response=200,description="Organisme mis à jour",@OA\JsonContent(ref="#/components/schemas/Organisme")),
     *   @OA\Response(response=400,description="Données invalides"),
     *   @OA\Response(response=404,description="Organisme non trouvé")
     * )
     *
     * @param Request $request
     * @param Organisme $organisme
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function update(Request $request, Organisme $organisme)
    {
        //
        return response()->json([$request->all(),$organisme]);

        $validator = Validator::make($request->all(),[
            'name'=>"required|string|unique:organismes,name"
        ]);
        if ($validator->fails()) {
            return ApiResponseTools::format(
                DefaultMessageTools::fieldValidation(),
                (array) $validator->errors()->messages(),
                false
            );
        }
        $data = $request->all();
        return CrudTools::saveOrUpdate($data,$organisme,Organisme::class);
    }

    
    /**
     * @OA\Delete(
     *     path="/api/organisme/delete/{id}",
     *     tags={"Organisme"},
     *     security={{"sanctum":{}}},
     *     summary="Supprime un Organisme",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Identifiant de l' Organisme à supprimer",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Si (Success = true) Organisme supprimé avec succès "
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Organisme non trouvé"
     *     )
     * )
     */
    public function destroy(Organisme $organisme)
    {
        //
     
        if (!$organisme->projets) {
            return ApiResponseTools::format('Cet organisme à un ou plusieurs projet de lois enrégistré. Vous ne pouvez pas le supprimé.',null,false,400);
        }

        return CrudTools::deleteItem(Organisme::class,$organisme->id);

    }
}
