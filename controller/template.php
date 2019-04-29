<?php
// *************************************************************************************************
// * Template pour un contrôleur
// *************************************************************************************************
class NomController {

    /**
     * Par défaut.
     */
    public static function index() {
        return Controller::push("Nom de la page", "./view/vue.php", 
                                [ "nom1" => "valeur1",
                                  "nom2" => "valeur2" ]);
    }
    
    
}