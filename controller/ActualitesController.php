<?php
// *************************************************************************************************
// * Contrôleur pour les actualités.
// *************************************************************************************************
class ActualitesController {

    /**
     * Récupère une actualité depuis un formulaire.
     * @return une actualité
     */
    private static function __getFromForm() {
        $inputId = -1;
        $inputDate = -1;
        $inputTitre = "";
        $inputContenu = "";
        $inputTitreLien = "";
        $inputLien = "";
        $inputPrioritaire = 0;
        $inputAnnees = "";
        $inputActive = 0;
        
        if(isset($_POST['inputId'])) $inputId = intval($_POST['inputId']);
        if(isset($_POST['inputDate'])) $inputDate = $_POST['inputDate'];
        if(isset($_POST['inputTitre'])) $inputTitre = $_POST['inputTitre'];
        if(isset($_POST['inputContenu'])) $inputContenu = $_POST['inputContenu'];
        if(isset($_POST['inputTitreLien'])) $inputTitreLien = $_POST['inputTitreLien'];
        if(isset($_POST['inputLien'])) $inputLien = $_POST['inputLien'];
        if(isset($_POST['inputPrioritaire'])) {
            $inputPrioritaire = intval($_POST['inputPrioritaire']);
            if(($inputPrioritaire != 0) && ($inputPrioritaire != 1))
                $inputPrioritaire = 0;
        }    
        if(isset($_POST['inputAnnees'])) {
            if(in_array("L1", $_POST['inputAnnees'])) $inputAnnees .= "L1;";
            if(in_array("L2", $_POST['inputAnnees'])) $inputAnnees .= "L2;";
            if(in_array("L3", $_POST['inputAnnees'])) $inputAnnees .= "L3;";
            if(in_array("L3P", $_POST['inputAnnees'])) $inputAnnees .= "L3P;";
        }
        if(isset($_POST['inputActive'])) {
            $inputActive = intval($_POST['inputActive']);
            if(($inputActive != 0) && ($inputActive != 1))
                $inputActive = 1;
        }

        if(($inputDate = DateTools::date2Timestamp($inputDate, false)) === false)
            $inputDate = 0;
        
        return new Actualite($inputId, $inputDate, $inputTitre, $inputContenu, $inputTitreLien, 
                             $inputLien, DataTools::int2Boolean($inputPrioritaire),
                             $inputAnnees, DataTools::int2Boolean($inputActive));
    }

    /**
     * Ajoute une actualité.
     * #RIGHTS# : uniquement un administrateur
     */
    public static function ajouter() {
        if(!UserModel::estAdmin())
            Controller::goTo("", "", "Vous n'avez pas les droits suffisants.");
        
        // L'utilisateur a annulé
        if(isset($_POST['btnAnnuler']))
            Controller::goTo("actualites/index.php", "L'actualité n'a pas été ajoutée.");
        
        // Le formulaire a été validé
        $data = [];
        if(isset($_POST['btnAjouter'])) {
            $data['actualite'] = self::__getFromForm();
            
            if(($data['actualite']->getDate() != -1) && ($data['actualite']->getTitre() != "") && ($data['actualite']->getContenu() != "")) {
                if($data['actualite']->getDate() == 0)
                    WebPage::setCurrentErrorMsg("La date saisie est invalide.");
                else {
                    if(ActualiteModel::create($data['actualite']))
                        Controller::goTo("actualites/index.php", "L'actualité a été ajoutée.");
                    else
                        WebPage::setCurrentErrorMsg("L'actualité n'a pas été ajoutée dans la base de données.");
                }
            }        
            else
                WebPage::setCurrentErrorMsg("Vous devez remplir toutes les informations obligatoires.");
        }        
        
        return Controller::push("Ajouter une actualité", "./view/actualites/ajouter.php", $data);
    }    
    
