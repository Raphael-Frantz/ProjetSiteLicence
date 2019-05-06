<?php
// *************************************************************************************************
// * Contrôleur pour les groupes
// *************************************************************************************************
class GroupesController {

    /**
     * Récupère un groupe depuis un formulaire.
     * @return un groupe
     */
    public static function __getFromForm() : Groupe {
        $inputIntitule = "";
        $inputType = Groupe::GRP_UNDEF;
        $inputDiplome = -1;
        $inputSemestre = -1;
        $inputPlanning = "";
        
        if(isset($_POST['inputIntitule'])) $inputIntitule = $_POST['inputIntitule'];
        if(isset($_POST['inputType'])) $inputType = intval($_POST['inputType']);
        if(isset($_POST['inputPlanning'])) $inputPlanning = $_POST['inputPlanning'];
        if(isset($_SESSION['current']['diplome'])) $inputDiplome = $_SESSION['current']['diplome'];
        if(isset($_SESSION['current']['semestre'])) $inputSemestre = $_SESSION['current']['semestre'];

        return new Groupe(-1, $inputIntitule, $inputType, $inputDiplome, $inputSemestre, $inputPlanning);
    }
    
    /**
     * Ajoute un groupe.
     * #RIGHTS# : administrateur, responsable du diplôme
     */
    public static function ajouter() {
        // Vérification des droits et des données
        if(!isset($_SESSION['current']['diplome']) || ($_SESSION['current']['diplome'] == -1) ||
           !isset($_SESSION['current']['semestre']) || ($_SESSION['current']['semestre'] == -1) ||
           !UserModel::estRespDiplome($_SESSION['current']['diplome']))
            Controller::goTo("", "", "Vous n'avez pas les droits pour réaliser cette action.");
        
        // L'utilisateur a annulé
        if(isset($_POST['btnAnnuler']))
            Controller::goTo("groupes/index.php", "Aucun groupe n'a été ajouté.");
        
        // Le formulaire a été validé
        $data = [];
        if(isset($_POST['btnAjouter'])) {
            $data['groupe'] = self::__getFromForm();
            
            // Vérification des erreurs
            $erreur = "";
            if($data['groupe']->getIntitule() == "") $erreur .= "Vous devez spécifier un intitulé.";
            if($data['groupe']->getType() == Groupe::GRP_UNDEF) $erreur .= "Vous devez spécifier un type.";
            
            if($erreur == "") {
                if(GroupeModel::create($data['groupe'])) {
                    Controller::goTo("groupes/index.php", "Le groupe a été ajouté.");
                }
                else
                    WebPage::setCurrentErrorMsg("Le groupe n'a pas été ajouté dans la base de données.");
            }        
            else
                WebPage::setCurrentErrorMsg($erreur);
        }
        
        return Controller::push("Ajouter un groupe", "./view/groupes/ajouter.php", $data);
    }
    
    /**
     * Modifie un groupe.
     */
    public static function modifier() {
        // Vérification des droits et des données
        if(!isset($_SESSION['current']['diplome']) || ($_SESSION['current']['diplome'] == -1) ||
           !isset($_SESSION['current']['semestre']) || ($_SESSION['current']['semestre'] == -1) ||
           !UserModel::estRespDiplome($_SESSION['current']['diplome']))
            Controller::goTo("", "", "Vous n'avez pas les droits pour réaliser cette action.");
        
        // L'utilisateur a annulé
        if(isset($_POST['btnAnnuler']))
            Controller::goTo("groupes/index.php", "Le groupe n'a pas été modifié.");
        
        // Le formulaire a été validé
        $data = [];
        if(isset($_POST['btnModifier'])) {
            if(!isset($_SESSION['current']['groupe']))
                Controller::goTo("groupes/index.php", "", "Erreur lors de la récupération du groupe.");
            
            $data['groupe'] = self::__getFromForm();
            $data['groupe']->setId($_SESSION['current']['groupe']);
            
            // Vérification des erreurs
            $erreur = "";
            if($data['groupe']->getIntitule() == "") $erreur .= "Vous devez spécifier un intitulé.";
            
            if($erreur == "") {
                if(GroupeModel::update($data['groupe']))
                    Controller::goTo("groupes/index.php", "Le groupe a été modifié.");
                else
                    WebPage::setCurrentMsg("Le groupe n'a pas été modifié dans la base de données.");
            }        
            else
                WebPage::setCurrentErrorMsg($erreur);
        }      
        else {
            // Chargement du groupe depuis la base
            if(($data['groupe'] = GroupeModel::read(intval($_POST['idModi']))) == null)
                return Controller::goTo("groupes/index.php", "", "Erreur lors de la récupération du groupe.");
            $_SESSION['current']['groupe'] = intval($_POST['idModi']);
        }
        
        return Controller::push("Modifier un groupe", "./view/groupes/modifier.php", $data);
    }

