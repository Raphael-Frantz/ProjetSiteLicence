<?php
// *************************************************************************************************
// * Contrôleur pour les étudiants
// *************************************************************************************************
class EtudiantsController {
    
    /**
     * Récupère un étudiant depuis un formulaire.
     * @return un étudiant
     */
    public static function __getFromForm() : Etudiant {
        $inputNom = "";
        $inputPrenom = "";
        $inputNumero = -1;
        $inputEmail = "";
        
        if(isset($_POST['inputNom'])) $inputNom = $_POST['inputNom'];
        if(isset($_POST['inputPrenom'])) $inputPrenom = $_POST['inputPrenom'];
        if(isset($_POST['inputNumero'])) $inputNumero = intval($_POST['inputNumero']);
        if(isset($_POST['inputEmail'])) $inputEmail = $_POST['inputEmail'];
        
        return new Etudiant(-1, $inputNom, $inputPrenom, $inputNumero, $inputEmail);
    }

    /**
     * Ajoute un étudiant.
     * #RIGHTS# : administrateur, responsable du diplôme
     */
    public static function ajouter() {
        if(!isset($_SESSION['current']['diplome']) ||
           !UserModel::estRespDiplome($_SESSION['current']['diplome']))
            Controller::goTo("", "", "Vous n'avez pas les droits pour réaliser cette action.");
        
        // L'utilisateur a annulé
        if(isset($_POST['btnAnnuler']))
            Controller::goTo("etudiants/index.php", "Aucun étudiant n'a été ajouté.");
        
        // Le formulaire a été validé
        $data = [];
        if(isset($_POST['btnAjouter'])) {
            $data['etudiant'] = self::__getFromForm();
            
            if(($data['etudiant']->getNom() != "") &&
               ($data['etudiant']->getPrenom() != "") &&
               ($data['etudiant']->getNumero() != -1) &&
               ($data['etudiant']->getEmail() != "")) {
                if(EtudiantModel::create($data['etudiant'])) {
                    if(isset($_SESSION['current']['diplome']) && ($_SESSION['current']['diplome'] != -1) &&
                       isset($_SESSION['current']['semestre']) && ($_SESSION['current']['semestre'] != -1)) {
                        if(InscriptionDiplomeModel::inscrire($_SESSION['current']['diplome'], 
                                                             $data['etudiant']->getIdUtilisateur(),
                                                             $_SESSION['current']['semestre'])) {
                            Controller::goTo("etudiants/index.php", "L'étudiant a été ajouté et inscrit au diplôme courant.");
                        }
                        else {
                            Controller::goTo("etudiants/index.php", "L'étudiant a été ajouté mais n'a pas été inscrit au diplôme courant.");
                        }
                    }
                    else
                        Controller::goTo("etudiants/index.php", "L'étudiant a été ajouté.");                    
                }
                else
                    WebPage::setCurrentErrorMsg("L'étudiant n'a pas été ajouté dans la base de données.");
            }        
            else
                WebPage::setCurrentErrorMsg("Vous devez remplir toutes les informations obligatoires.");
        }
        
        return Controller::push("Ajouter un étudiant", "./view/etudiants/ajouter.php", $data);
    }  
    
