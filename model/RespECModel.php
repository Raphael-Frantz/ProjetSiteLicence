<?php
// *************************************************************************************************
// * Modèle pour les responsables d'EC.
// *************************************************************************************************
class RespECModel {
        
    // Constantes pour la table des responsables de diplôme
    const DB = "inf_respec";                     // Table des responsables d'EC
    const DBF_EC = "rec_ec";                     // Identifiant de l'EC
    const DBF_USER = "rec_user";                 // Identifiant de l'utilisateur

    /**
     * Retourne la liste des ECS dont l'utilisateur est responsable.
     * @param id l'identifiant de l'utilisateur
     * @return la liste des ECS (ou null en cas d'erreur)
     */
    public static function getList(int $id) : array {
        $DB = MyPDO::getInstance();
        $SQL = "SELECT ".ECModel::DBF_ID." as id, ".
                         ECModel::DBF_CODE." as code, ".
                         ECModel::DBF_INTITULE." as intitule, ".
                         "CONCAT(".ECModel::DBF_CODE.",' - ',".ECModel::DBF_INTITULE.") as nom ".
               " FROM ".self::DB.", ".ECModel::DB.
               " WHERE ".self::DBF_EC."=".ECModel::DBF_ID." AND ".
                         self::DBF_USER."=:id".
               " ORDER BY ".ECModel::DBF_CODE." ASC;";
        
        if(($request = $DB->prepare($SQL)) && $request->execute([':id' => $id])) {
            $result = array();
                
            while($row = $request->fetch())
                $result[] = $row;
            return $result;
        }
        else
            return null;
    }  

    /**
     * Retourne la liste des responsables d'un EC
     * @param EC l'identifiant de l'EC
     * @return la liste des utilisateurs ou null
     */
    public static function getListResponsables(int $EC) : array {
        $DB = MyPDO::getInstance();
        
        $SQL = "SELECT ".self::DBF_USER." as id, ".
                         UserModel::DBF_NAME." as nom, ".
                         UserModel::DBF_FIRSTNAME." as prenom".
               " FROM ".self::DB.", ".UserModel::DB.
               " WHERE ".self::DBF_USER."=".UserModel::DBF_ID." AND ".
                         self::DBF_EC."=:EC".
               " ORDER BY nom ASC, prenom ASC;";
        
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
     * Retourne la liste des identifiants d'EC dont l'utilisateur est responsable
     * @param user l'identifiant de l'utilisateur
     * @return la liste des identifiants d'EC ou null
     */
    public static function getListECs(int $user) : array {
        $DB = MyPDO::getInstance();
        
        $SQL = "SELECT ".self::DBF_EC.
               " FROM ".self::DB.
               " WHERE ".self::DBF_USER."=:user;";
        
        if(($request = $DB->prepare($SQL)) && $request->execute([':user' => $user])) {
            $result = [];
            while($row = $request->fetch())
                $result[] = $row[self::DBF_EC];
            return $result;
        }
        else
            return null;
    }
    
    /**
     * Ajoute un responsable d'EC.
     * @param EC l'identifiant de l'EC
     * @param user l'identifiant de l'utilisateur
     * @return 'true' en cas de réussite sinon 'false'
     */
    public static function ajouter(int $EC, int $user) : bool {
        $DB = MyPDO::getInstance();
        
        $SQL = "INSERT INTO ".self::DB." (".self::DBF_EC.", ".self::DBF_USER.") ".
               "VALUES (:EC, :user);";

        if(($requete = $DB->prepare($SQL)) &&
           $requete->execute([ ":EC" => $EC, ":user" => $user ])) {
            return true;
        }
        else
            return false;
    }
    
    /**
     * Supprime un responsable d'EC.
     * @param EC l'identifiant de l'EC
     * @param user l'identifiant de l'utilisateur
     * @return 'true' en cas de réussite sinon 'false'
     */
    public static function supprimer(int $EC, int $user) : bool {
        $DB = MyPDO::getInstance();
        
        if($user != -1) {
            $SQL = "DELETE FROM ".self::DB.
                   " WHERE ".self::DBF_EC."=:EC AND ".
                             self::DBF_USER."=:user;";
            $data = [ ':EC' => $EC, ':user' => $user ];
        }
        else {
            $SQL = "DELETE FROM ".self::DB.
                   " WHERE ".self::DBF_EC."=:EC;";
            $data = [ ':EC' => $EC ];
        }

        if(($requete = $DB->prepare($SQL)) && 
            $requete->execute($data))
            return true;
        else
            return false;
    }
    
} // class RespECModel