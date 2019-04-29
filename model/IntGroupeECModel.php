<?php
// *************************************************************************************************
// * Modèle pour les intervenant de groupes d'EC.
// *************************************************************************************************
class IntGroupeECModel {
        
    // Constantes pour la table des responsables de diplôme
    const DB = "inf_intgroec";                   // Table des intervenants de groupes d'EC
    const DBF_GROUPE = "ige_groupe";             // Identifiant du diplôme
    const DBF_USER = "ige_user";                 // Identifiant de l'utilisateur

    /**
     * Retourne la liste des ECS dans lesquel l'utilisateur intervient.
     * @param id l'identifiant de l'utilisateur
     * @return la liste des ECS (ou null en cas d'erreur)
     */
    public static function getListECs(int $id) : ?array {
        $DB = MyPDO::getInstance();
        $SQL = "SELECT ".ECModel::DBF_ID." as id, ".
                         ECModel::DBF_CODE." as code, ".
                         ECModel::DBF_INTITULE." as intitule, ".
                         "CONCAT(".ECModel::DBF_CODE.",' - ',".ECModel::DBF_INTITULE.") as nom ".
               " FROM ".self::DB.", ".ECModel::DB.", ".GroupeECModel::DB.
               " WHERE ".GroupeECModel::DBF_ID."=".self::DBF_GROUPE." AND ".
                         GroupeECModel::DBF_EC."=".ECModel::DBF_ID." AND ".
                         self::DBF_USER."=:user".
               " GROUP BY ".ECModel::DBF_ID.
               " ORDER BY ".ECModel::DBF_CODE." ASC;";
        
        if(($request = $DB->prepare($SQL)) && $request->execute([':user' => $id])) {
            $result = array();
                
            while($row = $request->fetch())
                $result[] = $row;
            return $result;
        }
        else
            return null;
    }  
    
    /**
     * Retourne la liste des identifiants d'ECS dans lesquel l'utilisateur intervient.
     * @param id l'identifiant de l'utilisateur
     * @return la liste des ECS (ou null en cas d'erreur)
     */
    public static function getListIdECs(int $id) : ?array {
        $DB = MyPDO::getInstance();
        $SQL = "SELECT ".ECModel::DBF_ID.
               " FROM ".self::DB.", ".ECModel::DB.", ".GroupeECModel::DB.
               " wHERE ".GroupeECModel::DBF_ID."=".self::DBF_GROUPE." AND ".
                         GroupeECModel::DBF_EC."=".ECModel::DBF_ID." AND ".
                         self::DBF_USER."=:user;";
        
        if(($request = $DB->prepare($SQL)) && $request->execute([':user' => $id])) {
            $result = [];
            while($row = $request->fetch())
                $result[] = $row[ECModel::DBF_ID];
            return $result;
        }
        else
            return null;
    } 
    
    /**
     * Retourne la liste des intervenants d'un groupe d'EC
     * @param groupe l'identifiant du groupe
     * @return la liste des utilisateurs ou null
     */
    public static function getList(int $groupe) : ?array {
        $DB = MyPDO::getInstance();
        
        $SQL = "SELECT ".self::DBF_USER." as id, ".
                         UserModel::DBF_NAME." as nom, ".
                         UserModel::DBF_FIRSTNAME." as prenom".
               " FROM ".self::DB.", ".UserModel::DB.
               " WHERE ".self::DBF_USER."=".UserModel::DBF_ID." AND ".
                         self::DBF_GROUPE."=:groupe".
               " ORDER BY nom ASC, prenom ASC;";
        
        if(($request = $DB->prepare($SQL)) && $request->execute([':groupe' => $groupe])) {
            $result = [];
            while($row = $request->fetch())
                $result[] = $row;
            return $result;
        }
        else
            return null;
    }

