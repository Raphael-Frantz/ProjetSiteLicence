<?php
// *************************************************************************************************
// * Contrôleur pour les groupes d'EC
// *************************************************************************************************
class GroupesecController {
    
    /**
     * Récupère un groupe d'EC depuis un formulaire.
     * @return un groupe d'EC
     */
    public static function __getFromForm() : GroupeEC {
        $inputIntitule = "";
        $inputType = Groupe::GRP_UNDEF;
        $inputEC = -1;
        
        if(isset($_POST['inputIntitule'])) $inputIntitule = $_POST['inputIntitule'];
        if(isset($_POST['inputType'])) $inputType = intval($_POST['inputType']);
        if(isset($_SESSION['current']['EC'])) $inputEC = $_SESSION['current']['EC'];
        
        return new GroupeEC(-1, $inputIntitule, $inputType, $inputEC);
    }
    
    /**
     * Ajoute un groupe d'EC.
     *  #RIGHTS# : responsable du diplôme, responsable de l'EC
     */
    public static function ajouter() : void {
        if(!isset($_SESSION['current']['EC']) || 
           (!UserModel::estRespEC($_SESSION['current']['EC']) &&
            !RespDiplomeModel::estResponsableDiplomeEC(UserModel::getId(), $_SESSION['current']['EC'])))
            Controller::goTo("groupesec/index.php", "", "Vous n'avez pas les droits pour réaliser cette action.");
        
        // L'utilisateur a annulé
        if(isset($_POST['btnAnnuler']))
            Controller::goTo("groupesec/index.php", "Le groupe n'a pas été ajouté.");
        
        // Le formulaire a été validé
        $data = [];
        
        if(($data['EC'] = ECModel::read($_SESSION['current']['EC'])) == null)
            Controller::goTo("groupesec/index.php", "", "Problème lors de la récupération de l'EC.");
        
        if(isset($_POST['btnAjouter'])) {
            $data['groupe'] = self::__getFromForm();

            $erreur = "";
            if($data['groupe']->getIntitule() == "") $erreur .= "Vous n'avez pas saisi l'intitulé. ";
            if($data['groupe']->getType() == Groupe::GRP_UNDEF) $erreur .= "Vous n'avez pas choisi le type du groupe. ";
            if($data['groupe']->getEC() == -1) $erreur .= "Le groupe n'est pas associé à un EC. ";
            
            if($erreur == "") {
                if(GroupeECModel::create($data['groupe'])) {
                    // Ajout des intervenants
                    if(isset($_POST['int'])) {
                        foreach($_POST['int'] as $int) {
                            IntGroupeECModel::ajouter($data['groupe']->getId(), $int);
                        }
                    }
                    Controller::goTo("groupesec/index.php", "Le groupe a été ajouté.");
                }
                else
                    WebPage::setCurrentErrorMsg("Le groupe n'a pas été ajouté dans la base de données.");
            }
            else
                WebPage::setCurrentErrorMsg($erreur);
        }
        if(($data['users'] = UserModel::getList(User::TYPE_ENSEIGNANT)) === null)
            Controller::goTo("groupesec/index.php", "Erreur lors de la récupération de la liste des enseignants.");
        
        Controller::push("Ajouter un groupe dans un EC", "./view/groupesec/ajouter.php", $data);
    }
    
