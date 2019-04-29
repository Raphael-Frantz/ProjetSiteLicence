<?php
// *************************************************************************************************
// * Contrôleur pour les épreuves
// *************************************************************************************************
class EpreuvesController {

    /**
     * Récupère une épreuve depuis un formulaire.
     * @return une épreuve
     */
    public static function __getFromForm() : Epreuve {
        $inputIntitule = "";
        $type = Epreuve::TYPE_UNDEF;
        $max = 20.0;
        $active = true;
        $visible = true;
        $session1 = 0;
        $session2 = 0;
        $session1Disp = 0;
        $session2Disp = 0;
        
        if(isset($_POST['inputIntitule'])) $inputIntitule = $_POST['inputIntitule'];
        if(isset($_POST['inputType'])) $type = $_POST['inputType'];
        if(isset($_POST['inputMax'])) $max = floatval($_POST['inputMax']);
        if(isset($_POST['inputActive'])) $active = intval($_POST['inputActive']) == 1;
        if(isset($_POST['inputVisible'])) $visible = intval($_POST['inputVisible']) == 1;
        if(isset($_POST['inputSession1'])) $session1 = intval($_POST['inputSession1']);
        if(isset($_POST['inputSession2'])) $session2 = intval($_POST['inputSession2']);
        if(isset($_POST['inputSession1Disp'])) $session1Disp = intval($_POST['inputSession1Disp']);
        if(isset($_POST['inputSession2Disp'])) $session2Disp = intval($_POST['inputSession2Disp']);
        
        return new Epreuve(-1, $inputIntitule, $type, $_SESSION['current']['EC'], $max, $active, 
                           $visible, $session1, $session2, $session1Disp, $session2Disp);
    }
    
    /**
     * Ajoute une épreuve.
     * #RIGHTS# : administrateur, le responsable du diplôme ou de l'EC
     */
    public static function ajouter() {
        if(!isset($_SESSION['current']['EC']) || 
           (!UserModel::estRespEC($_SESSION['current']['EC']) &&
            !RespDiplomeModel::estResponsableDiplomeEC(UserModel::getId(), $_SESSION['current']['EC'])))
            Controller::goTo("epreuves/index.php", "", "Vous n'avez pas les droits pour réaliser cette action.");
        
        // L'utilisateur a annulé
        if(isset($_POST['btnAnnuler']))
            Controller::goTo("epreuves/index.php", "L'épreuve n'a pas été ajoutée.");
        
        // Le formulaire a été validé
        $data = [];
        if(isset($_POST['btnAjouter'])) {
            $data['epreuve'] = self::__getFromForm();
            
            $erreur = "";
            if($data['epreuve']->getIntitule() == "") $erreur .= "Vous devez spécifier l'intitulé. ";
            if($data['epreuve']->getType() == Epreuve::TYPE_UNDEF) $erreur .= "Vous devez spécifier un type. ";
            if($data['epreuve']->getMax() <= 0) $erreur .= "Vous devez spécifier la note maximale pour l'épreuve. ";
            if($data['epreuve']->getSession1() < 0) $erreur .= "Vous devez spécifier la répartition pour la session 1. ";
            if($data['epreuve']->getSession2() < 0) $erreur .= "Vous devez spécifier la répartition pour la session 2. ";
            if($data['epreuve']->getSession1Disp() < 0) $erreur .= "Vous devez spécifier la répartition pour la session 1 pour les dispenses. ";
            if($data['epreuve']->getSession2Disp() < 0) $erreur .= "Vous devez spécifier la répartition pour la session 2 pour les dispenses. ";
            
            if($erreur == "") {
                if(EpreuveModel::create($data['epreuve']))
                    Controller::goTo("epreuves/index.php", "L'épreuve a été ajoutée.");
                else
                    WebPage::setCurrentErrorMsg("L'épreuve n'a pas été ajoutée dans la base de données.");
            }        
            else
                WebPage::setCurrentErrorMsg($erreur);
        }
        
        return Controller::push("Ajouter une épreuve", "./view/epreuves/ajouter.php", $data);
    }
    
