<?php
// *************************************************************************************************
// * Modèle pour les responsables de diplôme.
// *************************************************************************************************
class RespDiplomeModel {
        
    // Constantes pour la table des responsables de diplôme
    const DB = "inf_respdiplome";                // Table des responsables de diplôme
    const DBF_DIPLOME = "rdi_diplome";           // Identifiant du diplôme
    const DBF_USER = "rdi_user";                 // Identifiant de l'utilisateur

    /**
     * Vérifie si un utilisateur est responsable d'un diplôme qui contient une EC.
     * @param user l'identifiant de l'utilisateur
     * @param EC l'identifiant de l'EC
     * @return 'true' si oui, 'false' sinon
     */
    public static function estResponsableDiplomeEC(int $user, int $EC) : bool {
        $DB = MyPDO::getInstance();
        
        $SQL = "SELECT ".UEModel::DBF_DIPLOME.
               " FROM ".UEECModel::DB.", ".
                        UEModel::DB.", ".
                        self::DB.
               " WHERE ".UEECModel::DBF_EC."=:EC AND ".
                         UEECModel::DBF_UE."=".UEModel::DBF_ID." AND ".
                         self::DBF_DIPLOME."=".UEModel::DBF_DIPLOME." AND ".
                         self::DBF_USER."=:user;";
                         
        if(($request = $DB->prepare($SQL)) && $request->execute([':EC' => $EC, ':user' => $user])) {
            $trouve = false;
            $i = 0;
            while(!$trouve && ($row = $request->fetch()))
                $trouve = UserModel::estRespDiplome($row[UEModel::DBF_DIPLOME]);
            return $trouve;
        }
        else
            return false;
    }  
    
    /**
     * Retourne la liste des diplômes dont l'utilisateur est responsable.
     * @param id l'identifiant de l'utilisateur
     */
    public static function getList(int $id) : array {
        $DB = MyPDO::getInstance();
        
        $SQL = "SELECT ".DiplomeModel::DBF_ID." as id, ".
                         DiplomeModel::DBF_INTITULE." as intitule, ".
                         DiplomeModel::DBF_NBSEMESTRES." as nbSemestres".
               " FROM ".DiplomeModel::DB.", ".self::DB.
               " WHERE ".DiplomeModel::DBF_ID."=".self::DBF_DIPLOME." AND ".
                         self::DBF_USER."=:user".
               " ORDER BY ".DiplomeModel::DBF_INTITULE." ASC;";
        
        if(($request = $DB->prepare($SQL)) && $request->execute([':user' => $id])) {
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
     * Retourne la liste des responsables d'un diplôme
     * @param diplome l'identifiant du diplôme
     * @return la liste des utilisateurs ou null
     */
    public static function getListResponsables(int $diplome) : array {
        $DB = MyPDO::getInstance();
        
        $SQL = "SELECT ".self::DBF_USER." as id, ".
                         UserModel::DBF_NAME." as nom, ".
                         UserModel::DBF_FIRSTNAME." as prenom".
               " FROM ".self::DB.", ".UserModel::DB.
               " WHERE ".self::DBF_USER."=".UserModel::DBF_ID." AND ".
                         self::DBF_DIPLOME."=:diplome".
               " ORDER BY nom ASC, prenom ASC;";
        
        if(($request = $DB->prepare($SQL)) && $request->execute([':diplome' => $diplome])) {
            $result = [];
            while($row = $request->fetch())
                $result[] = $row;
            return $result;
        }
        else
            return null;
    }
    
    /**
     * Retourne la liste des identifiants de diplômes dont l'utilisateur est responsable
     * @param user l'identifiant de l'utilisateur
     * @return la liste des identifiants de diplôme ou null
     */
    public static function getListDiplomes(int $user) : array {
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
     * Ajoute un responsable de diplôme.
     * @param diplome l'identifiant du diplôme
     * @param user l'identifiant de l'utilisateur
     * @return 'true' en cas de réussite sinon 'false'
     */
    public static function ajouter(int $diplome, int $user) : bool {
        $DB = MyPDO::getInstance();
        
        $SQL = "INSERT INTO ".self::DB." (".self::DBF_DIPLOME.", ".self::DBF_USER.") ".
               "VALUES (:diplome, :user);";

        if(($requete = $DB->prepare($SQL)) &&
           $requete->execute([ ":diplome" => $diplome, ":user" => $user ])) {
            return true;
        }
        else
            return false;
    }
    
    /**
     * Supprime un responsable de diplôme.
     * @param diplome l'identifiant du diplôme
     * @param user l'identifiant de l'utilisateur (ou -1)
     * @return 'true' en cas de réussite sinon 'false'
     */
    public static function supprimer(int $diplome, int $user) : bool {
        $DB = MyPDO::getInstance();
        
        if($user != -1) {
            $SQL = "DELETE FROM ".self::DB.
                   " WHERE ".self::DBF_DIPLOME."=:diplome AND ".
                             self::DBF_USER."=:user;";
            $data = [ ':diplome' => $diplome, ':user' => $user ];
        }
        else {
            $SQL = "DELETE FROM ".self::DB.
                   " WHERE ".self::DBF_DIPLOME."=:diplome";
            $data = [ ':diplome' => $diplome ];
        }

        if(($requete = $DB->prepare($SQL)) && $requete->execute($data))
            return true;
        else
            return false;
    }
    
} // class RespDiplomeModel