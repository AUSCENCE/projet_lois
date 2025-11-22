<?php

namespace App\Http\Controllers;
/**
 * @OA\Info(
 *   title="Documentation de l'API Laravel",
 *   version="1.0.0",
 *   description="Implémentation de Swagger avec L5-Swagger.",
 *   @OA\Contact(
 *     name="KANHONOU AUSCENCE",
 *     email="contact@example.com"
 *   )
 * )
 *
 * @OA\Server(
 *   url="http://127.0.0.1:8000",
 *   description="Serveur local de l'API"
 * )
 *
 * @OA\Tag(
 *   name="Authentication",
 *   description="Opérations d'authentification (Register, Login, Logout, Profil)"
 * )
 *
 * @OA\SecurityScheme(
 *   securityScheme="sanctum",
 *   type="http",
 *   scheme="bearer",
 *   bearerFormat="Bearer"
 * )
 */
abstract class Controller
{
    //
}
