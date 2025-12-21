<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
/**
* @OA\Schema(
*   schema="Projet",
*   title="Projet",
*   type="object",
*   required={"title","filePath"},
*   @OA\Property(type="integer",property="id",title="id",description="Identifiant du projet de lois",example=1,readOnly=true),
*   @OA\Property(type="string",property="title",title="title",description="Titre du projet de lois",example="Loi de finances 2025"),
*   @OA\Property(type="string",property="filePath",title="filePath",description="Chemin du fichier du projet de lois",example="/files/projet_1.pdf"),
*   @OA\Property(type="string",property="etat",title="etat",description="État du projet (en_cours, promulgue, etc)",example="en_cours"),
*   @OA\Property(type="boolean",property="avoter",title="avoter",description="Indique si le projet est à voter",example=true),
*   @OA\Property(type="string",property="cloturevoter",title="cloturevoter",description="Date de clôture du vote",example="2025-12-31T23:59:59Z",nullable=true),
*   @OA\Property(type="integer",property="organisme_id",title="organisme_id",description="Identifiant de l'organisme responsable",example=1),
*   @OA\Property(type="string",property="created_at",format="date-time",title="created_at",description="Date de création",example="2025-01-01T12:00:00Z",readOnly=true),
*   @OA\Property(type="string",property="updated_at",format="date-time",title="updated_at",description="Date de mise à jour",example="2025-01-02T12:00:00Z",readOnly=true)
* )
*
* @OA\Schema(
*   schema="Projets",
*   title="Projets",
*   @OA\Property(property="data", title="data", type="array",
*     @OA\Items(ref="#/components/schemas/Projet")
*   )
* )
*
* @OA\Parameter(
*   parameter="projet--id",
*   in="path",
*   name="projet",
*   required=true,
*   description="Identifiant de projet",
*   @OA\Schema(type="integer", example=1)
* )
*/
class Projet extends Model
{
    /** @use HasFactory<\Database\Factories\ProjetFactory> */
    use HasFactory;
    protected $fillable = ['id','title','filePath', 'etat','    ','cloturevoter', 'organisme_id','createdBy','updatedBy'];

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_projet', 'projet_id', 'user_id')
                    ->withPivot('vote', 'commentaire');
    }

    public function organisme(){
        return $this->belongsTo(Organisme::class,'organisme_id');
    }
}