    /**
     * Modifie une épreuve.
     * #RIGHTS# : administrateur, responsable d'un diplôme contenant l'EC
     */
    public static function modifier() {
        if(!isset($_SESSION['current']['EC']) ||
           (!RespDiplomeModel::estResponsableDiplomeEC(UserModel::getId(), $_SESSION['current']['EC']) &&
            !UserModel::estAdmin()))
            Controller::goTo("epreuves/index.php", "", "Vous n'avez pas les droits pour réaliser cette action.");
        
        // L'utilisateur a annulé
        if(isset($_POST['btnAnnuler']))
            Controller::goTo("epreuves/index.php", "L'épreuve n'a pas été modifiée.");
        
        // Le formulaire a été validé
        $data = [];
        if(isset($_POST['btnModifier'])) {
            if(!isset($_SESSION['current']['epreuve']))
                Controller::goTo("epreuves/index.php", "", "Erreur lors de la récupération de l'épreuve.");
                        
            $data['epreuve'] = self::__getFromForm();
            $data['epreuve']->setId($_SESSION['current']['epreuve']);
            
            $erreur = "";
            if($data['epreuve']->getIntitule() == "") $erreur .= "Vous devez saisir un intitulé.";
            if($data['epreuve']->getType() == -1) $erreur .= "Vous devez choisir un type d'épreuve.";
            if($data['epreuve']->getMax() <= 0) $erreur .= "Vous devez spécifier la note maximale pour l'épreuve. ";
            if($data['epreuve']->getSession1() < 0) $erreur .= "Vous devez spécifier la répartition pour la session 1. ";
            if($data['epreuve']->getSession2() < 0) $erreur .= "Vous devez spécifier la répartition pour la session 2. ";            
            if($data['epreuve']->getSession1Disp() < 0) $erreur .= "Vous devez spécifier la répartition pour la session 1 pour les dispenses. ";
            if($data['epreuve']->getSession2Disp() < 0) $erreur .= "Vous devez spécifier la répartition pour la session 2 pour les dispenses. ";
            
            if($erreur == "") {
                if(EpreuveModel::update($data['epreuve']))
                    Controller::goTo("epreuves/index.php", "L'épreuve a été modifiée.");
                else
                    WebPage::setCurrentMsg("L'épreuve n'a pas été modifée dans la base de données.");
            }        
            else
                WebPage::setCurrentErrorMsg($erreur);
        }      
        else {
            if(($data['epreuve'] = EpreuveModel::read(intval($_POST['idModi']))) === null)
                Controller::goTo("epreuves/index.php", "", "Erreur lors de la récupération de l'épreuve.");
            $_SESSION['current']['epreuve'] = intval($_POST['idModi']);
        }
        
        if(($data['EC'] = ECModel::read(intval($_SESSION['current']['EC']))) === null)
            Controller::goTo("epreuves/index.php", "", "Erreur lors de la récupération de l'EC.");
        
        return Controller::push("Modifier une épreuve", "./view/epreuves/modifier.php", $data);
    }