    /**
     * Modifie un groupe.
     *  #RIGHTS# : responsable de l'EC
     */
    public static function modifier() : void {
        if(!isset($_SESSION['current']['EC']) || 
           (!UserModel::estRespEC($_SESSION['current']['EC']) &&
            !RespDiplomeModel::estResponsableDiplomeEC(UserModel::getId(), $_SESSION['current']['EC'])))
            Controller::goTo("groupesec/index.php", "", "Vous n'avez pas les droits pour réaliser cette action.");
        
        // L'utilisateur a annulé
        if(isset($_POST['btnAnnuler']))
            Controller::goTo("groupesec/index.php", "Le groupe n'a pas été modifié.");
        
        // Le formulaire a été validé
        $data = [];
        if(isset($_POST['btnModifier'])) {
            if(!isset($_SESSION['current']['groupeEC']))
                Controller::goTo("groupesec/index.php", "", "Erreur lors de la récupération du groupe.");
            
            $data['groupe'] = self::__getFromForm();
            $data['groupe']->setId($_SESSION['current']['groupeEC']);
            
            // Mise-à-jour du/des intervenant(s)
            IntGroupeECModel::supprimer($_SESSION['current']['groupeEC'], -1);
            if(isset($_POST['int'])) {
                foreach($_POST['int'] as $int) {
                    IntGroupeECModel::ajouter($_SESSION['current']['groupeEC'], $int);
                }
            }
            
            // Mise-à-jour du groupe
            $erreur = "";
            if($data['groupe']->getIntitule() == "") $erreur .= "Vous n'avez pas spécifié l'intitulé.";
            
            if($erreur == "") {
                if(GroupeECModel::update($data['groupe']))
                    Controller::goTo("groupesec/index.php", "Le groupe a été modifié.");
                else
                    WebPage::setCurrentMsg("Le groupe n'a pas été modifié dans la base de données.");
            }        
            else
                WebPage::setCurrentErrorMsg($erreur);
        }      
        else {
            $data['groupe'] = GroupeECModel::read(intval($_POST['idModi']));
            if($data['groupe'] === null)
                Controller::goTo("groupesec/index.php", "", "Erreur lors de la récupération du groupe.");
            $_SESSION['current']['groupeEC'] = intval($_POST['idModi']);
        }
        if(($data['EC'] = ECModel::read($_SESSION['current']['EC'])) === null)
            Controller::goTo("groupessec/index.php", "", "Erreur lors de la récupération de l'EC.");
        
        $data['users'] = UserModel::getList(User::TYPE_ENSEIGNANT);
        $data['int'] = IntGroupeECModel::getList($_SESSION['current']['groupeEC']);
        if(($data['users'] === null) || ($data['int'] === null))
            Controller::goTo("ecs/index.php", "", "Erreur lors de la récupération des utilisateurs.");
        
        Controller::push("Modifier un groupe d'EC", "./view/groupesec/modifier.php", $data);
    }
    
    /**
     * Supprime un groupe.
     *  #RIGHTS# : responsable de l'EC
     */
    public static function supprimer() : void {
        if(!isset($_SESSION['current']['EC']) || 
           (!UserModel::estRespEC($_SESSION['current']['EC']) &&
            !RespDiplomeModel::estResponsableDiplomeEC(UserModel::getId(), $_SESSION['current']['EC'])))
            Controller::goTo("groupesec/index.php", "", "Vous n'avez pas les droits pour réaliser cette action.");
        
        // L'utilisateur a annulé
        if(isset($_POST['btnAnnuler']))
            Controller::goTo("groupesec/index.php", "Le groupe n'a pas été supprimé.");
        
        // Le formulaire a été validé
        $data = [];        
        if(isset($_POST['btnSupprimer'])) {
            if(!isset($_SESSION['current']['groupeEC']))
                Controller::goTo("groupesec/index.php", "Le groupe n'a pas été supprimé.");
                        
            if(GroupeECModel::delete($_SESSION['current']['groupeEC']) &&
               InscriptionGroupeECModel::desinscrire($_SESSION['current']['groupeEC'], -1)) {
                Controller::goTo("groupesec/index.php", "Le groupe a été supprimé.");
            }
            else {
                WebPage::setCurrentErrorMsg("Le groupe n'a pas été supprimé de la base de données.");
                if(($data['groupe'] = GroupeECModel::read($_SESSION['current']['groupeEC'])) === null)
                    Controller::goTo("groupesec/index.php", "", "Erreur lors de la récupération du groupe");
            }
        }
        else {
            if(($data['groupe'] = GroupeECModel::read(intval($_POST['idSupp']))) === null)
                Controller::goTo("groupesec/index.php", "", "Erreur lors de la récupération du groupe");
            $_SESSION['current']['groupeEC'] = intval($_POST['idSupp']);
        }
        if(($data['EC'] = ECModel::read($_SESSION['current']['EC'])) === null)
            Controller::goTo("groupesec/index.php", "", "Erreur lors de la récupération de l'EC.");

        Controller::push("Supprimer un groupe d'EC", "./view/groupesec/supprimer.php", $data);
    }
    