    /**
     * Importe une liste d'étudiants.
     * #RIGHTS# : administrateur, responsable du diplôme
     */
    public static function importer() {
        if(!isset($_SESSION['current']['diplome']) ||
           !UserModel::estRespDiplome($_SESSION['current']['diplome']))
            Controller::goTo("", "", "Vous n'avez pas les droits pour réaliser cette action.");
        
        if(isset($_FILES) && isset($_FILES['inputFichier']) && ($_FILES['inputFichier']['error'] == UPLOAD_ERR_OK)) {
            $filename = $_FILES['inputFichier']['tmp_name'];
            
            $row = 1;
            if (($handle = fopen($filename, "r")) !== FALSE) {
                // Lecture de l'en-tête (9 lignes)
                $i = 0;
                while((($entete = fgetcsv($handle, 1000, ";")) !== FALSE) && ($i != 8)) $i++;
                if(($entete[0] != 'Numero etudiant') || ($entete[1] != 'Nom') ||
                   ($entete[2] != 'Prenom') || ($entete[3] != 'Adresse email'))
                    Controller::goTo("etudiants/index.php", "", "Le format du fichier est incorrect.");
                
                // Lecture des étudiants
                $liste = array();
                while (($data = fgetcsv($handle, 1000, ";")) !== FALSE) {
                    $liste[] = array("numero" => $data[0],
                                     "nom" => $data[1],
                                     "prenom" => $data[2],
                                     "email" => $data[3]);
                }
                fclose($handle);
                
                // Création des étudiants
                EtudiantModel::updateList($liste);
                                    
                // Si un diplôme/semestre est choisi, on inscrit les étudiants à ce diplôme/semestre                                        
                $diplome = -1;
                if(isset($_SESSION['current']['diplome'])) $diplome = $_SESSION['current']['diplome'];
                $semestre = -1;
                if(isset($_SESSION['current']['semestre'])) $semestre = $_SESSION['current']['semestre'];
                
                $inscrit = 0;
                $ajoutes = 0;
                $existant = 0;
                $erreurs = 0;
                if(($diplome != - 1) && ($semestre != -1)) {
                    foreach($liste as $etudiant) {
                        if(($etudiant['statut'] == 'CREE') || ($etudiant['statut'] == 'EXISTE')) {
                            if($etudiant['statut'] == 'CREE')
                                $ajoutes++;
                            else
                                $existant++;
                            if(InscriptionDiplomeModel::inscrire($diplome, $etudiant['id'], $semestre))
                                $inscrit++;
                        }
                        else
                            $erreurs++;
                    }
                }
                Controller::goTo("etudiants/index.php", 
                                 "$ajoutes étudiant(s) ajouté(s) / ".
                                 "$existant étudiant(s) existant(s) / ".
                                 "$erreurs erreurs / ".
                                 "$inscrit étudiant(s) inscrit(s) dans le diplôme courant.");
            }
            else
                Controller::goTo("etudiants/index.php", "", "Erreur lors de l'ouverture du fichier.");
        }
        else
            Controller::goTo("etudiants/index.php", "", "Vous n'avez pas sélectionné de fichier.");
    }    

    /**
     * Modifie un étudiant.
     * #RIGHTS# : administrateur, responsable d'un diplôme
     */
    public static function modifier() {
        if(!UserModel::estRespDiplome())
            Controller::goTo("", "", "Vous n'avez pas les droits pour réaliser cette action.");
        
        // L'utilisateur a annulé
        if(isset($_POST['btnAnnuler']))
            Controller::goTo("etudiants/index.php", "L'étudiant n'a pas été modifié.");
        
        // Le formulaire a été validé
        $data = [];
        if(isset($_POST['btnModifier'])) {
            if(!isset($_SESSION['current']['etudiant']))
                Controller::goTo("etudiants/index.php", "", "Erreur lors de la récupération de l'étudiant.");
                        
            $data['etudiant'] = self::__getFromForm();
            $data['etudiant']->setIdUtilisateur($_SESSION['current']['etudiant']);
            
            $erreur = "";
            
            if($data['etudiant']->getNom() == "") $erreur .= "Vous devez spécifier le nom.";
            if($data['etudiant']->getPrenom() == "") $erreur .= "Vous devez spécifier le prénom.";
            if($data['etudiant']->getNumero() == -1) $erreur .= "Vous devez spécifier un numéro.";
            if($data['etudiant']->getEmail() == "") $erreur .= "Vous devez spécifier une adresse email.";
            
            if($erreur == "") {
                if(EtudiantModel::update($data['etudiant']))
                    Controller::goTo("etudiants/index.php", "L'étudiant a été modifié.");
                else
                    WebPage::setCurrentMsg("L'étudiant n'a pas été modifié dans la base de données.");
            }        
            else
                WebPage::setCurrentErrorMsg($erreur);
        }      
        else {
            if(($data['etudiant'] = EtudiantModel::read(intval($_POST['idModi']))) === null)
                return Controller::goTo("etudiants/index.php", "", "Erreur lors de la récupération de l'étudiant.");
            $_SESSION['current']['etudiant'] = intval($_POST['idModi']);
        }
        
        return Controller::push("Modifier un étudiant", "./view/etudiants/modifier.php", $data);
    }
    
