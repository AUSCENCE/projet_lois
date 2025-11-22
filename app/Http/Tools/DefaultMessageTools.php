<?php

namespace App\Http\Tools;

class DefaultMessageTools
{
    /**
     * @return string
     * Message d'enregistrement
     */
    public static function successSave($sens=true): string
    {
        if ($sens)
            return "Enregistrement effectué avec succès";
        else
            return "Échec de l'enregistrement. veuillez vérifier votre connexion et réessayer.";
    }

    /**
     * @return string
     * Message de modification
     */
    public static function successUpdate($sens=true): string
    {
        if ($sens)
            return "Modification effectué avec succès";
        else
            return "Échec de l'enregistrement. veuillez vérifier votre connexion et réessayer.";
    }

    /**
     * @return string
     * Pour les exception dans le catch
     */
    public static function exceptionError(): string
    {
        return "Une erreur est inattendu lors de l'exécution du programme. Veuillez réessayer ou contact l'administrateur.";
    }

    /**
     * @return string
     * Lorsque les validator ne passent pas.
     */
    public static function fieldValidation(): string
    {
        return "Erreur de validation des champs";
    }

    /**
     * @return string
     */
    public static function listeMessage(): string
    {
        return "Liste de données";
    }

    /**
     * Message lorsqu'une recherche n'aboutie pas.
     * @return string
     */
    public static function echecRecherche(): string
    {
        return "Aucune information trouvée.";
    }

    /**
     * Message lorsqu'une recherche n'aboutie pas.
     * @return string
     */
    public static function succesRecherche(): string
    {
        return "Recherche fructueuse.";
    }

    /**
     * Message lorsqu'un supprime.
     * @return string
     */
    public static function succesSuppression($allOk=true): string
    {
        if($allOk)
            return "Suppression effectuée avec succès.";
        else
            return "Certains élément n'on pas pu être supprimés";
    }

    /**
     * Message lorsque la suppression échoue.
     * @return string
     */
    public static function echecSuppression($message = 'Suppression impossible'): string
    {
        return $message;
    }




}
