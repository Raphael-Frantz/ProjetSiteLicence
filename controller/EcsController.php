<?php
// *************************************************************************************************
// * Contrôleur pour les ECS
// *************************************************************************************************
class EcsController {
    
    /**
     * Récupère un EC depuis un formulaire.
     * @return un EC
     */
    public static function __getFromForm() : EC {
        $inputCode = "";
        $inputIntitule = "";
        
        if(isset($_POST['inputCode'])) $inputCode = $_POST['inputCode'];
        if(isset($_POST['inputIntitule'])) $inputIntitule = $_POST['inputIntitule'];
        
        return new EC(-1, $inputCode, $inputIntitule);
    }    
    
    /**
     * Ajoute un EC.
     * #RIGHTS# : administrateur, responsable d'un diplôme
     */
    public static function ajouter() {
        if(!UserModel::isConnected())
            Controller::goTo("", "", "Vous devez être connecté pour accéder à cette section.");
        
        if(!UserModel::estRespDiplome())
            Controller::goTo("", "", "Vous n'avez pas les droits suffisants.");
        
        // L'utilisateur a annulé
        if(isset($_POST['btnAnnuler']))
            Controller::goTo("ecs/index.php", "L'EC n'a pas été ajouté.");
        
        // Le formulaire a été validé
        $data = [];
        if(isset($_POST['btnAjouter'])) {
            $data['EC'] = self::__getFromForm();
            
            if(($data['EC']->getIntitule() != "") && ($data['EC']->getCode() != "")) {
                if(ECModel::create($data['EC'])) {
                    // Ajout des responsables
                    if(isset($_POST['resp'])) {
                        foreach($_POST['resp'] as $resp) {
                            RespECModel::ajouter($data['EC']->getId(), $resp);
                        }
                    }
                    
                    Controller::goTo("ecs/index.php", "L'EC a été ajouté.");
                }
                else
                    WebPage::setCurrentErrorMsg("L'EC n'a pas été ajouté dans la base de données.");
            }        
            else
                WebPage::setCurrentErrorMsg("Vous devez remplir toutes les informations obligatoires.");
        }
        $data['users'] = UserModel::getList(User::TYPE_ENSEIGNANT);
        
        return Controller::push("Ajouter un EC", "./view/ecs/ajouter.php", $data);
    }
    
    /**
     * Modifie un EC.
     * #RIGHTS# : administrateur, responsable d'un diplôme
     */
    public static function modifier() {
        if(!UserModel::isConnected())
            Controller::goTo("", "", "Vous devez être connecté pour accéder à cette section.");
        
        if(!UserModel::estRespDiplome())
            Controller::goTo("", "", "Vous n'avez pas les droits suffisants.");
        
        // L'utilisateur a annulé
        if(isset($_POST['btnAnnuler']))
            Controller::goTo("ecs/index.php", "L'EC n'a pas été modifié.");
        
        // Le formulaire a été validé
        $data = [];
        if(isset($_POST['btnModifier'])) {
            if(!isset($_SESSION['current']['EC']))
                Controller::goTo("ecs/index.php", "", "Erreur lors de la récupération de l'EC.");
            
            $data['EC'] = self::__getFromForm();
            $data['EC']->setId($_SESSION['current']['EC']);
            
            // Mise-à-jour du/des responsable(s)
            RespECModel::supprimer($_SESSION['current']['EC'], -1);
            if(isset($_POST['resp'])) {
                foreach($_POST['resp'] as $resp) {
                    RespECModel::ajouter($_SESSION['current']['EC'], $resp);
                }
            }
            
            // Mise-à-jour de l'EC
            $erreur = "";
            if($data['EC']->getIntitule() == "") $erreur .= "Vous devez saisir l'intitulé. ";
            if($data['EC']->getCode() == "") $erreur .= "Vous devez saisir un code. ";
            
            if($erreur === "") {
                if(ECModel::update($data['EC']))
                    Controller::goTo("ecs/index.php", "L'EC a été modifié.");
                else
                    WebPage::setCurrentMsg("L'EC n'a pas été modifé dans la base de données.");
            }        
            else
                WebPage::setCurrentErrorMsg($erreur);
        }      
        else {
            if(($data['EC'] = ECModel::read(intval($_POST['idModi']))) === null)
                return Controller::goTo("ecs/index.php", "", "Erreur lors de la récupération de l'EC.");
            $_SESSION['current']['EC'] = intval($_POST['idModi']);
        }

        $data['users'] = UserModel::getList(User::TYPE_ENSEIGNANT);
        $data['resp'] = RespECModel::getListResponsables($_SESSION['current']['EC']);
        if(($data['users'] === null) || ($data['resp'] === null))
            return Controller::goTo("ecs/index.php", "", "Erreur lors de la récupération des utilisateurs.");
        
        return Controller::push("Modifier un EC", "./view/ecs/modifier.php", $data);
    }