    /**
     * Supprime un groupe.
     */
    public static function supprimer() {
        // Vérification des données
        if(!isset($_SESSION['current']['diplome']) || ($_SESSION['current']['diplome'] == -1) ||
           !UserModel::estRespDiplome($_SESSION['current']['diplome']))
            Controller::goTo("", "", "Vous n'avez pas les droits pour réaliser cette action.");
        
        // L'utilisateur a annulé
        if(isset($_POST['btnAnnuler']))
            Controller::goTo("groupes/index.php", "Le groupe n'a pas été supprimé.");
        
        // Le formulaire a été validé
        $data = [];
        if(isset($_POST['btnSupprimer'])) {
            if(!isset($_SESSION['current']['groupe']))
                Controller::goTo("groupes/index.php", "", "Erreur lors de la récupération du groupe.");
            
            if(GroupeModel::delete($_SESSION['current']['groupe']))
                Controller::goTo("groupes/index.php", "Le groupe a été supprimé.");
            else {
                if(($data['groupe'] = GroupeModel::read($_SESSION['current']['groupe'])) === null)
                    return Controller::goTo("groupes/index.php", "", "Erreur lors de la récupération du groupe.");
                WebPage::setCurrentErrorMsg("Le groupe n'a pas été supprimé de la base de données.");
            }
        }
        else {
            // Chargement du groupe depuis la base
            if(($data['groupe'] = GroupeModel::read(intval($_POST['idSupp']))) == null)
                return Controller::goTo("groupes/index.php", "", "Erreur lors de la récupération du groupe.");
            $_SESSION['current']['groupe'] = intval($_POST['idSupp']);
        }

        return Controller::push("Supprimer un groupe", "./view/groupes/supprimer.php", $data);
    }
    