    /**
     * Service Web
     */
    public static function ws() : void {
        $mode = 0;
        if(isset($_POST['mode'])) $mode = intval($_POST['mode']);
        
        $data['mode'] = $mode;
        switch($mode) {
            case 1: // Tous les groupes d'une EC
                // #RIGHTS# : public (juste une liste)
                $json = [];
                $EC = -1;
                if(isset($_POST['EC']))
                    $EC = intval($_POST['EC']);
                else
                    if(isset($_SESSION['current']['EC']))
                        $EC = $_SESSION['current']['EC'];

                if($EC != -1) {
                    if(($json['groupes'] = GroupeECModel::getList($EC, Groupe::GRP_UNDEF, true)) === null) {
                        $json['code'] = -1;
                        $json['erreur'] = "Erreur lors du chargement du groupe.";
                    }
                    else
                        $json['code'] = 1;
                }
                else {
                    $json['code'] = -1;
                    $json['erreur'] = "Il manque des données.";
                    $json['groupes'] = [];
                }
                
                Controller::JSONpush($json);
                break;
            case 2: // Liste des étudiants dans un groupe d'EC donné
                // #RIGHTS# : intervenant dans l'EC
                if(isset($_POST['groupe']))
                    $data['groupe'] = intval($_POST['groupe']);
                else
                    $data['groupe'] = -1;
                $_SESSION['current']['groupeEC'] = intval($data['groupe']);
                
                if(!isset($_SESSION['current']['EC']) || 
                   (!UserModel::estIntEC($_SESSION['current']['EC']) &&
                    !RespDiplomeModel::estResponsableDiplomeEC(UserModel::getId(), $_SESSION['current']['EC'])))
                    $data['etudiants'] = [];
                else {
                    $data['EC'] = $_SESSION['current']['EC'];
                    if($data['groupe'] != -1)
                        $data['etudiants'] = InscriptionGroupeECModel::getListe($data['groupe']);
                    else {
                        $data['groupesCM'] = GroupeECModel::getList($_SESSION['current']['EC'], Groupe::GRP_CM);
                        $data['groupesTD'] = GroupeECModel::getList($_SESSION['current']['EC'], Groupe::GRP_TD);
                        $data['groupesTP'] = GroupeECModel::getList($_SESSION['current']['EC'], Groupe::GRP_TP);
                        $data['etudiants'] = InscriptionGroupeECModel::getListeInscriptions($_SESSION['current']['EC']);
                    }
                }
                
                Controller::push("", "./view/groupesec/etudiants_liste.php", $data, "");
                break;
            case 3: // Inscription d'un étudiant dans un groupe
                // #RIGHTS# : responsable de l'EC ou du diplôme
                $json = [];
                $json['code'] = -1;
                
                if(!isset($_SESSION['current']['EC']) || !isset($_SESSION['current']['groupeEC']) || !isset($_POST['etudiant']))
                    $json['erreur'] = "Il manque des données !!!";
                elseif(!UserModel::estRespEC($_SESSION['current']['EC']) &&
                       !RespDiplomeModel::estResponsableDiplomeEC(UserModel::getId(), $_SESSION['current']['EC']))
                    $json['erreur'] = "Vous n'avez pas les droits pour modifier les étudiants de groupe.";
                else {
                    $json['etudiant'] = intval($_POST['etudiant']);
                    $json['EC'] = $_SESSION['current']['EC'];
                    
                    if(($groupe = GroupeECModel::read($_SESSION['current']['groupeEC'])) === null)
                        $json['erreur'] = "Erreur lors de la récupération du groupe.";
                    else {
                        if(InscriptionGroupeECModel::inscrire($_SESSION['current']['groupeEC'],
                                                              $json['etudiant'],
                                                              $json['EC'],
                                                              $groupe->getType()))
                            $json['code'] = 1;
                        else
                            $json['erreur'] = "Problème lors de l'inscription de l'étudiant.";
                    }
                }
                Controller::JSONpush($json);
                break; 
            case 4: // Desinscription d'un étudiant dans un groupe
                // #RIGHTS# : responsable de l'EC ou du diplôme
                $json = [];
                $json['code'] = -1;
                
                if(!isset($_POST['groupe']) || !isset($_POST['etudiant']) || !isset($_SESSION['current']['EC']))
                    $json['erreur'] = "Il manque des données.";
                elseif(!UserModel::estRespEC($_SESSION['current']['EC']) &&
                       !RespDiplomeModel::estResponsableDiplomeEC(UserModel::getId(), $_SESSION['current']['EC']))
                    $json['erreur'] = "Vous n'avez pas les droits pour réaliser cette action.";
                else {
                    $json['groupe'] = intval($_POST['groupe']);
                    $json['etudiant'] = intval($_POST['etudiant']);
                    if(InscriptionGroupeECModel::desinscrire(intval($_POST['groupe']), intval($_POST['etudiant'])))
                        $json['code'] = 1;
                    else
                        $json['erreur'] = "Problème lors de la desinscription de l'étudiant.";
                }                
                Controller::JSONpush($json);
                break;
            case 5: // Spécifie l'EC et le groupe
                // #RIGHTS# : aucun, ce n'est pas critique
                $json = [ 'code' => 1 ];
                if(isset($_POST['EC'])) $_SESSION['current']['EC'] = intval($_POST['EC']);
                if(isset($_POST['groupeEC'])) $_SESSION['current']['groupeEC'] = intval($_POST['groupeEC']);
                Controller::JSONpush($json);
                break;
            case 6: // Inscription/désinscription d'un étudiant dans un groupe
                // #RIGHTS# : responsable de l'EC ou du diplôme
                $json = [];
                $json['code'] = -1;
                
                if(!isset($_SESSION['current']['EC']) || !isset($_POST['groupeEC']) || 
                   !isset($_POST['etudiant']) || !isset($_POST['type']))
                    $json['erreur'] = "Il manque des données !!!";
                elseif(!UserModel::estRespEC($_SESSION['current']['EC']) &&
                       !RespDiplomeModel::estResponsableDiplomeEC(UserModel::getId(), $_SESSION['current']['EC']))
                    $json['erreur'] = "Vous n'avez pas les droits pour modifier les étudiants de groupe.";
                else {
                    $json['etudiant'] = intval($_POST['etudiant']);
                    $json['EC'] = $_SESSION['current']['EC'];
                    $json['groupeEC'] = intval($_POST['groupeEC']);
                    $json['type'] = intval($_POST['type']);
                    
                    if(InscriptionGroupeECModel::inscrire($json['groupeEC'],
                                                          $json['etudiant'],
                                                          $json['EC'],
                                                          $json['type']))
                        $json['code'] = 1;
                    else
                        $json['erreur'] = "Problème lors de l'inscription de l'étudiant.";
                }
                Controller::JSONpush($json);
                break; 
            case 7: // Inscription/désinscription d'un étudiant dans un groupe
                // #RIGHTS# : responsable de l'EC ou du diplôme
                $json = [];
                $json['code'] = -1;
                
                if(!isset($_POST['EC']) || !isset($_POST['groupeEC']) || !isset($_POST['etudiant']) || !isset($_POST['type']))
                    $json['erreur'] = "Il manque des données !!!";
                elseif(!UserModel::estRespEC(intval($_POST['EC'])) &&
                       !RespDiplomeModel::estResponsableDiplomeEC(UserModel::getId(), intval($_POST['EC'])))
                    $json['erreur'] = "Vous n'avez pas les droits pour modifier les étudiants de groupe.";
                else {
                    $json['etudiant'] = intval($_POST['etudiant']);
                    $json['EC'] = intval($_POST['EC']);
                    $json['groupeEC'] = intval($_POST['groupeEC']);
                    $json['type'] = intval($_POST['type']);
                    $json['typeStr'] = Groupe::type2String(intval($_POST['type']));
                    
                    if(InscriptionGroupeECModel::inscrire($json['groupeEC'], $json['etudiant'],
                                                          $json['EC'], $json['type']))
                        $json['code'] = 1;
                    else
                        $json['erreur'] = "Problème lors de l'inscription de l'étudiant.";
                }
                Controller::JSONpush($json);
                break;
            case 8: // Retourne la liste des étudiants non inscrits dans un groupe d'EC
                // #DROITS# : les intervenants de l'EC
                $json = [];
                if(!isset($_SESSION['current']['EC']) || !isset($_SESSION['current']['groupeEC']) || !isset($_POST['type'])) {
                    $json['code'] = -1;
                    $json['erreur'] = "Il manque des données !!!";
                    $json['etudiants'] = [];
                }
                else {
                    if(UserModel::estIntEC($_SESSION['current']['EC'])) {
                        $json['code'] = 1;
                        $json['etudiants'] = InscriptionGroupeECModel::rechercher($_SESSION['current']['EC'],
                                                                                  $_SESSION['current']['groupeEC'],
                                                                                  intval($_POST['type']),
                                                                                  $_POST['nom']);
                    }
                    else {
                        $json['code'] = -1;
                        $json['etudiants'] = [];
                        $json['erreur'] = "Vous n'avez pas les droits";
                    }
                }
                Controller::JSONpush($json);
                break;
        }
    }
    