    /**
     * Supprime un étudiant.
     * #RIGHTS# : administrateur
     */
    public static function supprimer() {
        if(!UserModel::estAdmin())
            Controller::goTo("", "", "Vous n'avez pas les droits pour réaliser cette action.");
        
        // L'utilisateur a annulé
        if(isset($_POST['btnAnnuler']))
            Controller::goTo("etudiants/index.php", "L'étudiant n'a pas été supprimé.");
        
        // Le formulaire a été validé
        $data = [];
        if(isset($_POST['btnSupprimer'])) {
            if(!isset($_SESSION['current']['etudiant']))
                Controller::goTo("etudiants/index.php", "L'étudiant n'a pas été supprimé.");
            
            if(EtudiantModel::delete($_SESSION['current']['etudiant']))
                Controller::goTo("etudiants/index.php", "L'étudiant a été supprimé.");
            else {
                WebPage::setCurrentErrorMsg("L'étudiant n'a pas été supprimé de la base de données.");
                $data['etudiant'] = EtudiantModel::read($_SESSION['current']['etudiant']);
                if($data['etudiant'] == null)
                    return Controller::goTo("etudiants/index.php", "", "Erreur lors de la récupération de l'étudiant.");
            }
        }
        else {
            $data['etudiant'] = EtudiantModel::read(intval($_POST['idSupp']));
            if($data['etudiant'] == null)
                return Controller::goTo("etudiants/index.php", "", "Erreur lors de la récupération de l'étudiant.");
            $_SESSION['current']['etudiant'] = intval($_POST['idSupp']);
        }

        return Controller::push("Supprimer un étudiant", "./view/etudiants/supprimer.php", $data);
    }
    
