<?php
// *************************************************************************************************
// * Contrôleur pour le tutorat
// *************************************************************************************************
class TutoratController {

    /**
     * Exporte les IP des étudiants.
     * #RIGHTS# : administrateur, responsable du diplôme
     */
    public static function exporter() {
        $diplome = -1;
        if(isset($_SESSION['current']['diplome'])) $diplome = $_SESSION['current']['diplome'];
        $semestre = -1;
        if(isset($_SESSION['current']['semestre'])) $semestre = $_SESSION['current']['semestre'];
        
        if(!UserModel::estRespDiplome($diplome) || ($diplome == -1) || ($semestre == -1))
            Controller::goTo("", "", "Vous n'avez pas les droits suffisants.");
                
        $data = InscriptionECModel::getListeInscriptionsCSV($_SESSION['current']['diplome'], $_SESSION['current']['semestre']);
        
        return Controller::CSVpush("IP", $data['etudiants'], $data['header']);
    }

    /**
     * Importe les IP des étudiants.
     * #RIGHTS# : administrateur, responsable du diplôme
     */
    public static function importer() {
        $diplome = -1;
        if(isset($_SESSION['current']['diplome'])) $diplome = $_SESSION['current']['diplome'];
        $semestre = -1;
        if(isset($_SESSION['current']['semestre'])) $semestre = $_SESSION['current']['semestre'];
        
        if(!UserModel::estRespDiplome($diplome) || ($diplome == -1) || ($semestre == -1))
            Controller::goTo("", "", "Vous n'avez pas les droits suffisants.");
        
        if(isset($_FILES) && isset($_FILES['inputFichier']) && ($_FILES['inputFichier']['error'] == UPLOAD_ERR_OK)) {
            $filename = $_FILES['inputFichier']['tmp_name'];

            if (($handle = fopen($filename, "r")) !== FALSE) {
                // Lecture de l'en-tête (1 ligne)
                if(($entete = fgetcsv($handle, 2000, ";")) === FALSE)
                    Controller::goTo("tutorat/etudiants.php", "", "Impossible de lire la ligne d'en-tête du fichier.");
                
                if(($entete[0] != 'numero') || ($entete[1] != 'nom') ||
                   ($entete[2] != 'prenom') || ($entete[3] != 'email'))
                    Controller::goTo("tutorat/etudiants.php", "", "Le format du fichier est incorrect.");
                
                // Récupération des id des ECs (ou erreurs)                
                $listeEC = [];
                for($i = 4; $i < count($entete); $i++) {
                    $listeEC[] = ['code' => $entete[$i]];
                }
                if(ECModel::getListFromCode($listeEC) == false) {
                    $erreur = "";
                    foreach($listeEC as $EC) {
                        if($EC['id'] == -1)
                            $erreur .= $EC['code']. " ";
                    }                    
                    Controller::goTo("tutorat/etudiants.php", "",
                                     "Une ou des erreurs de code d'EC a/ont été détectée(s) : $erreur.".print_r($listeEC, true));
                }
                
                // Lecture des étudiants avec leurs groupes
                $liste = array();
                while (($data = fgetcsv($handle, 1000, ";")) !== FALSE) {
                    $tmp = [];
                    foreach($entete as $cle => $champ) {
                        $tmp[$champ] = $data[$cle];
                    }
                    $liste[] = $tmp;
                }
                fclose($handle);
                
                // Récupération des id des étudiants
                EtudiantModel::updateList($liste, false);
                
                // Inscrit les étudiants aux ECs
                $OK = 0;
                $erreursIns = 0;
                $inexistants = 0;
                foreach($liste as $etudiant) {
                    if($etudiant['statut'] == 'EXISTE') {
                        for($i = 4; $i < count($entete); $i++) {
                            $val = $etudiant[$entete[$i]];
                            $type = InscriptionECModel::TYPE_NONINSCRIT;
                            $note = 0.0;
                            $bareme = 20.0;
                            if($val == 'X')
                                $type = InscriptionECModel::TYPE_INSCRIT;
                            elseif($val != '') {
                                $array = explode("/", $val);
                                $note = floatval($array[0]);
                                if(count($array) > 1)
                                    $bareme = floatval($array[1]);
                                $type = InscriptionECModel::TYPE_VALIDE;
                            }
                            if(InscriptionECModel::inscrire($listeEC[$i - 4]['id'],
                                                            $etudiant['id'],
                                                            $type, $note, $bareme)) {
                                $OK++;                            
                            }
                            else {
                                $erreursIns++;
                            }
                        }
                    }
                    else
                        $inexistants++;
                }
                Controller::goTo("tutorat/etudiants.php", 
                                 "$OK inscription(s) / ".
                                 "$erreursIns erreur(s) d'inscription / ".
                                 "$inexistants étudiant(s) inexistant(s).");
            }
            else
                Controller::goTo("tutorat/etudiants.php", "", "Erreur lors de l'ouverture du fichier.");
        }
        else
            Controller::goTo("tutorat/etudiants.php", "", "Vous n'avez pas sélectionné de fichier.");
    }