    /**
     * Importer un groupe existant.
     *  #RIGHTS# : administrateur, responsable de diplôme
     */
    public static function importer() : void {
        if(!isset($_SESSION['current']['EC']) || 
           (!UserModel::estRespEC($_SESSION['current']['EC']) &&
            !RespDiplomeModel::estResponsableDiplomeEC(UserModel::getId(), $_SESSION['current']['EC'])))
            Controller::goTo("groupesec/index.php", "", "Vous n'avez pas les droits pour réaliser cette action.");

        if(($data['EC'] = ECModel::read($_SESSION['current']['EC'])) === null)
            Controller::goTo("groupesec/index.php", "", "Erreur lors de la récupération de l'EC.");
        
        if(isset($_POST['inputType'])) {
            $type = intval($_POST['inputType']);
            
            if($type == 1) {
                // Importation d'un ou tous les groupes d'un diplôme
                
                if(!isset($_POST['inputGroupe']))
                    Controller::goTo("groupesec/index.php", "", "Groupe non spécifié.");
                
                if(intval($_POST['inputGroupe']) == -1) {
                    // Importation de tous les groupes du diplôme et semestre
                    if(!isset($_POST['inputDiplome']) || (intval($_POST['inputDiplome']) == -1) ||
                       !isset($_POST['inputSemestre']) || (intval($_POST['inputSemestre']) == -1)) {
                        WebPage::setCurrentErrorMsg("Le groupe et le diplôme n'ont pas été spécifiés.");
                    }
                    else {
                        // Récupération des groupes du diplôme et du semestre
                        $groupes = GroupeModel::getList(intval($_POST['inputDiplome']), 
                                                        intval($_POST['inputSemestre']),
                                                        Groupe::GRP_UNDEF,
                                                        true);
                        
                        // Ajout des groupes
                        $erreur = "";
                        foreach($groupes as $groupe) {
                            $groupeEC = new GroupeEC(-1, $groupe['intitule'], $groupe['type'], $_SESSION['current']['EC']);
                            if(GroupeECModel::create($groupeEC)) {
                                if(!InscriptionGroupeECModel::inscrireListeFromGroupe($groupeEC->getId(), $groupe['id'], 
                                                                                      $_SESSION['current']['EC'], $groupe['type']))
                                    $erreur .= "Erreur lors des inscriptions dans le groupe ".$groupeEC->getIntitule()." ";
                            }
                            else {
                                $erreur .= "Erreur lors de la création du groupe ".$groupeEC->getIntitule()." ";
                            }
                        }
                        if($erreur == "")
                            WebPage::setCurrentMsg("Les groupes ont été importés.");
                        else
                            WebPage::setCurrentErrorMsg($erreur);
                    }
                }
                else {
                    // Importation d'un groupe en particulier
                    if(isset($_POST['inputIntitule']) && ($_POST['inputIntitule'] != "")) {
                        $groupe = GroupeModel::read(intval($_POST['inputGroupe']));
                        
                        $groupeEC = new GroupeEC(-1, $_POST['inputIntitule'], $groupe->getType(), $_SESSION['current']['EC']);
                        if(GroupeECModel::create($groupeEC)) {
                            InscriptionGroupeECModel::inscrireListeFromGroupe($groupeEC->getId(), $groupe->getId(), $_SESSION['current']['EC'], $groupe->getType());
                        }
                        else {
                            WebPage::setCurrentErrorMsg("Erreur lors de la création du groupe.");
                        }
                    }
                    else {
                        WebPage::setCurrentErrorMsg("Le nom du groupe n'a pas été spécifié.");
                    }
                }
            } elseif($type == 2) {
                // Importation d'un ou tous les groupes d'un EC
                
                if(!isset($_POST['inputGroupeEC']))
                    Controller::goTo("groupesec/index.php", "", "Groupe non spécifié.");
                
                if(intval($_POST['inputGroupeEC']) == -1) {
                    // Importation de tous les groupes de l'EC
                    if(!isset($_POST['inputEC']) || (intval($_POST['inputEC']) == -1)) {
                        WebPage::setCurrentErrorMsg("L'EC n'a pas été spécifié.");
                    }
                    else {                    
                        $groupes = GroupeECModel::getList(intval($_POST['inputEC']));

                        // Ajout des groupes
                        $erreur = "";
                        foreach($groupes as $groupe) {
                            $groupeEC = new GroupeEC(-1, $groupe['intitule'], $groupe['type'], $_SESSION['current']['EC']);
                            if(GroupeECModel::create($groupeEC)) {
                                if(!InscriptionGroupeECModel::inscrireListeFromGroupeEC($groupeEC->getId(), $groupe['id'], 
                                                                                       $_SESSION['current']['EC'], $groupe['type']))
                                    $erreur .= "Erreur lors des inscriptions dans le groupe ".$groupeEC->getIntitule()." ";
                            }
                            else {
                                $erreur .= "Erreur lors de la création du groupe ".$groupeEC->getIntitule()." ";
                            }
                        }
                        if($erreur == "")
                            WebPage::setCurrentMsg("Les groupes ont été importés.");
                        else
                            WebPage::setCurrentErrorMsg($erreur);
                    }
                }
                else {
                    // Importation d'un groupe en particulier
                    if(isset($_POST['inputIntitule']) && ($_POST['inputIntitule'] != "")) {
                        $groupe = GroupeECModel::read(intval($_POST['inputGroupeEC']));
                        if($groupe->getEC() == $_SESSION['current']['EC'])
                            WebPage::setCurrentErrorMsg("Importation d'un groupe au sein du même EC impossible.");
                        else {
                            $groupeEC = new GroupeEC(-1, $_POST['inputIntitule'], $groupe->getType(), $_SESSION['current']['EC']);
                            if(GroupeECModel::create($groupeEC)) {
                                InscriptionGroupeECModel::inscrireListeFromGroupeEC($groupeEC->getId(), $groupe->getId(), $_SESSION['current']['EC'], $groupe->getType());
                            }
                            else {
                                WebPage::setCurrentErrorMsg("Erreur lors de la création du groupe.");
                            }
                        }
                    }
                    else {
                        WebPage::setCurrentErrorMsg("Le nom du groupe n'a pas été spécifié.");
                    }
                }
            }
        }
        else
            WebPage::setCurrentErrorMsg("Importation impossible.");

        Controller::goTo("groupesec/index.php");
    }
    
