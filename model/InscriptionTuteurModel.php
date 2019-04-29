<?php
// *************************************************************************************************
// * Modèle pour les inscriptions des étudiants à un tuteur
// *************************************************************************************************
class InscriptionTuteurModel {
    
    // Constantes pour la tables des inscriptions à un tuteur
    const DB = "inf_instut";                     // Table des inscriptions aux tuteurs
    const DBF_ETUDIANT = "int_etudiant";         // Identifiant de l'étudiant
    const DBF_DIPLOME = "int_diplome";           // Identifiant du diplôme
    const DBF_TUTEUR = "int_tuteur";             // Identifiant du tuteur

    /**
     * Vérifie si un enseignant est tuteur d'un étudiant.
     * @param enseignant l'identifiant de l'enseignant
     * @param etudiant l'identifiant de l'étudiant
     * @return 'true' si oui, 'false' sinon
     */
    public static function estTuteur(int $enseignant, int $etudiant) : bool {
        $DB = MyPDO::getInstance();
        
        $SQL = "SELECT *".
               " FROM ".self::DB.
               " WHERE ".self::DBF_ETUDIANT."=:etudiant AND ".
                         self::DBF_TUTEUR."=:tuteur;";
                         
        return (($request = $DB->prepare($SQL)) && 
                $request->execute([':etudiant' => $etudiant, ':tuteur' => $enseignant]) &&
                ($request->rowCount() != 0));
    }
    
