<?php
// *************************************************************************************************
// * Contrôleur pour la gestion du présentiel
// *************************************************************************************************
class PresentielController {

    /**
     * Service Web
     */
    public static function ws() {
        $mode = 0;
        if(isset($_POST['mode'])) $mode = intval($_POST['mode']);
        
        switch($mode) {
            case 1: // Liste des justificatifs
                if(!UserModel::isConnected()) {
                    WebPage::setCurrentErrorMsg("Vous n'êtes pas ou plus connecté. Veuillez vous connecter.");
                    return Controller::push("", "", [], "");
                }
                
                if(!UserModel::estEnseignant())
                    $data['justificatifs'] = [];
                else {
                    if(isset($_POST['diplome']) && (intval($_POST['diplome']) != -1)) {
                        $data['justificatifs'] = JustificatifModel::getList(intval($_POST['diplome']));
                        if($data['justificatifs'] === null)
                            $data['justificatifs'] = [];
                        else {
                            $data['diplome'] = intval($_POST['diplome']);
                            $_SESSION['current']['diplome'] = $data['diplome'];
                        }
                    }
                    else {
                        $data['justificatifs'] = [];
                    }
                }
                return Controller::push("", "./view/presentiel/justificatifs_liste.php", $data, "");
                break;
            case 2: // Spécifie le justificatif courant
                $json = [];
                if(!UserModel::isConnected()) {
                    WebPage::setCurrentErrorMsg("Vous n'êtes pas ou plus connecté. Veuillez vous connecter.");
                    $json['code'] = -2;
                }
                else {
                    if(!UserModel::estEnseignant()) {
                        $json['code'] = -1;
                        $json['msg'] = "Vous n'avez pas les droits suffisants.";
                    }
                    else {
                        if(!isset($_POST['justif']) || (intval($_POST['justif']) <= 0)) {
                            $json['code'] = -1;
                            $json['msg'] = "Données insuffisantes.";
                        }
                        else {
                            // #TODO# : vérification d'accès au justificatif
                            $json['code'] = 1;
                            $_SESSION['current']['justificatif'] = intval($_POST['justif']);
                        }
                    }
                }
                Controller::JSONpush($json);
                break;
            case 3: // Récupère les informations sur un justificatif
                if(!UserModel::isConnected()) {
                    WebPage::setCurrentErrorMsg("Vous n'êtes pas ou plus connecté. Veuillez vous connecter.");
                    return Controller::push("", "", [], "");
                }
            
                if(UserModel::estEnseignant() &&
                   isset($_POST['justif']) &&
                   (intval($_POST['justif']) > 0)) {
                    if(($data['justificatif'] = JustificatifModel::read(intval($_POST['justif']))) !== null)
                        $data['editeur'] = UserModel::read($data['justificatif']->getEditeur());
                }
                else {
                    $data['justificatif'] = null;
                }
                return Controller::push("", "./view/presentiel/infos.php", $data, "");
                break;
            case 4: // Récupère les séances d'un groupe
                $json = [];
                if(!UserModel::isConnected()) {
                    WebPage::setCurrentErrorMsg("Vous n'êtes pas ou plus connecté. Veuillez vous connecter.");
                    $json['code'] = -2;
                }
                else {
                    if(UserModel::estEnseignant() && isset($_POST['groupe']) && 
                       isset($_SESSION['current']['EC']) && ($_SESSION['current']['EC'] != -1)) {
                        $json['code'] = 1;
                        $json['seances'] = SeanceModel::getListeSeancesGroupe(intval($_POST['groupe']));

                        $_SESSION['current']['groupeEC'] = intval($_POST['groupe']);
                        
                        // Droits de saisie
                        $json['droits'] = UserModel::estIntGrpEC($_SESSION['current']['groupeEC']) ||
                                          UserModel::estRespEC($_SESSION['current']['EC']) ||
                                          RespDiplomeModel::estResponsableDiplomeEC(UserModel::getId(), $_SESSION['current']['EC']);
                    }
                    else
                        $json['code'] = -1;
                }                                     
                Controller::JSONpush($json);
                break;
            case 5: // Récupère le présentiel d'un groupe pour une séance donnée ou pour toutes les séances
                if(!UserModel::isConnected()) {
                    WebPage::setCurrentErrorMsg("Vous n'êtes pas ou plus connecté. Veuillez vous connecter.");
                    return Controller::push("", "", [], "");
                }
            
                if(UserModel::estEnseignant() &&
                   isset($_SESSION['current']['EC']) && ($_SESSION['current']['EC'] != -1) &&
                   isset($_POST['groupe']) && isset($_POST['seance'])) {
                    $seance = intval($_POST['seance']);
                    if($seance <= 0) $seance = -1;

                    $data['presences'] = InscriptionSeanceModel::getListePresencesGroupe(intval($_POST['groupe']), $seance);
                    
                    $_SESSION['current']['groupeEC'] = intval($_POST['groupe']);
                    $_SESSION['current']['seance'] = $seance;
                    $data['seance'] = $_SESSION['current']['seance'];
                    $data['groupeEC'] = $_SESSION['current']['groupeEC'];
                    
                    // Récupération de la séance ou de la liste des séances
                    if($_SESSION['current']['seance'] != -1) {
                        $tmp = SeanceModel::read($_SESSION['current']['seance']);
                        $data['seances'][] = ['id' => $tmp->getId(), 
                                              'debut' => DateTools::timestamp2Date($tmp->getDebut(), true),
                                              'fin' => DateTools::timestamp2Date($tmp->getFin(), true) ];
                    }
                    else {
                        $data['seances'] = SeanceModel::getListeSeancesGroupe($_SESSION['current']['groupeEC']);
                        $data['rattrapages'] = InscriptionSeanceModel::getListeRattrapagesGroupe($_SESSION['current']['groupeEC'], $_SESSION['current']['seance']);
                    }
                    
                    // Récupération des justificatifs pour les séances
                    $data['justificatifs'] = JustificatifModel::getListSeances($data['seances']);
                    
                    // Droits de saisie
                    $data['droits'] = UserModel::estIntGrpEC($data['groupeEC']) ||
                                      UserModel::estRespEC($_SESSION['current']['EC']) ||
                                      RespDiplomeModel::estResponsableDiplomeEC(UserModel::getId(), $_SESSION['current']['EC']);
                }
                else {
                    $data['presences'] = [];
                }
                return Controller::push("", "./view/presentiel/saisie_liste.php", $data, "");
                break;
            case 6: // Modifie le présentiel d'un étudiant ou d'un groupe
                $json = [];
                if(!UserModel::isConnected()) {
                    WebPage::setCurrentErrorMsg("Vous n'êtes pas ou plus connecté. Veuillez vous connecter.");
                    $json['code'] = -2;
                }
                else {
                    if(isset($_SESSION['current']['seance']) && ($_SESSION['current']['seance'] != -1) && 
                       isset($_SESSION['current']['EC']) && ($_SESSION['current']['EC'] != -1) &&
                       isset($_SESSION['current']['groupeEC']) && isset($_POST['etudiant']) && isset($_POST['type'])) {
                        
                        if(!UserModel::estRespEC($_SESSION['current']['EC']) &&
                           !UserModel::estIntGrpEC($_SESSION['current']['groupeEC']) &&
                           !RespDiplomeModel::estResponsableDiplomeEC(UserModel::getId(), $_SESSION['current']['EC'])) {
                            $json['code'] = -1;
                            $json['erreur'] = "Droits insuffisants pour réaliser cette action.";
                        }
                        else {
                            $type = intval($_POST['type']);
                            if(($type != InscriptionSeanceModel::TYPE_NON_SPECIFIE) &&
                               ($type != InscriptionSeanceModel::TYPE_PRESENT) &&
                               ($type != InscriptionSeanceModel::TYPE_ABSENT)) {
                                $json['code'] = -1;
                                $json['erreur'] = "Etudiant incorrect.";
                            }
                            else {
                                if(intval($_POST['etudiant']) == -1) {
                                    if(InscriptionSeanceModel::modifierGroupe($_SESSION['current']['groupeEC'],
                                                                              $_SESSION['current']['seance'],
                                                                              $type)) {
                                        $json['code'] = 1;
                                        $json['seance'] = $_SESSION['current']['seance'];
                                        $json['etudiant'] = intval($_POST['etudiant']);
                                        $json['type'] = $type;
                                    }
                                    else {
                                        $json['code'] = -1;
                                        $json['erreur'] = "Erreur lors de la modification de la base de données.";
                                    }                            
                                }
                                else {
                                    if(InscriptionSeanceModel::modifier($_SESSION['current']['seance'],
                                                                    intval($_POST['etudiant']),
                                                                    $type)) {
                                        $json['code'] = 1;
                                        $json['seance'] = $_SESSION['current']['seance'];
                                        $json['etudiant'] = intval($_POST['etudiant']);
                                        $json['type'] = $type;
                                    }
                                    else {
                                        $json['code'] = -1;
                                        $json['erreur'] = "Erreur lors de la modification de la base de données.";
                                    }
                                }
                            }
                        }
                    }
                    else {
                        $json['code'] = -1;
                        $json['erreur'] = "Données insuffisantes.";
                    }
                }
                Controller::JSONpush($json);
                break;
            case 7 : // Récupération des informations sur une séance
                $json = [];
                if(!UserModel::isConnected()) {
                    WebPage::setCurrentErrorMsg("Vous n'êtes pas ou plus connecté. Veuillez vous connecter.");
                    $json['code'] = -2;
                }
                else {
                    if(UserModel::estEnseignant() && 
                       isset($_SESSION['current']['seance']) && ($_SESSION['current']['seance'] != -1)) {
                        if(($seance = SeanceModel::read($_SESSION['current']['seance'])) != null) {
                            $json['code'] = 1;
                            $json['seance'] = $_SESSION['current']['seance'];
                            $json['debut'] = DateTools::timestamp2Date($seance->getDebut(), true);
                            $json['fin'] = DateTools::timestamp2Date($seance->getFin(), true);
                        }
                        else {
                            $json['code'] = -1;
                            $json['erreur'] = "Erreur lors de la récupération de la séance ";
                        }
                    }
                    else {
                        $json['code'] = -1;
                        $json['erreur'] = "Données insuffisantes.";
                    }
                }
                Controller::JSONpush($json);
                break;
            case 8: // Modification des informations d'une séance
                // #RIGHTS# : administrateur, responsable de l'EC ou du diplôme, intervenant du groupe
                $json = [];
                if(!UserModel::isConnected()) {
                    WebPage::setCurrentErrorMsg("Vous n'êtes pas ou plus connecté. Veuillez vous connecter.");
                    $json['code'] = -2;
                }
                else {
                    if(isset($_SESSION['current']['seance']) && ($_SESSION['current']['seance'] != -1) &&
                       isset($_SESSION['current']['groupeEC']) && ($_SESSION['current']['groupeEC'] != -1) &&
                       isset($_SESSION['current']['EC']) && ($_SESSION['current']['EC'] != -1) &&
                       isset($_POST['inputDateDebut']) && isset($_POST['inputDateFin'])) {
                        if(!UserModel::estRespEC($_SESSION['current']['EC']) &&
                           !UserModel::estIntGrpEC($_SESSION['current']['groupeEC']) &&
                           !RespDiplomeModel::estResponsableDiplomeEC(UserModel::getId(), $_SESSION['current']['EC'])) {
                            $json['code'] = -1;
                            $json['erreur'] = "Vous n'avez pas les droits suffisants. ";
                            if(!UserModel::estIntEC($_SESSION['current']['EC']))
                                $json['erreur'] .= "Vous n'intervenez pas dans l'EC.";
                            else
                                $json['erreur'] .= "Vous n'intervenez pas dans ce groupe.";
                        }
                        else {
                            $inputDateDebut = DateTools::date2Timestamp($_POST['inputDateDebut'], true);
                            $inputDateFin = DateTools::date2Timestamp($_POST['inputDateFin'], true);
                            
                            if(($inputDateDebut === false) || ($inputDateFin === false)) {
                                $json['code'] = -1;
                                $json['erreur'] = "";
                                if($inputDateDebut === false) $json['erreur'] .= "La date de début est invalide. ";
                                if($inputDateFin === false) $json['erreur'] .= "La date de fin est invalide. ";
                            }
                            else {
                                // Vérifications
                                // #TODO# : autres vérifications à faire => durée min. (moins de 1h ?) ; même journée ?
                                if($inputDateFin < $inputDateDebut) {
                                    $tmp = $inputDateFin;
                                    $inputDateFin = $inputDateDebut;
                                    $inputDateDebut = $tmp;
                                }
                                
                                $jour = date('j', $inputDateDebut);
                                $mois = date('n', $inputDateDebut);
                                $annee = date('Y', $inputDateDebut);
                                $heure = date('G', $inputDateDebut);
                                $minute = date('i', $inputDateDebut);
                                $debut = mktime($heure, $minute, 0, $mois, $jour, $annee);
                                $jour = date('j', $inputDateFin);
                                $mois = date('n', $inputDateFin);
                                $annee = date('Y', $inputDateFin);
                                $heure = date('G', $inputDateFin);
                                $minute = date('i', $inputDateFin);
                                $fin = mktime($heure, $minute, 0, $mois, $jour, $annee);
                                
                                if(SeanceModel::update($_SESSION['current']['seance'], $debut, $fin)) {
                                    $json['code'] = 1;
                                    $json['seances'] = SeanceModel::getListeSeancesGroupe(intval($_SESSION['current']['groupeEC']));
                                    $json['seance'] = $_SESSION['current']['seance'];
                                }
                                else {
                                    $json['code'] = -1;
                                    $json['erreur'] = "Erreur lors de la mise-à-jour dans la base de données.";
                                }
                            }
                        }
                    }
                    else {
                        $json['code'] = -1;
                        $json['erreur'] = "Données insuffisantes.";
                    }
                }
                Controller::JSONpush($json);
                break;
            case 9: // Suppression d'une séance
                // #RIGHTS# : administrateur, responsable de l'EC ou du diplôme, intervenant du groupe
                $json = [];
                if(!UserModel::isConnected()) {
                    WebPage::setCurrentErrorMsg("Vous n'êtes pas ou plus connecté. Veuillez vous connecter.");
                    $json['code'] = -2;
                }
                else {
                    if(isset($_SESSION['current']['seance']) && ($_SESSION['current']['seance'] != -1) &&
                       isset($_SESSION['current']['groupeEC']) && ($_SESSION['current']['groupeEC'] != -1) &&
                       isset($_SESSION['current']['EC']) && ($_SESSION['current']['EC'] != -1)) {
                        if(!UserModel::estRespEC($_SESSION['current']['EC']) &&
                           !UserModel::estIntGrpEC($_SESSION['current']['groupeEC']) &&
                           !RespDiplomeModel::estResponsableDiplomeEC(UserModel::getId(), $_SESSION['current']['EC'])) {
                            $json['code'] = -1;
                            $json['erreur'] = "Vous n'avez pas les droits suffisants. ";
                            if(!UserModel::estIntEC($_SESSION['current']['EC']))
                                $json['erreur'] .= "Vous n'intervenez pas dans l'EC.";
                            else
                                $json['erreur'] .= "Vous n'intervenez pas dans ce groupe.";
                        }
                        else {
                            $json['seances'] = SeanceModel::getListeSeancesGroupe(intval($_SESSION['current']['groupeEC']));
                            if(SeanceModel::delete($_SESSION['current']['seance'])) {
                                // Recherche de la séance dans la liste
                                $i = 0;
                                while($json['seances'][$i]['id'] != $_SESSION['current']['seance'])
                                    $i++;
                                
                                // Suppression de la séance
                                array_splice($json['seances'], $i, 1);

                                // Mise à jour de la séance courante
                                if(count($json['seances']) == 0) {
                                    $_SESSION['current']['seance'] = -1;
                                }
                                else {
                                    if($i > count($json['seances']) - 1)
                                        $i = count($json['seances']) - 1;
                                    $_SESSION['current']['seance'] = $json['seances'][$i]['id'];
                                }
                                
                                $json['code'] = 1;
                                $json['seance'] = $_SESSION['current']['seance'];
                            }
                            else {
                                $json['code'] = -1;
                                $json['erreur'] = "Erreur lors de la mise-à-jour dans la base de données.";
                            }
                        }
                    }
                    else {
                        $json['code'] = -1;
                        $json['erreur'] = "Données insuffisantes.";
                    }
                }
                Controller::JSONpush($json);
                break;
            case 10: // Ajout d'une ou plusieurs séances
                // #RIGHTS# : administrateur, responsable de l'EC ou du diplôme, intervenant du groupe
                $json = [];
                if(!UserModel::isConnected()) {
                    WebPage::setCurrentErrorMsg("Vous n'êtes pas ou plus connecté. Veuillez vous connecter.");
                    $json['code'] = -2;
                }
                else {
                    if(isset($_SESSION['current']['EC']) && isset($_SESSION['current']['groupeEC']) &&
                       ($_SESSION['current']['EC'] != -1) && ($_SESSION['current']['groupeEC'] != -1) &&
                       isset($_POST['inputDateDebut']) && isset($_POST['inputDateFin']) && isset($_POST['inputOcc'])) {
                        if(!UserModel::estRespEC($_SESSION['current']['EC']) &&
                           !UserModel::estIntGrpEC($_SESSION['current']['groupeEC']) &&
                           !RespDiplomeModel::estResponsableDiplomeEC(UserModel::getId(), $_SESSION['current']['EC'])) {
                            $json['code'] = -1;
                            $json['erreur'] = "Vous n'avez pas les droits suffisants. ";
                            if(!UserModel::estIntEC($_SESSION['current']['EC']))
                                $json['erreur'] .= "Vous n'intervenez pas dans l'EC.";
                            else
                                $json['erreur'] .= "Vous n'intervenez pas dans ce groupe.";
                        }
                        else {
                            $nbOcc = intval($_POST['inputOcc']);
                            if($nbOcc <= 0) $nbOcc = 1;
                            if($nbOcc > 20) $nbOcc = 20;
                            
                            $inputDateDebut = DateTools::date2Timestamp($_POST['inputDateDebut'], true);
                            $inputDateFin = DateTools::date2Timestamp($_POST['inputDateFin'], true);
                
                            if(($inputDateDebut === false) || ($inputDateFin === false)) {
                                $json['code'] = -1;
                                $json['erreur'] = "";
                                if($inputDateDebut === false) $json['erreur'] .= "La date de début est invalide ({$_POST['inputDateDebut']}). ";
                                if($inputDateFin === false) $json['erreur'] .= "La date de fin est invalide. ";
                            }
                            else {
                                if($inputDateFin < $inputDateDebut) {
                                    $tmp = $inputDateFin;
                                    $inputDateFin = $inputDateDebut;
                                    $inputDateDebut = $tmp;
                                }
                                
                                $texte = "";
                                $jourD = date('j', $inputDateDebut);
                                $moisD = date('n', $inputDateDebut);
                                $anneeD = date('Y', $inputDateDebut);
                                $heureD = date('G', $inputDateDebut);
                                $minuteD = date('i', $inputDateDebut);
                                $jourF = date('j', $inputDateFin);
                                $moisF = date('n', $inputDateFin);
                                $anneeF = date('Y', $inputDateFin);
                                $heureF = date('G', $inputDateFin);
                                $minuteF = date('i', $inputDateFin);
                    
                                if(!isset($_SESSION['current']['seance']) || ($_SESSION['current']['seance'] <= 0))
                                    $_SESSION['current']['seance'] = -1;
                                $json['seance'] = $_SESSION['current']['seance'];
                                
                                // Création de toutes les séances
                                for($i = 0; $i < $nbOcc; $i++) {
                                    $debut = mktime($heureD, $minuteD, 0, $moisD, $jourD + $i * 7, $anneeD);
                                    $fin = mktime($heureF, $minuteF, 0, $moisF, $jourF + $i * 7, $anneeF);
                                    $texte .= " ".DateTools::timestamp2Date($debut, true)."-".DateTools::timestamp2Date($fin, true);
                                    $seance = new Seance(-1, $_SESSION['current']['groupeEC'], $debut, $fin);
                                    SeanceModel::create($seance);
                                    if($nbOcc == 1) {
                                        // Si une seule séance est créée, elle est sélectionnée par défaut
                                        $json['seance'] = $seance->getId();
                                        $_SESSION['current']['seance'] = $json['seance'];
                                    }
                                }
                                $json['code'] = 1;
                                $json['msg'] = "$nbOcc séance(s) ajoutée(s) :$texte.";
                                $json['seances'] = SeanceModel::getListeSeancesGroupe(intval($_SESSION['current']['groupeEC']));
                            }
                        }
                    }
                    else {
                        $json['code'] = -1;
                        $json['erreur'] = "Données insuffisantes.";
                    }
                }
                Controller::JSONpush($json);
                break;
            case 11: // Ajout d'un étudiant à la séance courante
                // #RIGHTS# : administrateur, responsable de l'EC ou du diplôme, intervenant du groupe
                $json = [];
                if(!UserModel::isConnected()) {
                    WebPage::setCurrentErrorMsg("Vous n'êtes pas ou plus connecté. Veuillez vous connecter.");
                    $json['code'] = -2;
                }
                else {
                    if(isset($_SESSION['current']['EC']) && ($_SESSION['current']['EC'] != -1) &&
                       isset($_SESSION['current']['groupeEC']) && ($_SESSION['current']['groupeEC'] != -1) &&
                       isset($_SESSION['current']['seance']) && ($_SESSION['current']['seance'] != -1)) {
                        $json['code'] = -1;
                        $json['erreur'] = "Non implémenté.";
                    }
                    else {
                        $json['code'] = -1;
                        $json['erreur'] = "Il manque des données.";
                    }
                }
                Controller::JSONpush($json);
                break;
            case 12: // Retourne le bilan pour un EC
                // #RIGHTS# : administrateur, responsable de l'EC ou du diplôme, intervenant du groupe
                if(!UserModel::isConnected()) {
                    WebPage::setCurrentErrorMsg("Vous n'êtes pas ou plus connecté. Veuillez vous connecter.");
                    return Controller::push("", "", [], "");
                }
            
                if(UserModel::estEnseignant() &&
                   isset($_SESSION['current']['EC']) && ($_SESSION['current']['EC'] != -1)) {
                    $data['bilan'] = InscriptionSeanceModel::getBilan($_SESSION['current']['EC']);
                }
                else {
                    $data['bilan'] = [];
                }
                return Controller::push("", "./view/presentiel/saisie_bilan.php", $data, "");                
                break;
        }
        
        // Mode incorrect
        exit();
    }