    /**
     * Liste des étudiants d'un groupe.
     *  #RIGHTS# : responsable de l'EC ou du diplôme, intervenant de l'EC
     */
    public static function etudiants() : void {
        if(!isset($_SESSION['current']['EC']) || 
           (!UserModel::estIntEC($_SESSION['current']['EC']) &&
            !RespDiplomeModel::estResponsableDiplomeEC(UserModel::getId(), $_SESSION['current']['EC'])))
            Controller::goTo("groupesec/index.php", "", "Vous n'avez pas les droits pour réaliser cette action.");

        if(($data['EC'] = ECModel::read($_SESSION['current']['EC'])) === null)
            Controller::goTo("groupesec/index.php", "", "Erreur lors de la récupération de l'EC.");
        
        if(isset($_POST['groupeEC']))
            $_SESSION['current']['groupeEC'] = intval($_POST['groupeEC']);
        if(isset($_SESSION['current']['groupeEC']))
            $data['groupeEC'] = $_SESSION['current']['groupeEC'];
        
        $data['groupes'] = GroupeECModel::getList($_SESSION['current']['EC']);
        
        Controller::push("Liste des étudiants des groupes d'un EC", "./view/groupesec/etudiants.php", $data);
    }

    /**
     * Importe les groupes des étudiants.
     */
    public static function importergroupes() {
        if(!isset($_SESSION['current']['EC']) || ($_SESSION['current']['EC'] == -1) ||
           (!UserModel::estIntEC($_SESSION['current']['EC']) &&
            !RespDiplomeModel::estResponsableDiplomeEC(UserModel::getId(), $_SESSION['current']['EC'])))
            Controller::goTo("groupesec/index.php", "", "Vous n'avez pas les droits suffisants.");
        
        if(isset($_FILES) && isset($_FILES['inputFichier']) && ($_FILES['inputFichier']['error'] == UPLOAD_ERR_OK)) {
            $filename = $_FILES['inputFichier']['tmp_name'];

            if (($handle = fopen($filename, "r")) !== FALSE) {
                // Lecture de l'en-tête (1 ligne)
                if(($data = fgetcsv($handle, 1000, ";")) === FALSE)
                    Controller::goTo("groupesec/etudiants.php", "", "Impossible de lire la ligne d'en-tête du fichier.");

                if(($data[0] != 'numero') || ($data[1] != 'nom') ||
                   ($data[2] != 'prenom') || ($data[3] != 'email') ||
                   ($data[4] != 'CM') || ($data[5] != 'TD') || ($data[6] != 'TP'))
                    Controller::goTo("groupesec/attribution.php", "", "Le format du fichier est incorrect.");
                
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
                $groupesCM = GroupeECModel::getList($_SESSION['current']['EC'], Groupe::GRP_CM, false);
                $groupesTD = GroupeECModel::getList($_SESSION['current']['EC'], Groupe::GRP_TD, false);
                $groupesTP = GroupeECModel::getList($_SESSION['current']['EC'], Groupe::GRP_TP, false);

                // Ajout des étudiants (s'ils n'existent pas)
                InscriptionECModel::updateList($_SESSION['current']['EC'], $liste);
                
                // Inscrit les étudiants à ce diplôme/semestre
                $existant = 0;                   // Nombre d'étudiants existants
                $erreurs = 0;                    // Erreurs de création des étudiants
                $erreursGrp = 0;                 // Erreurs de groupe (inexistant)
                $erreursIns = 0;                 // Erreurs d'inscription à des groupes

                foreach($liste as $etudiant) {
                    if($etudiant['statut'] == 'EXISTE') {
                        $existant++;
                        
                        if(($idCM = array_search($etudiant['CM'], $groupesCM)) !== false) {
                            // Groupe CM existe
                            if(InscriptionGroupeECModel::inscrire($idCM, $etudiant['id'], $_SESSION['current']['EC'], Groupe::GRP_CM) == false)
                                $erreursIns++;
                        }
                        else
                            $erreursGrp++;
                        if(($idTD = array_search($etudiant['TD'], $groupesTD)) !== false) {
                            // Groupe TD existe
                            if(InscriptionGroupeECModel::inscrire($idTD, $etudiant['id'], $_SESSION['current']['EC'], Groupe::GRP_TD) == false)
                                $erreursIns++;
                        }
                        else
                            $erreursGrp++;
                        if(($idTP = array_search($etudiant['TP'], $groupesTP)) !== false) {
                            // Groupe TP existe
                            if(InscriptionGroupeECModel::inscrire($idTP, $etudiant['id'], $_SESSION['current']['EC'], Groupe::GRP_TP) == false)
                                $erreursIns++;
                        }
                        else
                            $erreursGrp++;
                    }
                    else
                        $erreurs++;
                }

                Controller::goTo("groupesec/etudiants.php",
                                 "$existant étudiant(s) existant(s) / ".
                                 ($erreurs+$erreursIns)." erreurs / ".
                                 "$erreursGrp erreurs de groupe.");
            }
            else
                Controller::goTo("groupesec/etudiants.php", "", "Erreur lors de l'ouverture du fichier.");
        }
        else
            Controller::goTo("groupesec/etudiants.php", "", "Vous n'avez pas sélectionné de fichier.");
    }   
    
