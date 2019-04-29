<?php
// *************************************************************************************************
// * Model for users.
// *************************************************************************************************
class UserModel {
    
    const USER_SESSION = "currentUser";          // Name of the session variable
    const RIGHTS_SESSION = "currentRights";      // Name of the session variable for rights
    const TMP_USER = "tmpUser";                  // Name of the session variable for the temporary user
    
    const DB = "inf_user";                       // Table of users
    
    const DBF_ID = "use_id";                     // Identifier
    const DBF_NAME = "use_name";                 // Name
    const DBF_FIRSTNAME = "use_firstname";       // Firstname
    const DBF_MAIL = "use_mail";                 // Mail address
    const DBF_PASSWORD = "use_password";         // Password
    const DBF_MAILDATE = "use_maildate";         // Date of the last password recovery mail (for password recovery)
    const DBF_KEY = "use_key";                   // Key used for the password recovery
    const DBF_ACTIVE = "use_active";             // If '1', the user is active
    const DBF_TYPE = "use_type";                 // Type of the user

    /**
     * Return an user from an associative array.
     * @param array the associative array
     * @return the user
     */
    public static function fromArray(array $array) : User {
        return new User($array[self::DBF_ID], $array[self::DBF_NAME], $array[self::DBF_FIRSTNAME], $array[self::DBF_MAIL], 
                        $array[self::DBF_PASSWORD], $array[self::DBF_TYPE],
                        $array[self::DBF_MAILDATE], $array[self::DBF_KEY], $array[self::DBF_ACTIVE]);
    }
    
    /**
     * Return the users list.
     * @param type the type of the users
     * @return the array of the users or null
     */
    public static function getList(int $type) : array {
        $DB = MyPDO::getInstance();
        
        if($type == User::TYPE_ENSEIGNANT) {
            $SQL = "SELECT ".self::DBF_ID." as id, ".
                             self::DBF_NAME." as nom, ".
                             self::DBF_FIRSTNAME." as prenom, ".
                             self::DBF_MAIL." as email".
                   " FROM ".self::DB.
                   " WHERE ".self::DBF_TYPE."=".User::TYPE_ENSEIGNANT." OR ".
                             self::DBF_TYPE."=".User::TYPE_ADMIN.
                   " ORDER BY ".self::DBF_NAME." ASC, ".self::DBF_FIRSTNAME." ASC;";
            $data = [];
        }
        else {
            $SQL = "SELECT ".self::DBF_ID." as id, ".
                             self::DBF_NAME." as nom, ".
                             self::DBF_FIRSTNAME." as prenom, ".
                             self::DBF_MAIL." as email".
                   " FROM ".self::DB.
                   " WHERE ".self::DBF_TYPE."=:type".
                   " ORDER BY ".self::DBF_NAME." ASC, ".self::DBF_FIRSTNAME." ASC;";
            $data = [':type' => $type];
        }
        
        if(($request = $DB->prepare($SQL)) && $request->execute($data)) {
            $result = array();
                
            while($row = $request->fetch()) {
                $result[] = $row;
            }
            return $result;
        }
        else
            return null;
    }
    
    /**
     * Return the identifier of the connected user.
     * @return the identifier or -1
     */
    public static function getId() : int {
        if(isset($_SESSION[self::USER_SESSION]) && ($_SESSION[self::USER_SESSION] != null))
            return $_SESSION[self::USER_SESSION]->getId();
        else
            return -1;
    }
    
    /**
     * Return the current connected user.
     * @return the user or null
     */
    public static function getCurrentUser() : ?User {
        if(isset($_SESSION[self::USER_SESSION]) && ($_SESSION[self::USER_SESSION] != null))
            return $_SESSION[self::USER_SESSION];
        else
            return null;
    }    
    
