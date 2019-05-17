<?php
// *************************************************************************************************
// * Contrôleur par défaut
// *************************************************************************************************
class AccueilController {

    /**
     * Ressource non trouvée.
     */
    public static function error() {
        return Controller::push("Erreur", "./view/error404.php");
    }

    /**
     * Changement de mot de passe
     */
    public static function newpassword() {
        if(isset($_POST['inputEmail']) &&
           isset($_POST['inputKey']) &&
           isset($_POST['inputPassword']) &&
           isset($_POST['inputConfPassword'])) {
               
            if(($_POST['inputEmail'] == "") ||
               ($_POST['inputKey'] == "") ||
               ($_POST['inputPassword'] == "") ||
               ($_POST['inputConfPassword'] == ""))
               WebPage::setCurrentErrorMsg("Vous devez saisir toutes les informations.");
            else {               
                if($_POST['inputPassword'] != $_POST['inputConfPassword'])
                    WebPage::setCurrentErrorMsg("Le mot de passe et la confirmation doivent être identiques.");
                else {
                   if(!preg_match('#^(?=.*[a-zA-Z])(?=.*[0-9])(?=.*\W).{6,40}$#', $_POST['inputPassword']))
                       WebPage::setCurrentErrorMsg("Le mot de passe doit contenir entre 6 et 40 caractères avec des lettres, des chiffres et au moins un caractère spécial.");
                   else {
                       if(UserModel::updatePassword($_POST['inputEmail'], $_POST['inputKey'], $_POST['inputPassword']))
                           Controller::goTo("login.php", "Votre mot de passe a été changé. Vous pouvez vous loguer.");
                       else
                           WebPage::setCurrentErrorMsg("La modification du mot de passe est impossible.");
                   }
                }
            }
        }
        else {
            if(isset($_POST['btnValider']))
                WebPage::setCurrentErrorMsg("Vous devez saisir toutes les informations.");
        }
        
        return Controller::push("Changement de mot de passe", "./view/newpassword.php");
    }
    
    /**
     * Demande de récupération de mot de passe
     */
    public static function recovery() {
        if(isset($_POST['inputEmail'])) {
            // Recherche si un utilisateur avec cet email existe
            
            if(($id = UserModel::emailExists($_POST['inputEmail'])) != -1) {
                if(($user = UserModel::read($id)) == null)
                    Controller::goTo("", "", "Erreur de base de données$id.");
                
                // Check the time               
                $diff = 0;
                if($user->getMailDate() != 0) {
                    $today = new DateTime("now");
                    $lastSent = new DateTime();
                    $lastSent->setTimestamp($user->getMailDate());
                    $diff = $today->diff($lastSent, true)->days;
                }        
        
                if(($user->getMailDate() == 0) || ($diff > 0)) {
                    if(($data['key'] = UserModel::updateEmailDate($id)) === "")
                        Controller::goTo("", "", "Erreur de base de données.");
                    $data['email'] = $user->getMail();
                    
                    // Get body mail                    
                    ob_start();
                    require("./view/recovery_email.php");
                    $emailBody = ob_get_contents();
                    ob_end_clean();
                    
                    // Send email
                    if(Mail::send($user->getMail(), "Mot de passe pour le site ".SITE_TITLE, $emailBody)) {
                        Controller::goTo("newpassword.php", "Un email vous a été envoyé. Consultez votre messagerie.");
                    }
                    else {
                        WebPage::setCurrentErrorMsg("Erreur lors de l'envoi de l'email.");
                    }
                }
                else {
                    WebPage::setCurrentErrorMsg("Un email vous a déjà été envoyé. Veuillez consulter votre messagerie.");
                }
            }
            else {
                WebPage::setCurrentErrorMsg("Aucun utilisateur ne possède cet email.");
            }
        }
        
        return Controller::push("Récupération de mot de passe", "./view/recovery.php");
    }
    