    /**
     * Exportation des étudiants.
     * #RIGHTS# : responsable de diplôme ou de l'EC, intervenant
     */
    public static function exporter() {
        if(!isset($_SESSION['current']['EC']) || !isset($_SESSION['current']['groupeEC']) ||
           (!UserModel::estIntEC($_SESSION['current']['EC']) &&
            !RespDiplomeModel::estResponsableDiplomeEC(UserModel::getId(), $_SESSION['current']['EC'])))
            Controller::goTo("groupesec/index.php", "", "Vous n'avez pas les droits suffisants.");
            
        // Chargement de l'EC et des étudiants
        $nomFichier = "";
        if(($EC = ECModel::read($_SESSION['current']['EC'])) === null)
            Controller::goTo("groupesec/index.php", "", "Erreur lors de la récupération de l'EC.");
        
        if($_SESSION['current']['groupeEC'] != -1) {
            if((($groupe = GroupeECModel::read($_SESSION['current']['groupeEC'])) === null) ||
               (($etudiants = InscriptionGroupeECModel::getListe($_SESSION['current']['groupeEC'])) === null))
                Controller::goTo("groupesec/index.php", "", "Erreur lors de la récupération des données.");
            $nomFichier = $EC->getCode()."_".$groupe->getIntitule();
            $header = [ "numero" => "numero", "nom" => "nom",
                        "prenom" => "prenom", "email" => "email" ];
        }
        else {
            if((($groupesCM = GroupeECModel::getList($_SESSION['current']['EC'], Groupe::GRP_CM)) === null) ||
               (($groupesTD = GroupeECModel::getList($_SESSION['current']['EC'], Groupe::GRP_TD)) === null) ||
               (($groupesTP = GroupeECModel::getList($_SESSION['current']['EC'], Groupe::GRP_TP)) === null) ||
               (($etudiants = InscriptionGroupeECModel::getListeInscriptions($_SESSION['current']['EC'])) === null))
                Controller::goTo("groupesec/index.php", "", "Erreur lors de la récupération des données.");
            
            foreach($etudiants as &$etudiant) {
                if($etudiant['groupeCM'] == -1)
                    $etudiant['groupeCM'] = "Aucun";
                else {
                    $i = 0;
                    while(($i < count($groupesCM)) && ($groupesCM[$i]['id'] != $etudiant['groupeCM'])) $i++;
                    if($i < count($groupesCM))
                        $etudiant['groupeCM'] = $groupesCM[$i]['intitule'];
                    else
                        $etudiant['groupeCM'] = "Aucun";
                }
                if($etudiant['groupeTD'] == -1)
                    $etudiant['groupeTD'] = "Aucun";
                else {
                    $i = 0;
                    while(($i < count($groupesTD)) && ($groupesTD[$i]['id'] != $etudiant['groupeTD'])) $i++;
                    if($i < count($groupesTD))
                        $etudiant['groupeTD'] = $groupesTD[$i]['intitule'];
                    else
                        $etudiant['groupeTD'] = "Aucun";
                }
                if($etudiant['groupeTP'] == -1)
                    $etudiant['groupeTP'] = "Aucun";
                else {
                    $i = 0;
                    while(($i < count($groupesTP)) && ($groupesTP[$i]['id'] != $etudiant['groupeTP'])) $i++;
                    if($i < count($groupesTP))
                        $etudiant['groupeTP'] = $groupesTP[$i]['intitule'];
                    else
                        $etudiant['groupeTP'] = "Aucun";
                }
            }
            $nomFichier = $EC->getCode();
            $header = [ "numero" => "numero", "nom" => "nom",
                        "prenom" => "prenom", "email" => "email", 
                        "groupeCM" => "groupeCM", "groupeTD" => "groupeTD", "groupeTP" => "groupeTP" ];
        }
        
        // Exporte en CSV
        Controller::CSVpush($nomFichier, $etudiants, $header);
    }
    