    /**
     * Change the current user.
     * @param id the identifier of the new user
     * @return 'true' on success or 'false' on error
     */
    public static function setUser(int $id) : bool {
        $DB = MyPDO::getInstance();

        // Chekc if an user is connected
        if(!self::isConnected())
            return false;
        
        if($id == -1) {
            if(isset($_SESSION[self::TMP_USER]['user']) && ($_SESSION[self::TMP_USER]['user'] !== null) &&
               isset($_SESSION[self::TMP_USER]['rights']) && ($_SESSION[self::TMP_USER]['rights'] !== null)) {
                $_SESSION[self::USER_SESSION] = $_SESSION[self::TMP_USER]['user'];
                $_SESSION[self::RIGHTS_SESSION] = $_SESSION[self::TMP_USER]['rights'];
                
                $_SESSION[self::TMP_USER]['rights'] = null;
                $_SESSION[self::TMP_USER]['user'] = null;
                unset($_SESSION[self::TMP_USER]);
            }
            else
                return false;
        }
        else {
            // Save the current user
            $_SESSION[self::TMP_USER]['user'] = $_SESSION[self::USER_SESSION];
            $_SESSION[self::TMP_USER]['rights'] = $_SESSION[self::RIGHTS_SESSION];
            
            // Load the user
            $SQL = "SELECT * FROM ".self::DB." WHERE ".self::DBF_ID."=:id;";
            
            if(($request = $DB->prepare($SQL)) && $request->execute(array(':id' => $id)) &&
               ($row = $request->fetch())) {
                $_SESSION[self::USER_SESSION] = self::fromArray($row);
            }
            else
                return false;
            
            self::loadRights();
        }
        
        return true;
    }
    
    /**
     * Check if the user is a temporary user.
     * @return 'true' or 'false'
     */
    public static function isTemporary() {
        return(isset($_SESSION[self::TMP_USER]['user']) && ($_SESSION[self::TMP_USER]['user'] !== null) &&
               isset($_SESSION[self::TMP_USER]['rights']) && ($_SESSION[self::TMP_USER]['rights'] !== null));
    }
    
    /**
     * Login of an user.
     * @param email the email of the user
     * @param password the password of the user
     * @return 'true' on success or 'false' on error
     */
    public static function login(string $email, string $password) : bool {
        $DB = MyPDO::getInstance();
        
        // Load the user
        $SQL = "SELECT * FROM ".self::DB.
               " WHERE ".self::DBF_MAIL."=:email AND ".
                         self::DBF_PASSWORD."=md5(:password) AND ".
                         self::DBF_ACTIVE."=:active;";
        if(($request = $DB->prepare($SQL)) &&
           $request->execute(array(':email' => $email, ':password' => $password, ':active' => DataTools::boolean2Int(true))) &&
           ($row = $request->fetch())) {
            $_SESSION[self::USER_SESSION] = self::fromArray($row);
        }
        else
            return false;
        
        self::loadRights();
        
        return true;
    }
    
    /**
     * Load rights (specific).
     */
    private static function loadRights() : void {
        if(($_SESSION[self::USER_SESSION]->getType() == User::TYPE_ENSEIGNANT) ||
           ($_SESSION[self::USER_SESSION]->getType() == User::TYPE_ADMIN)) {
            $_SESSION[self::RIGHTS_SESSION] = [];
            
            // Responsabilité de diplômes
            $_SESSION[self::RIGHTS_SESSION]['diplomes'] = RespDiplomeModel::getListDiplomes($_SESSION[self::USER_SESSION]->getId());
            
            // Responsabilité d'EC
            $_SESSION[self::RIGHTS_SESSION]['resp'] = RespECModel::getListECs($_SESSION[self::USER_SESSION]->getId());
            
            // Intervenant d'EC
            $_SESSION[self::RIGHTS_SESSION]['int'] = IntGroupeECModel::getListIdECs($_SESSION[self::USER_SESSION]->getId());
            
            // Intervenant dans un groupe d'EC
            $_SESSION[self::RIGHTS_SESSION]['grpec'] = IntGroupeECModel::getListGroupes($_SESSION[self::USER_SESSION]->getId());
            
            // Tuteur
            $_SESSION[self::RIGHTS_SESSION]['tuteur'] = TuteurModel::getListIdDiplomes($_SESSION[self::USER_SESSION]->getId());
        }
        else
            $_SESSION[self::RIGHTS_SESSION] = [];
    }
    
    /**
     * Logout of an user.
     */
    public static function logout() : void {
        $_SESSION[self::USER_SESSION] = null;
        unset($_SESSION[self::USER_SESSION]);
        $_SESSION[self::RIGHTS_SESSION] = null;
        unset($_SESSION[self::RIGHTS_SESSION]);
        $_SESSION[self::TMP_USER] = null;
        unset($_SESSION[self::TMP_USER]);
        if(isset($_SESSION['current'])) unset($_SESSION['current']);
    }
    
    /**
     * Check if an user is actually connected.
     * @return 'true' if an user is actually connected else 'false'
     */
	public static function isConnected() : bool {
		return (isset($_SESSION[self::USER_SESSION]) && ($_SESSION[self::USER_SESSION] != null));
	}
    
