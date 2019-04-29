<?php
// *************************************************************************************************
// * Contrôleur pour les diplômes
// *************************************************************************************************
class DiplomesController {

    /**
     * Récupère un diplôme depuis un formulaire.
     * @return un diplôme
     */
    private static function __getFromForm() : Diplome {
        $inputIntitule = "";
        $minSemestre = -1;
        
        if(isset($_POST['inputIntitule'])) $inputIntitule = $_POST['inputIntitule'];
        if(isset($_POST['inputMinSemestre'])) $minSemestre = intval($_POST['inputMinSemestre']);
        
        return new Diplome(-1, $inputIntitule, $minSemestre);
    }
    
    /**
     * Ajoute un diplôme.
     * #RIGHTS# : uniquement un administrateur
     */
    public static function ajouter() : void {
        if(!UserModel::estAdmin())
            Controller::goTo("", "", "Vous n'avez pas les droits pour réaliser cette action.");
        
        // L'utilisateur a annulé
        if(isset($_POST['btnAnnuler']))
            Controller::goTo("diplomes/index.php", "Le diplôme n'a pas été ajouté.");
        
        // Le formulaire a été validé
        $data = [];
        if(isset($_POST['btnAjouter'])) {
            $data['diplome'] = self::__getFromForm();
            
            $erreur = "";
            if($data['diplome']->getIntitule() === "") $erreur .= "Vous devez saisir l'intitulé. ";
            if($data['diplome']->getMinSemestre() === "") $erreur .= "Vous devez saisir le semestre minimum. ";
            
            if($erreur == "") {
                if(DiplomeModel::create($data['diplome'])) {
                    // Ajout des responsables
                    if(isset($_POST['resp'])) {
                        foreach($_POST['resp'] as $resp) {
                            RespDiplomeModel::ajouter($data['diplome']->getId(), $resp);
                        }
                    }
                    Controller::goTo("diplomes/index.php", "Le diplôme a été ajouté.");
                }
                else
                    WebPage::setCurrentErrorMsg("Le diplôme n'a pas été ajouté dans la base de données.");
            }        
            else
                WebPage::setCurrentErrorMsg($erreur);
        }
        if(($data['users'] = UserModel::getList(User::TYPE_ENSEIGNANT)) === null)
            Controller::goTo("diplomes/index.php", "Erreur lors de la récupération de la liste des enseignants.");
        
        Controller::push("Ajouter un diplôme", "./view/diplomes/ajouter.php", $data);
    }
    
    /**
     * Modifie un diplôme.
     * #RIGHTS# : uniquement un administrateur
     */
    public static function modifier() : void {
        if(!UserModel::estAdmin())
            Controller::goTo("", "", "Vous n'avez pas les droits pour réaliser cette action.");
        
        // L'utilisateur a annulé
        if(isset($_POST['btnAnnuler']))
            Controller::goTo("diplomes/index.php", "Le diplôme n'a pas été modifié.");
        
        // Le formulaire a été validé
        $data = [];
        if(isset($_POST['btnModifier'])) {
            if(!isset($_SESSION['current']['diplome']))
                Controller::goTo("diplomes/index.php", "", "Erreur lors de la récupération du diplôme.");
            
            $data['diplome'] = self::__getFromForm();
            $data['diplome']->setId($_SESSION['current']['diplome']);
            
            // Mise-à-jour du/des responsable(s)
            RespDiplomeModel::supprimer($_SESSION['current']['diplome'], -1);
            if(isset($_POST['resp'])) {
                foreach($_POST['resp'] as $resp) {
                    RespDiplomeModel::ajouter($_SESSION['current']['diplome'], $resp);
                }
            }
            
            // Mise-à-jour du diplôme
            $erreur = "";
            if($data['diplome']->getIntitule() === "") $erreur .= "Vous n'avez pas saisi l'intitulé.";
            if($data['diplome']->getMinSemestre() === "") $erreur .= "Vous devez saisir le semestre minimum. ";
            
            if($erreur == "") {
                if(DiplomeModel::update($data['diplome']))
                    Controller::goTo("diplomes/index.php", "Le diplôme a été modifié.");
                else
                    WebPage::setCurrentMsg("Le diplôme n'a pas été modifié dans la base de données.");
            }        
            else
                WebPage::setCurrentErrorMsg($erreur);
        }      
        else {
            $data['diplome'] = DiplomeModel::read(intval($_POST['idModi']));
            if($data['diplome'] === null)
                Controller::goTo("diplomes/index.php", "", "Erreur lors de la récupération du diplôme.");
            $_SESSION['current']['diplome'] = intval($_POST['idModi']);
        }
        $data['users'] = UserModel::getList(User::TYPE_ENSEIGNANT);
        $data['resp'] = RespDiplomeModel::getListResponsables($_SESSION['current']['diplome']);
        
        if(($data['users'] === null) || ($data['resp'] === null))
            Controller::goTo("diplomes/index.php", "", "Erreur lors de la récupération du diplôme.");
        
        Controller::push("Modifier un diplôme", "./view/diplomes/modifier.php", $data);
    }