    /**
     * Récupère un justificatif depuis un formulaire.
     * @return un justificatif
     */
    public static function __getFromForm() : Justificatif {
        $inputId = -1;
        $inputDateDebut = 0;
        $inputDateFin = 0;
        $inputMotif = "";
        $inputRemarque = "";
        
        if(isset($_POST['inputId']) && ($_POST['inputId'] != 0)) $inputId = intval($_POST['inputId']);
        if(isset($_POST['inputType'])) {
            if(intval($_POST['inputType']) == 1) {
                // Journée(s) entières
                if(isset($_POST['inputDateDebut']) && ($_POST['inputDateDebut'] != ""))
                    $inputDateDebut = DateTools::date2Timestamp($_POST['inputDateDebut'], false);
                if(isset($_POST['inputDateFin']) && ($_POST['inputDateFin'] != ""))
                    $inputDateFin = DateTools::date2Timestamp($_POST['inputDateFin'], false);
                
                // Si la date de fin n'est pas spécifiée, on copie la date de début
                if(($inputDateDebut !== false) && ($inputDateDebut != 0) &&
                   ($inputDateFin !== false) && ($inputDateFin == 0))
                    $inputDateFin = $inputDateDebut;
            }
            elseif(intval($_POST['inputType']) == 2) {
                // Date avec heure
                if(isset($_POST['inputDateDebutPlage']) && ($_POST['inputDateDebutPlage'] != ""))
                    $inputDateDebut = DateTools::date2Timestamp($_POST['inputDateDebutPlage'], true);
                if(isset($_POST['inputDateFinPlage']) && ($_POST['inputDateFinPlage']))
                    $inputDateFin = DateTools::date2Timestamp($_POST['inputDateFinPlage'], true);
            }
            
            // Si les dates sont invalides
            if($inputDateDebut === false) $inputDateDebut = -1;
            if($inputDateFin === false) $inputDateFin = -1;
            
            // Debut inférieur à fin
            if(($inputDateDebut > 0) && ($inputDateFin > 0))
                if($inputDateDebut > $inputDateFin) {
                    $tmp = $inputDateDebut;
                    $inputDateDebut = $inputDateFin;
                    $inputDateFin = $tmp;
                }
        }
        if(isset($_POST['inputMotif'])) $inputMotif = $_POST['inputMotif'];
        if(isset($_POST['inputRemarque'])) $inputRemarque = $_POST['inputRemarque'];
        
        return new Justificatif(-1, $inputId, $inputDateDebut, $inputDateFin, $inputMotif, $inputRemarque, UserModel::getId(), time());
    }
    