    /**
     * Inscrire des étudiants à un groupe courant.
     *  #RIGHTS# : responsable de l'EC ou du diplôme
     */
    public static function inscrire() : void {
        if(!isset($_SESSION['current']['EC']) || 
           (!UserModel::estRespEC($_SESSION['current']['EC']) &&
            !RespDiplomeModel::estResponsableDiplomeEC(UserModel::getId(), $_SESSION['current']['EC'])))
            Controller::goTo("groupesec/etudiants.php", "", "Vous n'avez pas les droits pour réaliser cette action.");

        if(!isset($_SESSION['current']['groupeEC']) || ($_SESSION['current']['groupeEC'] == -1))
            Controller::goTo("groupesec/index.php", "", "Aucun groupe sélectionné.");
        
        $data['groupe'] = $_SESSION['current']['groupeEC'];
        
        $data['EC'] = ECModel::read($_SESSION['current']['EC']);
        $data['groupeObj'] = GroupeECModel::read($_SESSION['current']['groupeEC']);
        $data['etudiants'] = InscriptionGroupeECModel::getListeNonInscrits($data['EC']->getId(), $data['groupe'], $data['groupeObj']->getType());
        
        if(($data['EC'] === null) || ($data['groupeObj'] === null) || ($data['etudiants'] === null))
            Controller::goTo("groupesec/index.php", "", "Erreur lors de la récupération des données.");
        
        Controller::push("", "./view/groupesec/inscrire.php", $data);
    }

