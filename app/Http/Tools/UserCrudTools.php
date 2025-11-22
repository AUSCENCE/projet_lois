<?php

namespace App\Http\Tools;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class UserCrudTools
{
    public static function saveOrUpdate($requestDatas, $rules, $object, $model): \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
    {
        //Validation du formulaire avec les règles passées en paramètre.
        $validator = Validator::make($requestDatas, $rules);
        if ($validator->fails()) {
            return ApiResponseTools::format(DefaultMessageTools::fieldValidation(), (array) $validator->errors()->messages(), false);
        }
        $roles = explode(',',$requestDatas['role_id']);
        unset($requestDatas['role_id']);
        unset($requestDatas['password']);
        DB::beginTransaction();
        if ($object) {
            //Modification de l'objet passé en paramètre
            try {
                if(key_exists('photo_profil',$requestDatas)){
                    $data = FunctionTools::copyFileToStorage($requestDatas['photo_profil'],'public/images/profils/',FunctionTools::createUniqueName(True,'IMG'));
                    $path = 'profils/'.$data;
                    //
                }
                if($data){
                    $requestDatas['photo_profil'] = $path;

                    $updateObjet = $object->update($requestDatas);
                    if ( $updateObjet){
                        $object = $model::find($object->id);
                        $object->roles()->sync($roles);

                        DB::commit();
                        return ApiResponseTools::format(DefaultMessageTools::successUpdate(), $object);
                    } else {
                        DB::rollBack();
                        return ApiResponseTools::format(DefaultMessageTools::successUpdate(false), null, false);
                    }
                }

            } catch (\Exception $exception) {
                DB::rollBack();
                return ApiResponseTools::devFormat(env('APP_DEBUG'), DefaultMessageTools::exceptionError(), $exception);
            }
        } else {
            //Creation de nouvel enregistrement.
            try {
                $newObject = $model::create($requestDatas);
                if ($newObject) {
                    $newObject->roles()->sync($roles);
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
}