    /**
     * Supprime un EC.
     * #RIGHTS# : administrateur
     */
    public static function supprimer() {
        if(!UserModel::isConnected())
            Controller::goTo("", "", "Vous devez être connecté pour accéder à cette section.");
        
        if(!UserModel::estAdmin())
            Controller::goTo("", "", "Vous n'avez pas les droits suffisants.");
        
        // L'utilisateur a annulé
        if(isset($_POST['btnAnnuler']))
            Controller::goTo("ecs/index.php", "L'EC n'a pas été supprimé.");
        
        // Le formulaire a été validé
        $data = [];
        if(isset($_POST['btnSupprimer'])) {
            if(!isset($_SESSION['current']['EC']))
                Controller::goTo("ecs/index.php", "L'EC n'a pas été supprimé.");
            
            if(ECModel::delete($_SESSION['current']['EC']))
                Controller::goTo("ecs/index.php", "L'EC a été supprimé.");
            else {
                WebPage::setCurrentErrorMsg("L'EC n'a pas été supprimé de la base de données.");
                if(($data['EC'] = ECModel::read($_SESSION['current']['EC'])) === null)
                    Controller::goTo("ecs/index.php", "", "Erreur lors de la récupération de l'EC.");
            }
        }
        else {
            if(($data['EC'] = ECModel::read(intval($_POST['idSupp']))) == null)
                return Controller::goTo("ecs/index.php", "", "Erreur lors de la récupération de l'EC.");
            $_SESSION['current']['EC'] = intval($_POST['idSupp']);
        }

        return Controller::push("Supprimer un EC", "./view/ecs/supprimer.php", $data);
    }
    
    /**
     * Service Web.
     * #RIGHTS# : variable
     */
    public static function ws() : void {
        $mode = 0;
        if(isset($_POST['mode']))
            $mode = intval($_POST['mode']);
        
        switch($mode) {
            case 1: // Liste des EC
                // #RIGHTS# : aucun
                $json = [];
                $json['liste'] = ECModel::getList();
                if($json['liste'] === null) {
                    $json['code'] = -1;
                    $json['msg'] = "Erreur lors de la récupération de la liste des EC.";
                }
                else
                    $json['code'] = 1;
                Controller::JSONpush($json);
                break;
            case 2: // Liste des étudiants d'une EC
                // #RIGHTS# : intervenant d'EC
                $data['etudiants'] = [];
                if(isset($_SESSION['current']['EC']) && UserModel::estIntEC($_SESSION['current']['EC'])) {
                    if(isset($_POST['groupe'])) {
                        if(intval($_POST['groupe']) == -1) {
                            if(isset($_SESSION['current']['EC']))
                                $data['etudiants'] = InscriptionECModel::getListeEtudiants($_SESSION['current']['EC']);
                        }
                        else {
                            $data['etudiants'] = InscriptionGroupeECModel::getListe(intval($_POST['groupe']));
                        }
                    }
                }
                Controller::push("", "./view/ecs/etudiants_liste.php", $data, "");
                break;
            default:
                Controller::goTo();
        }
    }    
    