    /**
     * Ajout d'un justificatif d'absence d'un étudiant.
     * #RIGHTS# : administrateur, responsable de diplôme, tuteur
     */
    public static function ajouter() {
        // Accès si l'utilisateur est connecté
        if(!UserModel::isConnected())
            Controller::goTo("", "", "Vous devez être connecté pour accéder à cette section.");
        
        // Seuls les enseignants ont accès à l'ajout d'un justificatif
        if(!UserModel::estEnseignant())
            Controller::goTo("presentiel/justificatifs.php", "", "Vous n'avez pas les droits suffisants.");
        
        // L'utilisateur a annulé ou il manque des données
        if(isset($_POST['btnAnnuler']) ||
           !isset($_SESSION['current']['diplome']))
            Controller::goTo("presentiel/justificatifs.php", "Aucun justificatif n'a été ajouté.");
        
        // Le formulaire a été validé
        $data = [];
        if(isset($_POST['btnAjouter'])) {
            $data['justificatif'] = self::__getFromForm();
            $erreur = "";
            
            // Vérification des droits
            if($data['justificatif']->getEtudiant() != -1) {
                if(!UserModel::estAdmin() &&
                   !InscriptionTuteurModel::estTuteur(UserModel::getId(), $data['justificatif']->getEtudiant()) &&
                   !UserModel::estRespDiplome($_SESSION['current']['diplome'])) {
                   $erreur .= "Vous n'avez pas le droit de spécifier un justificatif pour cet étudiant. ".
                              "Vous devez être responsable du diplôme ou tuteur de l'étudiant.";
                }
            }
            
            // Vérification des données
            if($data['justificatif']->getEtudiant() == -1) $erreur .= "Aucun étudiant sélectionné. ";
            if($data['justificatif']->getDateDebut() == 0) $erreur .= "Vous n'avez pas spécifié la date de début. ";
            if($data['justificatif']->getDateDebut() == -1) {
                $erreur .= "Saisie : ".$_POST['inputDateDebut'];
                $erreur .= "Le format de la date de début est incorrect. ";
            }
            if($data['justificatif']->getDateFin() == 0) $erreur .= "Vous n'avez pas spécifié la date de fin. ";
            if($data['justificatif']->getDateFin() == -1) $erreur .= "Le format de la date de fin est incorrect. ";
            if($data['justificatif']->getMotif() == "") $erreur .= "Aucun motif spécifié. ";
            
            if($erreur == "") {
                if(JustificatifModel::create($data['justificatif'])) {
                    Controller::goTo("presentiel/justificatifs.php", "Le justificatif a été ajouté dans la base.");
                }
                else
                    WebPage::setCurrentErrorMsg("Le justificatif n'a pas été ajouté dans la base de données.");
            }        
            else {
                if($data['justificatif']->getEtudiant() != -1)
                    $data['etudiant'] = EtudiantModel::read($data['justificatif']->getEtudiant());
                WebPage::setCurrentErrorMsg($erreur);
            }
        }
        
        return Controller::push("Ajouter un justificatif", "./view/presentiel/ajouter.php", $data);
    }
    