    /**
     * Service Web.
     * #RIGHTS# : variables
     */
    public static function ws() : void {
        $mode = 0;
        if(isset($_POST['mode'])) $mode = intval($_POST['mode']);
        
        if(isset($_POST['diplome']))
            $_SESSION['current']['diplome'] = intval($_POST['diplome']);
        $data['diplome'] = $_SESSION['current']['diplome'];
        
        if(isset($_POST['semestre'])) 
            $_SESSION['current']['semestre'] = intval($_POST['semestre']);
        $data['semestre'] = $_SESSION['current']['semestre'];
        
        $data['mode'] = $mode;
        $view = "./view/groupes/groupes_liste.php";
        switch($mode) {
            case 1: // Tous les groupes ou les groupes d'un diplôme/semestre
                // #RIGHTS# : aucun
                // Liste des groupes du diplôme et du semestre spécifié
                if(isset($_POST['type']))
                    $type = intval($_POST['type']);
                else
                    $type = Groupe::GRP_UNDEF;
                $data['groupes'] = GroupeModel::getList($data['diplome'], $data['semestre'], $type,
                                                        !isset($_POST['json']) || ($_POST['json'] != true));
                if(isset($_POST['json']) && $_POST['json']) {
                    if($data['groupes'] === null) {
                        $data['code'] = -1;
                        $data['msg'] = "Erreur lors de la récupération de la liste des groupes.";
                    }
                    else {
                        $data['code'] = 1;
                    }
                    Controller::JSONpush($data);
                }
                break;
            case 2: // Liste des inscriptions des étudiants dans les groupes
                // #RIGHTS# : administrateur, responsable de diplôme
                if(($data['diplome'] == -1) || ($data['semestre'] == -1) ||
                   !UserModel::estRespDiplome($data['diplome'])) {
                    $data['etudiants'] = [];
                    $data['groupesCM'] = [];
                    $data['groupesTD'] = [];
                    $data['groupesTP'] = [];
                }
                else {
                    // Récupération des inscriptions
                    $data['etudiants'] = InscriptionGroupeModel::getListeInscriptions($data['diplome'], $data['semestre']);
                    
                    // Récupération des groupes
                    $data['groupesCM'] = GroupeModel::getList($data['diplome'], $data['semestre'], Groupe::GRP_CM);
                    $data['groupesTD'] = GroupeModel::getList($data['diplome'], $data['semestre'], Groupe::GRP_TD);
                    $data['groupesTP'] = GroupeModel::getList($data['diplome'], $data['semestre'], Groupe::GRP_TP);
                }
                $view = "./view/groupes/attribution_liste.php";
                break;
            case 7: // Inscription d'un étudiant dans un groupe à partir des données courantes
                // #RIGHTS# : administrateur, responsable de diplôme
                $json = [];
                if(($data['diplome'] != -1) && UserModel::estRespDiplome($data['diplome']) &&
                   ($data['semestre'] != -1) && 
                   isset($_SESSION['current']['groupe']) && ($_SESSION['current']['groupe'] != -1) &&
                   isset($_POST['etudiant'])) {
                    $json['etudiant'] = intval($_POST['etudiant']);
                    if((($groupe = GroupeModel::read($_SESSION['current']['groupe'])) !== null) &&
                        InscriptionGroupeModel::inscrire($_SESSION['current']['groupe'],
                                                         intval($_POST['etudiant']),
                                                         $data['diplome'], $data['semestre'],
                                                         $groupe->getType()))
                        $json['code'] = 1;
                    else {
                        $json['code'] = -1;
                        $json['msg'] = "Problème lors de l'inscription de l'étudiant.";
                    }
                }
                else {
                    $json['code'] = -1;
                    $json['msg'] = "Il manque des données !!!";
                }
                Controller::JSONpush($json);
                break;
            case 3: // Inscription d'un étudiant dans un groupe
                // #RIGHTS# : administrateur, responsable de diplôme
                $json = [];
                if(($data['diplome'] != -1) && UserModel::estRespDiplome($data['diplome']) &&
                   ($data['semestre'] != -1) && 
                   isset($_POST['groupe']) && isset($_POST['etudiant']) && isset($_POST['type'])) {
                    $type = Groupe::GRP_UNDEF;
                    switch(intval($_POST['type'])) {
                        case 1:
                            $type = Groupe::GRP_CM;
                            break;
                        case 2:
                            $type = Groupe::GRP_TD;
                            break;
                        case 3:
                            $type = Groupe::GRP_TP;
                            break;
                    }
                    if($type == Groupe::GRP_UNDEF) {
                        $json['code'] = -1;
                        $json['erreur'] = "Type de groupe incorrect.";
                    }
                    else {
                        if(InscriptionGroupeModel::inscrire(intval($_POST['groupe']), intval($_POST['etudiant']),
                                                            $data['diplome'], $data['semestre'], $type)) {
                            $json['code'] = 1;
                            $json['diplome'] = intval($_POST['diplome']);
                            $json['semestre'] = intval($_POST['semestre']);
                            $json['etudiant'] = intval($_POST['etudiant']);
                            $json['groupe'] = intval($_POST['groupe']);
                            $json['type'] = $type;
                            $json['typeStr'] = Groupe::type2String($type);
                        }
                        else {
                            $json['code'] = -1;
                            $json['erreur'] = "Erreur lors de l'inscription.";
                        }
                    }
                }
                else {
                    $json['code'] = -1;
                    $json['erreur'] = "Il manque des données.";
                }
                Controller::JSONpush($json);
                break;
            case 6: // Desinscription d'un étudiant dans un groupe
                // #RIGHTS# : administrateur, responsable de diplôme
                $json = [];
                if(($data['diplome'] != -1) && UserModel::estRespDiplome($data['diplome']) &&
                   ($data['semestre'] != -1) && 
                   isset($_POST['groupe']) && isset($_POST['etudiant'])) {
                    $json['groupe'] = intval($_POST['groupe']);
                    $json['etudiant'] = intval($_POST['etudiant']);
                    if(InscriptionGroupeModel::desinscrire(intval($_POST['groupe']), intval($_POST['etudiant'])))
                        $json['code'] = 1;
                    else
                        $json['code'] = -1;
                }
                else {
                    $json['code'] = -1;
                    $json['msg'] = "Il manque des données !!!";
                }
                Controller::JSONpush($json);
                break;
            case 4: // Liste des étudiants dans un groupe donné
                // #RIGHTS# : administrateur, responsable de diplôme
                if(($data['diplome'] != -1) && UserModel::estRespDiplome($data['diplome'])) {
                    if(isset($_POST['groupe']))
                        $data['groupe'] = intval($_POST['groupe']);
                    else
                        $data['groupe'] = -1;
                    $_SESSION['current']['groupe'] = $data['groupe'];
                    
                    if(($data['diplome'] != -1) && ($data['semestre'] != -1) && ($data['groupe'] != -1)) {
                        $data['etudiants'] = InscriptionGroupeModel::getListe($data['groupe']);
                    }
                    else
                        $data['etudiants'] = [];
                }
                else {
                    $data['groupe'] = -1;
                    $data['etudiants'] = [];
                }   
                
                $view = "./view/groupes/etudiants_liste.php";
                break;
            case 5: // Liste des étudiants
                // Droit : uniquement la liste, donc pas de droit spécifique nécessaire
                if(($data['diplome'] == -1) || ($data['semestre'] == -1)) {
                    $data['etudiants'] = [];
                    $data['groupesCM'] = [];
                    $data['groupesTD'] = [];
                    $data['groupesTP'] = [];
                }
                else {
                    $data['etudiants'] = InscriptionGroupeModel::getListeInscriptions($data['diplome'], $data['semestre'], true);
                    $data['groupesCM'] = GroupeModel::getList($data['diplome'], $data['semestre'], Groupe::GRP_CM, false);
                    $data['groupesTD'] = GroupeModel::getList($data['diplome'], $data['semestre'], Groupe::GRP_TD, false);
                    $data['groupesTP'] = GroupeModel::getList($data['diplome'], $data['semestre'], Groupe::GRP_TP, false);
                }
                $view = "./view/groupes/affichage.php";
                break;
        }
        
        Controller::push("", $view, $data, "");
    }

