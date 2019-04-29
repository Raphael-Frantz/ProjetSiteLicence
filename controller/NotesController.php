<?php
// *************************************************************************************************
// * Contrôleur pour les notes
// *************************************************************************************************
class NotesController {
    
    /**
     * Service Web.
     * #RIGHTS# : variable
     */
    public static function ws() {
        $mode = 0;
        if(isset($_POST['mode'])) $mode = intval($_POST['mode']);
        
        switch($mode) {
            case 2: // Toutes les notes des étudiants d'une épreuve et d'un groupe
                /* #RIGHTS# : 
                 * affichage : responsables/intervenants de l'EC pour affichage
                 * modification : responsables, intervenants du groupe avec droits pour modification                              
                 */
                $data['etudiants'] = [];
                $data['groupeEC'] = -1;
                $data['saisie'] = false;
                
                if(isset($_SESSION['current']['EC']) && ($_SESSION['current']['EC'] != -1) &&
                   isset($_POST['epreuve']) && isset($_POST['groupe'])) {
                    $respDip = RespDiplomeModel::estResponsableDiplomeEC(UserModel::getId(), $_SESSION['current']['EC']);
                    
                    if(UserModel::estIntEC($_SESSION['current']['EC']) || $respDip) {
                        if(intval($_POST['epreuve']) == -1) {
                            /* Toutes les épreuves */
                            $_SESSION['current']['epreuve'] = intval($_POST['epreuve']);
                            $_SESSION['current']['groupeEC'] = intval($_POST['groupe']);
                            $data['groupeEC'] = $_SESSION['current']['groupeEC'];
                            
                            $data['epreuves'] = EpreuveModel::getList($_SESSION['current']['EC']);
                            $data['etudiants'] = [];
                            
                            // Toutes les notes des étudiants inscrits à la matière pour toutes les épreuves
                            foreach($data['epreuves'] as $epreuve)
                                $data['etudiants'][] = NoteModel::getListeNotesEpreuves($_SESSION['current']['EC'],
                                                                                        $epreuve['id'],
                                                                                        $_SESSION['current']['groupeEC']);
                            
                            return Controller::push("", "./view/notes/saisie_recap.php", $data, "");
                        }
                        else {
                            if(($epreuve = EpreuveModel::read(intval($_POST['epreuve']))) === false) {
                                $data['erreur'] = "Erreur lors du chargement de l'épreuve.";
                            }
                            else {
                                $_SESSION['current']['epreuve'] = intval($_POST['epreuve']);
                                $_SESSION['current']['groupeEC'] = intval($_POST['groupe']);
                                
                                $data['groupeEC'] = $_SESSION['current']['groupeEC'];
                                $data['saisie'] = $epreuve->isActive() &&
                                                  (UserModel::estRespEC($_SESSION['current']['EC']) || $respDip ||
                                                   (UserModel::estIntGrpEC($_SESSION['current']['groupeEC']) &&
                                                    NoteDroitModel::hasDroit($_SESSION['current']['epreuve'], UserModel::getId())));

                                // Toutes les notes des étudiants inscrits à la matière pour l'épreuve considérée
                                $data['etudiants'] = NoteModel::getListeNotesEpreuves($_SESSION['current']['EC'],
                                                                                      $_SESSION['current']['epreuve'],
                                                                                      $_SESSION['current']['groupeEC']);
                            }
                            return Controller::push("", "./view/notes/saisie_liste.php", $data, "");
                        }
                    }
                }
                return Controller::push("", "./view/error404.php", $data, "");
            default:
                // Mode incorrect
                exit();
                break;
        }
    }
    