    /**
     * Service Web
     */
    public static function ws() {
        $data['mode'] = 0;
        if(isset($_POST['mode'])) $data['mode'] = intval($_POST['mode']);
                
        if(isset($_POST['diplome'])) {
            $data['diplome'] = intval($_POST['diplome']);
            $_SESSION['current']['diplome'] = $data['diplome'];
        }
        else {
            if(!isset($_SESSION['current']['diplome'])) $_SESSION['current']['diplome'] = -1;
            $data['diplome'] = $_SESSION['current']['diplome'];
        }
        if(isset($_POST['semestre'])) {
            $data['semestre'] = intval($_POST['semestre']);
            $_SESSION['current']['semestre'] = $data['semestre'];
        }
        else {
            if(!isset($_SESSION['current']['semestre'])) $_SESSION['current']['semestre'] = -1;
            $data['semestre'] = $_SESSION['current']['semestre'];
        }
        
        switch($data['mode']) {
            case 1: // Étudiants inscrits dans un diplôme spécifié ou tous les diplômes
                // #DROITS# : administrateur, responsable de diplôme
                if(UserModel::estRespDiplome()) {
                    if(($data['diplome'] == -1) || !UserModel::estRespDiplome($data['diplome'])) {
                        // Liste de tous les étudiants
                        $data['etudiants'] = EtudiantModel::getList();
                    }
                    else {
                        // Liste des étudiants du diplôme spécifié
                        $data['etudiants'] = InscriptionDiplomeModel::getListeEtudiantsInscrits($data['diplome'], $data['semestre']);
                    }
                }
                else
                    $data['etudiants'] = [];
                return Controller::push("", "./view/etudiants/etudiants_liste.php", $data, "");
                break;
            case 3 : // Inscription d'un étudiant dans un diplôme spécifié
                // #DROITS# : administrateur, responsable de diplôme                
                $json = [];
                if(UserModel::estRespDiplome($data['diplome'])) {
                    $etudiant = -1;
                    if(isset($_POST['etudiant'])) $etudiant = intval($_POST['etudiant']);
                    $json['etudiant'] = $etudiant;
                    if(($data['diplome'] != -1) && ($etudiant != -1) && ($data['semestre'] != -1)) {
                        if(InscriptionDiplomeModel::inscrire($data['diplome'], $etudiant, $data['semestre'])) {
                            $json['code'] = 1;
                        }
                        else {
                            $json['code'] = -1;
                            $json['msg'] = "Erreur de base de données.";
                        }
                    }
                    else {
                        $json['code'] = -1;
                        $json['msg'] = "Données insuffisantes.";
                    }
                }
                else {
                    $json['code'] = -1;
                    $json['msg'] = "Vous n'avez pas les droits suffisants.";
                }
                return Controller::JSONpush($json);
                break;
            case 4: // Désinscription d'un étudiant d'un diplôme spécifié
                // #DROITS# : administrateur, responsable de diplôme
                $json = [];
                if(UserModel::estRespDiplome($data['diplome'])) {
                    $etudiant = -1;
                    if(isset($_POST['etudiant'])) $etudiant = intval($_POST['etudiant']);
                    $json['etudiant'] = $etudiant;
                    if(($data['diplome'] != -1) && ($etudiant != -1) && ($data['semestre'] != -1)) {
                        if(InscriptionDiplomeModel::desinscrire($data['diplome'], $etudiant, $data['semestre'])) {
                            $json['code'] = 1;
                        }
                        else {
                            $json['code'] = -1;
                            $json['msg'] = "Erreur de base de données.";
                        }
                    }
                    else {
                        $json['code'] = -1;
                        $json['msg'] = "Données insuffisantes.";
                    }
                }
                else {
                    $json['code'] = -1;
                    $json['msg'] = "Vous n'avez pas les droits suffisants.";
                }
                return Controller::JSONpush($json);
                break;
            case 5: // Recherche d'un étudiant
                // #DROITS# : les enseignants uniquement
                $json = [];
                if(UserModel::estEnseignant()) {
                    if(isset($_SESSION['current']['diplome']) && ($_SESSION['current']['diplome'] != -1) && isset($_POST['nom'])) {
                        $json['code'] = 1;
                        $json['etudiants'] = InscriptionDiplomeModel::rechercher($_SESSION['current']['diplome'], $_POST['nom']);
                    }
                    else {
                        $json['code'] = -1;
                        $json['etudiants'] = [];
                    }
                }
                return Controller::JSONpush($json);
                break;
            case 6: // Étudiants inscrits dans un diplôme spécifié ou tous les diplômes
                // #DROITS# : les enseignants uniquement
                if(UserModel::estEnseignant()) {
                    if($data['diplome'] == -1) {
                        $data['etudiants'] = [];
                    }
                    else {
                        // Liste des étudiants du diplôme spécifié
                        $data['etudiants'] = InscriptionDiplomeModel::getListeEtudiantsInscrits($data['diplome']);
                    }
                }
                else
                    $data['etudiants'] = [];
                return Controller::push("", "./view/etudiants/infos_liste.php", $data, "");
                break;
            case 7: // Sélectionne un étudiant
                // #DROITS# : les enseignants uniquement
                if(UserModel::estEnseignant()) {
                    if(isset($_POST['etudiant']))
                        $_SESSION['current']['etudiant'] = intval($_POST['etudiant']);
                    if(isset($_POST['back']))
                        $_SESSION['current']['back'] = substr($_POST['back'], 0, 50); /* To avoid memory attack */
                    
                    $json = [ 'code' => 1 ];
                }
                else {
                    $json = [ 'code' => -1 ];
                }
                Controller::JSONpush($json);
                break;
            default: // Mode invalide
                exit();
                break;
        }
    }    
    
    /**
     * Inscrire des étudiants à un diplome/semestre courant
     * #RIGHTS# : administrateur, responsable du diplôme dans lequel on inscrit l'étudiant
     */
    public static function inscrire() {
        if(!isset($_SESSION['current']['diplome']) ||
           !UserModel::estRespDiplome($_SESSION['current']['diplome']))
            Controller::goTo("", "", "Vous n'avez pas les droits pour réaliser cette action.");
        
        if(!isset($_SESSION['current']['diplome']) || ($_SESSION['current']['diplome'] == -1))
            Controller::goTo("etudiants/index.php", "", "Aucun diplôme sélectionné.");
        $data['diplomeObj'] = DiplomeModel::read($_SESSION['current']['diplome']);
        $data['diplome'] = $_SESSION['current']['diplome'];
        
        if(!isset($_SESSION['current']['semestre']) || ($_SESSION['current']['semestre'] == -1))
            Controller::goTo("etudiants/index.php", "", "Aucun semestre sélectionné.");
        if(($_SESSION['current']['semestre'] < 1) ||
           ($_SESSION['current']['semestre'] > $data['diplomeObj']->getNbSemestres()))
           $_SESSION['current']['semestre'] = 1;
        $data['semestre'] = $_SESSION['current']['semestre'];
        
        $data['etudiants'] = InscriptionDiplomeModel::getListeEtudiantsNonInscrits($data['diplome'], $data['semestre']);
        $data['mode'] = 2;
        
        return Controller::push("", "./view/etudiants/inscrire.php", $data);
    }
    