    /**
     * Importe et actualise les URL des planning des groupes depuis Celcat
     * #RIGHTS# : administrateur
     */
    public static function celcat() {

        if(!UserModel::estAdmin())
            Controller::goTo("", "", "Vous n'avez pas les droits pour réaliser cette action.");

        if(!isset($_POST['inputSubmit']))
            Controller::goTo("", "", "Cette page n'est accessible que par le formulaire.");

        $start = microtime(true);

        if(isset($_POST['inputImporterPlanning'])) {
            $trouves = 0;
            $modifies = 0;
            $data = CelcatFinder::getAllGroups();
            $groups = GroupeModel::getList();

            foreach ($groups as $groupe) {

                foreach ($data as $xml) {

                    if ($xml->getCode() == $groupe['intitule']) {

                        $trouves++;

                        if ($groupe['planning'] != $xml->getPlanningURL()) {

                            $modifies++;

                            if (!GroupeModel::updatePlanning($groupe['id'], $xml->getPlanningURL())) {
                                WebPage::setCurrentErrorMsg("Une erreur s'est produite dans la base de données.");
                            }
                        }
                    }
                }
            }

            $temps = microtime(true) - $start;
            $temps = round($temps, 5);
            WebPage::setCurrentMsg("$trouves planning importés(s), $modifies planning modifiés(s) en $temps secondes.");
        }
        else if(isset($_POST['inputImporterSeances'])) {

            $trouves = 0;

            $groupes = GroupeModel::getList();
            foreach($groupes as $groupe) {
                $planning = $groupe['planning'];
                if(!empty($planning)) {

                    $seances = CelcatFinder::getAllSeances($planning, $groupe['id']);

                    if(!is_null($seances)) {

                        CCSeanceModel::deleteSeancesFromGroupe($groupe['id']);

                        foreach($seances as $seance) {
                            CCSeanceModel::create($seance);
                            $trouves++;
                        }
                    }
                }
            }

            $temps = microtime(true) - $start;
            $temps = round($temps, 5);
            WebPage::setCurrentMsg("$trouves séances importée(s) en $temps secondes");
        }
        else {
            Controller::goTo("errors/404.php", "");
        }

        Controller::goTo("groupes/index.php");
    }
    