    /**
     * Modifie un justificatif.
     * #RIGHTS# : administrateur, responsable du diplôme, éditeur du justificatif (le tuteur + responsable)
     */
    public static function modifier() : void {
        // Accès si l'utilisateur est connecté
        if(!UserModel::isConnected())
            Controller::goTo("", "", "Vous devez être connecté pour accéder à cette section.");
        
        // Seuls les enseignants ont accès à la modification d'un justificatif
        if(!UserModel::estEnseignant())
            Controller::goTo("", "", "Vous n'avez pas les droits pour réaliser cette action.");
        
        // L'utilisateur a annulé
        if(isset($_POST['btnAnnuler']))
            Controller::goTo("presentiel/justificatifs.php", "Le justificatif n'a pas été modifié.");
        
        // Le formulaire a été validé
        $data = [];
        if(isset($_POST['btnModifier'])) {
            if(!isset($_SESSION['current']['justificatif']))
                Controller::goTo("presentiel/justificatifs.php", "", "Erreur lors de la récupération du justificatif.");
            
            $data['justificatif'] = self::__getFromForm();
            $data['justificatif']->setId($_SESSION['current']['justificatif']);
            
            $erreur = "";
            
            // Vérification des droits
            if($data['justificatif']->getEtudiant() != -1) {
                if(!UserModel::estAdmin() &&
                   !InscriptionTuteurModel::estTuteur(UserModel::getId(), $data['justificatif']->getEtudiant()) &&
                   !UserModel::estRespDiplome($_SESSION['current']['diplome'])) {
                   $erreur .= "Vous n'avez pas le droit de spécifier un justificatif pour cet étudiant. ".
                              "Vous devez être responsable du diplôme ou tuteur de l'étudiant.";
                }
            }
            
            // Vérification des données
            if($data['justificatif']->getEtudiant() == -1) $erreur .= "Aucun étudiant sélectionné. ";
            if($data['justificatif']->getDateDebut() == 0) $erreur .= "Vous n'avez pas spécifié la date de début. ";
            if($data['justificatif']->getDateDebut() == -1) {
                $erreur .= "Saisie : ".$_POST['inputDateDebut'];
                $erreur .= "Le format de la date de début est incorrect. ";
            }
            if($data['justificatif']->getDateFin() == 0) $erreur .= "Vous n'avez pas spécifié la date de fin. ";
            if($data['justificatif']->getDateFin() == -1) $erreur .= "Le format de la date de fin est incorrect. ";
            if($data['justificatif']->getMotif() == "") $erreur .= "Aucun motif spécifié. ";
            
            // Mise-à-jour du justificatif
            if($erreur == "") {
                if(JustificatifModel::update($data['justificatif']))
                    Controller::goTo("presentiel/justificatifs.php", "Le justificatif a été modifié.");
                else
                    WebPage::setCurrentMsg("Le justificatif n'a pas été modifié dans la base de données.");
            }        
            else
                WebPage::setCurrentErrorMsg($erreur);
        }      
        else {
            if(!isset($_SESSION['current']['justificatif']))
                Controller::goTo("presentiel/justificatifs.php", "Le justificatif n'a pas été spécifié.");
            
            $data['justificatif'] = JustificatifModel::read(intval($_SESSION['current']['justificatif']));
            if($data['justificatif'] === null)
                Controller::goTo("presentiel/justificatifs.php", "", "Erreur lors de la récupération du justificatif.");
            
            if($data['justificatif']->getEtudiant() != -1) {
                $data['etudiant'] = EtudiantModel::read($data['justificatif']->getEtudiant());
                if($data['etudiant'] == null)
                    Controller::goTo("presentiel/justificatifs.php", "", "Erreur lors de la récupération de l'étudiant associé.");

                if(!UserModel::estAdmin() &&
                   (UserModel::getId() != $data['justificatif']->getEditeur()) &&
                   !UserModel::estRespDiplome($_SESSION['current']['diplome'])) {
                   Controller::goTo("presentiel/justificatifs.php", "", 
                                    "Vous ne pouvez pas éditer le justificatif de quelqu'un d'autre, sauf si vous êtes responsable du diplôme.");
                }
            }
        }
        
        Controller::push("Modifier un justificatif", "./view/presentiel/modifier.php", $data);
    }
    