    /**
     * Liste des ECs.
     * #RIGHTS# : administrateur, responsable d'un diplôme, responsable d'un EC, intervenant d'un EC
     */
    public static function liste() {
        if(!UserModel::isConnected())
            Controller::goTo("", "", "Vous devez être connecté pour accéder à cette section.");
        
        if(!UserModel::estEnseignant())
            Controller::goTo("", "", "Vous n'avez pas les droits suffisants.");
        
        if(UserModel::estAdmin()) {
            // L'administrateur accède à tous les ECs
            $data['ECS'] = ECModel::getList();
        }
        else {
            // On récupère la liste des ECs accessibles comme :
            // 1) responsable de diplôme
            // 2) responsable d'EC 
            // 3) intervenant
            
            // Liste des ECs dont on est le responsable
            $resp = RespECModel::getList(UserModel::getId());
            // Liste des ECs dont on est intervenant
            $int = IntGroupeECModel::getListECs(UserModel::getId());
                        
            // Merge des deux listes en gardant l'ordre alphabétique
            $data['ECS'] = DataTools::mergeArray('code', $resp, $int);
            
            // Idem pour chaque responsabilité de diplôme !!!
            $diplomes = UserModel::getListDiplomes();
            foreach($diplomes as $diplome) {
                $ECs = UEECModel::getListEC($diplome);
                $data['ECS'] = DataTools::mergeArray('code', $data['ECS'], $ECs);
            }
        }  
        if(isset($_SESSION['current']['EC'])) {
            $data['current'] = $_SESSION['current']['EC'];
            unset($_SESSION['current']['EC']);
        }
        
        return Controller::push("Liste des ECs", "./view/ecs/liste.php", $data);
    }
    
    /**
     * Exportation des étudiants d'un EC.
     * #RIGHTS# : administrateur, responsable d'un diplôme, responsable EC
     */
    public static function exporter() {
        if(!UserModel::isConnected())
            Controller::goTo("", "", "Vous devez être connecté pour accéder à cette section.");
        
        if(!isset($_SESSION['current']['EC']))
            Controller::goTo("ecs/liste.php", "", "L'identifiant de l'EC n'a pas été précisé.");
        
        if(!UserModel::estRespEC($_SESSION['current']['EC']))
            Controller::goTo("ecs/liste.php", "", "Vous n'avez pas les droits suffisants.");
        
        if(($EC = ECModel::read($_SESSION['current']['EC'])) === null)
            Controller::goTo("ecs/liste.php", "", "Erreur lors du chargement de l'EC.");
        
        if(isset($_POST['groupe']) && (intval($_POST['groupe']) != -1)) {
            if(($groupe = GroupeECModel::read(intval($_POST['groupe']))) === null)
                Controller::goTo("ecs/liste.php", "", "L'identifiant du groupe est incorrect.");
            else {
                $nomFichier = "etudiants_".$EC->getCode()."_".$groupe->getIntitule();
                if(($data = InscriptionGroupeECModel::getListe(intval($_POST['groupe']))) === null)
                    Controller::goTo("ecs/liste.php", "", "Erreur lors de la récupération de la liste.");
            }
        }
        else {
            $nomFichier = "etudiants_".$EC->getCode();
            if(($data = InscriptionECModel::getListeEtudiants($_SESSION['current']['EC'])) === null)
                Controller::goTo("ecs/liste.php", "", "Erreur lors de la récupération de la liste.");
        }
 
        return Controller::CSVpush($nomFichier, $data,
                                   [ "numero" => "numero", "nom" => "nom",
                                     "prenom" => "prenom", "email" => "email" ]);
    }    
    
