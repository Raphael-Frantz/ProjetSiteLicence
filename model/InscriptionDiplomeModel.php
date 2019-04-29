<?php
// *************************************************************************************************
// * Modèle pour les inscriptions des étudiants dans un diplôme
// *************************************************************************************************
class InscriptionDiplomeModel {
        
    // Constantes pour la tables des inscriptions à un diplôme
    const DB = "inf_insdip";                     // Table des inscriptions aux diplômes
    const DBF_ETUDIANT = "ind_etudiant";         // Identifiant de l'étudiant
    const DBF_DIPLOME = "ind_diplome";           // Identifiant du diplôme
    const DBF_SEMESTRE = "ind_semestre";         // Numéro du semestre
    const DBF_ANNEE = "ind_annee";               // Année d'inscription
    
    /**
     * Recherche un étudiant en fonction de son nom.
     * @param id l'identifiant du diplôme
     * @param nom le nom de l'étudiant
     * @return un tableau d'étudiant
     */
    public static function rechercher(int $id, string $nom) : ?array {
        $DB = MyPDO::getInstance();
        $SQL = "SELECT ".UserModel::DBF_ID." as id, ".
                         EtudiantModel::DBF_NUMERO." as numero, ".
                         UserModel::DBF_NAME." as nom, ".
                         UserModel::DBF_FIRSTNAME." as prenom, ".
                         UserModel::DBF_MAIL." as email ".
               " FROM ".EtudiantModel::DB.", ".UserModel::DB.", ".self::DB.
               " WHERE ".EtudiantModel::DBF_USER."=".UserModel::DBF_ID." AND ".
                         self::DBF_DIPLOME."=:diplome AND ".
                         self::DBF_ETUDIANT."=".UserModel::DBF_ID." AND ".
                         "(".UserModel::DBF_NAME." LIKE :nom OR ".
                             UserModel::DBF_FIRSTNAME." LIKE :nom)".
               " GROUP BY ".UserModel::DBF_ID.
               " ORDER BY ".UserModel::DBF_NAME." ASC, ".UserModel::DBF_FIRSTNAME." ASC".
               " LIMIT 10";
        
        if(($request = $DB->prepare($SQL)) && $request->execute([':nom' => "%$nom%", ':diplome' => $id])) {
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
     * Retourne la liste des étudiants inscrits dans un diplôme.
     * @param id l'identifiant du diplome
     * @param semestre le numéro du semestre (ou -1 pour tous)
     * @return la liste des étudiants ou null en cas d'erreur
     */
    public static function getListeEtudiantsInscrits(int $id, int $semestre = -1) : array {
        $DB = MyPDO::getInstance();
        
        $SQL = "SELECT ".UserModel::DBF_ID." as id, ".
                         EtudiantModel::DBF_NUMERO." as numero, ".
                         UserModel::DBF_NAME." as nom, ".
                         UserModel::DBF_FIRSTNAME." as prenom, ".
                         UserModel::DBF_MAIL." as email ".
               " FROM ".EtudiantModel::DB.", ".UserModel::DB.", ".self::DB.
               " WHERE ".EtudiantModel::DBF_USER."=".UserModel::DBF_ID." AND ".
                         self::DBF_ETUDIANT."=".EtudiantModel::DBF_USER." AND ".
                         self::DBF_DIPLOME."=:id";
        $data[':id'] = $id;
        if($semestre != -1) {
            $SQL .= " AND `".self::DBF_SEMESTRE."`=:semestre";
            $data[':semestre'] = $semestre;
        }
        $SQL .= " GROUP BY `".UserModel::DBF_ID."` ORDER BY `".UserModel::DBF_NAME."` ASC, `".UserModel::DBF_FIRSTNAME."` ASC;";
        
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
     * Retourne la liste des étudiants non inscrits dans un diplôme.
     * @param id l'identifiant du diplome
     * @param semestre le numéro du semestre (ou -1 pour tous)
     * @return la liste des étudiants ou null en cas d'erreur
     */
    public static function getListeEtudiantsNonInscrits(int $id, int $semestre = -1) : array {
        $DB = MyPDO::getInstance();
        
        $SQL = "SELECT A.".EtudiantModel::DBF_USER." as id, ".
                       "C.".UserModel::DBF_NAME." as nom, ".
                       "C.".UserModel::DBF_FIRSTNAME." as prenom, ".
                       "A.".EtudiantModel::DBF_NUMERO." as numero, ".
                       "C.".UserModel::DBF_MAIL." as email ".
               " FROM ".EtudiantModel::DB.
               " AS A LEFT OUTER JOIN ".UserModel::DB." AS C ON C.".UserModel::DBF_ID."=A.".EtudiantModel::DBF_USER.
               " LEFT OUTER JOIN (SELECT * FROM ".EtudiantModel::DB.", ".self::DB." WHERE ".
               self::DBF_ETUDIANT."=".EtudiantModel::DBF_USER." AND ".self::DBF_DIPLOME."=:id";
        $data[':id'] = $id;
        if($semestre != -1) {
            $SQL .= " AND ".self::DBF_SEMESTRE."=:semestre";
            $data[':semestre'] = $semestre;
        }   
        $SQL .= ") AS B ON A.".EtudiantModel::DBF_USER."=B.".EtudiantModel::DBF_USER.
                " WHERE ".self::DBF_DIPLOME." IS NULL".
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
     * Retourne la liste des diplômes auxquels est inscrit un étudiant.
     * @param id l'identifiant de l'étudiant
     */
    public static function getListDiplomeEtudiant(int $id) {
        $DB = MyPDO::getInstance();
        
        $SQL = "SELECT CONCAT(".UserModel::DBF_NAME.", \" \", ".UserModel::DBF_FIRSTNAME.") as nom,".
                       UserModel::DBF_MAIL." as email, ".
                       DiplomeModel::DBF_INTITULE." as diplome".
               " FROM ".UserModel::DB.", ".RespDiplomeModel::DB.", ".
               "(SELECT ".DiplomeModel::DBF_ID.", ".DiplomeModel::DBF_INTITULE.
               " FROM ".DiplomeModel::DB.", ".self::DB.
               " WHERE ".DiplomeModel::DBF_ID."=".self::DBF_DIPLOME." AND ".
                         self::DBF_ETUDIANT."=:id AND ".
                         self::DBF_ANNEE."=:annee".
               " GROUP BY ".DiplomeModel::DBF_ID.
               ") AS A".
               " WHERE ".UserModel::DBF_ID."=".RespDiplomeModel::DBF_USER." AND ".
                         RespDiplomeModel::DBF_DIPLOME."=".DiplomeModel::DBF_ID.
               " ORDER BY diplome ASC;";
               
        
        if(($request = $DB->prepare($SQL)) && $request->execute([':id' => $id, ':annee' => 2018])) {
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
     * Inscrire un étudiant à un diplôme donné.
     * @param diplome l'identifiant du diplôme
     * @param etudiant l'identifiant de l'étudiant
     * @param semestre le numéro du semestre
     * @return 'true' on success or 'false' on error
     */
    public static function inscrire(int $diplome, int $etudiant, int $semestre) : bool {
        $DB = MyPDO::getInstance();
        
        $SQL1 = "SELECT * FROM `".self::DB."` WHERE `".self::DBF_DIPLOME."`=:diplome AND `".
                self::DBF_ETUDIANT."`=:etudiant AND `".self::DBF_SEMESTRE."`=:semestre AND `".
                self::DBF_ANNEE."`=:annee";
                
        $SQL2 = "INSERT INTO `".self::DB."` (`".self::DBF_DIPLOME."`, `".self::DBF_ETUDIANT."`, `".
                self::DBF_SEMESTRE."`, `".self::DBF_ANNEE."`) VALUES (:diplome, :etudiant, :semestre, :annee);";
        
        if(($requete1 = $DB->prepare($SQL1)) && ($requete2 = $DB->prepare($SQL2))) {
            if($requete1->execute(array(":diplome" => $diplome, ":etudiant" => $etudiant, 
                                        ":semestre" => $semestre, ":annee" => 2018))
               && ($requete1->rowCount() == 0)) {
                return ($requete2->execute(array(":diplome" => $diplome, ":etudiant" => $etudiant, 
                                                 ":semestre" => $semestre, ":annee" => 2018)));
            }
            else
                return false;
        }
        else
            return false;
    }
    
    /**
     * Désinscrire un étudiant d'un diplôme donné.
     * @param diplome l'identifiant du diplôme
     * @param etudiant l'identifiant de l'étudiant
     * @param semestre le numéro du semestre
     * @return 'true' on success or 'false' on error
     */
    public static function desinscrire(int $diplome, int $etudiant, int $semestre) : bool {
        $DB = MyPDO::getInstance();
        
        $SQL1 = "DELETE FROM `".self::DB."` WHERE `".self::DBF_DIPLOME."`=:diplome AND `".
                self::DBF_ETUDIANT."`=:etudiant AND `".self::DBF_SEMESTRE."`=:semestre AND `".
                self::DBF_ANNEE."`=:annee LIMIT 1";
        
        if($requete1 = $DB->prepare($SQL1)) {
            return ($requete1->execute(array(":diplome" => $diplome, ":etudiant" => $etudiant,
                                             ":semestre" => $semestre, ":annee" => 2018)));
        }
        else
            return false;
    }    
    
} // class InscriptionDiplomeModel