    /**
     * Supprime un justificatif d'un étudiant.
     * #RIGHTS# : administrateur, responsable du diplôme, éditeur du justificatif (le tuteur + responsable)
     */
    public static function supprimer() {
        // Accès si l'utilisateur est connecté
        if(!UserModel::isConnected())
            Controller::goTo("", "", "Vous devez être connecté pour accéder à cette section.");
        
        // Seuls les enseignants ont accès à la suppression d'un justificatif
        if(!UserModel::estEnseignant())
            Controller::goTo("", "", "Vous n'avez pas les droits pour réaliser cette action.");
        
        // L'utilisateur a annulé ou il manque des données
        if(isset($_POST['btnAnnuler']) ||
           !isset($_SESSION['current']['justificatif']) || 
           !isset($_SESSION['current']['diplome']))
            Controller::goTo("presentiel/justificatifs.php", "Le justificatif n'a pas été supprimé.");
        
        // Récupération du justificatif
        $data = [];
        $data['justificatif'] = JustificatifModel::read(intval($_SESSION['current']['justificatif']));
        if($data['justificatif'] === null)
            Controller::goTo("presentiel/justificatifs.php", "", "Erreur lors de la récupération du justificatif.");
        
        // Vérification des droits
        // #RIGHTS# : administrateur, responsable du diplôme, éditeur du justificatif (le tuteur + responsable)
        if(!UserModel::estAdmin() &&
           (UserModel::getId() != $data['justificatif']->getEditeur()) &&
           !UserModel::estRespDiplome($_SESSION['current']['diplome'])) {
            Controller::goTo("presentiel/justificatifs.php", "", 
                             "Vous ne pouvez pas supprimer le justificatif de quelqu'un d'autre, sauf si vous êtes responsable du diplôme.");
        }
           
        // Le formulaire a été validé
        if(isset($_POST['btnSupprimer'])) {
            // Suppression
            if(JustificatifModel::delete($_SESSION['current']['justificatif']))
                Controller::goTo("presentiel/justificatifs.php", "Le justificatif a été supprimé.");
            else
                WebPage::setCurrentErrorMsg("Le justificatif n'a pas été supprimé de la base de données.");
        }

        // Récupération de l'étudiant associé au justificatif
        if($data['justificatif']->getEtudiant() != -1) {
            $data['etudiant'] = EtudiantModel::read($data['justificatif']->getEtudiant());
            if($data['etudiant'] == null)
                Controller::goTo("presentiel/justificatifs.php", "", "Erreur lors de la récupération de l'étudiant associé.");
        }

        return Controller::push("Supprimer un justificatif", "./view/presentiel/supprimer.php", $data);
    }
    