    /**
     * Génère une fiche de présence au format PDF.
     * #RIGHTS# : responsable de diplôme, responsable de l'EC, intervenant dans l'EC
     */
    public static function fiche() : void {
        if(!isset($_SESSION['current']['EC']) || !isset($_SESSION['current']['groupeEC']) ||
           (!UserModel::estIntEC($_SESSION['current']['EC']) &&
            !RespDiplomeModel::estResponsableDiplomeEC(UserModel::getId(), $_SESSION['current']['EC'])))
            Controller::goTo("groupesec/index.php", "", "Vous n'avez pas les droits pour réaliser cette action.");

        if(($data['EC'] = ECModel::read($_SESSION['current']['EC'])) === null)
            Controller::goTo("groupesec/index.php", "", "Erreur lors de la récupération de l'EC.");
        
        if($_SESSION['current']['groupeEC'] == -1) {
            $data['etudiants'] = GroupeECModel::getList($_SESSION['current']['EC']);
            $data['groupe'] = null;
            $data['nomFichier'] = $data['EC']->getCode().".pdf";
        }
        else {
            $data['etudiants'] = InscriptionGroupeECModel::getListe($_SESSION['current']['groupeEC']);
            if(($data['groupe'] = GroupeECModel::read($_SESSION['current']['groupeEC'])) === null)
                Controller::goTo("groupesec/index.php", "", "Erreur lors de la récupération du groupe.");
            $data['nomFichier'] = $data['EC']->getCode()."_".Groupe::type2String($data['groupe']->getType())."_".$data['groupe']->getIntitule().".pdf";
        }
        
        if($data['etudiants'] === null)
            Controller::goTo("groupesec/index.php", "", "Erreur lors de la récupération des étudiants.");
        
        Controller::push("", "./view/groupesec/fiche.php", $data);
    }

    /**
     * Par défaut : la liste des groupes.
     * #RIGHTS# : administrateur, responsable de diplôme, responsable de l'EC, intervenant dans l'EC
     */
    public static function index() : void {
        if(!isset($_SESSION['current']['EC']) || ($_SESSION['current']['EC'] == -1))
            Controller::goTo("ecs/liste.php", "", "Aucun EC sélectionné.");
        
        if(!UserModel::estIntEC($_SESSION['current']['EC']) &&
           !RespDiplomeModel::estResponsableDiplomeEC(UserModel::getId(), $_SESSION['current']['EC']))
            Controller::goTo("ecs/liste.php", "", "Vous n'avez pas accès à cette section.");
        
        if(($data['EC'] = ECModel::read($_SESSION['current']['EC'])) === null)
            Controller::goTo("ecs/liste.php", "", "EC invalide.");

        if(($data['groupes'] = GroupeECModel::getList($_SESSION['current']['EC'])) === null)
            Controller::goTo("ecs/liste.php", "", "Erreur lors de la récupération des groupes.");
        
        Controller::push("Gestion des groupes de l'EC", "./view/groupesec/groupesec.php", $data);
    }

} // class GroupesecController