    /**
     * Supprime une épreuve.
     * #RIGHTS# : administrateur, responsable d'un diplôme contenant l'EC
     */
    public static function supprimer() {
        if(!isset($_SESSION['current']['EC']) ||
           (!RespDiplomeModel::estResponsableDiplomeEC(UserModel::getId(), $_SESSION['current']['EC']) &&
            !UserModel::estAdmin()))
            Controller::goTo("epreuves/index.php", "", "Vous n'avez pas les droits pour réaliser cette action.");
        
        // L'utilisateur a annulé
        if(isset($_POST['btnAnnuler']))
            Controller::goTo("epreuves/index.php", "L'épreuve n'a pas été supprimée.");
        
        // Le formulaire a été validé
        $data = [];
        if(isset($_POST['btnSupprimer'])) {
            if(!isset($_SESSION['current']['epreuve']))
                Controller::goTo("epreuves/index.php", "L'épreuve n'a pas été supprimée.");
            
            if(EpreuveModel::delete($_SESSION['current']['epreuve']))
                Controller::goTo("epreuves/index.php", "L'épreuve a été supprimée.");
            else {
                WebPage::setCurrentErrorMsg("L'épreuve n'a pas été supprimée de la base de données.");
                if(($data['epreuve'] = EpreuveModel::read(intval($_POST['btnSupprimer']))) === null)
                    return Controller::goTo("epreuves/index.php", "", "Erreur lors de la récupération de l'épreuve.");
            }
        }
        else {
            if(($data['epreuve'] = EpreuveModel::read(intval($_POST['idSupp']))) === null)
                return Controller::goTo("epreuves/index.php", "", "Erreur lors de la récupération de l'épreuve.");
            $_SESSION['current']['epreuve'] = intval($_POST['idSupp']);
        }
        
        if(($data['EC'] = ECModel::read(intval($_SESSION['current']['EC']))) === null)
            Controller::goTo("epreuves/index.php", "", "Erreur lors de la récupération de l'EC.");

        return Controller::push("Supprimer une épreuve", "./view/epreuves/supprimer.php", $data);
    }
    