    /**
     * Supprime un diplôme.
     * #RIGHTS# : uniquement un administrateur
     */
    public static function supprimer() : void {
        if(!UserModel::estAdmin())
            Controller::goTo("", "", "Vous n'avez pas les droits pour réaliser cette action.");
        
        // L'utilisateur a annulé
        if(isset($_POST['btnAnnuler']))
            Controller::goTo("diplomes/index.php", "Le diplôme n'a pas été supprimé.");
        
        // Le formulaire a été validé
        $data = [];
        if(isset($_POST['btnSupprimer'])) {
            if(!isset($_SESSION['current']['diplome']))
                Controller::goTo("diplomes/index.php", "Le diplôme n'a pas été supprimé.");
            
            if(DiplomeModel::delete($_SESSION['current']['diplome'])) {
                unset($_SESSION['current']['diplome']);
                Controller::goTo("diplomes/index.php", "Le diplôme a été supprimé.");
            }
            else {
                WebPage::setCurrentErrorMsg("Le diplôme n'a pas été supprimé de la base de données.");
                if(($data['diplome'] = DiplomeModel::read($_SESSION['current']['diplome'])) === null)
                    Controller::goTo("diplomes/index.php", "", "Erreur lors de la récupération du diplôme.");
            }
        }
        else {
            if(($data['diplome'] = DiplomeModel::read(intval($_POST['idSupp']))) === null)
                Controller::goTo("diplomes/index.php", "", "Erreur lors de la récupération du diplôme.");
            $_SESSION['current']['diplome'] = intval($_POST['idSupp']);
        }

        Controller::push("Supprimer un diplôme", "./view/diplomes/supprimer.php", $data);
    }

    /**
     * Structure du diplôme.
     * #RIGHTS# : administrateur, responsable du diplôme
     */
    public static function structure() : void {
        if(!isset($_POST['idEdit']))
            Controller::goTo("diplomes/index.php", "", "L'identifiant du diplôme n'a pas été précisé.");
        $id = intval($_POST['idEdit']);

        if(!UserModel::estRespDiplome($id))
            Controller::goTo("", "", "Vous n'avez pas les droits pour réaliser cette action.");
        
        if(($diplome = DiplomeModel::read($id)) == null)
            Controller::goTo("diplomes/index.php", "", "Erreur lors du chargement du diplôme.");
        
        $_SESSION['current']['diplome'] = $id;
           
        Controller::push("Structure du diplôme", "./view/diplomes/structure.php",
                         [ 'diplome' => $diplome, 'structure' => UEECModel::getUEEC($id, 1) ]);
    }
    