    /**
     * Liste des étudiants avec leur IP
     * #RIGHTS# : administrateur, responsable de diplôme
     */
    public static function etudiants() {
        if(!UserModel::estRespDiplome() && !UserModel::estTuteur())
            Controller::goTo("", "", "Vous n'avez pas les droits suffisants.");
        
        if(UserModel::estAdmin())
            $data = ['diplomes' => DiplomeModel::getList() ];
        else
            if(UserModel::estTuteur())
                $data = ['diplomes' => TuteurModel::getListDiplomes(UserModel::getId()) ];
            else
                $data = ['diplomes' => RespDiplomeModel::getList(UserModel::getId()) ];
        
        if(count($data['diplomes']) > 0) {
            if((!isset($_SESSION['current']['diplome'])) || ($_SESSION['current']['diplome'] == -1))
                $_SESSION['current']['diplome'] = $data['diplomes'][0]['id'];
            $data['diplome'] = DiplomeModel::read($_SESSION['current']['diplome']);
            if($data['diplome'] == null) {
                unset($_SESSION['current']['diplome']);
                Controller::goTo("", "", "Problème lors du chargement du diplôme.");
            }
            
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
        
        return Controller::push("IP des étudiants", "./view/tutorat/etudiants.php", $data);
    }

    /**
     * Service Web
     */
    public static function ws() {
        $mode = 0;
        if(isset($_POST['mode'])) $mode = intval($_POST['mode']);
        $data['diplome'] = -1;
        if(isset($_POST['diplome'])) $data['diplome'] = intval($_POST['diplome']);
        $data['semestre'] = -1;
        if(isset($_POST['semestre'])) $data['semestre'] = intval($_POST['semestre']);
        
        switch($mode) {
            case 1: // Inscription dans des ECs des étudiants d'un diplome/semestre pour un semestre donné
                // #RIGHTS# : administrateur, responsable du diplôme
                if(($data['diplome'] != -1) && ($data['semestre'] != -1) && isset($_POST['semestreIns']) &&
                   UserModel::estRespDiplome($data['diplome']) || UserModel::estTuteurDiplome($data['diplome'])) {
                    $_SESSION['current']['semestre'] = intval($data['semestre']);
                    $_SESSION['current']['diplome'] = intval($data['diplome']);
                    
                    $data = InscriptionECModel::getListeInscriptions($_SESSION['current']['diplome'],
                                                                     $_SESSION['current']['semestre'],
                                                                     intval($_POST['semestreIns']));
                }
                else {                    
                    $data = ['EC' => [], 'etudiants' => []];
                }
                return Controller::push("", "./view/tutorat/etudiants_liste.php", $data, "");
                break;
            case 2: // Récupère l'inscription à un EC d'un étudiant
                // #RIGHTS# : administrateur, responsable du diplôme
                $json = [];
                if(!UserModel::isConnected()) {
                    $json['code'] = -2;
                    WebPage::setCurrentErrorMsg("Vous n'êtes pas ou plus connecté. Veuillez vous connecter.");
                }
                else {
                    if(!isset($_POST['etudiant']) || !isset($_POST['EC']) || 
                       !isset($_SESSION['current']['diplome']) || ($_SESSION['current']['diplome'] == -1)) {
                        $json['code'] = -1;
                        $json['erreur'] = "Il manque des données.";
                    }
                    else {
                        if(UserModel::estRespDiplome($_SESSION['current']['diplome'])) {
                            $json = InscriptionECModel::getInscription(intval($_POST['etudiant']), intval($_POST['EC']));
                            if(count($json) == 0) {
                                $json['code'] = -1;
                                $json['erreur'] = "Erreur lors de la récupération des données.";
                            }
                            else {
                                $json['code'] = 1;
                                $json['etudiant'] = intval($_POST['etudiant']);
                                $json['EC'] = intval($_POST['EC']);
                            }   
                        }
                        else {
                            $json['code'] = -1;
                            $json['erreur'] = "Vous n'avez pas les droits pour effectuer cette action.";
                        }
                    }
                }
                return Controller::JSONpush($json);
                break;
            case 3: // Modifie l'inscription à un EC d'un étudiant
                // #RIGHTS# : administrateur, responsable du diplôme
                $json = [];
                if(!UserModel::isConnected()) {
                    $json['code'] = -2;
                    WebPage::setCurrentErrorMsg("Vous n'êtes pas ou plus connecté. Veuillez vous connecter.");
                }
                else {
                    if(!isset($_POST['etudiant']) || !isset($_POST['EC']) || !isset($_POST['type']) ||
                       !isset($_POST['note']) || !isset($_POST['bareme'])) {
                        $json['code'] = -1;
                        $json['erreur'] = "Il manque des données.";
                    }
                    else {
                        if(UserModel::estRespDiplome($_SESSION['current']['diplome'])) {
                            $type = intval($_POST['type']);
                            if(($type == InscriptionECModel::TYPE_NONINSCRIT) || 
                               ($type == InscriptionECModel::TYPE_INSCRIT) || 
                               ($type == InscriptionECModel::TYPE_VALIDE)) {
                                if($type != InscriptionECModel::TYPE_VALIDE) {
                                    $note = 0.0;
                                    $bareme = 0.0;
                                }
                                else {
                                    $note = floatval($_POST['note']);
                                    $bareme = floatval($_POST['bareme']);
                                    if($bareme <= 0) $bareme = 20.0;
                                    if(($note < 0) || ($note > $bareme))
                                        $note = $bareme / 2;
                                }
                                    
                                // #TODO# Vérification EC et etudiant ?
                                if(InscriptionECModel::inscrire(intval($_POST['EC']), intval($_POST['etudiant']), $type, $note, $bareme)) {
                                    $json['code'] = 1;
                                    $json['msg'] = "Inscription réussie.";
                                    $json['etudiant'] = intval($_POST['etudiant']);
                                    $json['EC'] = intval($_POST['EC']);
                                    $json['type'] = $type;
                                    $json['note'] = $note;
                                    $json['bareme'] = $bareme;
                                }
                                else {
                                    $json['code'] = -1;
                                    $json['erreur'] = "Erreur lors de l'inscription.";
                                }
                            }
                            else {
                                $json['code'] = -1;
                                $json['erreur'] = "Type invalide.";
                            }
                        }
                        else {
                            $json['code'] = -1;
                            $json['erreur'] = "Vous n'avez pas les droits pour effectuer cette action.";
                        }
                    }
                }
                return Controller::JSONpush($json);
                break;
            case 4: // Récupère la liste des tuteurs d'un diplôme
                // #RIGHTS# : administrateur, responsable du diplôme
                if(($data['diplome'] != -1) && UserModel::estRespDiplome($data['diplome'])) {
                    $_SESSION['current']['diplome'] = $data['diplome'];
                    $data['tuteurs'] = TuteurModel::getList($data['diplome']);
                }
                else
                    $data['tuteurs'] = [];
                
                if(isset($_POST['json']) && ($_POST['json'] == true))
                    Controller::JSONpush($data);
                else {
                    $data['mode'] = 1;
                    return Controller::push("", "./view/tutorat/tuteurs_liste.php", $data, "");
                }
                break;
            case 5: // Ajout d'un enseignant comme tuteur d'un diplôme
                // #RIGHTS# : administrateur, responsable du diplôme
                $json = [];
                if(isset($_SESSION['current']['diplome']) && UserModel::estRespDiplome($_SESSION['current']['diplome'])) {
                    $json['enseignant'] = -1;
                    if(isset($_POST['enseignant'])) $json['enseignant'] = intval($_POST['enseignant']);
                    
                    if($json['enseignant'] == -1) {
                        $json['code'] = -1;
                        $json['msg'] = "Aucun enseignant spécifié.";
                    }
                    else {
                        if(TuteurModel::ajouter($json['enseignant'], $_SESSION['current']['diplome']))
                            $json['code'] = 1;
                        else {
                            $json['code'] = -1;
                            $json['msg'] = "Erreur lors de l'ajout de l'enseignant comme tuteur.";
                        }
                    }
                }
                else {
                    $json['code'] = -1;
                    if($data['diplome'] == -1)
                        $json['msg'] = "Aucun diplôme sélectionné.";
                    else
                        $json['msg'] = "Vous n'êtes pas autorisé à ajouter un tuteur dans ce diplôme.";
                }
                
                return Controller::JSONpush($json);                
                break;
            case 6: // Suppression d'un enseignant comme tuteur d'un diplôme
                // #RIGHTS# : administrateur, responsable du diplôme
                $json = [];
                if(isset($_SESSION['current']['diplome']) && UserModel::estRespDiplome($_SESSION['current']['diplome'])) {
                    $json['enseignant'] = -1;
                    if(isset($_POST['enseignant'])) $json['enseignant'] = intval($_POST['enseignant']);
                    
                    if($json['enseignant'] == -1) {
                        $json['code'] = -1;
                        $json['msg'] = "Aucun enseignant spécifié.";
                    }
                    else {
                        if(TuteurModel::supprimer($json['enseignant'], $_SESSION['current']['diplome']))
                            $json['code'] = 1;
                        else {
                            $json['code'] = -1;
                            $json['msg'] = "Erreur lors de la suppression de l'enseignant comme tuteur.";
                        }
                    }
                }
                else {
                    $json['code'] = -1;
                    if($data['diplome'] == -1)
                        $json['msg'] = "Aucun diplôme sélectionné.";
                    else
                        $json['msg'] = "Vous n'êtes pas autorisé à supprimer ce tuteur dans ce diplôme.";
                }
                
                return Controller::JSONpush($json);                
                break;
            case 8: // Liste des étudiants avec leur tuteur
                
                break;
            case 9: // Liste des étudiants d'un tuteur
                if(isset($_SESSION['current']['diplome']) && UserModel::estRespDiplome($_SESSION['current']['diplome'])) {
                    if(isset($_POST['tuteur']) && (intval($_POST['tuteur']) != -1)) {
                        $data['etudiants'] = InscriptionTuteurModel::getListeEtudiantsInscrits($_SESSION['current']['diplome'],
                                                                                               intval($_POST['tuteur']));
                        $_SESSION['current']['tuteur'] = intval($_POST['tuteur']);
                        $data['tuteur'] = $_SESSION['current']['tuteur'];
                    }
                    else {
                        $_SESSION['current']['tuteur'] = -1;
                        $data['etudiants'] = InscriptionTuteurModel::getListeEtudiantsTuteurs($_SESSION['current']['diplome']);
                        $data['tuteurs'] = TuteurModel::getList($data['diplome']);
                    }
                }
                return Controller::push("", "./view/tutorat/attribution_liste.php", $data, "");
                break;
            case 10: // Attribution d'un tuteur à un étudiant
                if(isset($_SESSION['current']['diplome']) && UserModel::estRespDiplome($_SESSION['current']['diplome']) &&
                   isset($_POST['tuteur']) &&
                   isset($_POST['etudiant']) && (intval($_POST['etudiant']) != -1)) {
                    if(intval($_POST['tuteur']) != -1) {
                        if(InscriptionTuteurModel::inscrire($_SESSION['current']['diplome'],
                                                            intval($_POST['etudiant']),
                                                            intval($_POST['tuteur']))) {
                            $json['code'] = 1;
                            $json['tuteur'] = intval($_POST['etudiant']);
                            $json['etudiant'] = intval($_POST['tuteur']);
                        }
                        else {
                            $json['code'] = -1;
                            $json['msg'] = "Erreur lors de l'inscription.";
                        }
                    }
                    else {
                        if(InscriptionTuteurModel::desinscrire($_SESSION['current']['diplome'],
                                                               intval($_POST['etudiant']))) {
                            $json['code'] = 1;
                            $json['tuteur'] = -1;
                            $json['etudiant'] = intval($_POST['etudiant']);
                        }
                        else {
                            $json['code'] = -1;
                            $json['msg'] = "Erreur lors de la desinscription.";
                        }
                    }
                }
                else {
                    $json['code'] = -1;
                    $json['msg'] = "Il manque des données.";
                }
                Controller::JSONpush($json);
                break;
        }
        
        // Mode incorrect
        Controller::goTo();
    }
    
    /**
     * Ajouter un tuteur pour le diplôme courant.
     * #RIGHTS# : administrateur, responsable du diplôme
     */
    public static function ajouter() {
        if(!UserModel::estAdmin() && 
           (!isset($_SESSION['current']['diplome']) ||
            !UserModel::estRespDiplome($_SESSION['current']['diplome'])))
            Controller::goTo("", "", "Vous n'avez pas accès à cette section.");
        
        if(!isset($_SESSION['current']['diplome']) || ($_SESSION['current']['diplome'] == -1))
            Controller::goTo("tutorat/tuteurs.php", "", "Aucun diplôme sélectionné.");
        
        $data['diplomeObj'] = DiplomeModel::read($_SESSION['current']['diplome']);
        $data['diplome'] = $_SESSION['current']['diplome'];
        
        $data['mode'] = 2;
        $data['tuteurs'] = TuteurModel::getListeTuteursNonInscrits($data['diplome']);
        
        return Controller::push("", "./view/tutorat/ajouter.php", $data);
    }
    
    /**
     * Liste des tuteurs.
     * #RIGHTS# : administrateur, responsable du diplôme
     */
    public static function tuteurs() {
        if(!UserModel::estRespDiplome())
            Controller::goTo("", "", "Vous n'avez pas accès à cette section.");
        
        if(UserModel::estAdmin())
            $data = ["diplomes" => DiplomeModel::getList()];
        else
            $data = ["diplomes" => RespDiplomeModel::getList(UserModel::getId())];
        
        if(isset($_SESSION['current']['diplome']) && ($_SESSION['current']['diplome'] != -1) &&
           UserModel::estRespDiplome($_SESSION['current']['diplome'])) {
            $data['tuteurs'] = TuteurModel::getList($_SESSION['current']['diplome']);
            $data['diplome'] = $_SESSION['current']['diplome'];
        }
        else
            $data['tuteurs'] = [];
        $data['mode'] = 1;
        
        return Controller::push("Gestion des tuteurs", "./view/tutorat/tuteurs.php", $data);
    }
    
    /**
     * Liste des étudiants et leur tuteur attribué.
     */
    public static function attribution() {
        if(!UserModel::estRespDiplome())
            Controller::goTo("", "", "Vous n'avez pas accès à cette section.");
        
        if(UserModel::estAdmin())
            $data = ["diplomes" => DiplomeModel::getList()];
        else
            $data = ["diplomes" => RespDiplomeModel::getList(UserModel::getId())];
        
        $data['mode'] = 1;
        
        return Controller::push("Attribution des tuteurs", "./view/tutorat/attribution.php", $data);
    }
    
    /**
     * Exporter la liste des étudiants.
     */
    public static function exportertuteurs() {
        if(!isset($_SESSION['current']['diplome']) || ($_SESSION['current']['diplome'] == -1))
            Controller::goTo("tutorat/attribution.php", "", "Vous n'avez pas accès à cette section.");
        
        if(!UserModel::estRespDiplome($_SESSION['current']['diplome']))
            Controller::goTo("tutorat/attribution.php", "", "Vous n'avez pas accès à cette section.");
        
        if(($diplome = DiplomeModel::read($_SESSION['current']['diplome'])) == null)
            Controller::goTo("tutorat/attribution.php", "", "Erreur lors de la récupération du diplôme.");
        
        if(isset($_SESSION['current']['tuteur']) && ($_SESSION['current']['tuteur'] != -1)) {
            $tuteur = UserModel::read($_SESSION['current']['tuteur']);
            $nomFichier = $tuteur.'_etudiants';
            $etudiants = InscriptionTuteurModel::getListeEtudiantsInscrits($_SESSION['current']['diplome'], $_SESSION['current']['tuteur']);
            $entete = [ "numero" => "numero", "nom" => "nom", "email" => "email" ];            
        }
        else {
            $nomFichier = "tuteurs_".DataTools::convert2Filename($diplome->getIntitule());
            
            $etudiants = InscriptionTuteurModel::getListeEtudiantsTuteurs($_SESSION['current']['diplome']);
            $tuteurs = TuteurModel::getList($_SESSION['current']['diplome'], true);
            foreach($etudiants as &$etudiant) {
                if($etudiant['tuteur'] != null) {
                    if(!isset($tuteurs[$etudiant['tuteur']]))
                        echo $etudiant['nom'].'/'.$etudiant['tuteur'];
                    $etudiant['tuteur'] = $tuteurs[$etudiant['tuteur']]['nom'];
                }
                else
                    $etudiant['tuteur'] = "Aucun";
            }
            $entete = [ "numero" => "numero", "nom" => "nom",
                        "email" => "email", "tuteur" => "tuteur" ];
        }

        // Exporte en CSV
        Controller::CSVpush($nomFichier, $etudiants, $entete);
    }

} // class TutoratController