    /**
     * Retourne les tuteurs d'un étudiant (un par diplôme, potentiellement).
     * @param etudiant l'identifiant de l'étudiant
     * @return la liste des tuteurs ou null en cas d'erreur
     */
    public static function getTuteurs(int $etudiant) : array {
        $DB = MyPDO::getInstance();
        
        $SQL = "SELECT ".UserModel::DBF_ID." as id, ".
                         "CONCAT(".UserModel::DBF_NAME.", ' ',".UserModel::DBF_FIRSTNAME.") as nom, ".
                         UserModel::DBF_MAIL." as email, ".
                         DiplomeModel::DBF_INTITULE." as diplome".
               " FROM ".UserModel::DB.", ".self::DB.", ".DiplomeModel::DB.
               " WHERE ".UserModel::DBF_ID."=".self::DBF_TUTEUR." AND ".
                         self::DBF_ETUDIANT."=:etudiant AND ".
                         self::DBF_DIPLOME."=".DiplomeModel::DBF_ID." AND ".
                         self::DBF_TUTEUR."=".UserModel::DBF_ID.
               " ORDER BY ".DiplomeModel::DBF_INTITULE." ASC, nom ASC;";
        
        if(($request = $DB->prepare($SQL)) && $request->execute([':etudiant' => $etudiant])) {
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
     * Retourne la liste des étudiants d'un tuteur.
     * @param diplome l'identifiant du diplôme (ou de tous les diplômes)
     * @param tuteur l'identifiant du tuteur
     * @return la liste des étudiants ou null en cas d'erreur
     */
    public static function getListeEtudiantsInscrits(int $diplome, int $tuteur) : array {
        $DB = MyPDO::getInstance();
        
        $data[':tuteur'] = $tuteur;
        $SQL = "SELECT ".UserModel::DBF_ID." as id, ".
                         EtudiantModel::DBF_NUMERO." as numero, ".
                         "CONCAT(".UserModel::DBF_NAME.", ' ',".
                         UserModel::DBF_FIRSTNAME.") as nom, ".
                         UserModel::DBF_MAIL." as email ".
               " FROM ".EtudiantModel::DB.", ".UserModel::DB.", ".self::DB.
               " WHERE ".EtudiantModel::DBF_USER."=".UserModel::DBF_ID." AND ".
                         self::DBF_ETUDIANT."=".EtudiantModel::DBF_USER." AND ".
                         self::DBF_TUTEUR."=:tuteur";
        if($diplome != -1) {
            $SQL .= " AND ".self::DBF_DIPLOME."=:diplome";
            $data[':diplome'] = $diplome;
        }
        $SQL .= " GROUP BY ".UserModel::DBF_ID.
                " ORDER BY ".UserModel::DBF_NAME." ASC, ".UserModel::DBF_FIRSTNAME." ASC;";
        
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
     * Retourne la liste des étudiants avec leur tuteur.
     * @param diplome l'identifiant du diplôme
     * @param tuteur l'identifiant du tuteur
     * @return la liste des étudiants ou null en cas d'erreur
     */
    public static function getListeEtudiantsTuteurs(int $diplome) : array {
        $DB = MyPDO::getInstance();
                
        /*$SQL = "SELECT ".EtudiantModel::DBF_USER." as id, ".
                       "CONCAT(".UserModel::DBF_NAME.", ' ', ".UserModel::DBF_FIRSTNAME.") as nom, ".
                       EtudiantModel::DBF_NUMERO." as numero, ".
                       UserModel::DBF_MAIL." as email, ".
                       self::DBF_TUTEUR." as tuteur".
               " FROM ".
               "(SELECT * FROM ".EtudiantModel::DB.", ".UserModel::DB.", ".InscriptionDiplomeModel::DB.
               " WHERE ".EtudiantModel::DBF_USER."=".UserModel::DBF_ID." AND ".
                         InscriptionDiplomeModel::DBF_DIPLOME."=:diplome AND ".
                         InscriptionDiplomeModel::DBF_ANNEE."=2018 AND ".
                         InscriptionDiplomeModel::DBF_ETUDIANT."=".EtudiantModel::DBF_USER.
               " ORDER BY ".UserModel::DBF_NAME." ASC, ".UserModel::DBF_FIRSTNAME." ASC".
               ") AS A LEFT OUTER JOIN ".self::DB." ON A.".UserModel::DBF_ID."=".self::DBF_ETUDIANT.
               " ORDER BY nom ASC;";*/
               
        $SQL = "SELECT ".UserModel::DBF_ID." as id, ".
                         "CONCAT(".UserModel::DBF_NAME.", ' ', ".UserModel::DBF_FIRSTNAME.") as nom, ".
                         EtudiantModel::DBF_NUMERO." as numero, ".
                         UserModel::DBF_MAIL." as email, ".
                         self::DBF_TUTEUR." as tuteur".
               " FROM (".
               "(SELECT ".UserModel::DBF_ID.", ".UserModel::DBF_NAME.", ".UserModel::DBF_FIRSTNAME.", ".
                          EtudiantModel::DBF_NUMERO.", ".UserModel::DBF_MAIL.
               " FROM ".EtudiantModel::DB.", ".UserModel::DB.", ".InscriptionDiplomeModel::DB.
               " WHERE ".EtudiantModel::DBF_USER."=".UserModel::DBF_ID." AND ".
                         InscriptionDiplomeModel::DBF_DIPLOME."=:diplome AND ".
                         InscriptionDiplomeModel::DBF_ANNEE."=2018 AND ".
                         InscriptionDiplomeModel::DBF_ETUDIANT."=".EtudiantModel::DBF_USER.") AS A".               
               " LEFT OUTER JOIN".
               " (SELECT ".self::DBF_TUTEUR.", ".self::DBF_ETUDIANT." FROM ".self::DB." WHERE ".self::DBF_DIPLOME."=:diplome) AS B".
               " ON A.".UserModel::DBF_ID."=B.".self::DBF_ETUDIANT.
               ") GROUP BY nom ASC;";

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
     * Retourne la liste des étudiants qui n'ont pas de tuteur.
     * @param diplome l'identifiant du diplome
     * @return la liste des étudiants ou null en cas d'erreur
     */
    public static function getListeEtudiantsNonInscrits(int $diplome) : array {
        $DB = MyPDO::getInstance();
        
        $SQL = "SELECT A.".EtudiantModel::DBF_USER." as id, ".
                       "CONCAT(C.".UserModel::DBF_NAME.", ' ', ".
                       "C.".UserModel::DBF_FIRSTNAME.") as nom, ".
                       "A.".EtudiantModel::DBF_NUMERO." as numero, ".
                       "C.".UserModel::DBF_MAIL." as email ".
               " FROM ".EtudiantModel::DB.
               " AS A LEFT OUTER JOIN ".UserModel::DB." AS C ON C.".UserModel::DBF_ID."=A.".EtudiantModel::DBF_USER.
               " LEFT OUTER JOIN (SELECT * FROM ".EtudiantModel::DB.", ".self::DB." WHERE ".
               self::DBF_ETUDIANT."=".EtudiantModel::DBF_USER." AND ".self::DBF_DIPLOME."=:diplome";
        $SQL .= ") AS B ON A.".EtudiantModel::DBF_USER."=B.".EtudiantModel::DBF_USER.
                " WHERE ".self::DBF_DIPLOME." IS NULL".
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
     * Associer un tuteur à un étudiant.
     * @param diplome l'identifiant du diplôme
     * @param etudiant l'identifiant de l'étudiant
     * @param tuteur l'identifiant du tuteur
     * @return 'true' on success or 'false' on error
     */
    public static function inscrire(int $diplome, int $etudiant, int $tuteur) : bool {
        $DB = MyPDO::getInstance();
        
        $SQL1 = "DELETE FROM ".self::DB.
                " WHERE ".self::DBF_DIPLOME."=:diplome AND ".
                          self::DBF_ETUDIANT."=:etudiant;";
                
        $SQL2 = "INSERT INTO ".self::DB." (".self::DBF_DIPLOME.", ".self::DBF_ETUDIANT.", ".
                self::DBF_TUTEUR.") VALUES (:diplome, :etudiant, :tuteur);";
        
        if(($requete1 = $DB->prepare($SQL1)) && ($requete2 = $DB->prepare($SQL2))) {
            if($requete1->execute([":diplome" => $diplome, ":etudiant" => $etudiant]) &&
               $requete2->execute([":diplome" => $diplome, ":etudiant" => $etudiant, ":tuteur" => $tuteur]))
                return true;
            else
                return false;
        }
        else
            return false;
    }
    
    /**
     * Desassocier un étudiant d'un tuteur donné.
     * @param diplome l'identifiant du diplôme
     * @param etudiant l'identifiant de l'étudiant
     * @return 'true' on success or 'false' on error
     */
    public static function desinscrire(int $diplome, int $etudiant) : bool {
        $DB = MyPDO::getInstance();
        
        $SQL1 = "DELETE FROM ".self::DB.
                " WHERE ".self::DBF_DIPLOME."=:diplome AND ".
                          self::DBF_ETUDIANT."=:etudiant;";
        
        return (($requete1 = $DB->prepare($SQL1)) &&
                $requete1->execute([":diplome" => $diplome, ":etudiant" => $etudiant]));
    }        
    
} // class InscriptionTuteurModel