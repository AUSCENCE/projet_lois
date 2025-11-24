<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/** @OA\Schema(
 *   schema="Organisme",
 *   title="Organisme",
 *   type="object",
 *   required ={},
 *   @OA\Property(type="integer",property="id",title="id" , description="Identifiant de l'Organisme",example=1,readOnly="true"),
 *   @OA\Property( type="string",property="name",title="name" , description="Nom de l'Organisme",example="Présidence")
 * ),
 * 
 * @OA\Schema(
 *   schema="Organismes",
 *   title="Organismes",
 *   @OA\Property(title="data",property="data",type="array",
 *     @OA\Items(type="object",ref="#/components/schemas/Organisme"),
 *   )
 * ),
 * 
 * 
 * @OA\Parameter(
 *      parameter="organisme--id",
 *      in="path",
 *      name="organisme_id",
 *      required=true,
 *      description="Identifiant de organisme",
 *      @OA\Schema(
 *          type="integer",
 *          example="1",
 *      )
 * ),
*/

class Organisme extends Model
{
    /** @use HasFactory<\Database\Factories\OrganismeFactory> */
    use HasFactory;
    protected $fillable = ['id', 'name','createdBy','updatedBy'];

    public function user()
    {
        return $this->belongsTo(User::class,'createdBy');
    }

    public function projets()
    {
        return $this->hasMany(Projet::class);
    }

}