    /**
	 * Add an user into the database.
	 * @return 'true' on success or 'false' on error
	 */
	public static function create(User $user) : bool {
        $DB = MyPDO::getInstance();
        $SQL = "INSERT INTO ".self::DB." (".self::DBF_ID.", ".
                                            self::DBF_NAME.", ".
                                            self::DBF_FIRSTNAME.", ".
                                            self::DBF_MAIL.", ".
                                            self::DBF_PASSWORD.", ".
                                            self::DBF_TYPE.", ".
                                            self::DBF_MAILDATE.", ".
                                            self::DBF_KEY.", ".
                                            self::DBF_ACTIVE.
               ") VALUES (NULL, :name, :firstname, :mail, :password, :type, :maildate, :key, :active);";

        // #TODO# : secure the password !!!
        if(($requete = $DB->prepare($SQL)) &&
           $requete->execute(array(":name" => $user->getName(),
                                   ":firstname" => $user->getFirstname(),
                                   ":mail" => $user->getMail(),
                                   ":password" => md5($user->getPassword()),
                                   ":type" => $user->getType(),
                                   ":maildate" => $user->getMailDate(),
                                   ":key" => $user->getKey(),
                                   ":active" => DataTools::boolean2Int($user->isActive())))) {
            $user->setId($DB->lastInsertId());
            return true;
        }
        else
            return false;
    }
    
    /**
	 * Get an user from the database.
     * @param identifier the identifier of the user
	 * @return the user or 'null' on error
	 */
	public static function read(int $identifier) : ?User {
        $DB = MyPDO::getInstance();
        $SQL = "SELECT * FROM ".self::DB." WHERE ".self::DBF_ID."=:id";
        
        if(($request = $DB->prepare($SQL)) &&
           $request->execute([':id' => $identifier]) && 
           ($row = $request->fetch()))
            return self::fromArray($row);

        return null;
	}	
    
    /**
	 * Save modifications of an user into the database.
	 * @return 'true' on success or 'false' on error
	 */
	public static function update(User $user) : bool {
        $data = array(array(self::DBF_NAME,      $user->getName()),
                      array(self::DBF_FIRSTNAME, $user->getFirstname()),
                      array(self::DBF_MAIL,      $user->getMail()),
                      array(self::DBF_KEY,       $user->getKey()),
                      array(self::DBF_MAILDATE,  $user->getMailDate()),
                      array(self::DBF_ACTIVE,    DataTools::boolean2Int($user->isActive()))
                      );
                      
        // #TODO# : secure the password !!!
        if($user->getPassword() != "")
            $data[] = array(self::DBF_PASSWORD, md5($user->getPassword()));
        
        return MyPDO::update(self::DB, $data, $user->getId(), self::DBF_ID);
	}	
    
    /**
     * Delete an user of the database.
     * @param identifier the identifier of the user
     * @return 'true' on success or 'false' on error
     */
    public static function delete(int $identifier) : bool {
        $DB = MyPDO::getInstance();
        $SQL = "DELETE FROM ".self::DB." WHERE ".self::DBF_ID."=:id LIMIT 1";
        
        if(($request = $DB->prepare($SQL)) &&
           $request->execute([':id' => $identifier])) {
            // #TODO# : delete all data associated to the user
            return true;
        }
        else
            return false;
    }
    
    /**
     * Return the user identifier of an email address.
     * @param email the email address
     * @return the identifier of the user of -1 on error
     */
    public static function emailExists(string $email) : int {
        $DB = MyPDO::getInstance();
        
        $SQL = "SELECT ".self::DBF_ID.
               " FROM ".self::DB.
               " WHERE ".self::DBF_MAIL."=:email;";
        if(($request = $DB->prepare($SQL)) && $request->execute([ ':email' => $email ])) {
            if($row = $request->fetch())
                return $row[self::DBF_ID];
            else
                return -1;
        }
        else {
            return -1;
        }
    }
    
    /**
     * Update the email date of an user.
     * @param id the user identifier
     * @return the new key of "" on error
     */
    public static function updateEmailDate(int $id) : string {
        $DB = MyPDO::getInstance();
        
        $today = new DateTime("now");
        $key = DataTools::generateKey(40);
        
        $SQL = "UPDATE ".self::DB." SET ".self::DBF_MAILDATE."=:today, ".self::DBF_KEY."=:key WHERE ".self::DBF_ID."=:id;";
            
        if(($request = $DB->prepare($SQL)) &&
           $request->execute([':today' => $today->getTimestamp(), ':id' => $id, ':key' => $key]))
           return $key;
        else
            return "";
    }
    