    /**
     * Retourne la liste des intervenants d'un EC avec leurs groupes associés.
     * @param EC l'identifiant de l'EC
     * @return la liste des utilisateurs ou null
     */
    public static function getListIntervenantsGroupe(int $EC) : ?array {
        $DB = MyPDO::getInstance();
        
        $SQL = "SELECT ".self::DBF_USER." as id, ".
                         UserModel::DBF_NAME." as nom, ".
                         UserModel::DBF_FIRSTNAME." as prenom, ".
                         GroupeECModel::DBF_INTITULE." as groupe, ".
                         GroupeECModel::DBF_TYPE." as type".
               " FROM ".self::DB.", ".UserModel::DB.", ".GroupeECModel::DB.
               " WHERE ".self::DBF_USER."=".UserModel::DBF_ID." AND ".
                         self::DBF_GROUPE."=".GroupeECModel::DBF_ID." AND ".
                         GroupeECModel::DBF_EC."=:EC".
               " ORDER BY ".GroupeECModel::DBF_INTITULE." ASC, ".
                            UserModel::DBF_NAME." ASC, ".
                            UserModel::DBF_FIRSTNAME." ASC;";
        
        if(($request = $DB->prepare($SQL)) && $request->execute([':EC' => $EC])) {
            $result = [];
            while($row = $request->fetch())
                $result[] = $row;
            return $result;
        }
        else
            return null;
    }
    
    /**
     * Retourne la liste des intervenants d'un EC.
     * @param EC l'identifiant de l'EC
     * @return la liste des utilisateurs ou null
     */
    public static function getListIntervenantsEC(int $EC) : ?array {
        $DB = MyPDO::getInstance();
        
        $SQL = "SELECT ".self::DBF_USER." as id, ".
                         UserModel::DBF_NAME." as nom, ".
                         UserModel::DBF_FIRSTNAME." as prenom".
               " FROM ".self::DB.", ".UserModel::DB.", ".GroupeECModel::DB.
               " WHERE ".self::DBF_USER."=".UserModel::DBF_ID." AND ".
                         self::DBF_GROUPE."=".GroupeECModel::DBF_ID." AND ".
                         GroupeECModel::DBF_EC."=:EC".
               " GROUP BY ".self::DBF_USER.
               " ORDER BY ".UserModel::DBF_NAME." ASC, ".
                            UserModel::DBF_FIRSTNAME." ASC;";
        
        if(($request = $DB->prepare($SQL)) && $request->execute([':EC' => $EC])) {
            $result = [];
            while($row = $request->fetch())
                $result[] = $row;
            return $result;
        }
        else
            return null;
    }    
    
    /**
     * Retourne la liste des groupes dont l'utilisateur est intervenant.
     * @param user l'identifiant de l'utilisateur
     * @return la liste des identifiants de groupe ou null
     */
    public static function getListGroupes(int $user) : ?array {
        $DB = MyPDO::getInstance();
        
        $SQL = "SELECT ".self::DBF_GROUPE.
               " FROM ".self::DB.
               " WHERE ".self::DBF_USER."=:user;";
        
        if(($request = $DB->prepare($SQL)) && $request->execute([':user' => $user])) {
            $result = [];
            while($row = $request->fetch())
                $result[] = $row[self::DBF_GROUPE];
            return $result;
        }
        else
            return null;
    }
    
    /**
     * Ajoute un intervenant de groupe d'EC.
     * @param diplome l'identifiant du groupe
     * @param user l'identifiant de l'utilisateur
     * @return 'true' en cas de réussite sinon 'false'
     */
    public static function ajouter(int $groupe, int $user) : bool {
        $DB = MyPDO::getInstance();
        
        $SQL = "INSERT INTO ".self::DB." (".self::DBF_GROUPE.", ".self::DBF_USER.") ".
               "VALUES (:groupe, :user);";

        if(($requete = $DB->prepare($SQL)) &&
           $requete->execute([ ":groupe" => $groupe, ":user" => $user ])) {
            return true;
        }
        else
            return false;
    }
    
    /**
     * Supprime un intervenant de groupe.
     * @param groupe l'identifiant du groupe
     * @param user l'identifiant de l'utilisateur
     * @return 'true' en cas de réussite sinon 'false'
     */
    public static function supprimer(int $groupe, int $user) : bool {
        $DB = MyPDO::getInstance();
        
        if($user != -1) {
            $SQL = "DELETE FROM ".self::DB.
                   " WHERE ".self::DBF_GROUPE."=:groupe AND ".
                             self::DBF_USER."=:user;";
            $data = [ ':groupe' => $groupe, ':user' => $user ];
        }
        else {
            $SQL = "DELETE FROM ".self::DB.
                   " WHERE ".self::DBF_GROUPE."=:groupe";
            $data = [ ':groupe' => $groupe ];
        }

        if(($requete = $DB->prepare($SQL)) && $requete->execute($data))
            return true;
        else
            return false;        
    }
    
} // class IntGroupeECModel