    /**
     * Modifie une actualité.
     * #RIGHTS# : uniquement un administrateur
     */
    public static function modifier() {
        if(!UserModel::estAdmin())
            Controller::goTo("", "", "Vous n'avez pas les droits suffisants.");
        
        // L'utilisateur a annulé
        if(isset($_POST['btnAnnuler']))
            Controller::goTo("actualites/index.php", "L'actualité n'a pas été modifiée.");
        
        // Le formulaire a été validé
        $data = [];
        if(isset($_POST['btnModifier'])) {
            $data['actualite'] = self::__getFromForm();
            
            if(($data['actualite']->getDate() != -1) && ($data['actualite']->getTitre() != "") && ($data['actualite']->getContenu() != "")) {
                if($data['actualite']->getDate() == 0)
                    WebPage::setCurrentErrorMsg("La date saisie est invalide.");
                else {
                    if(ActualiteModel::update($data['actualite']))
                        Controller::goTo("actualites/index.php", "L'actualité a été modifiée.");
                    else
                        WebPage::setCurrentMsg("L'actualité n'a pas été modifée dans la base de données.");
                }
            }        
            else
                WebPage::setCurrentErrorMsg("Vous devez remplir toutes les informations obligatoires.");
        }      
        else
            $data['actualite'] = ActualiteModel::read(intval($_POST['idModi']));
        
        return Controller::push("Modifier une actualité", "./view/actualites/modifier.php", $data);
    }

    /**
     * Supprime une actualité.
     * #RIGHTS# : uniquement un administrateur
     */
    public static function supprimer() {
        if(!UserModel::estAdmin())
            Controller::goTo("", "", "Vous n'avez pas les droits suffisants.");
        
        // L'utilisateur a annulé
        if(isset($_POST['btnAnnuler']))
            Controller::goTo("actualites/index.php", "L'actualité n'a pas été supprimée.");
        
        // Le formulaire a été validé
        $data = [];
        if(isset($_POST['btnSupprimer'])) {
            if(ActualiteModel::delete(intval($_POST['btnSupprimer'])))
                Controller::goTo("actualites/index.php", "L'actualité a été supprimée.");
            else {
                WebPage::setCurrentErrorMsg("L'actualité n'a pas été supprimée de la base de données.");
                $data['actualite'] = ActualiteModel::load(intval($_POST['btnSupprimer']));
            }
        }
        else
            $data['actualite'] = ActualiteModel::read(intval($_POST['idSupp']));

        return Controller::push("Supprimer une actualité", "./view/actualites/supprimer.php", $data);
    }
    
    /**
     * Active une actualité.
     * #RIGHTS# : uniquement un administrateur
     */
    public static function activer() {
        $json = array();
        if(!UserModel::estAdmin())
            $json["code"] = -1;
        else {
            if(!isset($_POST['mode']) || !isset($_POST['id']))
                $json["code"] = -1;
            else {
                $id = intval($_POST['id']);
                $mode = DataTools::int2Boolean($_POST['mode']);
                if(ActualiteModel::active($id, $mode))
                    $json["code"] = 1;
                else
                    $json["code"] = -1;
            }
        }
        return Controller::JSONpush($json);
    }
    
    /**
     * Récupère les actualités.
     * #RIGHTS# : aucun (liste publique)
     */
    public static function get() {
        $annee = "";
        if(isset($_POST['annee'])) {
            switch($_POST['annee']) {
                case 1: $annee = "L1"; break;
                case 2: $annee = "L2"; break;
                case 3: $annee = "L3"; break;
                case 4: $annee = "L3P"; break;
            }
        }
        $mode = true;
        if(isset($_POST['mode'])) {
            if(intval($_POST['mode']) == 0) $mode = false;
        }
        $data['actualites'] = ActualiteModel::getList(false, $mode, $annee);
        
        Controller::push("", "./view/actualites/liste.php", $data, "");
        exit();
    }
    
    /**
     * Récupère la liste des actualités
     * #RIGHTS# : vue suivant les droits
     */
    public static function index() {
        return Controller::push("Liste des actualités", "./view/actualites/actualites.php",
                                [ "actualites" => ActualiteModel::getList(true) ]);
    }
}