    /**
     * Service Web.
     * #RIGHTS# : variables
     */
    public static function ws() {
        $mode = 0;
        if(isset($_POST['mode']))
            $mode = intval($_POST['mode']);
        
        switch($mode) {
            case 1: // Active/désactive une épreuve
                // #RIGHTS# : responsable de l'EC ou du diplôme
                $json = [];
                $json['code'] = -1;
                
                if(!isset($_SESSION['current']['EC']) || 
                   (!UserModel::estRespEC($_SESSION['current']['EC']) &&
                    !RespDiplomeModel::estResponsableDiplomeEC(UserModel::getId(), $_SESSION['current']['EC'])))
                    $json['erreur'] = "Vous n'avez pas les droits pour réaliser cette action.";
                else {
                    if(isset($_POST['epreuve']) && isset($_POST['etat'])) {
                        if(EpreuveModel::active(intval($_POST['epreuve']), intval($_POST['etat']) == 1)) {
                            $json['code'] = 1;
                            $json['epreuve'] = intval($_POST['epreuve']);
                            $json['etat'] = intval($_POST['etat']);
                        }
                        else {
                            $json['erreur'] = "Impossible de modifier l'état de l'épreuve.";
                        }
                    }
                    else
                        $json['erreur'] = "Les données nécessaires n'ont pas été spécifiées.";
                }
                return Controller::JSONpush($json);
                break;
            case 2: // Cache/rend visible une épreuve
                // #RIGHTS# : responsable de l'EC ou du diplôme
                $json = [];
                $json['code'] = -1;
                
                if(!isset($_SESSION['current']['EC']) || 
                   (!UserModel::estRespEC($_SESSION['current']['EC']) &&
                    !RespDiplomeModel::estResponsableDiplomeEC(UserModel::getId(), $_SESSION['current']['EC'])))
                    $json['erreur'] = "Vous n'avez pas les droits pour réaliser cette action.";
                else {
                    if(isset($_POST['epreuve']) && isset($_POST['etat'])) {
                        if(EpreuveModel::visible(intval($_POST['epreuve']), intval($_POST['etat']) == 1)) {
                            $json['code'] = 1;
                            $json['epreuve'] = intval($_POST['epreuve']);
                            $json['etat'] = intval($_POST['etat']);
                        }
                        else {
                            $json['erreur'] = "Impossible de modifier l'état de l'épreuve.";
                        }
                    }
                    else
                        $json['erreur'] = "Les données nécessaires n'ont pas été spécifiées.";
                }
                return Controller::JSONpush($json);
                break;
            case 3: // Active/désactive le droit d'un intervenant pour une épreuve
                // #RIGHTS# : responsable de l'EC ou du diplôme
                $json = [];
                $json['code'] = -1;
                
                if(!isset($_SESSION['current']['EC']) || 
                   (!UserModel::estRespEC($_SESSION['current']['EC']) &&
                    !RespDiplomeModel::estResponsableDiplomeEC(UserModel::getId(), $_SESSION['current']['EC'])))
                    $json['erreur'] = "Vous n'avez pas les droits pour réaliser cette action.";
                else {
                    if(isset($_POST['epreuve']) && isset($_POST['user']) && isset($_POST['type'])) {
                        $type = intval($_POST['type']);
                        $epreuve = intval($_POST['epreuve']);
                        $user = intval($_POST['user']);
                        
                        $json['type'] = $type;
                        $json['epreuve'] = $epreuve;
                        $json['user'] = $user;                                
                        
                        // #TODO# Vérifications : epreuve => de l'EC ; user => intervenant de l'EC
                        if($type == 1) {
                            if(NoteDroitModel::activer($epreuve, $user)) {
                                $json['code'] = 1;
                            }
                        }
                        elseif($type == 0) {
                            if(NoteDroitModel::desactiver($epreuve, $user)) {
                                $json['code'] = 1;
                            }
                        }
                        else
                            $json['erreur'] = "Type incorrect.";
                    }
                    else
                        $json['erreur'] = "Les données sont insuffisantes.".print_r($_POST, true);
                }
                return Controller::JSONpush($json);
                break;
            case 4: // Spécifie l'EC et l'épreuve
                // #RIGHTS# : aucun, ce n'est pas critique
                $json = [ 'code' => 1 ];
                if(isset($_POST['EC'])) $_SESSION['current']['EC'] = intval($_POST['EC']);
                if(isset($_POST['epreuve'])) $_SESSION['current']['epreuve'] = intval($_POST['epreuve']);
                Controller::JSONpush($json);
                break;
            case 5: // Bloque/débloque une épreuve
                // #RIGHTS# : responsable du diplôme
                $json = [];
                $json['code'] = -1;
                
                if(!isset($_SESSION['current']['EC']) ||
                   (!RespDiplomeModel::estResponsableDiplomeEC(UserModel::getId(), $_SESSION['current']['EC']) &&
                    !UserModel::estAdmin()))
                    $json['erreur'] = "Vous n'avez pas les droits pour réaliser cette action.";
                else {
                    if(isset($_POST['epreuve']) && isset($_POST['etat'])) {
                        if(EpreuveModel::bloquee(intval($_POST['epreuve']), intval($_POST['etat']) == 1)) {
                            $json['code'] = 1;
                            $json['epreuve'] = intval($_POST['epreuve']);
                            $json['etat'] = intval($_POST['etat']);
                        }
                        else {
                            $json['erreur'] = "Impossible de modifier l'état de l'épreuve.";
                        }
                    }
                    else
                        $json['erreur'] = "Les données nécessaires n'ont pas été spécifiées.";
                }
                return Controller::JSONpush($json);
                break;
        }
    }    
    
    /**
     * Par défaut : la liste des épreuves d'un EC.
     * #RIGHTS# : responsable de diplôme ou de l'EC, intervenant
     */
    public static function index() {
        if(!isset($_SESSION['current']['EC']) || ($_SESSION['current']['EC'] == -1))
            Controller::goTo("ecs/liste.php", "", "Aucun EC sélectionné.");
        
        if(!UserModel::estIntEC($_SESSION['current']['EC']) &&
           !RespDiplomeModel::estResponsableDiplomeEC(UserModel::getId(), $_SESSION['current']['EC']))
            Controller::goTo("", "", "Vous n'avez pas les droits pour réaliser cette action.");
        
        if(($data['EC'] = ECModel::read($_SESSION['current']['EC'])) == null)
            Controller::goTo("ecs/liste.php", "", "Erreur lors du chargement de l'EC.");
        
        if(($data['epreuves'] = EpreuveModel::getList($_SESSION['current']['EC'])) === null)
            Controller::goTo("ecs/liste.php", "", "Erreur lors du chargement de la liste des épreuves.");
        
        return Controller::push("Liste des épreuves", "./view/epreuves/epreuves.php", $data);
    }

} // class EpreuvesController