    /**
     * Update the password of a user.
     * @param email the email of the user
     * @param key the key of the user
     * @param password the new password
     * @return 'true' if the password is set or 'false' on error
     */
    public static function updatePassword(string $email, string $key, string $password) : bool {
        $DB = MyPDO::getInstance();
               
        $SQL = "UPDATE ".self::DB.
               " SET ".self::DBF_PASSWORD."=:password, ".
                       self::DBF_MAILDATE."=:date, ".
                       self::DBF_KEY."=:keyOld".
               " WHERE ".self::DBF_MAIL."=:email AND ".
                         self::DBF_KEY."=:key;";
                         
        if(($request = $DB->prepare($SQL)) &&
           $request->execute([':password' => md5($password), ':date' => 0, ':keyOld' => '', ':email' => $email, ':key' => $key]) &&
           ($request->rowCount() != 0))
           return true;
        else
            return false;
    }
    
    /**
     * Update the password of a user.
     * @param id the id of the user
     * @param password the new password
     * @return 'true' if the password is set or 'false' on error
     */
    public static function updatePasswordId(int $id, string $password) : bool {
        $DB = MyPDO::getInstance();
               
        $SQL = "UPDATE ".self::DB.
               " SET ".self::DBF_PASSWORD."=:password, ".
                       self::DBF_MAILDATE."=:date, ".
                       self::DBF_KEY."=:keyOld".
               " WHERE ".self::DBF_ID."=:id;";
            
        if(($request = $DB->prepare($SQL)) &&
           $request->execute([':password' => md5($password), ':date' => 0, ':keyOld' => '', ':id' => $id]))
           return true;
        else
            return false;
    }
    
    // *************************************************************************************************
    // * Specific for users rights
    // *************************************************************************************************
    
    /**
     * Indique si l'utilisateur est responsable de ou d'un diplôme.
     * @param id l'identifiant du diplôme (ou -1 pour un responsable de diplôme)
     * @return true ou false
     */
    public static function estRespDiplome(int $id = -1) {
        if(isset($_SESSION[self::USER_SESSION]) && ($_SESSION[self::USER_SESSION] != null)) {
            if($_SESSION[self::USER_SESSION]->getType() == User::TYPE_ADMIN)
                return true;
            else {
                if($_SESSION[self::USER_SESSION]->getType() == User::TYPE_ENSEIGNANT && 
                   isset($_SESSION[self::RIGHTS_SESSION]['diplomes'])) {
                    if($id == -1)
                        return count($_SESSION[self::RIGHTS_SESSION]['diplomes']) > 0;
                    else
                        return in_array($id, $_SESSION[self::RIGHTS_SESSION]['diplomes']);
                }
                else
                    return false;
            }   
        }
        else 
            return false;
    }
    
    /**
     * Retourne la liste des diplômes dont l'utilisateur est responsable.
     * @return la liste des diplômes
     */
    public static function getListDiplomes() {
        if(isset($_SESSION[self::USER_SESSION]) && ($_SESSION[self::USER_SESSION] != null)) {
            if(isset($_SESSION[self::RIGHTS_SESSION]['diplomes']))
                return $_SESSION[self::RIGHTS_SESSION]['diplomes'];
            else
                return [];
        }
        else
            return [];
    }
    
    /**
     * Indique si l'utilisateur est tuteur d'un diplôme.
     * @param id l'identifiant du diplôme (ou -1 pour un tuteur de diplôme)
     * @return true ou false
     */
    public static function estTuteurDiplome(int $id = -1) {
        if(isset($_SESSION[self::USER_SESSION]) && ($_SESSION[self::USER_SESSION] != null)) {
            if($_SESSION[self::USER_SESSION]->getType() == User::TYPE_ADMIN)
                return true;
            else {
                if(isset($_SESSION[self::RIGHTS_SESSION]['tuteur'])) {
                    if($id == -1)
                        return count($_SESSION[self::RIGHTS_SESSION]['tuteur']) > 0;
                    else
                        return in_array($id, $_SESSION[self::RIGHTS_SESSION]['tuteur']);
                }
                else
                    return false;
            }
        }
        else 
            return false;
    }
    
