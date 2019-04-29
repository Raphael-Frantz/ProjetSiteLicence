<?php
// *************************************************************************************************
// * Contrôleur pour la gestion des utilisateurs
// *************************************************************************************************
class UsersController {
    
    /**
     * Récupère un enseignant depuis un formulaire.
     * @return un enseignant
     */
    private static function __getFromForm() : User {
        $inputNom = "";
        $inputPrenom = "";
        $inputEmail = "";
        $inputPassword = "";
        
        if(isset($_POST['inputNom'])) $inputNom = $_POST['inputNom'];
        if(isset($_POST['inputPrenom'])) $inputPrenom = $_POST['inputPrenom'];
        if(isset($_POST['inputEmail'])) $inputEmail = $_POST['inputEmail'];
        if(isset($_POST['inputPassword'])) $inputPassword = $_POST['inputPassword'];
        
        return new User(-1, $inputNom, $inputPrenom, $inputEmail, $inputPassword, User::TYPE_ENSEIGNANT);
    }
    
    /**
     * Ajoute un enseignant.
     * #RIGHTS# : administrateur
     */
    public static function ajouter() {
        if(!UserModel::estAdmin())
            Controller::goTo();
        
        // L'utilisateur a annulé
        if(isset($_POST['btnAnnuler']))
            Controller::goTo("users/index.php", "L'enseignant n'a pas été ajouté.");
        
        // Le formulaire a été validé
        $data = [];
        if(isset($_POST['btnAjouter'])) {
            $data['user'] = self::__getFromForm();
            
            $erreur = "";
            if(!isset($_POST['inputConf']) || ($_POST['inputConf'] != $data['user']->getPassword()))
                $erreur .= "Le mot de passe et la confirmation sont différents. ";
            if($data['user']->getName() == "")
                $erreur .= "Vous devez saisir un nom. ";
            if($data['user']->getFirstname() == "")
                $erreur .= "Vous devez saisir un prénom. ";
            if($data['user']->getMail() == "")
                $erreur .= "Vous devez saisir une adresse email. ";
            else
                if(!filter_var($data['user']->getMail(), FILTER_VALIDATE_EMAIL))
                    $erreur .= "L'adresse email n'est pas valide. ";
            
            if($erreur == "") {
                if(UserModel::create($data['user']))
                    Controller::goTo("users/index.php", "L'enseignant a été ajouté.");
                else
                    WebPage::setCurrentErrorMsg("L'enseignant n'a pas été ajouté dans la base de données.");
            }        
            else
                WebPage::setCurrentErrorMsg($erreur);
        }
        
        return Controller::push("Ajouter un enseignant", "./view/users/ajouter.php", $data);
    }
    
    /**
     * Modifie un enseignant.
     * #RIGHTS# : uniquement un administrateur
     */
    public static function modifier() {
        if(!UserModel::estAdmin())
            Controller::goTo();
        
        // L'utilisateur a annulé
        if(isset($_POST['btnAnnuler']))
            Controller::goTo("users/index.php", "L'enseignant n'a pas été modifié.");
        
        // Le formulaire a été validé
        $data = [];
        if(isset($_POST['btnModifier'])) {
            if(!isset($_SESSION['current']['user']))
                Controller::goTo("users/index.php", "", "Erreur lors de la récupération de l'utilisateur.");
            
            $data['user'] = self::__getFromForm();
            $data['user']->setId($_SESSION['current']['user']);
            
            $erreur = "";
            if(isset($_POST['inputConf']) && 
               ($_POST['inputConf'] != $data['user']->getPassword()))
                $erreur .= "Le mot de passe et la confirmation sont différents. ";
            if($data['user']->getName() == "")
                $erreur .= "Vous devez saisir un nom. ";
            if($data['user']->getFirstname() == "")
                $erreur .= "Vous devez saisir un prénom. ";
            if($data['user']->getMail() == "")
                $erreur .= "Vous devez saisir une adresse email. ";
            else
                if(!filter_var($data['user']->getMail(), FILTER_VALIDATE_EMAIL))
                    $erreur .= "L'adresse email n'est pas valide. ";

            if($erreur == "") {
                if(UserModel::update($data['user']))
                    Controller::goTo("users/index.php", "L'enseignant a été modifié.");
                else
                    WebPage::setCurrentMsg("L'enseignant n'a pas été modifié dans la base de données.");
            }        
            else
                WebPage::setCurrentErrorMsg($erreur);
        }      
        else {
            $data['user'] = UserModel::read(intval($_POST['idModi']));
            if($data['user'] == null)
                return Controller::goTo("users/index.php", "", "Erreur lors de la récupération de l'utilisateur.");
            $_SESSION['current']['user'] = intval($_POST['idModi']);
        }
        
        return Controller::push("Modifier un enseignant", "./view/users/modifier.php", $data);
    }
    
    /**
     * Supprime un utilisateur.
     * #RIGHTS# : uniquement un administrateur
     */
    public static function supprimer() {
        if(!UserModel::estAdmin())
            Controller::goTo();
        
        // L'utilisateur a annulé
        if(isset($_POST['btnAnnuler']))
            Controller::goTo("users/index.php", "L'enseignant n'a pas été supprimé.");
        
        // Le formulaire a été validé
        $data = [];
        if(isset($_POST['btnSupprimer'])) {
            if(!isset($_SESSION['current']['user']))
                Controller::goTo("users/index.php", "L'utilisateur n'a pas été supprimé.");
            
            if(UserModel::delete($_SESSION['current']['user']))
                Controller::goTo("users/index.php", "L'enseignant a été supprimé.");
            else {
                WebPage::setCurrentErrorMsg("L'enseignant n'a pas été supprimé de la base de données.");
                $data['user'] = UserModel::read($_SESSION['current']['user']);
            }
        }
        else {
            $data['user'] = UserModel::read(intval($_POST['idSupp']));
            if($data['user'] == null)
                Controller::goTo("users/index.php", "", "Erreur lors du chargement de l'utilisateur. ");
            $_SESSION['current']['user'] = intval($_POST['idSupp']);
        }

        return Controller::push("Supprimer un enseignant", "./view/users/supprimer.php", $data);
    }
    
    /**
     * Prendre le rôle d'un utilisateur.
     */
    public static function role() : void {
        if(UserModel::isConnected() && UserModel::isTemporary()) {
            UserModel::setUser(-1);
        }
        else {
            if(!UserModel::estAdmin() || !isset($_POST['idRole']))
                Controller::goTo();
                
            UserModel::setUser(intval($_POST['idRole']));
        }
        
        Controller::goTo("mesinfos/general.php", "Le rôle a été modifié.");
    }
        
    /**
     * Par défaut : la liste des utilisateurs du site
     * #RIGHTS# : administrateur
     */
    public static function index() {
        if(!UserModel::estAdmin())
            Controller::goTo();
            
        $data = ["users" => UserModel::getList(User::TYPE_ENSEIGNANT) ];        
        
        return Controller::push("Gestion des enseignants", "./view/users/users.php", $data);
    }
    
}