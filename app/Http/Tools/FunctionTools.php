<?php

namespace App\Http\Tools;

use App\Mail\ConfirmEmail;
use App\Mail\ResetPassword;
use App\Models\Article;
use App\Models\Media;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Image;

class FunctionTools
{

    /**
     * Cette fonction fait la sauvegarde du fichier  et retourne un nouveau chemin du fichier.
     * l'objet UploadedFile représentant le fichier à copier
     * le dossier de destination dans lequel le fichier doit être copié
     * Le nouveau nom du fichier
     * @author Auscence KANHONOU
     * @param  $file
     * @param  $destinationFolder
     * @param  $newFileName
     * @return string
     */
    public static function copyFileToStorage($file, $destinationFolder, $newFileName)
    {
        // Vérifie que le fichier a été correctement téléchargé
        if (!is_null($file) && $file->isValid()) {
            // Récupère l'extension du fichier
            $extension = $file->getClientOriginalExtension();
            //Supprimer les espaces du nom

            // Construit le nouveau nom de fichier
            $newFileNameWithExtension = $newFileName . '.' . $extension;

            // Copie le fichier dans le dossier de destination avec le nouveau nom de fichier
            $path = Storage::putFileAs($destinationFolder, $file, $newFileNameWithExtension);

            // Renvoie le chemin complet du fichier copié
            return $path;
        }

        // Renvoie null si le fichier n'a pas été correctement téléchargé
        return null;
    }

    /**
     * Cette fonction permet de supprimer un fichier média.
     * @param $path
     * @return bool|void
     */
    public static function deletefile($path)
    {
        Storage::delete($path);
        if (!Storage::exists($path)) {
            return true;
        }

    }


    /**
     * Cette function Crée les slugs
     *
     * @param [type] $titre
     * @return string
     */
    public static function slug($titre)
    {
        // transformer les chaines en minuscules
        $slug = strtolower($titre);
        //
        $slug = preg_replace('/[^A-Za-z0-9-]+/', '-', $slug);
        $slug = trim($slug, '-');

        return $slug;
    }

    /**
     * @param $cryper
     * @param $prefix
     * @return string
     * Cette fonction retourne un nom unique crypté par md5 au choix
     */
    public static function createUniqueName($cryper = false, $prefix = null,)
    {
        if ($cryper)
            if ($prefix)
                return strtoupper($prefix . '_' . md5(uniqid($prefix)));
            else
                return strtoupper(md5(uniqid()));
        else
            if ($prefix)
            return strtoupper($prefix . '_' . uniqid($prefix));
        else
            return strtoupper(uniqid());
    }


   

    /**
     * Redimensionner une image en utilisant la bibliothèque standard de PHP.
     *
     * @param string $path Chemin vers l'image d'origine.
     * @param int $newWidth Nouvelle largeur de l'image.
     * @param int $newHeight Nouvelle hauteur de l'image.
     * @return string Chemin complet vers l'image redimensionnée.
     */
    public static function redimensionnerImage($path, $newWidth, $newHeight)
    {
        // Ouvrir l'image d'origine
        $image = imagecreatefromjpeg($path);

        // Obtenir les dimensions de l'image d'origine
        $width = imagesx($image);
        $height = imagesy($image);

        // Calculer le ratio de redimensionnement pour conserver les proportions
        $ratio = $width / $height;

        // Vérifier si l'image doit être redimensionnée en largeur ou en hauteur
        if ($newWidth / $newHeight > $ratio) {
            $resizedWidth = $newHeight * $ratio;
            $resizedHeight = $newHeight;
        } else {
            $resizedWidth = $newWidth;
            $resizedHeight = $newWidth / $ratio;
        }

        // Créer une nouvelle image avec les dimensions souhaitées
        $newImage = imagecreatetruecolor($resizedWidth, $resizedHeight);

        // Redimensionner l'image en utilisant la nouvelle image créée
        imagecopyresampled($newImage, $image, 0, 0, 0, 0, $resizedWidth, $resizedHeight, $width, $height);

        // Enregistrer l'image redimensionnée dans le dossier de destination
        $imagePath = 'public/images/redimension/' . FunctionTools::createUniqueName(true, 'RED_IMG') . '.jpg';
        imagejpeg($newImage, $imagePath);

        // Libérer la mémoire en supprimant les ressources d'image
        imagedestroy($image);
        imagedestroy($newImage);

        // Retourner le chemin complet vers l'image redimensionnée
        return $imagePath;
    }

   

}