    /**
     * Exporte les justificatifs des étudiants.
     * #RIGHTS# : administrateur, enseignant (#TODO# : restriction)
     */
    public static function exporter() : void {
        // Accès si l'utilisateur est connecté
        if(!UserModel::isConnected())
            Controller::goTo("", "", "Vous devez être connecté pour accéder à cette section.");
        
        // Seuls les enseignants ont accès à l'exportation de la liste des justificatifs
        if(!UserModel::estEnseignant())
            Controller::goTo("", "", "Vous n'avez pas les droits pour réaliser cette action.");
        
        // Le diplôme doit être spécifié
        if(!isset($_SESSION['current']['diplome']) || ($_SESSION['current']['diplome'] == -1))
            Controller::goTo("presentiel/justificatifs.php", "", "L'identifiant du diplôme n'a pas été spécifié.");
        
        // Récupération de la liste
        if(($justificatifs = JustificatifModel::getList($_SESSION['current']['diplome'])) === null)
            Controller::goTo("presentiel/justificatifs.php", "", "Erreur lors de la récupération des justificatifs.");
        
        // Convertion des dates
        foreach($justificatifs as &$justificatif) {
            $heure = (date('G', $justificatif['debut']) != 0);
            if(!$heure)
                $justificatif['fin'] = strtotime('-1 day', $justificatif['fin']);

            $justificatif['debut'] = DateTools::timestamp2Date($justificatif['debut'], $heure);
            $justificatif['fin'] = DateTools::timestamp2Date($justificatif['fin'], $heure);
        }
                
        Controller::CSVpush("justificatifs_".DateTools::timestamp2Date(time(), false), $justificatifs, 
                            [ "numero" => "numero", "nom complet" => "nomcomplet",
                              "email" => "email", "debut" => "debut", "fin" => "fin", "motif" => "motif", "remarque" => "remarque" ] );
    }
    
    /**
     * Liste des justificatifs d'absence
     * #RIGHTS# : administrateur, enseignant (#TODO# : restriction)
     */
    public static function justificatifs() {
        // Accès si l'utilisateur est connecté
        if(!UserModel::isConnected())
            Controller::goTo("", "", "Vous devez être connecté pour accéder à cette section.");
        
        // Seuls les enseignants ont accès à la liste des justificatifs
        if(!UserModel::estEnseignant())
            Controller::goTo("", "", "Vous n'avez pas les droits pour réaliser cette action.");
        
        // Chargement de la liste des diplômes
        if(($data['diplomes'] = DiplomeModel::getList()) === null)
            Controller::goTo("", "", "Une erreur est survenue lors de la récupération de la liste des diplômes.");
                
        // On fixe le diplôme courant
        if(isset($_SESSION['current']['diplome'])) {
            // #TODO# : a-t-il le droit d'accéder à ce diplôme ?
            $data['diplome'] = $_SESSION['current']['diplome'];
        }
        else {
            if(count($data['diplomes']) > 0) {
                $data['diplome'] = $data['diplomes'][0]['id'];
                $_SESSION['current']['diplome'] = $data['diplome'];
            }
        }
        
        return Controller::push("Justificatifs d'absence", "./view/presentiel/justificatifs.php", $data);
    }   

    /**
     * Gestion du présentiel d'un EC.
     * #RIGHTS# : administrateur, responsable d'un diplôme, responsable EC, intervenant EC
     */
    public static function saisie() {
        // Accès si l'utilisateur est connecté
        if(!UserModel::isConnected())
            Controller::goTo("", "", "Vous devez être connecté pour accéder à cette section.");

        // Seuls les enseignants ont accès à la saisie/affichage du présentiel
        if(!UserModel::estEnseignant())
            Controller::goTo("", "", "Vous n'avez pas les droits pour réaliser cette action.");
        
        // L'EC doit être spécifiée
        if(!isset($_SESSION['current']['EC']) || ($_SESSION['current']['EC'] == -1))
            Controller::goTo("ecs/liste.php", "", "L'EC n'a pas été précisé.");
        
        // Pour accéder (mais pas forcément pour modifier), l'utilisateur doit être intervenant de l'EC ou
        // responsable du diplôme contenant l'EC
        if(!UserModel::estIntEC($_SESSION['current']['EC']) &&
           !RespDiplomeModel::estResponsableDiplomeEC(UserModel::getId(), $_SESSION['current']['EC']))
            Controller::goTo("ecs/liste.php", "", "Vous n'avez pas les droits suffisants.");
        
        // Chargement des groupes et des ECs
        if((($data['groupes'] = GroupeECModel::getList($_SESSION['current']['EC'])) === null) ||
           (($data['EC'] = ECModel::read($_SESSION['current']['EC'])) === null))
            Controller::goTo("presentiel/saisie.php", "", "Erreur lors de la récupération des données.");
        
        // Récupération du groupe d'EC courant ou sélection du premier groupe d'EC
        if(isset($_SESSION['current']['groupeEC']))
            $data['groupe'] = $_SESSION['current']['groupeEC'];
        else
            $data['groupe'] = $data['groupes'][0]['id'];
        $_SESSION['current']['groupeEC'] = $data['groupe'];
        
        // Droits de saisie
        $data['droits'] = UserModel::estIntGrpEC($_SESSION['current']['groupeEC']) ||
                          UserModel::estRespEC($_SESSION['current']['EC']) ||
                          RespDiplomeModel::estResponsableDiplomeEC(UserModel::getId(), $_SESSION['current']['EC']);
        
        // Récupération des séances du groupe
        $data['seances'] = SeanceModel::getListeSeancesGroupe($data['groupe']);
        
        // Récupération de la séance courante
        if(isset($_SESSION['current']['seance'])) {
            // La séances courante fait bien partie des séances ?
            $i = 0;
            while(($i < count($data['seances'])) && ($data['seances'][$i]['id'] != $_SESSION['current']['seance']))
                $i++;
            
            if($i < count($data['seances']))
                $data['seance'] = $_SESSION['current']['seance'];
            else {
                $_SESSION['current']['seance'] = -1;
                $data['seance'] = -1;
            }
        }
        
        return Controller::push("Saisie du présentiel", "./view/presentiel/saisie.php", $data);
    }
    