    /**
     * Liste de l'étudiant
     * #RIGHTS# : enseignant
     */
    public static function ip() {
        if(!UserModel::estTuteur() && !UserModel::estRespDiplome())
            Controller::goTo("", "", "Vous n'avez pas accès à cette section.");
        
        // Un étudiant doit être sélectionné
        if(!isset($_SESSION['current']['etudiant']) || ($_SESSION['current']['etudiant'] == -1))
            Controller::goTo("etudiants/infos.php", "", "Aucun étudiant sélectionné.");
        
        // Retour en arrière
        if(isset($_SESSION['current']['back']))
            $data['back'] = $_SESSION['current']['back'];
        
        // Vérification de l'accès
        // #TODO# : tuteur de l'étudiant
        // #TODO# : étudiant inscrit dans un diplôme dont l'utilisateur est responsable
        if(!UserModel::estRespDiplome() && 
           !InscriptionTuteurModel::estTuteur(UserModel::getId(), $_SESSION['current']['etudiant'])) {
           $data['erreur'] = "Vous n'êtes pas tuteur de cet étudiant et vous ne pouvez avoir accès à ses informations.";
        }
        else {
            // Récupération de l'IP
            if((($data['IPs'] = InscriptionECModel::getListeInscriptionsEtudiantComplete($_SESSION['current']['etudiant'])) === null) ||
               (($data['etudiant'] = EtudiantModel::read($_SESSION['current']['etudiant'])) === null))
                Controller::goTo("", "", "Une erreur est survenue lors de la récupération des données.");
        }
        
        return Controller::push("IP d'un étudiant", "./view/etudiants/ip.php", $data);
    }
    
    /**
     * Notes d'un étudiant
     * #RIGHTS# : enseignant
     */
    public static function notes() {
        if(!UserModel::estTuteur() && !UserModel::estRespDiplome())
            Controller::goTo("", "", "Vous n'avez pas accès à cette section.");
        
        // Un étudiant doit être sélectionné
        if(!isset($_SESSION['current']['etudiant']) || ($_SESSION['current']['etudiant'] == -1))
            Controller::goTo("etudiants/infos.php", "", "Aucun étudiant sélectionné.");
        
        // Retour en arrière
        if(isset($_SESSION['current']['back']))
            $data['back'] = $_SESSION['current']['back'];
        
        // Vérification de l'accès
        // #TODO# : tuteur de l'étudiant
        // #TODO# : étudiant inscrit dans un diplôme dont l'utilisateur est responsable
        if(!UserModel::estRespDiplome() && 
           !InscriptionTuteurModel::estTuteur(UserModel::getId(), $_SESSION['current']['etudiant'])) {
           $data['erreur'] = "Vous n'êtes pas tuteur de cet étudiant et vous ne pouvez avoir accès à ses informations.";
        }
        else {
            // Récupération des notes
            if((($data['notes'] = NoteModel::getListEtudiant($_SESSION['current']['etudiant'])) === null) ||
               (($data['etudiant'] = EtudiantModel::read($_SESSION['current']['etudiant'])) === null))
                Controller::goTo("", "", "Une erreur est survenue lors de la récupération des données.");
        }
        
        return Controller::push("Notes d'un étudiant", "./view/etudiants/note.php", $data);
    }
    