    /**
     * Importe les groupes des étudiants.
     */
    public static function importer() {
        $diplome = -1;
        if(isset($_SESSION['current']['diplome'])) $diplome = $_SESSION['current']['diplome'];
        $semestre = -1;
        if(isset($_SESSION['current']['semestre'])) $semestre = $_SESSION['current']['semestre'];
        
        if(($diplome == -1) || ($semestre == -1) || !UserModel::estRespDiplome($diplome))
            Controller::goTo("", "", "Vous n'avez pas les droits pour réaliser cette action.");
        
        if(isset($_FILES) && isset($_FILES['inputFichier']) && ($_FILES['inputFichier']['error'] == UPLOAD_ERR_OK)) {
            $filename = $_FILES['inputFichier']['tmp_name'];

            if (($handle = fopen($filename, "r")) !== FALSE) {
                // Lecture de l'en-tête (1 ligne)
                if(($data = fgetcsv($handle, 1000, ";")) === FALSE)
                    Controller::goTo("groupes/attribution.php", "", "Impossible de lire la ligne d'en-tête du fichier.");

                if(($data[0] != 'numero') || ($data[1] != 'nom') ||
                   ($data[2] != 'prenom') || ($data[3] != 'email') ||
                   ($data[4] != 'CM') || ($data[5] != 'TD') || ($data[6] != 'TP'))
                    Controller::goTo("groupes/attribution.php", "", "Le format du fichier est incorrect.");
                
                // Lecture des étudiants avec leurs groupes
                $liste = array();
                while (($data = fgetcsv($handle, 1000, ";")) !== FALSE) {
                    $liste[] = array("numero" => $data[0],
                                     "nom" => utf8_encode($data[1]),
                                     "prenom" => utf8_encode($data[2]),
                                     "email" => $data[3],
                                     "CM" => $data[4],
                                     "TD" => $data[5],
                                     "TP" => $data[6]);
                }
                fclose($handle);

                // Chargement des groupes du diplôme/semestre
                $groupesCM = GroupeModel::getList($diplome, $semestre, Groupe::GRP_CM, false);
                $groupesTD = GroupeModel::getList($diplome, $semestre, Groupe::GRP_TD, false);
                $groupesTP = GroupeModel::getList($diplome, $semestre, Groupe::GRP_TP, false);

                // Ajout des étudiants (s'ils n'existent pas)
                EtudiantModel::updateList($liste);
                
                // Inscrit les étudiants à ce diplôme/semestre
                $inscrits = 0;                   // Inscription dans le diplôme
                $ajoutes = 0;                    // Nombre d'étudiants ajoutés
                $existant = 0;                   // Nombre d'étudiants existants
                $erreurs = 0;                    // Erreurs de création des étudiants
                $erreursGrp = 0;                 // Erreurs de groupe (inexistant)
                $erreursIns = 0;                 // Erreurs d'inscription à des groupes
                if(($diplome != - 1) && ($semestre != -1)) {
                    foreach($liste as $etudiant) {
                        if(($etudiant['statut'] == 'CREE') || ($etudiant['statut'] == 'EXISTE')) {
                            if($etudiant['statut'] == 'CREE')
                                $ajoutes++;
                            else
                                $existant++;
                            
                            if(InscriptionDiplomeModel::inscrire($diplome, $etudiant['id'], $semestre))
                                $inscrits++;
                            
                            if(($idCM = array_search($etudiant['CM'], $groupesCM)) !== false) {
                                // Groupe CM existe
                                if(InscriptionGroupeModel::inscrire($idCM, $etudiant['id'], $diplome, $semestre, Groupe::GRP_CM) == false)
                                    $erreursIns++;
                            }
                            else
                                $erreursGrp++;
                            if(($idTD = array_search($etudiant['TD'], $groupesTD)) !== false) {
                                // Groupe TD existe
                                if(InscriptionGroupeModel::inscrire($idTD, $etudiant['id'], $diplome, $semestre, Groupe::GRP_TD) == false)
                                    $erreursIns++;
                            }
                            else
                                $erreursGrp++;
                            if(($idTP = array_search($etudiant['TP'], $groupesTP)) !== false) {
                                // Groupe TP existe
                                if(InscriptionGroupeModel::inscrire($idTP, $etudiant['id'], $diplome, $semestre, Groupe::GRP_TP) == false)
                                    $erreursIns++;
                            }
                            else
                                $erreursGrp++;
                        }
                        else
                            $erreurs++;
                    }
                }

                Controller::goTo("groupes/attribution.php",
                                 "$ajoutes étudiant(s) ajouté(s) / ".
                                       "$existant étudiant(s) existant(s) / ".
                                       ($erreurs+$erreursIns)." erreurs / ".
                                       "$erreursGrp erreurs de groupe / ".
                                       "$inscrits étudiant(s) inscrit(s) dans le diplôme courant.");
            }
            else
                Controller::goTo("groupes/attribution.php", "", "Erreur lors de l'ouverture du fichier.");
        }
        else
            Controller::goTo("groupes/attribution.php", "", "Vous n'avez pas sélectionné de fichier.");
    }   