    /**
     * Gestion du rattrapage.
     * #RIGHTS# : administrateur, responsable du diplôme, intervenant
     */
    public static function rattrapage() : void {
        if(!UserModel::isConnected())
            Controller::goTo("", "", "Vous n'êtes pas ou plus connecté. Veuillez vous connecter.");
        
        // L'EC doit être spécifiée
        if(!isset($_SESSION['current']['EC']) || ($_SESSION['current']['EC'] == -1) ||
           !isset($_SESSION['current']['groupeEC']) || ($_SESSION['current']['groupeEC'] == -1))
            Controller::goTo("presentiel/saisie.php", "", "Le groupe n'a pas été spécifié.");

        if(($groupe = GroupeECModel::read($_SESSION['current']['groupeEC'])) === null)
            Controller::goTo("presentiel/saisie.php", "", "Erreur lors de la récupération des données.");
        
        // L'utilisateur doit être responsable de l'EC ou du groupe d'EC
        if(!UserModel::estRespEC($_SESSION['current']['EC']) &&
           !UserModel::estIntGrpEC($_SESSION['current']['groupeEC']) &&
           !RespDiplomeModel::estResponsableDiplomeEC(UserModel::getId(), $_SESSION['current']['EC'])) {
            Controller::goTo("presentiel/saisie.php", "", "Vous n'avez pas les droits pour réaliser cette action.");
        }
        
        // Liste des étudiants
        $data['etudiants'] = InscriptionGroupeECModel::getListeNonInscritsGrp($_SESSION['current']['EC'],
                                                                              $_SESSION['current']['groupeEC'],
                                                                              $groupe->getType());
        
        Controller::push("Saisie du rattrapage", "./view/presentiel/rattrapage.php", $data);
    }
    
    /**
     * Exporte le présentiel des étudiants.
     * #RIGHTS# : administrateur, responsable du diplôme, intervenant
     */
    public static function exporterpresentiel() : void {
        // Accès si l'utilisateur est connecté
        if(!UserModel::isConnected())
            Controller::goTo("", "", "Vous devez être connecté pour accéder à cette section.");

        // Seuls les enseignants ont accès à l'exportation du présentiel
        if(!UserModel::estEnseignant())
            Controller::goTo("", "", "Vous n'avez pas les droits pour réaliser cette action.");

        // L'EC doit être spécifiée
        if(!isset($_SESSION['current']['EC']) || ($_SESSION['current']['EC'] == -1))
            Controller::goTo("presentiel/saisie.php", "", "L'EC n'a pas été spécifiée.");
        $EC = ECModel::read($_SESSION['current']['EC']);
        
        // Le groupe d'EC doit être spécifié
        if(!isset($_SESSION['current']['groupeEC']) || ($_SESSION['current']['groupeEC'] == -1))
            Controller::goTo("presentiel/saisie.php", "", "Le groupe n'a pas été spécifié.");
        $groupe = GroupeECModel::read($_SESSION['current']['groupeEC']);
        
        // Vérification du chargement
        if(($EC === null) || ($groupe === null))
            Controller::goTo("presentiel/saisie.php", "", "Erreur lors du chargement des données.");
        
        // L'utilisateur doit être responsable de l'EC ou du groupe d'EC
        if(!UserModel::estRespEC($_SESSION['current']['EC']) &&
           !UserModel::estIntGrpEC($_SESSION['current']['groupeEC']) &&
           !RespDiplomeModel::estResponsableDiplomeEC(UserModel::getId(), $_SESSION['current']['EC'])) {
            Controller::goTo("presentiel/saisie.php", "", "Vous n'avez pas les droits pour réaliser cette action.");
        }
        
        // La séance doit être spécifiée (identifiant ou -1 pour toutes les séances)
        if(!isset($_SESSION['current']['seance']))
            Controller::goTo("presentiel/saisie.php", "", "La séance n'a pas été spécifiée.");
        
        // Récupération du présentiel de la séance ou des séances du groupe d'EC
        $data['presences'] = InscriptionSeanceModel::getListePresencesGroupe($_SESSION['current']['groupeEC'],
                                                                             $_SESSION['current']['seance']);                   
        
        if($_SESSION['current']['seance'] != -1) {
            // Séance sélectionnée
            $tmp = SeanceModel::read($_SESSION['current']['seance']);
            $data['seances'][] = ['id' => $tmp->getId(), 
                                  'debut' => DateTools::timestamp2Date($tmp->getDebut(), true), 
                                  'fin' => DateTools::timestamp2Date($tmp->getFin(), true) ];
        }
        else {
            // Aucune séance sélectionnée : présentiel de toutes les séances
            $data['seances'] = SeanceModel::getListeSeancesGroupe($_SESSION['current']['groupeEC']);
        }
        
        // Récupération du présentiel associé à la ou aux séances
        $data['justificatifs'] = JustificatifModel::getListSeances($data['seances']);
        
        // Transformation du présentiel en un présentiel lisible par l'utilisateur
        // => conversion des codes en ABI, ABJ, NS, etc.
        foreach($data['presences'] as &$presence) {
            foreach($data['seances'] as $seance) {
                if(isset($data['justificatifs'][$seance['id']]) &&
                   array_key_exists($presence['id'], $data['justificatifs'][$seance['id']]) !== false) {
                    $presence[$seance['id']] = "ABJ";
                }
                else {
                    if(isset($presence['pres'][$seance['id']])) {
                        switch($presence['pres'][$seance['id']]) {
                            case InscriptionSeanceModel::TYPE_NON_SPECIFIE:
                                $presence[$seance['id']] = "NS";
                                break;
                            case InscriptionSeanceModel::TYPE_PRESENT:
                                $presence[$seance['id']] = "P";
                                break;
                            case InscriptionSeanceModel::TYPE_ABSENT:
                                $presence[$seance['id']] = "ABI";
                                break;
                        }
                    }
                    else
                        $presence[$seance['id']] = "NS";
                }
            }
        }
        
        // Création de l'en-tête des colonnes
        $entete = [ "numero" => "numero", "nom" => "nom", "prenom" => "prenom" ];
        foreach($data['seances'] as $seance) {
            $entete[$seance['debut']."-".$seance['fin']] = $seance['id'];
        }

        Controller::CSVpush("presentiel_".$EC->getCode()."_".$groupe."_".DateTools::timestamp2Date(time(), false), $data['presences'], $entete);
    }
    
    /**
     * Exporte le bilan du présentiel des étudiants.
     * #RIGHTS# : administrateur, responsable du diplôme, intervenant
     */
    public static function exporterbilan() : void {
        // Accès si l'utilisateur est connecté
        if(!UserModel::isConnected())
            Controller::goTo("", "", "Vous devez être connecté pour accéder à cette section.");

        // Seuls les enseignants ont accès à l'exportation du présentiel
        if(!UserModel::estEnseignant())
            Controller::goTo("", "", "Vous n'avez pas les droits pour réaliser cette action.");

        // L'EC doit être spécifiée
        if(!isset($_SESSION['current']['EC']) || ($_SESSION['current']['EC'] == -1))
            Controller::goTo("presentiel/saisie.php", "", "L'EC n'a pas été spécifiée.");
        if(($EC = ECModel::read($_SESSION['current']['EC'])) === null)
            Controller::goTo("presentiel/saisie.php", "", "Erreur lors du chargement des données.");

        $data['bilan'] = InscriptionSeanceModel::getBilan($_SESSION['current']['EC']);
        
        foreach($data['bilan'] as &$etudiant) {
            foreach([Groupe::GRP_CM => "CM", Groupe::GRP_TD => "TD", Groupe::GRP_TP => "TP"] as $type => $strGrp) {
                foreach([InscriptionSeanceModel::TYPE_PRESENT => "/P", 
                         InscriptionSeanceModel::TYPE_ABSENT => "/ABI", 
                         InscriptionSeanceModel::TYPE_JUSTIFIE => "/ABJ",
                         InscriptionSeanceModel::TYPE_RATTRAPAGE => "/R"] as $typeP => $strP) {
                    $etudiant[$strGrp.$strP] = $etudiant['pres'][$type][$typeP];
                }
            }
        }
        
        // Création de l'en-tête des colonnes
        $entete = [ "numero" => "numero", "nom" => "nom", "prenom" => "prenom", 
                    "CM/P" => "CM/P", "CM/ABI" => "CM/ABI", "CM/ABJ" => "CM/ABJ", "CM/R" => "CM/R",
                    "TD/P" => "TD/P", "TD/ABI" => "TD/ABI", "TD/ABJ" => "TD/ABJ", "TD/R" => "TD/R",
                    "TP/P" => "TP/P", "TP/ABI" => "TP/ABI", "TP/ABJ" => "TP/ABJ", "TP/R" => "TP/R" ];
        
        Controller::CSVpush("presentiel_".$EC->getCode()."_bilan_".DateTools::timestamp2Date(time(), false), $data['bilan'], $entete);
    }    
    
