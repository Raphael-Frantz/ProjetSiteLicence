<?php
// *************************************************************************************************
// * Contrôleur pour les informations des étudiants
// *************************************************************************************************
class MesinfosController {

    /**
     * Informations générales
     */
    public static function general() {
        if(!UserModel::isConnected())
            Controller::goTo("", "", "Vous devez être connecté pour accéder à cette section.");
        
        if(UserModel::estEtudiant()) {
            $data['tuteurs'] = InscriptionTuteurModel::getTuteurs(UserModel::getId());
            $data['diplomes'] = InscriptionDiplomeModel::getListDiplomeEtudiant(UserModel::getId());
            
            return Controller::push("Mes infos", "./view/mesinfos/general_etudiant.php", $data);
        }
        elseif(UserModel::estEnseignant()) {
            $data['ECs'] = RespECModel::getList(UserModel::getId());
            $data['intECs'] = IntGroupeECModel::getListECs(UserModel::getId());
            
            return Controller::push("Mes infos", "./view/mesinfos/general_enseignant.php", $data);
        }
        else {
            Controller::goTo("", "", "Vous n'avez pas les droits suffisants.");
        }
    }

    /**
     * La liste des étudiants tutorés
     */
    public static function tutorat() {
        if(UserModel::estEtudiant())
            Controller::goTo("", "", "Vous n'avez pas les droits suffisants.");

        $data['etudiants'] = InscriptionTuteurModel::getListeEtudiantsInscrits(-1, UserModel::getId());        
            
        return Controller::push("Tutorat", "./view/mesinfos/tutorat.php", $data);
    }    
    
    /**
     * La liste des notes de l'étudiant connecté.
     */
    public static function notes() {
        if(!UserModel::estEtudiant())
            Controller::goTo("", "", "Vous n'avez pas les droits suffisants.");
            
        if(($data['notes'] = NoteModel::getListEtudiant(UserModel::getId())) === null)
            Controller::goTo("", "", "Une erreur est survenue lors de la récupération des données.");
            
        return Controller::push("Mes notes", "./view/mesinfos/notes.php", $data);
    }

    /**
     * Emploi du temps de l'étudiant connecté
     * @need GET week string numéro de semaine à afficher
     * @need GET group string intitulé du groupe à afficher
     */
    public static function emploidutemps() {
        if(!UserModel::estEtudiant())
            Controller::goTo("", "", "Vous n'avez pas les droits suffisants.");

        if((($data['groupes'] = InscriptionGroupeModel::getListeGroupes(UserModel::getId())) === null))
            Controller::goTo("", "", "Une erreur est survenue lors de la récupération des données.");

        $data['week'] = intval($_GET['week'] ?? date('W'));
        $data['seances'] = array();
        $data['current'] = '';
        $maxWeek = 53;

        if($data['week'] < 1)
            $data['week'] = 1;
        else if($data['week'] > $maxWeek)
            $data['week'] = $maxWeek;

        // Si l'étudiant a au moins un groupe
        if(!empty($data['groupes'])) {

            if(isset($_GET['group'])) {
                foreach($data['groupes'] as $groupe) {
                    if($groupe['intitule'] == $_GET['group']) {
                        $data['current'] = $groupe['intitule'];
                        $data['seances'] = CCSeanceModel::getSeancesOfTheWeek($data['week'], $groupe['idGroupe']);
                        break;
                    }
                }
            }

            if(empty($data['current'])) {

                // On cherche les groupes de TP en priorité, puis les groupes de TD, puis les groupes de CM
                $priorites = array(Groupe::GRP_TP, Groupe::GRP_TD, Groupe::GRP_CM);
                $trouve = false;

                foreach($priorites as $priorite) {
                    foreach ($data['groupes'] as $groupe) {
                        if ($groupe['type'] == $priorite) {
                            $data['current'] = $groupe['intitule'];
                            $data['seances'] = CCSeanceModel::getSeancesOfTheWeek($data['week'], $groupe['idGroupe']);
                            $trouve = true;
                            break;
                        }
                    }

                    if($trouve)
                        break;
                }

                if(!$trouve) {
                    $groupe = $data['groupes'][0];
                    $data['current'] = $groupe['intitule'];
                    $data['seances'] = CCSeanceModel::getSeancesOfTheWeek($data['week'], $groupe['idGroupe']);
                }
            }
        }

        return Controller::push("Mes groupes", "./view/mesinfos/emploidutemps.php", $data);
    }

