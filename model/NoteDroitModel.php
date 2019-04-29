<?php
// *************************************************************************************************
// * Modèle pour les droits de saisie de notes
// *************************************************************************************************
class NoteDroitModel {
        
    // Constantes pour la table des droits de saisie de notes
    const DB = "inf_notedroit";                  // Table des intervenants de groupes d'EC
    const DBF_EPREUVE = "nod_epreuve";           // Identifiant de l'épreuve
    const DBF_USER = "nod_user";                 // Identifiant de l'utilisateur

    /**
     * Retourne la liste des intervenants avec leur droit pour chaque épreuve.
     * @param EC l'identifiant de l'EC
     * @return la liste des utilisateurs ou null
     */
    public static function getList(int $EC) : array {
        $DB = MyPDO::getInstance();

        $SQL = "SELECT ".UserModel::DBF_ID.", ".EpreuveModel::DBF_ID.
               " FROM ".self::DB.", ".UserModel::DB.", ".EpreuveModel::DB.
               " WHERE ".self::DBF_EPREUVE."=".EpreuveModel::DBF_ID." AND ".
                         self::DBF_USER."=".UserModel::DBF_ID." AND ".
                         EpreuveModel::DBF_EC."=:EC";

        if(($request = $DB->prepare($SQL)) && $request->execute([':EC' => $EC])) {
            $result = array();
                
            while($row = $request->fetch()) {
                if(!isset($result[$row[UserModel::DBF_ID]])) $result[$row[UserModel::DBF_ID]] = [];
                $result[$row[UserModel::DBF_ID]][] = $row[EpreuveModel::DBF_ID];
            }
            return $result;
        }
        else
            return null;
    }
    
    /**
     * Ajout le droit pour un utilisateur.
     * @param EC l'identifiant de l'EC
     * @return la liste des utilisateurs ou null
     */
    public static function activer(int $epreuve, int $user) : bool {
        $DB = MyPDO::getInstance();
        
        $SQL = "INSERT INTO ".self::DB." (".self::DBF_EPREUVE.", ".self::DBF_USER.") ".
               "VALUES (:epreuve, :user);";
        
        return (self::desactiver($epreuve, $user) && 
                ($requete = $DB->prepare($SQL)) && 
                $requete->execute([':epreuve' => $epreuve, ':user' => $user]));
    }

    /**
     * Retire le droit pour un utilisateur.
     * @param EC l'identifiant de l'EC
     * @return la liste des utilisateurs ou null
     */
    public static function desactiver(int $epreuve, int $user) : bool {
        $DB = MyPDO::getInstance();
        
        $SQL = "DELETE FROM ".self::DB.
               " WHERE ".self::DBF_EPREUVE."=:epreuve AND ".
                         self::DBF_USER."=:user;";
        
        return (($requete = $DB->prepare($SQL)) && $requete->execute([':epreuve' => $epreuve, ':user' => $user]));
    }
    
    /**
     * Retire le droit pour un utilisateur.
     * @param EC l'identifiant de l'EC
     * @return la liste des utilisateurs ou null
     */
    public static function hasDroit(int $epreuve, int $user) : bool {
        $DB = MyPDO::getInstance();
        
        $SQL = "SELECT * FROM ".self::DB.
               " WHERE ".self::DBF_EPREUVE."=:epreuve AND ".
                         self::DBF_USER."=:user;";
        
        if(($requete = $DB->prepare($SQL)) && $requete->execute([':epreuve' => $epreuve, ':user' => $user])) {
            return $requete->rowCount() > 0;
        }
        else
            return false;
    }
    
} // class NoteDroitModel