    /**
     * Service Web.
     * #RIGHTS# : variable
     */
    public static function ws() : void {
        $mode = 0;
        if(isset($_POST['mode']))
            $mode = intval($_POST['mode']);
        $semestre = -1;
        if(isset($_POST['semestre']))
            $semestre = intval($_POST['semestre']);
        
        switch($mode) {
            case 1: // Ajoute une UE à un semestre
                // #RIGHTS# : administrateur, responsable du diplôme
                if(isset($_SESSION['current']['diplome']) && UserModel::estRespDiplome($_SESSION['current']['diplome']) &&
                   isset($_POST['semestre'])) {
                    $semestre = intval($_POST['semestre']);
                    UEModel::ajouterUE($_SESSION['current']['diplome'], $semestre);
                }
                break;
            case 2: // Supprime une UE d'un semestre
                // #RIGHTS# : administrateur, responsable du diplôme
                if(isset($_SESSION['current']['diplome']) && UserModel::estRespDiplome($_SESSION['current']['diplome']) &&
                   isset($_POST['UE'])) {
                    $UE = intval($_POST['UE']);
                    DiplomeModel::supprimerUE($_SESSION['current']['diplome'], $UE);
                }
                break;
            case 3: // Monter une UE
                // #RIGHTS# : administrateur, responsable du diplôme
                if(isset($_SESSION['current']['diplome']) && UserModel::estRespDiplome($_SESSION['current']['diplome']) &&
                   isset($_POST['UE'])) {
                    $UE = intval($_POST['UE']);
                    DiplomeModel::monterUE($_SESSION['current']['diplome'], $UE);
                }
                break;
            case 4: // Descendre un UE
                // #RIGHTS# : administrateur, responsable du diplôme
                if(isset($_SESSION['current']['diplome']) && UserModel::estRespDiplome($_SESSION['current']['diplome']) &&
                   isset($_POST['UE'])) {
                    $UE = intval($_POST['UE']);
                    DiplomeModel::descendreUE($_SESSION['current']['diplome'], $UE);
                }
                break;
            case 5: // Ajouter un EC à une UE
                // #RIGHTS# : administrateur, responsable du diplôme
                if(isset($_SESSION['current']['diplome']) && UserModel::estRespDiplome($_SESSION['current']['diplome']) &&
                   isset($_POST['UE']) && isset($_POST['EC'])) {
                    $UE = intval($_POST['UE']);
                    $EC = intval($_POST['EC']);
                    DiplomeModel::ajouterEC($_SESSION['current']['diplome'], $UE, $EC);
                }
                break;
            case 6: // Monter un EC
                // #RIGHTS# : administrateur, responsable du diplôme
                if(isset($_SESSION['current']['diplome']) && UserModel::estRespDiplome($_SESSION['current']['diplome']) &&
                   isset($_POST['UE']) && isset($_POST['EC'])) {
                    $UE = intval($_POST['UE']);
                    $EC = intval($_POST['EC']);
                    DiplomeModel::monterEC($_SESSION['current']['diplome'], $UE, $EC);
                }
                break;                
            case 7: // Descendre un EC
                // #RIGHTS# : administrateur, responsable du diplôme
                if(isset($_SESSION['current']['diplome']) && UserModel::estRespDiplome($_SESSION['current']['diplome']) &&
                   isset($_POST['UE']) && isset($_POST['EC'])) {
                    $UE = intval($_POST['UE']);
                    $EC = intval($_POST['EC']);
                    DiplomeModel::descendreEC($_SESSION['current']['diplome'], $UE, $EC);
                }
                break;
            case 8: // Supprimer un EC
                // #RIGHTS# : administrateur, responsable du diplôme
                if(isset($_SESSION['current']['diplome']) && UserModel::estRespDiplome($_SESSION['current']['diplome']) &&
                   isset($_POST['UE']) && isset($_POST['EC'])) {
                    $UE = intval($_POST['UE']);
                    $EC = intval($_POST['EC']);
                    DiplomeModel::supprimerEC($_SESSION['current']['diplome'], $UE, $EC);
                }
                break;
            case 9: // Ajouter un semestre
                // #RIGHTS# : administrateur, responsable du diplôme
                if(isset($_SESSION['current']['diplome']) && UserModel::estRespDiplome($_SESSION['current']['diplome']))
                    DiplomeModel::ajouterSemestre($_SESSION['current']['diplome']);
                break;
            case 10: // Supprimer un semestre
                // #RIGHTS# : administrateur, responsable du diplôme
                if(isset($_SESSION['current']['diplome']) && UserModel::estRespDiplome($_SESSION['current']['diplome']) &&
                   isset($_POST['semestre'])) {
                    $semestre = intval($_POST['semestre']);
                    DiplomeModel::supprimerSemestre($_SESSION['current']['diplome'], $semestre);
                }
                break;
            case 11: // Retourne le nombre de semestres d'un diplôme
                // #RIGHTS# : tout le monde
                $json = [];
                if(isset($_POST['diplome'])) {
                    if(($diplome = DiplomeModel::read(intval($_POST['diplome']))) !== null) {
                        $json['code'] = 1;
                        $json['nbSemestres'] = $diplome->getNbSemestres();
                        $json['minSemestre'] = $diplome->getMinSemestre();
                        $_SESSION['current']['diplome'] = intval($_POST['diplome']);
                    }
                    else {
                        $json['code'] = -1;
                        $json['nbSemestres'] = 0;
                        $json['msg'] = "Erreur lors du chargement du diplôme.";
                        unset($_SESSION['current']['diplome']);
                    }
                }
                else {
                    $json['code'] = -1;
                    $json['nbSemestres'] = 0;
                    $json['msg'] = "Données manquantes.";
                }
                Controller::JSONpush($json);
                break;
            case 12: // Retourne la liste des diplômes
                // #RIGHTS# : tout le monde
                if(($json['diplomes'] = DiplomeModel::getList()) === null) {
                    $json['code'] = -1;
                    $json['msg'] = "Erreur lors de la récupération de la liste des diplômes.";
                }
                else
                    $json['code'] = 1;
                    
                Controller::JSONpush($json);
                break;
            case 13: // Retourne la liste de toutes les épreuves d'un diplôme
                // #RIGHTS# : administrateur, responsable du diplôme
                if(isset($_POST['diplome']) && UserModel::estRespDiplome(intval($_POST['diplome'])) &&
                   isset($_POST['semestre'])) {
                    $_SESSION['current']['diplome'] = intval($_POST['diplome']);
                    $data['epreuves'] = EpreuveModel::getListDiplome(intval($_POST['diplome']), intval($_POST['semestre']));
                }
                else
                    $data['epreuves'] = [];
                
                Controller::push("", "./view/diplomes/epreuves_liste.php", $data, "");
                exit();
                break;
            case 0:
                break;
            default:
                exit();
                break;
        }
        
        Controller::push("", "./view/diplomes/content.php",
                         [ 'structure' => UEECModel::getUEEC($_SESSION['current']['diplome'], $semestre) ],
                         "");
    }
    
    /**
     * Gestion de toutes les épreuves d'un diplôme.
     * #RIGHTS# : administrateur, responsable de diplôme
     */
    public static function epreuves() : void {
        if(!UserModel::estRespDiplome())
            Controller::goTo("", "", "Vous n'avez pas les droits pour réaliser cette action.");
        
        if(UserModel::estAdmin())
            $data['diplomes'] = DiplomeModel::getList();
        else
            $data['diplomes'] = RespDiplomeModel::getList(UserModel::getId());
        
        Controller::push("Gestion des épreuves", "./view/diplomes/epreuves.php", $data);
    }
    
    /**
     * Par défaut : la liste des diplômes dont l'utilisateur est responsable
     * #RIGHTS# : administrateur, responsable de diplôme
     */
    public static function index() : void {
        if(!UserModel::estRespDiplome())
            Controller::goTo("", "", "Vous n'avez pas les droits pour réaliser cette action.");
        
        if(UserModel::estAdmin())
            $data['diplomes'] = DiplomeModel::getList();
        else
            $data['diplomes'] = RespDiplomeModel::getList(UserModel::getId());
        
        Controller::push("Gestion des diplômes", "./view/diplomes/diplomes.php", $data);
    }

}