    /**
     * Importe le présentiel des étudiants.
     * #RIGHTS# : administrateur, responsable du diplôme, intervenant
     */
    public static function importerpresentiel() : void {
        // Accès si l'utilisateur est connecté
        if(!UserModel::isConnected())
            Controller::goTo("", "", "Vous devez être connecté pour accéder à cette section.");

        // Seuls les enseignants ont accès à l'importation du présentiel
        if(!UserModel::estEnseignant())
            Controller::goTo("", "", "Vous n'avez pas les droits pour réaliser cette action.");
        
        // L'EC doit être spécifiée
        if(!isset($_SESSION['current']['EC']) || ($_SESSION['current']['EC'] == -1))
            Controller::goTo("presentiel/saisie.php", "", "L'EC n'a pas été spécifiée.");
        $EC = ECModel::read($_SESSION['current']['EC']);
        
        // Le groupe d'EC doit être spécifié
        if(!isset($_SESSION['current']['groupeEC']) || ($_SESSION['current']['groupeEC'] == -1))
            Controller::goTo("presentiel/saisie.php", "", "Le groupe n'a pas été spécifié.");

        // L'utilisateur doit être responsable de l'EC ou du groupe d'EC
        if(!UserModel::estRespEC($_SESSION['current']['EC']) &&
           !UserModel::estIntGrpEC($_SESSION['current']['groupeEC']) &&
           !RespDiplomeModel::estResponsableDiplomeEC(UserModel::getId(), $_SESSION['current']['EC'])) {
            Controller::goTo("presentiel/saisie.php", "", "Vous n'avez pas les droits pour réaliser cette action.");
        }
        
        if(isset($_FILES) && isset($_FILES['inputFichier']) && ($_FILES['inputFichier']['error'] == UPLOAD_ERR_OK)) {
            $filename = $_FILES['inputFichier']['tmp_name'];

            if (($handle = fopen($filename, "r")) !== FALSE) {
                $erreur = "";
                
                // Lecture de l'en-tête (1 ligne)
                if(($entete = fgetcsv($handle, 2000, ";")) === FALSE)
                    Controller::goTo("presentiel/saisie.php", "", "Impossible de lire la ligne d'en-tête du fichier.");
                
                if(($entete[0] != 'numero') || ($entete[1] != 'nom') ||
                   ($entete[2] != 'prenom'))
                    Controller::goTo("presentiel/saisie.php", "", "Le format du fichier est incorrect.");
                
                // Récupération et/ou création de la liste des séances
                $seances = [];
                for($i = 3; $i < count($entete); $i++) {
                    $tmp = explode("-",$entete[$i]);
                    if(count($tmp) != 2) {
                        $erreur .= "La date de la séance ".($i - 2)." est incorrecte. ";
                    }
                    else {
                        $debut = DateTools::date2Timestamp($tmp[0], true);
                        $fin = DateTools::date2Timestamp($tmp[1], true);
                        
                        if(($debut === false) || ($fin === false)) {
                            if($debut === false) $erreur .= "La date de début ".($i - 2)." est incorrecte. ";
                            if($fin === false) $erreur .= "La date de fin ".($i - 2)." est incorrecte. ";
                            $seance = null;
                        }
                        else {
                            // Vérifications
                            // #TODO# : autres vérifications à faire => durée min. (moins de 1h ?) ; même journée ?
                            if($fin < $debut) {
                                $tmp = $fin;
                                $fin = $debut;
                                $debut = $tmp;
                            }
                            
                            $seance = SeanceModel::readFromDate($_SESSION['current']['groupeEC'], $debut, $fin);
                            if($seance == null) {
                                if(isset($_POST['inputCreer'])) {
                                    $seance = new Seance(-1, $_SESSION['current']['groupeEC'], $debut, $fin);
                                    SeanceModel::create($seance);
                                }
                                else
                                    $erreur .= "La séance ".($i - 2)." n'existe pas. ";
                            }
                        }
                    }
                    $seances[] = $seance;
                }
                
                // Lecture des étudiants avec leur présentiel
                $liste = [];
                $presentiel = [];
                for($i = 0; $i < count($seances); $i++)
                    $presentiel[$i] = [];
                
                while (($data = fgetcsv($handle, 1000, ";")) !== FALSE) {
                    $liste[] = ['numero' => $data[0], 'nom' => $data[1], 'prenom' => $data[2]];
                    for($i = 0; $i < count($seances); $i++) {
                        switch($data[$i + 3]) {
                            case "P":
                                $presentiel[$i][] = InscriptionSeanceModel::TYPE_PRESENT;
                                break;
                            case "ABI":
                            case "ABJ":
                                $presentiel[$i][] = InscriptionSeanceModel::TYPE_ABSENT;
                                break;
                            case "NS":
                            default:
                                $presentiel[$i][] = InscriptionSeanceModel::TYPE_NON_SPECIFIE;
                                break;
                        }
                    }
                }                
                fclose($handle);
                
                // Récupération des id des étudiants et vérification qu'ils sont inscrits à l'EC !!!
                InscriptionGroupeECModel::updateList($_SESSION['current']['groupeEC'], $liste);
                
                // Mise-à-jour du présentiel des étudiants
                for($i = 0; $i < count($seances); $i++) {
                    if($seances[$i] != null) {
                        InscriptionSeanceModel::modifierPresentielGroupe($seances[$i]->getId(), $liste, $presentiel[$i]);
                    }
                }
                
                // Fin
                Controller::goTo("presentiel/saisie.php", 
                                 "",
                                 $erreur);
            }
            else
                Controller::goTo("presentiel/saisie.php", "", "Erreur lors de l'ouverture du fichier.");
        }
        else {
            Controller::goTo("presentiel/saisie.php", "", "Vous n'avez pas sélectionné de fichier.");
        }
    }

} // class PresentielController