    /**
     * La liste des groupe de l'étudiant connecté.
     */
    public static function groupes() {
        if(!UserModel::estEtudiant())
            Controller::goTo("", "", "Vous n'avez pas les droits suffisants.");
            
        if((($data['groupes'] = InscriptionGroupeModel::getListeGroupes(UserModel::getId())) === null) ||
           (($data['groupesEC'] = InscriptionGroupeECModel::getListeGroupes(UserModel::getId())) === null))
            Controller::goTo("", "", "Une erreur est survenue lors de la récupération des données.");
            
        return Controller::push("Mes groupes", "./view/mesinfos/groupes.php", $data);
    }
    
    /**
     * La liste des IP de l'étudiant.
     */
    public static function ip() {
        if(!UserModel::estEtudiant())
            Controller::goTo("", "", "Vous n'avez pas les droits suffisants.");
            
        if(($data['IPs'] = InscriptionECModel::getListeInscriptionsEtudiant(UserModel::getId())) === null)
            Controller::goTo("", "", "Une erreur est survenue lors de la récupération des données.");
            
        return Controller::push("Mes IP", "./view/mesinfos/ip.php", $data);
    }
    
    /**
     * La liste des présentiels de l'étudiant.
     */
    public static function presentiel() {
        if(!UserModel::estEtudiant())
            Controller::goTo("", "", "Vous n'avez pas les droits suffisants.");
        
        if((($data['presentiel'] = InscriptionSeanceModel::getListePresencesEtudiant(UserModel::getId())) === null) ||
           (($data['justificatifs'] = JustificatifModel::getListEtudiant(UserModel::getId())) === null))
            Controller::goTo("", "", "Une erreur est survenue lors de la récupération des données.");
        
        return Controller::push("Mon présentiel", "./view/mesinfos/presentiel.php", $data);
    }
    
    /**
     * Le changement de mot de passe.
     */
    public static function motdepasse() {
        if(!UserModel::isConnected())
            Controller::goTo("", "", "Vous devez être connecté pour accéder à cette section.");
        
        if(isset($_POST['btnValider'])) {
            if(($_POST['inputPassword'] == "") ||
               ($_POST['inputOldPassword'] == "") ||
               ($_POST['inputConfPassword'] == ""))
               WebPage::setCurrentErrorMsg("Vous devez saisir toutes les informations.");
            else {    
                if(md5($_POST['inputOldPassword']) != UserModel::getCurrentUser()->getPassword()) {
                    WebPage::setCurrentErrorMsg("Le mot de passe saisi ne correspond pas au mot de passe actuel.");
                }
                else {            
                    if($_POST['inputPassword'] != $_POST['inputConfPassword'])
                        WebPage::setCurrentErrorMsg("Le mot de passe et la confirmation doivent être identiques.");
                    else {
                       if(!preg_match('#^(?=.*[a-zA-Z])(?=.*[0-9])(?=.*\W).{6,40}$#', $_POST['inputPassword']))
                           WebPage::setCurrentErrorMsg("Le mot de passe doit contenir entre 6 et 40 caractères avec des lettres, des chiffres et au moins un caractère spécial.");
                       else {
                           if(UserModel::updatePasswordId(UserModel::getId(), $_POST['inputPassword']))
                               WebPage::setCurrentMsg("Votre mot de passe a été changé. Vous pouvez vous loguer.");
                           else
                               WebPage::setCurrentErrorMsg("La modification du mot de passe est impossible.");
                       }
                    }
                }
            }
        }
        
        return Controller::push("Changer de mot de passe", "./view/mesinfos/motdepasse.php", []);
    }
}