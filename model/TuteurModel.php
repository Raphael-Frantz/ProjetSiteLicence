<?php
// *************************************************************************************************
// * Modèle pour les tuteurs.
// *************************************************************************************************
class TuteurModel {

    // Constantes pour la table des tuteurs
    const DB = "inf_tuteur";                     // Table des tuteurs
    const DBF_USER = "tut_user";                 // Identifiant utilisateur
    const DBF_DIPLOME = "tut_diplome";           // Identifiant diplôme
    
    /**
     * Retourne la liste des identifiants de diplômes dont l'utilisateur est tuteur.
     * @param user l'identifiant de l'utilisateur
     * @return la liste des identifiants de diplôme ou null
     */
    public static function getListIdDiplomes(int $user) : array {
        $DB = MyPDO::getInstance();
        
        $SQL = "SELECT ".self::DBF_DIPLOME.
               " FROM ".self::DB.
               " WHERE ".self::DBF_USER."=:user;";
        
        if(($request = $DB->prepare($SQL)) && $request->execute([':user' => $user])) {
            $result = [];
            while($row = $request->fetch())
                $result[] = $row[self::DBF_DIPLOME];
            return $result;
        }
        else
            return null;
    }
    
    /**
     * Retourne la liste des diplômes dont l'utilisateur est tuteur.
     * @param user l'identifiant de l'utilisateur
     * @return la liste des identifiants de diplôme ou null
     */
    public static function getListDiplomes(int $user) : array {
        $DB = MyPDO::getInstance();
        $SQL = "SELECT ".DiplomeModel::DBF_ID." as id, ".
                         DiplomeModel::DBF_INTITULE." as intitule, ".
                         DiplomeModel::DBF_NBSEMESTRES." as nbSemestres".
               " FROM ".DiplomeModel::DB.", ".self::DB.
               " WHERE ".DiplomeModel::DBF_ID."=".self::DBF_DIPLOME." AND ".
                         self::DBF_USER."=:user".
               " ORDER BY ".DiplomeModel::DBF_INTITULE." ASC;";
        
        if(($request = $DB->prepare($SQL)) && $request->execute([':user' => $user])) {
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
     * Retourne la liste des tuteurs d'un diplôme.
     * @param diplome l'identifiant du diplome
     * @return la liste des tuteurs (ou null en cas d'erreur)
     */
    public static function getList(int $diplome, bool $assoc = false) : array {
        $DB = MyPDO::getInstance();
        
        $SQL = "SELECT ".self::DBF_USER." as id, ".
                         "CONCAT(".UserModel::DBF_NAME.", ' ',".UserModel::DBF_FIRSTNAME.") as nom, ".
                         UserModel::DBF_MAIL." as email".
               " FROM ".self::DB.", ".UserModel::DB.
               " WHERE ".self::DBF_USER."=".UserModel::DBF_ID." AND ".
                         self::DBF_DIPLOME."=:diplome".
               " ORDER BY nom ASC;";
        
        if(($request = $DB->prepare($SQL)) && $request->execute([':diplome' => $diplome])) {
            $result = array();
                
            while($row = $request->fetch()) {
                if($assoc)
                    $result[$row['id']] = $row;
                else
                    $result[] = $row;
            }
            return $result;
        }
        else
            return null;
    }
    
    /**
     * Retourne la liste des tuteurs non présents dans un diplôme.
     * @param diplome l'identifiant du diplôme
     * @return la liste des tuteurs ou [] en cas d'erreur
     */
    public static function getListeTuteursNonInscrits(int $diplome) : array {
        $DB = MyPDO::getInstance();
        
        $SQL = "SELECT ".UserModel::DBF_ID." as id, ".
                         "CONCAT(".UserModel::DBF_NAME.", ' ',".
                         UserModel::DBF_FIRSTNAME.") as nom, ".
                         UserModel::DBF_MAIL." as email".
               " FROM ".UserModel::DB.
               " LEFT OUTER JOIN (SELECT * FROM ".self::DB." WHERE ".self::DBF_DIPLOME."=:diplome".
               ") AS A ON ".UserModel::DBF_ID."=".self::DBF_USER.
               " WHERE (".UserModel::DBF_TYPE."=".User::TYPE_ENSEIGNANT." OR ".
                          UserModel::DBF_TYPE."=".User::TYPE_ADMIN.") AND ".
                         self::DBF_DIPLOME." IS NULL".
               " ORDER BY ".UserModel::DBF_NAME." ASC, ".UserModel::DBF_FIRSTNAME." ASC;";

        if(($request = $DB->prepare($SQL)) && $request->execute([':diplome' => $diplome])) {
            $result = array();
                
            while($row = $request->fetch()) {
                $result[] = $row;
            }
            return $result;
        }
        else
            return null;
        
        return null;
    }    
    
    /**
     * Ajoute un tuteur à un diplôme.
     * @param user l'identifiant de l'enseignant
     * @param diplome l'identifiant du diplôme
     * @return 'true' en cas de réussite, 'false' sinon
     */
    public static function ajouter(int $user, int $diplome) : bool {
        $DB = MyPDO::getInstance();
        
        // Est-il déjà tuteur dans ce diplôme ?
        $SQL1 = "SELECT * FROM ".self::DB." WHERE ".self::DBF_DIPLOME."=:diplome AND ".
                self::DBF_USER."=:user;";
                
        $SQL2 = "INSERT INTO ".self::DB." (".self::DBF_DIPLOME.", ".self::DBF_USER.
                ") VALUES (:diplome, :user);";
        
        if(($requete1 = $DB->prepare($SQL1)) && ($requete2 = $DB->prepare($SQL2))) {
            if($requete1->execute(array(":diplome" => $diplome, ":user" => $user))
               && ($requete1->rowCount() == 0)) {
                return $requete2->execute([ ":diplome" => $diplome, ":user" => $user ]);
            }
            else
                return false;
        }
        else
            return false;
    }
    
    /**
     * Supprimer un tuteur d'un diplôme.
     * @param user l'identifiant de l'enseignant
     * @param diplome l'identifiant du diplôme
     * @return 'true' en cas de réussite, 'false' sinon
     */
    public static function supprimer(int $user, int $diplome) : bool {
        $DB = MyPDO::getInstance();
        
        // Est-il déjà tuteur dans ce diplôme ?
        $SQL = "DELETE FROM ".self::DB." WHERE ".self::DBF_DIPLOME."=:diplome AND ".
               self::DBF_USER."=:user;";

        if($requete1 = $DB->prepare($SQL)) {
            return ($requete1->execute([ ':diplome' => $diplome, ':user' => $user]));
        }
        else
            return false;
    }
    
} // class TuteurModel