    /**
     * Importation des notes.
     * #RIGHTS# : administrateur, responsable de diplôme et de l'EC, intervenant du groupe avec droits pour modification
     */
    public static function importer() {
        // Vérification des données
        if(!isset($_SESSION['current']['epreuve']) || 
           !isset($_SESSION['current']['EC']) ||
           !isset($_SESSION['current']['groupeEC']))
            Controller::goTo("ecs/index.php", "", "Données insuffisantes.");

        // Vérification des droits
        if(!UserModel::estRespEC($_SESSION['current']['EC']) &&
           !RespDiplomeModel::estResponsableDiplomeEC(UserModel::getId(), $_SESSION['current']['EC']) &&
           (!UserModel::estIntGrpEC($_SESSION['current']['groupeEC']) ||
            !NoteDroitModel::hasDroit($_SESSION['current']['epreuve'], UserModel::getId())))
            Controller::goTo("ecs/index.php", "", "Vous n'avez pas les droits suffisants.");
        
        // Récupération de l'épreuve et du groupe
        $groupeEC = GroupeECModel::read($_SESSION['current']['groupeEC']);
        $epreuve = EpreuveModel::read($_SESSION['current']['epreuve']);
        if(($groupeEC == null) || ($epreuve == null)) {
            unset($_SESSION['current']['epreuve']);
            unset($_SESSION['current']['groupeEC']);
            Controller::goTo("ecs/index.php", "", "Erreur lors de la lecture de l'EC et/ou de l'épreuve.");
        }
        
        // Vérification que l'épreuve est active
        if(!$epreuve->isActive())
            Controller::goTo("ecs/index.php", "", "L'épreuve n'est plus active, nous ne pouvez modifier des notes.");

        // Lecture du fichier
        if(!isset($_FILES) || !isset($_FILES['inputFichier']) || ($_FILES['inputFichier']['error'] != UPLOAD_ERR_OK))
            Controller::goTo("notes/saisie.php", "", "Vous n'avez pas sélectionné de fichier.");
        
        $filename = $_FILES['inputFichier']['tmp_name'];
        if (($handle = fopen($filename, "r")) === FALSE)
            Controller::goTo("notes/saisie.php", "", "Erreur lors de l'ouverture du fichier.");
            
        // Lecture de l'en-tête (1 ligne)
        if(($entete = fgetcsv($handle, 2000, ";")) === FALSE)
            Controller::goTo("notes/saisie.php", "", "Impossible de lire la ligne d'en-tête du fichier.");
                
        // Vérification de l'en-tête
        if(($entete[0] != 'numero') || ($entete[1] != 'nom') ||
           ($entete[2] != 'prenom') || ($entete[3] != 'email') ||
           ($entete[4] != 'note') || ($entete[5] != 'max'))
            Controller::goTo("notes/saisie.php", "", "Le format du fichier est incorrect.");

        // Lecture des étudiants avec leurs notes
        $liste = array();
        while (($data = fgetcsv($handle, 1000, ";")) !== FALSE) {
            $tmp = [];
            foreach($entete as $cle => $champ) {
                $tmp[$champ] = $data[$cle];
            }
            $liste[] = $tmp;
        }
        fclose($handle);
                
        // Récupération des id des étudiants et vérification qu'ils sont inscrits au groupe !!!
        InscriptionGroupeECModel::updateList($groupeEC->getId(), $liste);
                
        // Mise-à-jour des notes des étudiants
        $erreur = "";
        $nbNotes = 0;
        $inexistants = 0;
        $maxE = $epreuve->getMax();
        foreach($liste as $etudiant) {
            if($etudiant['statut'] == 'EXISTE') {
                if($etudiant['note'] != '') {
                    if($etudiant['note'] == 'ABI')
                        $note = Note::TYPE_ABI;
                    elseif($etudiant['note'] == 'ABJ')
                        $note = Note::TYPE_ABJ;
                    elseif($etudiant['note'] == 'NS')
                        $note = Note::TYPE_AUCUNE;
                    else {
                        $note = floatval(str_replace(",", ".", $etudiant['note']));
                        $max = floatval(str_replace(",", ".", $etudiant['max']));
                        
                        if($max <= 0) $max = 20;
                        if($note < 0)
                            $note = 0;
                        elseif($note > $max) {
                            $erreur .= $etudiant['nom']." ".$etudiant['prenom']." (".$etudiant['numero'].") note supérieure au max. ";
                            $note = $max;
                        }
                        if($max != $maxE) {
                            $note = $note / $max * $maxE;
                        }
                    }                                
                    
                    if(NoteModel::setNote($etudiant['id'], $_SESSION['current']['epreuve'], $note))
                        $nbNotes++;
                    else
                        $erreur .= $etudiant['nom']." ".$etudiant['prenom']." (".$etudiant['numero'].") note non affectée. ";
                }
            }
            else {
                $erreur .= $etudiant['nom']." ".$etudiant['prenom']." (".$etudiant['numero'].") non inscrit. ";
                $inexistants++;
            }
        }
        Controller::goTo("notes/saisie.php", 
                         "$nbNotes notes saisie(s) / $inexistants étudiant(s) inexistant(s)",
                         $erreur);
    }