    /**
     * Présentiel d'un étudiant
     * #RIGHTS# : enseignant
     */
    public static function presentiel() {
        if(!UserModel::estTuteur() && !UserModel::estRespDiplome())
            Controller::goTo("", "", "Vous n'avez pas accès à cette section.");
        
        // Un étudiant doit être sélectionné
        if(!isset($_SESSION['current']['etudiant']) || ($_SESSION['current']['etudiant'] == -1))
            Controller::goTo("etudiants/infos.php", "", "Aucun étudiant sélectionné.");
        
        // Retour en arrière
        if(isset($_SESSION['current']['back']))
            $data['back'] = $_SESSION['current']['back'];

        // Vérification de l'accès
        // #TODO# : tuteur de l'étudiant
        // #TODO# : étudiant inscrit dans un diplôme dont l'utilisateur est responsable
        if(!UserModel::estRespDiplome() && 
           !InscriptionTuteurModel::estTuteur(UserModel::getId(), $_SESSION['current']['etudiant'])) {
           $data['erreur'] = "Vous n'êtes pas tuteur de cet étudiant et vous ne pouvez avoir accès à ses informations.";
        }
        else {
            // Récupération du présentiel et des justificatifs
            if((($data['presentiel'] = InscriptionSeanceModel::getListePresencesEtudiant($_SESSION['current']['etudiant'])) === null) ||
               (($data['justificatifs'] = JustificatifModel::getListEtudiant($_SESSION['current']['etudiant'])) === null) ||
               (($data['etudiant'] = EtudiantModel::read($_SESSION['current']['etudiant'])) === null))
                Controller::goTo("", "", "Une erreur est survenue lors de la récupération des données.");
        }
        
        return Controller::push("Notes d'un étudiant", "./view/etudiants/presentiel.php", $data);
    }    
    
    /**
     * Groupes d'un étudiant
     * #RIGHTS# : enseignant
     */
    public static function groupes() {
        if(!UserModel::estTuteur() && !UserModel::estRespDiplome())
            Controller::goTo("", "", "Vous n'avez pas accès à cette section.");
        
        // Un étudiant doit être sélectionné
        if(!isset($_SESSION['current']['etudiant']) || ($_SESSION['current']['etudiant'] == -1))
            Controller::goTo("etudiants/infos.php", "", "Aucun étudiant sélectionné.");
        
        // Retour en arrière
        if(isset($_SESSION['current']['back']))
            $data['back'] = $_SESSION['current']['back'];

        // Vérification de l'accès
        // #TODO# : tuteur de l'étudiant
        // #TODO# : étudiant inscrit dans un diplôme dont l'utilisateur est responsable
        if(!UserModel::estRespDiplome() && 
           !InscriptionTuteurModel::estTuteur(UserModel::getId(), $_SESSION['current']['etudiant'])) {
           $data['erreur'] = "Vous n'êtes pas tuteur de cet étudiant et vous ne pouvez avoir accès à ses informations.";
        }
        else {
            // Récupération des groupes et des groupes d'EC
            if((($data['groupes'] = InscriptionGroupeModel::getListeGroupesComplete($_SESSION['current']['etudiant'])) === null) ||
               (($data['groupesEC'] = InscriptionGroupeECModel::getListeGroupesComplete($_SESSION['current']['etudiant'])) === null) ||
               (($data['etudiant'] = EtudiantModel::read($_SESSION['current']['etudiant'])) === null))
                Controller::goTo("", "", "Une erreur est survenue lors de la récupération des données.");
        }
        
        return Controller::push("Notes d'un étudiant", "./view/etudiants/groupes.php", $data);
    }     
    
    /**
     * Par défaut : la liste des étudiants
     * #RIGHTS# : administrateur, responsable de diplôme
     */
    public static function index() {
        if(!UserModel::estRespDiplome())
            Controller::goTo("", "", "Vous n'avez pas accès à cette section");
        
        if(UserModel::estAdmin())
            $data = ["diplomes" => DiplomeModel::getList()];
        else
            $data = ["diplomes" => RespDiplomeModel::getList(UserModel::getId())];
        
        if(isset($_SESSION['current']['semestre']))
            $data['semestre'] = $_SESSION['current']['semestre'];
        else
            $data['semestre'] = -1;
        
        if(isset($_SESSION['current']['diplome']) && ($_SESSION['current']['diplome'] != -1) &&
           UserModel::estRespDiplome($_SESSION['current']['diplome'])) {
            $data['diplome'] = DiplomeModel::read($_SESSION['current']['diplome']);
            
            if($data['diplome'] === null) {
                echo "Erreur sur le chargement du diplôme : ".$_SESSION['current']['diplome'];
                unset($data['diplome']);
            }
            else {
                $data['nbSemestres'] = $data['diplome']->getNbSemestres();
                $data['minSemestre'] = $data['diplome']->getMinSemestre();
            }
        }
        
        return Controller::push("Gestion des étudiants", "./view/etudiants/etudiants.php", $data);
    }

} // class EtudiantsController