    /**
     * Connexion
     */
    public static function login() {
        if(UserModel::isConnected())
            Controller::goTo();
        
        if(isset($_POST['email']) && ($_POST['email'] != "") &&
            isset($_POST['password']) && ($_POST['password'] != "")) {
                if(UserModel::login($_POST['email'], $_POST['password'])) 
                    Controller::goTo("mesinfos/general.php");
                else
                    WebPage::setCurrentErrorMsg("Vous n'avez pas saisi des identifiants corrects.");
        }
        
        return Controller::push("Login", "./view/login.php", [], "");
    }
    
    /**
     * Déconnexion
     */
    public static function logout() {
        if(UserModel::isConnected()) {
            UserModel::logout();
            Controller::goTo("", "Vous avez été déconnecté.");
        }
        else
            Controller::goTo("", "", "Vous n'étiez pas connecté.");
    }

    /**
     * Stages en Licence
     */
    public static function stages() {
        return Controller::push("Stages en Licence", "./view/current/stages.php");
    }
    
    /**
     * Prérentrée
     */
    public static function prerentree() {
        return Controller::push("Prérentrée", "./view/current/prerentree.php");
    }   

    /**
     * Emplois du temps
     */
    public static function emploidutemps() {
        return Controller::push("Emploi du temps", "./view/current/emploidutemps.php");
    }    

    /**
     * Planning CC et EET
     */
    public static function planning() {

        $licenceInfoID = 3; // ID dans la base de la licence d'info
        $semestresImpaires = 0; // 1= afficher les semestres impairs, 0= afficher les semestres paires

        echo "<pre>";

        for($s = ($semestresImpaires ? 1 : 2); $s <= 6; $s += 2) {

            echo "Semestre $s\n";
            echo "Groupes: \n";
            print_r(GroupeModel::getList($licenceInfoID, $s, Groupe::GRP_TD));
            echo "UEECs: \n";
            print_r(UEECModel::getUEEC($licenceInfoID, $s));
        }

        echo "</pre>";


        echo "-----------------------------";

        for($s = ($semestresImpaires ? 1 : 2); $s <= 6; $s += 2) {

            echo "<p>Semestre $s</p>";

            $groupes = GroupeModel::getList($licenceInfoID, $s, Groupe::GRP_TD);
            echo "Groupes: " . join(", ", array_column($groupes, 'intitule'));


            $UEs = UEECModel::getUEEC($licenceInfoID, $s)[$s];

            foreach($UEs as $UE) {

                foreach($UE['EC'] as $EC) {

                    echo "{$EC['intitule']} / {$EC['code']} <br>";

                    // Vérifier si, pour une UE donnée, il existe des épreuves dans la base de données
                    var_dump(EpreuveDateModel::getList($EC['id']));
                }
            }
        }


        return Controller::push("Planning CC et EET", "./view/current/planning.php");
    }    
    
    /**
     * Contact
     */
    public static function contacts() {
        return Controller::push("Contacts", "./view/contacts.php");
    }
    
    /**
     * Accès
     */
    public static function acces() {
        return Controller::push("Accès", "./view/acces.php");
    }    

    /**
     * Liens
     */
    public static function liens() {
        return Controller::push("Liens", "./view/liens.php");
    }

    
    /**
     * Prérequis et programme
     */
    public static function prerequis() {
        return Controller::push("Prérequis et programme", "./view/prerequis.php");
    }
    
    /**
     * Poursuite d'études
     */
    public static function poursuite() {
        return Controller::push("Poursuite d'études", "./view/poursuite.php");
    }

    /**
     * Mobilité
     */
    public static function mobilite() {
        return Controller::push("Mobilité internationale", "./view/mobilite.php");
    }

    /**
     * Inscription
     */
    public static function inscription() {
        return Controller::push("Modalités d'admission", "./view/inscription.php");
    }
    
    /**
     * Structure
     */
    public static function structure() {
        return Controller::push("Structure de la Licence", "./view/structure.php");
    }
    
    /**
     * Par défaut : l'accueil.
     */
    public static function index() {
        return Controller::push("Accueil", "./view/accueil.php", 
                                ["actualites" => ActualiteModel::getList(true)]);
    }
       
} // class AccueilController