    /**
     * Exportation des notes.
     * #RIGHTS# : administrateur, responsable de diplôme, responsable de l'EC, intervenant
     */
    public static function exporter() {
        // Vérification des données
        if(!isset($_SESSION['current']['epreuve']) || !isset($_SESSION['current']['EC']) ||
           !isset($_SESSION['current']['groupeEC']))
            Controller::goTo("ecs/index.php", "", "Données insuffisantes.");
        
        // Vérification des droits
        if(!UserModel::estIntEC($_SESSION['current']['EC']) &&
           !RespDiplomeModel::estResponsableDiplomeEC(UserModel::getId(), $_SESSION['current']['EC']))
            Controller::goTo("ecs/index.php", "", "Vous n'avez pas les droits suffisants.");

        // Chargement de l'EC
        if(($EC = ECModel::read($_SESSION['current']['EC'])) === null) {
            Controller::goTo("ecs/index.php", "", "Erreur lors de la récupération de l'EC.");
        }
        $nomFichier = $EC->getCode();
        
        if($_SESSION['current']['epreuve'] == -1) {
            $entete = [ "numero" => "numero", "nom" => "nom",
                        "prenom" => "prenom", "email" => "email" ];
            
            /* Toutes les épreuves */
            $epreuves = EpreuveModel::getList($_SESSION['current']['EC']);
            
            foreach($epreuves as $epreuve)
                $entete[$epreuve['intitule']." ({$epreuve['max']})"] = $epreuve['intitule']." ({$epreuve['max']})";
                
                            
            // Toutes les notes des étudiants inscrits à la matière pour toutes les épreuves
            $data = NoteModel::getListeNotesEpreuves($_SESSION['current']['EC'],
                                                     $epreuves[0]['id'],
                                                     $_SESSION['current']['groupeEC']);
            for($j = 0; $j < count($data); $j++) {
                if($data[$j]['note'] == Note::TYPE_ABI)
                    $data[$j]['note'] = 'ABI';
                elseif($data[$j]['note'] == Note::TYPE_ABJ)
                    $data[$j]['note'] = 'ABJ';
                $data[$j][$epreuves[0]['intitule']." ({$epreuves[0]['max']})"] = $data[$j]['note'];
            }
            
            for($i = 1; $i < count($epreuves); $i++) {
                $tmp = NoteModel::getListeNotesEpreuves($_SESSION['current']['EC'],
                                                        $epreuves[$i]['id'],
                                                        $_SESSION['current']['groupeEC']);
                $j = 0;
                for($j = 0; $j < count($data); $j++) {
                    if($tmp[$j]['note'] == Note::TYPE_ABI)
                        $tmp[$j]['note'] = 'ABI';
                    elseif($tmp[$j]['note'] == Note::TYPE_ABJ)
                        $tmp[$j]['note'] = 'ABJ';                    
                    $data[$j][$epreuves[$i]['intitule']." ({$epreuves[$i]['max']})"] = $tmp[$j]['note'];
                }
            }
            $nomFichier .= "_total";            
        }
        else {
            // Chargement de l'épreuve et des notes
            if((($epreuve = EpreuveModel::read($_SESSION['current']['epreuve'])) === null) ||
               (($data = NoteModel::getListeNotesEpreuves($_SESSION['current']['EC'], $_SESSION['current']['epreuve'],
                                                          $_SESSION['current']['groupeEC'])) === null)) {
                unset($_SESSION['current']['epreuve']);
                unset($_SESSION['current']['groupeEC']);
                unset($_SESSION['current']['EC']);
                Controller::goTo("ecs/index.php", "", "Erreur lors de la récupération des données.");
            }
            $nomFichier .= "_".$epreuve->getIntitule();
            
            $entete = [ "numero" => "numero", "nom" => "nom",
                        "prenom" => "prenom", "email" => "email",
                        "note" => "note", "max" => "max" ];
        }
        
        // Chargement du groupe de l'EC (si non vide)
        if($_SESSION['current']['groupeEC'] != -1) {
            if(($groupe = GroupeECModel::read($_SESSION['current']['groupeEC'])) === null) {
                unset($_SESSION['current']['epreuve']);
                unset($_SESSION['current']['groupeEC']);
                unset($_SESSION['current']['EC']);
                Controller::goTo("ecs/index.php", "", "Erreur lors de la récupération des données.");
            }
            else
                $nomFichier .= "_".$groupe->getIntitule();
        }
        
        // Remplacement des codes par le texte correspondant
        foreach($data as &$row) {
            if($row['note'] == Note::TYPE_ABI)
                $row['note'] = 'ABI';
            elseif($row['note'] == Note::TYPE_ABJ)
                $row['note'] = 'ABJ';
        }
        
        // Exporte en CSV
        Controller::CSVpush($nomFichier, $data, $entete);
    }
    