    /**
     * Exporte les groupes des étudiants.
     */
    public static function exporter() {
        $diplome = -1;
        if(isset($_SESSION['current']['diplome'])) $diplome = $_SESSION['current']['diplome'];
        $semestre = -1;
        if(isset($_SESSION['current']['semestre'])) $semestre = $_SESSION['current']['semestre'];
        
        if(($diplome == -1) || ($semestre == -1) || !UserModel::estRespDiplome($diplome))
            Controller::goTo("", "", "Vous n'avez pas les droits pour réaliser cette action.");        
        
        $diplome = DiplomeModel::read($_SESSION['current']['diplome']);        
        $etudiants = InscriptionGroupeModel::getListeInscriptions($_SESSION['current']['diplome'], $_SESSION['current']['semestre']);
        
        $header = [ "numero" => "numero", "nom" => "nom",
                    "prenom" => "prenom", "email" => "email", 
                    "CM" => "intituleCM", "TD" => "intituleTD", "TP" => "intituleTP" ];
        
        // Exporte en CSV
        Controller::CSVpush($diplome->getIntitule()."_groupes", $etudiants, $header);
    }

    /**
     * Liste des étudiants d'un groupe.
     * #RIGHTS# : administrateur, responsable de diplôme
     */
    public static function etudiants() {
        if(!UserModel::estRespDiplome())
            Controller::goTo("", "", "Vous n'avez pas les droits pour réaliser cette action.");
        
        if(!isset($_SESSION['current']['diplome']) || ($_SESSION['current']['diplome'] == -1))
            Controller::goTo("groupes/index.php", "", "Vous n'avez pas les droits pour réaliser cette action.");
        
        if(UserModel::estAdmin())
            $data['diplomes'] = DiplomeModel::getList();
        else
            $data['diplomes'] = RespDiplomeModel::getList(UserModel::getId());
        
        if(isset($_SESSION['current']['semestre']))
            $data['semestre'] = $_SESSION['current']['semestre'];
        else
            $data['semestre'] = -1;

        $data['diplome'] = DiplomeModel::read($_SESSION['current']['diplome']);
        $data['nbSemestres'] = $data['diplome']->getNbSemestres();
        $data['minSemestre'] = $data['diplome']->getMinSemestre();

        if(isset($data['diplome']))
            $data['groupes'] = GroupeModel::getList($data['diplome']->getId(), $data['semestre']);
        
        if(isset($_POST['groupe']))
            $_SESSION['current']['groupe'] = intval($_POST['groupe']);
        $data['groupe'] = $_SESSION['current']['groupe'];
        
        return Controller::push("Liste des étudiants des groupes", "./view/groupes/etudiants.php", $data);
    }    
    
    /**
     * Attribution des groupes.
     * #RIGHTS# : administrateur, responsable de diplôme
     */
    public static function attribution() {
        if(!UserModel::estRespDiplome())
            Controller::goTo("", "", "Vous n'avez pas les droits pour réaliser cette action.");
        
        // Récupération de la liste des diplômes
        if(UserModel::estAdmin())
            $data['diplomes'] = DiplomeModel::getList();
        else
            $data['diplomes'] = RespDiplomeModel::getList(UserModel::getId());
        if($data['diplomes'] === null)
            Controller::goTo("", "", "Erreur lors de la récupération des diplômes.");

        if(count($data['diplomes']) > 0) {
            if((!isset($_SESSION['current']['diplome'])) || ($_SESSION['current']['diplome'] == -1))
                $_SESSION['current']['diplome'] = $data['diplomes'][0]['id'];
            $data['diplome'] = DiplomeModel::read($_SESSION['current']['diplome']);
            if($data['diplome'] == null)
                return Controller::goTo("groupes/index.php", "Erreur lors du chargement du diplome.");
            $data['nbSemestres'] = $data['diplome']->getNbSemestres();
            $data['minSemestre'] = $data['diplome']->getMinSemestre();
            
            if(isset($_SESSION['current']['semestre'])) {
                if(($_SESSION['current']['semestre'] < 1) ||
                   ($_SESSION['current']['semestre'] > $data['diplome']->getNbSemestres()))
                   $_SESSION['current']['semestre'] = 1;
                
                $data['semestre'] = $_SESSION['current']['semestre'];
            }
            else {
                $_SESSION['current']['semestre'] = 1;
                $data['semestre'] = 1;
            }
        }
        
        return Controller::push("Attribution des groupes", "./view/groupes/attribution.php", $data);
    }
    