    /**
     * Indique si l'utilisateur est responsable de ou d'un EC.
     * @param id l'identifiant de l'EC (ou -1 pour un responsable d'EC)
     * @return true ou false
     */
    public static function estRespEC(int $id = -1) : bool {
        if(isset($_SESSION[self::USER_SESSION]) && ($_SESSION[self::USER_SESSION] != null)) {
            if($_SESSION[self::USER_SESSION]->getType() == User::TYPE_ADMIN)
                return true;
            else {
                if(($_SESSION[self::USER_SESSION]->getType() == User::TYPE_ENSEIGNANT) && 
                    isset($_SESSION[self::RIGHTS_SESSION]['resp'])) {
                    if($id == -1)
                        return count($_SESSION[self::RIGHTS_SESSION]['resp']) > 0;
                    else
                        return in_array($id, $_SESSION[self::RIGHTS_SESSION]['resp']);
                }
                else
                    return false;
            }
        }
        else
            return false;
    }

    /**
     * Indique si l'utilisateur intervient dans un EC.
     * @param id l'identifiant de l'EC (ou -1 pour n'importe quel EC)
     * @return true ou false
     */
    public static function estIntEC(int $id = -1) : bool {
        if(isset($_SESSION[self::USER_SESSION]) && ($_SESSION[self::USER_SESSION] != null)) {
            if(!self::estRespEC($id)) {
                if(($_SESSION[self::USER_SESSION]->getType() == User::TYPE_ENSEIGNANT) && 
                   isset($_SESSION[self::RIGHTS_SESSION]['int'])) {
                    if($id == -1)
                        return count($_SESSION[self::RIGHTS_SESSION]['int']) > 0;
                    else
                        return in_array($id, $_SESSION[self::RIGHTS_SESSION]['int']);
                }
                else
                    return false;
            }
            else
                return true;
        }
        else
            return false;
    }
    
    /**
     * Indique si l'utilisateur intervient dans un groupe d'EC.
     * @param id l'identifiant du groupe d'EC
     * @return true ou false
     */
    public static function estIntGrpEC(int $id = -1) : bool {
        if(isset($_SESSION[self::USER_SESSION]) && ($_SESSION[self::USER_SESSION] != null)) {
            if(($_SESSION[self::USER_SESSION]->getType() == User::TYPE_ENSEIGNANT) && 
               isset($_SESSION[self::RIGHTS_SESSION]['grpec'])) {
                return in_array($id, $_SESSION[self::RIGHTS_SESSION]['grpec']);
            }
            else
                return false;
        }
        else
            return false;
    }
    
    /**
     * Indique si l'utilisateur est administrateur.
     * @return true ou false
     */
    public static function estAdmin() : bool {
        return (isset($_SESSION[self::USER_SESSION]) && ($_SESSION[self::USER_SESSION] != null) &&
                ($_SESSION[self::USER_SESSION]->getType() == User::TYPE_ADMIN));
    }   
    
    /**
     * Indique si l'utilisateur est enseignant.
     * @return true ou false
     */
    public static function estEnseignant() : bool {
        return (isset($_SESSION[self::USER_SESSION]) && ($_SESSION[self::USER_SESSION] != null) &&
                (($_SESSION[self::USER_SESSION]->getType() == User::TYPE_ENSEIGNANT) || 
                 ($_SESSION[self::USER_SESSION]->getType() == User::TYPE_ADMIN)));
    }    
    
    /**
     * Indique si l'utilisateur est étudiant.
     * @return true ou false
     */
    public static function estEtudiant() : bool {
        return (isset($_SESSION[self::USER_SESSION]) && ($_SESSION[self::USER_SESSION] != null) &&
                ($_SESSION[self::USER_SESSION]->getType() == User::TYPE_ETUDIANT));
    }    
    
    /**
     * Indique si l'utilisateur est tuteur.
     * @return true ou false
     */
    public static function estTuteur() : bool {
        return (isset($_SESSION[self::USER_SESSION]) && ($_SESSION[self::USER_SESSION] != null) &&        
                (($_SESSION[self::USER_SESSION]->getType() == User::TYPE_ENSEIGNANT) ||
                 ($_SESSION[self::USER_SESSION]->getType() == User::TYPE_ADMIN)) &&
                isset($_SESSION[self::RIGHTS_SESSION]['tuteur']) &&
                count($_SESSION[self::RIGHTS_SESSION]['tuteur']) > 0);
    }   

} // class UserModel