    /**
     * Ajout de notes (lors de la validation de modifications).
     * #RIGHTS# : administrateur, responsable de diplôme et de l'EC, intervenant du groupe avec droits pour modification
     */
    public static function ajouter() {
        // Vérification des données
        if(!isset($_SESSION['current']['EC']) || !isset($_SESSION['current']['epreuve']) ||
           !isset($_SESSION['current']['groupeEC']))
            Controller::goTo("ecs/index.php", "", "Données insuffisantes.");

        // Vérification des droits
        if(!UserModel::estRespEC($_SESSION['current']['EC']) &&
           !RespDiplomeModel::estResponsableDiplomeEC(UserModel::getId(), $_SESSION['current']['EC']) &&
           (!UserModel::estIntGrpEC($_SESSION['current']['groupeEC']) ||
            !NoteDroitModel::hasDroit($_SESSION['current']['epreuve'], UserModel::getId())))
            Controller::goTo("ecs/index.php", "", "Vous n'avez pas les droits suffisants.");
        
        // Récupération de l'épreuve
        if(($epreuve = EpreuveModel::read($_SESSION['current']['epreuve'])) === null) {
            unset($_SESSION['current']['epreuve']);
            unset($_SESSION['current']['groupeEC']);
            unset($_SESSION['current']['EC']);
            Controller::goTo("ecs/index.php", "", "Erreur lors de la lecture de l'épreuve.");
        }
        
        // Vérification que l'épreuve est active
        if(!$epreuve->isActive())
            Controller::goTo("ecs/index.php", "", "L'épreuve n'est plus active, nous ne pouvez modifier des notes.");
        
        // Récupération des notes
        if(($data = NoteModel::getListeNotesEpreuves($_SESSION['current']['EC'], $_SESSION['current']['epreuve'],
                                                     $_SESSION['current']['groupeEC'])) === null)
            Controller::goTo("ecs/index.php", "", "Erreur lors de la récupération des notes.");
        
        $erreur = "";
        $nbNotes = 0;
        $inexistants = 0;
        foreach($data as $etudiant) {
            if(isset($_POST['note_'.$etudiant['id']]) && ($_POST['note_'.$etudiant['id']] != '')) {
                $note = $_POST['note_'.$etudiant['id']];
                
                if($note == 'ABI')
                    $note = Note::TYPE_ABI;
                elseif($note == 'ABJ')
                    $note = Note::TYPE_ABJ;
                elseif($note == 'NS')
                    $note = Note::TYPE_AUCUNE;
                else {
                    $note = str_replace(",", ".", $note);
                    $note = floatval($note);
                    if($note < 0) {
                        $note = 0;
                        $erreur .= $etudiant['nom']." ".$etudiant['prenom']." (".$etudiant['numero'].") note inférieure à 0. ";
                    }
                    elseif($note > $epreuve->getMax()) {
                        $erreur .= $etudiant['nom']." ".$etudiant['prenom']." (".$etudiant['numero'].") note supérieure au max. ";
                        $note = $epreuve->getMax();
                    }
                }
                
                // Modification de la note
                if(NoteModel::setNote($etudiant['id'], $_SESSION['current']['epreuve'], $note))
                    $nbNotes++;
                else
                    $erreur .= $etudiant['nom']." ".$etudiant['prenom']." (".$etudiant['numero'].") note non affectée. ";
            }
        }
        
        Controller::goTo("notes/saisie.php", 
                         "$nbNotes notes saisie(s) / $inexistants étudiant(s) inexistant(s)",
                         $erreur);
    }
    
    /**
     * Saisie des notes.
     * #RIGHTS# : administrateur, responsable de diplôme, responsable de l'EC, intervenant
     */
    public static function saisie() {
        if(!isset($_SESSION['current']['EC']) || ($_SESSION['current']['EC'] == -1))
            Controller::goTo("ecs/liste.php", "", "L'EC n'a pas été spécifié.");
            
        if(!UserModel::estIntEC($_SESSION['current']['EC']) &&
           !RespDiplomeModel::estResponsableDiplomeEC(UserModel::getId(), $_SESSION['current']['EC']))
            Controller::goTo("ecs/liste.php", "", "Vous n'avez pas les droits suffisants.");
        
        if(isset($_SESSION['current']['epreuve']) && ($_SESSION['current']['epreuve'] != -1)) {
            $data['epreuve'] = EpreuveModel::read($_SESSION['current']['epreuve']);
            if($data['epreuve'] == null)
                Controller::goTo("epreuves/index.php", "", "Erreur lors de la récupération des données.");
        }
        
        if((($data['EC'] = ECModel::read($_SESSION['current']['EC'])) === null) ||
           (($data['epreuves'] = EpreuveModel::getList($_SESSION['current']['EC'])) === null) ||
           (($data['groupes'] = GroupeECModel::getList($_SESSION['current']['EC'])) === null))
            Controller::goTo("epreuves/index.php", "", "Erreur lors de la récupération des données.");

        if(isset($_SESSION['current']['groupeEC']))
            $data['groupeEC'] = $_SESSION['current']['groupeEC'];
        else
            $data['groupeEC'] = -1;
        $_SESSION['current']['groupeEC'] = $data['groupeEC'];
            
        return Controller::push("Saisie des notes", "./view/notes/saisie.php", $data);
    }

} // class NotesController