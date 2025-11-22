<?php

namespace App\Http\Tools\Requete;

use App\Models\Projet;
use Illuminate\Support\Facades\DB;

class ProjetRequeteTool{

    public static function nbreVoter(Projet $projet){
        return $projet->users()->count();

    }

    public static function nbreVoterTrue(Projet $projet){
        return $projet->users()->where('vote',true)->count();
    }
}