    /**
     * Inscrire des étudiants à un groupe courant.
     * #RIGHTS# : administrateur, responsable du diplôme courant
     */
    public static function inscrire() {
        if(!isset($_SESSION['current']['diplome']) || ($_SESSION['current']['diplome'] == -1))
            Controller::goTo("groupes/index.php", "", "Aucun diplôme sélectionné.");
        
        if(!UserModel::estRespDiplome($_SESSION['current']['diplome']))
            Controller::goTo("", "", "Vous n'avez pas les droits pour réaliser cette action.");
        
        // Récupération du diplôme associé
        if(($data['diplomeObj'] = DiplomeModel::read($_SESSION['current']['diplome'])) === null)
            Controller::goTo("groupes/index.php", "", "Erreur lors de la récupération du diplôme.");
        $data['diplome'] = $_SESSION['current']['diplome'];
        
        // Vérification de la validité du semestre
        if(!isset($_SESSION['current']['semestre']) || ($_SESSION['current']['semestre'] == -1))
            Controller::goTo("groupes/index.php", "", "Aucun semestre sélectionné.");
        if(($_SESSION['current']['semestre'] < 1) ||
           ($_SESSION['current']['semestre'] > $data['diplomeObj']->getNbSemestres()))
           $_SESSION['current']['semestre'] = 1;
        $data['semestre'] = $_SESSION['current']['semestre'];
        
        // Récupération du groupe
        if(!isset($_SESSION['current']['groupe']) || ($_SESSION['current']['groupe'] == -1))
            Controller::goTo("groupes/index.php", "", "Aucun groupe sélectionné.");
        $data['groupe'] = $_SESSION['current']['groupe'];
        if(($data['groupeObj'] = GroupeModel::read($_SESSION['current']['groupe'])) === null)
            Controller::goTo("groupes/index.php", "", "Erreur lors de la récupération du groupe.");
        
        $data['etudiants'] = InscriptionGroupeModel::getListeNonInscrits($data['diplome'], $data['semestre'],
                                                                         $data['groupe'], $data['groupeObj']->getType());
        if($data['etudiants'] === null)
            Controller::goTo("groupes/index.php", "", "Erreur lors de la récupération des étudiants.");
        
        return Controller::push("", "./view/groupes/inscrire.php", $data);
    }
    
    /**
     * Par défaut : la liste des groupes.
     * #RIGHTS# : un responsable de diplôme
     */
    public static function index() : void {
        if(!UserModel::estRespDiplome())
            Controller::goTo("", "", "Vous n'avez pas les droits pour réaliser cette action.");
        
        // Récupération de la liste des diplômes
        if(UserModel::estAdmin())
            $data['diplomes'] = DiplomeModel::getList();
        else
            $data['diplomes'] = RespDiplomeModel::getList(UserModel::getId());
        if($data['diplomes'] === null)
            Controller::goTo("", "", "Erreur lors de la récupération des diplômes.");
        
        if(isset($_SESSION['current']['semestre']))
            $data['semestre'] = $_SESSION['current']['semestre'];
        else
            $data['semestre'] = -1;
        
        if(isset($_SESSION['current']['diplome']) && ($_SESSION['current']['diplome'] != -1)) {
            $data['diplome'] = DiplomeModel::read($_SESSION['current']['diplome']);
           
            if($data['diplome'] === null) {
                //echo "Erreur sur le chargement du diplôme : ".$_SESSION['current']['diplome'];
                unset($data['diplome']);
            }
            else {
               $data['nbSemestres'] = $data['diplome']->getNbSemestres();
               $data['minSemestre'] = $data['diplome']->getMinSemestre();
            }
            
            if(($data['semestre'] < 1) || ($data['semestre'] > $data['nbSemestres']))
                $data['semestre'] = 1;
            // #TODO# : vérifier s'il ne faut pas mettre à jour le semestre courant
        }
        else
            $data['semestre'] = -1;
        
        Controller::push("Gestion des groupes", "./view/groupes/groupes.php", $data);
    }

} // class GroupesController