    /**
     * Liste des épreuves d'un EC.
     * #RIGHTS# : administrateur, responsable d'un diplôme, responsable EC, intervenant EC
     */
    public static function epreuves() {
        if(!UserModel::isConnected())
            Controller::goTo("", "", "Vous devez être connecté pour accéder à cette section.");
        
        if(!isset($_POST['idNot']))
            Controller::goTo("ecs/liste.php", "", "L'identifiant de l'EC n'a pas été précisé.");
        $_SESSION['current']['EC'] = intval($_POST['idNot']);
        
        if(!UserModel::estRespEC($_SESSION['current']['EC']))
            Controller::goTo("ecs/liste.php", "", "Vous n'avez pas les droits suffisants.");

        if(($data['EC'] = ECModel::read($_POST['idNot'])) === null)
            Controller::goTo("ecs/liste.php", "", "Erreur lors du chargement de l'EC.");
        
        if(($data['epreuves'] = EpreuveModel::getList($_SESSION['current']['EC'])) === null)
            Controller::goTo("ecs/liste.php", "", "Erreur lors du chargement de la liste des épreuves.");
        
        return Controller::push("Épreuves de l'EC", "./view/notes/epreuves.php", $data);
    }
    
    /**
     * Informations sur un EC.
     * #RIGHTS# : administrateur, responsable d'un diplôme, responsable EC
     */
    public static function informations() {
        if(!UserModel::isConnected())
            Controller::goTo("", "", "Vous devez être connecté pour accéder à cette section.");
        
        if(!isset($_SESSION['current']['EC']) || ($_SESSION['current']['EC'] == -1))
            Controller::goTo("ecs/liste.php", "", "L'identifiant de l'EC n'a pas été précisé.");
        
        if(!UserModel::estIntEC($_SESSION['current']['EC']) &&
           !RespDiplomeModel::estResponsableDiplomeEC(UserModel::getId(), $_SESSION['current']['EC']))
            Controller::goTo("ecs/liste.php", "", "Vous n'avez pas les droits suffisants.");
        
        if(($data['EC'] = ECModel::read($_SESSION['current']['EC'])) === null)
            Controller::goTo("ecs/liste.php", "", "Erreur lors de la récupération de l'EC.");
        if(($data['responsables'] = RespECModel::getListResponsables($_SESSION['current']['EC'])) === null)
            Controller::goTo("ecs/liste.php", "", "Erreur lors de la récupération des responsables.");
        if(($data['intervenantsGrp'] = IntGroupeECModel::getListIntervenantsGroupe($_SESSION['current']['EC'])) === null)
            Controller::goTo("ecs/liste.php", "", "Erreur lors de la récupération des intervenants.");
        if(($data['intervenants'] = IntGroupeECModel::getListIntervenantsEC($_SESSION['current']['EC'])) === null)
            Controller::goTo("ecs/liste.php", "", "Erreur lors de la récupération des intervenants.");
        if(($data['epreuves'] = EpreuveModel::getList($_SESSION['current']['EC'])) === null)
            Controller::goTo("ecs/liste.php", "", "Erreur lors de la récupération des épreuves.");
        if(($data['droits'] = NoteDroitModel::getList($_SESSION['current']['EC'])) === null)
            Controller::goTo("ecs/liste.php", "", "Erreur lors de la récupération des droits.");
        
        return Controller::push("Configuration d'un EC", "./view/ecs/informations.php", $data);
    }
    
    /**
     * Par défaut : la liste des ECS
     * #RIGHTS# : administrateur, responsable d'un diplôme
     */
    public static function index() {
        if(!UserModel::isConnected())
            Controller::goTo("", "", "Vous devez être connecté pour accéder à cette section.");
        
        if(!UserModel::estRespDiplome())
            Controller::goTo("ecs/liste.php", "", "Vous n'avez pas les droits suffisants.");
        
        return Controller::push("Gestion des ECs", "./view/ecs/ecs.php",
                                [ 'ECS' => ECModel::getList() ]);
    }

} // class EcsController