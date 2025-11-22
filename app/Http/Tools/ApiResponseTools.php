<?php


namespace App\Http\Tools;


use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\Response;

class ApiResponseTools
{
    /**
     * @param bool $sens
     * @param string $message
     * @param  $datas
     * @param int $status
     * @return Application|ResponseFactory|Response
     * Cette fonction pour le formatage de la reponse stantard api
     */
    public static function format(string $message='', $datas=null, bool $success=true,int $status=200,): Response|Application|ResponseFactory
    {
        return response([
            'message'=>$message,
            'datas'=>$datas,
            'success'=>$success
        ],$status);
    }

    /**
     * Cette fonction est utilisé pour retourner les erreurs
     * @param bool $dev
     * @param $message
     * @param $e
     * @return Response|Application|ResponseFactory
     */
    public static function devFormat(bool $dev=true, $message='Une erreur intatendue est survenue. veuillez réesayez ou contactez le support.', $e=null): Response|Application|ResponseFactory
    {
        if($dev)
            return ApiResponseTools::format($e->getMessage(),null,false);
        else
            return ApiResponseTools::format($message,null,false);
    }
}
