<?php

namespace App\Http\Tools;

use App\Models\Projet;
use App\Models\User;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class CrudToolsProjet
{
    /**
     * @param $requestDatas
     * @param $rules
     * @param $object
     * @param $model
     * @return Application|ResponseFactory|Response
     * Cette fonction permet de faire un enregistrement ou une modification.
     * Elle reçoit le request, la règle de validation l'objet à modifier et le model
     */
    public static function saveOrUpdate($requestDatas, $object, $model,): Response|Application|ResponseFactory
    {
        //Validation du formulaire avec les règles passées en paramètre.
        /*   
            $validator = Validator::make($requestDatas, $rules);
            if ($validator->fails()) {
                return ApiResponseTools::format(DefaultMessageTools::fieldValidation(), (array) $validator->errors()->messages(), false);
            } 
        */
        
        DB::beginTransaction();
        if ($object) {
            //Modification de l'objet passé en paramètre
            try {
                $requestDatas['updatedBy'] = auth()->user()->id;
                if ($object->update($requestDatas)) {
                    DB::commit();
                    return ApiResponseTools::format(DefaultMessageTools::successUpdate(), $object);
                } else {
                    DB::rollBack();
                    return ApiResponseTools::format(DefaultMessageTools::successUpdate(false), null, false);
                }
            } catch (\Exception $exception) {
                DB::rollBack();
                return ApiResponseTools::devFormat(env('APP_DEBUG'), DefaultMessageTools::exceptionError(), $exception);
            }
        } else {
            //Creation de nouvel enregistrement.
            try {
                $requestDatas['createdBy'] = auth()->user()->id;
                $newObject = $model::create($requestDatas);

                if ($newObject) {
                    DB::commit();
                    return ApiResponseTools::format(DefaultMessageTools::successSave(), $newObject);
                } else {
                    DB::rollBack();
                    return ApiResponseTools::format(DefaultMessageTools::successSave(false), null, false);
                }
            } catch (\Exception $exception) {
                DB::rollBack();
                return ApiResponseTools::devFormat(env('APP_DEBUG'), DefaultMessageTools::exceptionError(), $exception);
            }
        }
    }

    /**
     * @param $model
     * @param $id
     * @return Application|ResponseFactory|Response
     * Cette fonction faire la suppression d'un élément ou une liste d'élément fournir en tableau
     */
    public static function deleteItem($model, $id): Response|Application|ResponseFactory
    {
        try {
            if (is_array($id)) {
                //Cas d'un tableau de ID à supprimer
                $undelete = [];
                DB::beginTransaction();
                foreach ($id as $it) {
                    $item = $model::find($it);
                    if ($item) {
                        if (!$item->delete())
                            $undelete[] = $it;
                    } else {
                        $undelete[] = $it;
                    }
                }
                DB::commit();
                //Traitement du résultat de la suppression
                if (count($undelete) == 0)
                    return ApiResponseTools::format(DefaultMessageTools::succesSuppression());
                elseif (count($id) - count($undelete) == 0)
                    return ApiResponseTools::format(DefaultMessageTools::echecSuppression(), $undelete, false);
                else
                    return ApiResponseTools::format(DefaultMessageTools::succesSuppression(false));
            } else {
                //Cas d'une suppression unique.
                $item = $model::find($id);
                DB::beginTransaction();
                if ($item) {
                    if ($item->delete()) {
                        DB::commit();
                        return ApiResponseTools::format(DefaultMessageTools::succesSuppression());
                    } else {
                        DB::rollBack();
                        return ApiResponseTools::format(DefaultMessageTools::echecSuppression(), null, false);
                    }
                } else {
                    DB::rollBack();
                    return ApiResponseTools::format(DefaultMessageTools::echecRecherche(), null, false);
                }
            }
        } catch (\Exception $exception) {
            DB::rollBack();
            return ApiResponseTools::devFormat(env('APP_DEBUG'), DefaultMessageTools::exceptionError(), $exception);
        }
    }

    /**
     * @param $model
     * @return Application|ResponseFactory|Response
     * La liste des éléments d'un model
     */
    public static function list($model, $relations = [], $per_page = 50): Response|Application|ResponseFactory
    {
        try {
            if (count($relations) > 0) {
                $liste = $model::with($relations)->paginate($per_page);
            } else {
                $liste = $model::paginate($per_page);
            }
            return ApiResponseTools::format(DefaultMessageTools::listeMessage(), $liste);
        } catch (\Exception $exception) {
            return ApiResponseTools::devFormat(env('APP_DEBUG'), DefaultMessageTools::exceptionError(), $exception);
        }
    }

    /**
     * Cette fonction retourne la recherche d'un id sur une table
     * @param $model
     * @param $id
     * @return Application|ResponseFactory|Response
     */
    public static function searchById($model, $id): Response|Application|ResponseFactory
    {
        try {
            $item = $model::find($id);
            if ($item)
                return ApiResponseTools::format(DefaultMessageTools::succesRecherche(), $item);
            else
                return ApiResponseTools::format(DefaultMessageTools::echecRecherche(), null, false);
        } catch (\Exception $exception) {
            return ApiResponseTools::devFormat(env('APP_DEBUG'), DefaultMessageTools::exceptionError(), $exception);
        }
    }

    /**
     * Cette fonction retourne la recherche d'un id sur une table
     * @param $object
     * @param null $model |si le model est specifier il faut obligatoirement les relations
     * @param array $relation
     * @return Application|ResponseFactory|Response
     */
    public static function show($object,$model=null,$relation=[]): Response|Application|ResponseFactory
    {
        try {
            if(count($relation) > 0)
                return ApiResponseTools::format(DefaultMessageTools::succesRecherche(), $model::with($relation)->first());
            else
                return ApiResponseTools::format(DefaultMessageTools::succesRecherche(), $object);
        } catch (\Exception $exception) {
            return ApiResponseTools::devFormat(env('APP_DEBUG'), DefaultMessageTools::exceptionError(), $exception);
        }
    }

    /**
     * @param $colonnes
     * @param $text
     * @return Application|Response|ResponseFactory
     */
    public static function searchByColonns($model, $colonns, $text, $relations = [],$perPage=50): Response|Application|ResponseFactory
    {
        try {
            if (count($relations) > 0) {
                $liste = $model::with($relations)
                    ->where(function ($q) use ($colonns, $text) {
                        foreach ($colonns as $key => $colonn) {
                            if ($key == 0) {
                                $q->where($colonn, 'like', "%$text%");
                            } else {
                                $q->orWhere($colonn, 'like', "%$text%");
                            }
                        }
                    })->paginate($perPage);
            }
            else {
                $liste = $model::where(function ($q) use ($colonns, $text) {
                    foreach ($colonns as $key => $colonn) {
                        if ($key == 0) {
                            $q->where($colonn, 'like', "%$text%");
                        } else {
                            $q->orWhere($colonn, 'like', "%$text%");
                        }
                    }
                })->paginate($perPage);
            }
            return ApiResponseTools::format(DefaultMessageTools::listeMessage(), $liste);
        } catch (\Exception $exception) {
            return ApiResponseTools::devFormat(env('APP_DEBUG'), DefaultMessageTools::exceptionError(), $exception);
        }
    }
    
    /**
     * Cette fonction permet de retourner les projets promulguer. 
     * @param  $perPage : Nombre de ligne à renvoyer par par page. 
     * @author Auscence KANHONOU
     * @return  Response|Application|ResponseFactory
     */
    public static function projetPromulguer($perPage = 50){
        try {
            $listes = Projet::with(['organisme'])
                    ->where('etat', 'promulgue')
                    ->paginate($perPage);
            return ApiResponseTools::format(DefaultMessageTools::listeMessage(), $listes);
        } catch (\Exception $exception) {
            return ApiResponseTools::devFormat(env('APP_DEBUG'), DefaultMessageTools::exceptionError(), $exception);
        }
    }

    
    /**
     * Cette fonction permet de retourner les projets voter non promulguer. 
     * @param  $perPage : Nombre de ligne à renvoyer par par page. 
     * @author Auscence KANHONOU
     * @return  Response|Application|ResponseFactory
     */
    public static function projetNonPromulegue($perPage = 50){
        try {
            $liste = Projet::with(['organisme'])
                    ->where('etat', '!=', 'promulgue')
                    ->paginate($perPage);
            return ApiResponseTools::format(DefaultMessageTools::listeMessage(), $liste);
        } catch (\Exception $exception) {
            return ApiResponseTools::devFormat(env('APP_DEBUG'), DefaultMessageTools::exceptionError(), $exception);
        }
    }
    
    /**
     * Cette fonction permet de retourner les projets en attente de vote. 
     * @param  $perPage : Nombre de ligne à renvoyer par par page. 
     * @author Auscence KANHONOU
     * @return  Response|Application|ResponseFactory
     */
    public static function projetAvoter($perPage = 50): Response|Application|ResponseFactory
    {
        try {
            $liste = Projet::with(['organisme'])
                    ->where('avoter',true)
                    ->where('cloturevoter','>',now()->format('Y-m-d'))
                    ->paginate($perPage);
            return ApiResponseTools::format(DefaultMessageTools::listeMessage(), $liste);
        } catch (\Exception $exception) {
            return ApiResponseTools::devFormat(env('APP_DEBUG'), DefaultMessageTools::exceptionError(), $exception);
        }
    }

    /**
     * Cette fonction permet de voter. Elle prend en parametre le projet. 
     * @param Projet $projet 
     * @param $requestDatas 
     * @author Auscence KANHONOU
     * @return  Response|Application|ResponseFactory
     */
    public static function voter(Projet $projet, $requestDatas): Response|Application|ResponseFactory
    {
        try {

            if (Auth::user()->role != 'depute' ) {
                return ApiResponseTools::format(DefaultMessageTools::successSave(false));
            }

            $requestDatas['user_id'] = Auth::user()->id;

            $validator = Validator::make($requestDatas, [
                'vote' => 'required|boolean',
                'commentaire' => 'nullable|string',
                'user_id' => 'required|integer|exists:users,id',
            ]);

            if ($validator->fails()) {
                return ApiResponseTools::format(DefaultMessageTools::fieldValidation(), (array) $validator->errors()->messages(), false);
            }

            // Vérifier si l'utilisateur a déjà voté
            if ($projet->users()->where('user_id', $requestDatas['user_id'])->exists()) {
                return ApiResponseTools::format('Vous avez déjà voté pour ce projet.', null, false, 400);
            }

            $projet->users()->attach($requestDatas['user_id'], [
                'vote' => $requestDatas['vote'],
                'commentaire' => $requestDatas['commentaire'] ?? null
            ]);
            
            return ApiResponseTools::format(DefaultMessageTools::successSave());

        } catch (\Exception $exception) {
            return ApiResponseTools::devFormat(env('APP_DEBUG'), DefaultMessageTools::exceptionError(), $exception);
        }

    }
    

    

}
