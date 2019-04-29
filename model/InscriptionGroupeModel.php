<?php
// *************************************************************************************************
// * Modèle pour les inscriptions des étudiants dans un groupe
// *************************************************************************************************
class InscriptionGroupeModel {
        
    // Constantes pour la tables des inscriptions à un diplôme
    const DB = "inf_insgro";                     // Table des inscriptions aux groupes
    const DBF_GROUPE = "ing_groupe";             // Identifiant du groupe
    const DBF_ETUDIANT = "ing_etudiant";         // Identifiant de l'étudiant

    /**
     * Retourne la liste des étudiants d'un groupe.
     * @param groupe l'identifiant du groupe
     * @return la liste des étudiants ou null en cas d'erreur
     */
    public static function getListe(int $groupe) : array {
        $DB = MyPDO::getInstance();
        
        $SQL = "SELECT ".UserModel::DBF_ID." as id, ".
                         EtudiantModel::DBF_NUMERO." as numero, ".
                         UserModel::DBF_NAME." as nom, ".
                         UserModel::DBF_FIRSTNAME." as prenom".
               " FROM ".UserModel::DB.", ".
                        EtudiantModel::DB.", ".
                        self::DB.
               " WHERE ".UserModel::DBF_ID."=".EtudiantModel::DBF_USER." AND ".
                         UserModel::DBF_ID."=".self::DBF_ETUDIANT." AND ".
                         self::DBF_GROUPE."=:groupe".
               " ORDER BY ".UserModel::DBF_NAME." ASC, ".
                            UserModel::DBF_FIRSTNAME." ASC;";

        if(($requete = $DB->prepare($SQL)) && $requete->execute([":groupe" => $groupe])) {
            $result = array();
                
            while($row = $requete->fetch()) {
                $result[] = $row;
            }
            return $result;
        }
        else
            return [];
    }
    
    /**
     * Retourne la liste des étudiants non inscrit d'un groupe.
     * @param diplome l'identifiant du diplôme
     * @param semestre le semestre
     * @param groupe l'identifiant du groupe
     * @param type le type du groupe
     * @return la liste des étudiants ou null en cas d'erreur
     */
    public static function getListeNonInscrits(int $diplome, int $semestre, int $groupe, int $type) : array {
        $DB = MyPDO::getInstance();
        
        $SQL_etu = "(SELECT * FROM `".UserModel::DB."`, `".
                                      EtudiantModel::DB."`, `".
                                      InscriptionDiplomeModel::DB."` WHERE `".
                   UserModel::DBF_ID."`=`".EtudiantModel::DBF_USER."` AND `".
                   UserModel::DBF_ID."`=`".InscriptionDiplomeModel::DBF_ETUDIANT."` AND `".
                   InscriptionDiplomeModel::DBF_DIPLOME."`=:diplome AND `".
                   InscriptionDiplomeModel::DBF_SEMESTRE."`=:semestre) AS A";
        $SQL_grp = "(SELECT * FROM `".self::DB."` LEFT OUTER JOIN `".GroupeModel::DB."` ".
                   "ON `".self::DBF_GROUPE."`=`".GroupeModel::DBF_ID.
                   "` WHERE `".GroupeModel::DBF_TYPE."`=:type AND `".
                   GroupeModel::DBF_SEMESTRE."`=:semestre) AS B ".
                   "ON B.`".self::DBF_ETUDIANT."`=A.`".UserModel::DBF_ID."`";
                  
        $SQL = "SELECT `".EtudiantModel::DBF_USER."` AS id, `".
                          EtudiantModel::DBF_NUMERO."` AS numero, `".
                          UserModel::DBF_NAME."` AS nom, `".
                          UserModel::DBF_FIRSTNAME."` AS prenom FROM ".
               "($SQL_etu LEFT OUTER JOIN $SQL_grp)".
               " WHERE ".GroupeModel::DBF_ID." IS NULL ".
               " ORDER BY nom ASC, prenom ASC;";

        if(($requete = $DB->prepare($SQL)) && $requete->execute([":diplome" => $diplome, ":semestre" => $semestre, 
                                                                 ":groupe" => $groupe, ":type" => $type])) {
            $result = [];
                
            while($row = $requete->fetch())
                $result[] = $row;
            return $result;
        }
        else
            return [];
    }
    
    /**
     * Retourne la liste des groupes d'un étudiant.
     * @param etudiant l'identifiant de l'étudiant
     * @return la liste des groupes de l'étudiant
     */
    public static function getListeGroupes(int $etudiant) : array {
        $DB = MyPDO::getInstance();
        
        $SQL = "SELECT ".GroupeModel::DBF_ID." as idGroupe, ".
                         GroupeModel::DBF_INTITULE." as intitule, ".
                         GroupeModel::DBF_TYPE." as type, ".
                         GroupeModel::DBF_SEMESTRE." as semestre, ".
                         DiplomeModel::DBF_ID." as idDiplome, ".
                         DiplomeModel::DBF_INTITULE." as diplome, ".
                         DiplomeModel::DBF_MINSEMESTRE." as minSemestre".
               " FROM ".self::DB.", ".GroupeModel::DB.", ".DiplomeModel::DB.
               " WHERE ".self::DBF_ETUDIANT."=:etudiant AND ".
                         self::DBF_GROUPE."=".GroupeModel::DBF_ID." AND ".
                         GroupeModel::DBF_DIPLOME."=".DiplomeModel::DBF_ID.
               " ORDER BY ".DiplomeModel::DBF_INTITULE." ASC, ".
                            GroupeModel::DBF_SEMESTRE." ASC, ".
                            GroupeModel::DBF_TYPE." ASC;";
        
        if(($requete = $DB->prepare($SQL)) && $requete->execute([":etudiant" => $etudiant])) {
            $result = [];
            
            while($row = $requete->fetch())
                $result[] = $row;
            return $result;
        }
        else
            return [];
    }
    
    /**
     * Retourne la liste des groupes d'un étudiant (null si pas de groupe associé).
     * @param etudiant l'identifiant de l'étudiant
     * @return la liste des groupes de l'étudiant
     */
    public static function getListeGroupesComplete(int $etudiant, bool $tuteur = false) : array {
        $DB = MyPDO::getInstance();
        
        $SQL_etu = "(SELECT * FROM ".InscriptionDiplomeModel::DB.", ".DiplomeModel::DB." WHERE ".
                   DiplomeModel::DBF_ID."=".InscriptionDiplomeModel::DBF_DIPLOME." AND ".
                   InscriptionDiplomeModel::DBF_ETUDIANT."=:etudiant) AS A";
        $SQL_tuteurs = "(SELECT CONCAT(".UserModel::DBF_NAME.", ' ', ".UserModel::DBF_FIRSTNAME.") as tuteur, ".
                                InscriptionTuteurModel::DBF_ETUDIANT.
                       " FROM ".InscriptionTuteurModel::DB.", ".UserModel::DB.
                       " WHERE ".InscriptionTuteurModel::DBF_DIPLOME."=A.".InscriptionDiplomeModel::DBF_DIPLOME." AND ".
                                 UserModel::DBF_ID."=".InscriptionTuteurModel::DBF_TUTEUR." AND ".
                                 InscriptionTuteurModel::DBF_ETUDIANT."=:etudiant) AS T".
                       " ON A.".InscriptionDiplomeModel::DBF_ETUDIANT."=T.".InscriptionTuteurModel::DBF_ETUDIANT;        
        $SQL_CM = "(SELECT * FROM ".self::DB.", ".GroupeModel::DB.
                  " WHERE ".self::DBF_GROUPE."=".GroupeModel::DBF_ID." AND ".
                            GroupeModel::DBF_TYPE."=".Groupe::GRP_CM." AND ".
                            self::DBF_ETUDIANT."=:etudiant) AS B ".
                  "ON "."B.".GroupeModel::DBF_DIPLOME." IS NULL OR (".
                        "B.".GroupeModel::DBF_DIPLOME."=A.".InscriptionDiplomeModel::DBF_DIPLOME." AND ".
                        "B.".GroupeModel::DBF_SEMESTRE."=A.".InscriptionDiplomeModel::DBF_SEMESTRE.")";
        $SQL_TD = "(SELECT * FROM ".self::DB.", ".GroupeModel::DB.
                  " WHERE ".self::DBF_GROUPE."=".GroupeModel::DBF_ID." AND ".
                            GroupeModel::DBF_TYPE."=".Groupe::GRP_TD." AND ".
                            self::DBF_ETUDIANT."=:etudiant) AS C ".
                  "ON "."C.".GroupeModel::DBF_DIPLOME." IS NULL OR (".
                        "C.".GroupeModel::DBF_DIPLOME."=A.".InscriptionDiplomeModel::DBF_DIPLOME." AND ".
                        "C.".GroupeModel::DBF_SEMESTRE."=A.".InscriptionDiplomeModel::DBF_SEMESTRE.")";
        $SQL_TP = "(SELECT * FROM ".self::DB.", ".GroupeModel::DB.
                  " WHERE ".self::DBF_GROUPE."=".GroupeModel::DBF_ID." AND ".
                            GroupeModel::DBF_TYPE."=".Groupe::GRP_TP." AND ".
                            self::DBF_ETUDIANT."=:etudiant) AS D ".
                  "ON "."D.".GroupeModel::DBF_DIPLOME." IS NULL OR (".
                        "D.".GroupeModel::DBF_DIPLOME."=A.".InscriptionDiplomeModel::DBF_DIPLOME." AND ".
                        "D.".GroupeModel::DBF_SEMESTRE."=A.".InscriptionDiplomeModel::DBF_SEMESTRE.")";
                  
        $SQL = "SELECT A.".InscriptionDiplomeModel::DBF_DIPLOME." as idDiplome, ".
                      "A.".DiplomeModel::DBF_INTITULE." as diplome, ".
                      "A.".DiplomeModel::DBF_MINSEMESTRE." as minSemestre, ".
                      "A.".InscriptionDiplomeModel::DBF_SEMESTRE." as semestre, ";
        if($tuteur) $SQL .= "tuteur, ";
        $SQL .=          "B.".GroupeModel::DBF_ID." AS groupeCM, ".
                         "B.".GroupeModel::DBF_INTITULE." AS intituleCM, ".
                         "C.".GroupeModel::DBF_ID." AS groupeTD, ".
                         "C.".GroupeModel::DBF_INTITULE." AS intituleTD, ".
                         "D.".GroupeModel::DBF_ID." AS groupeTP, ".
                         "D.".GroupeModel::DBF_INTITULE." AS intituleTP".
               " FROM ".
               "($SQL_etu ";
        if($tuteur)
            $SQL .= "LEFT OUTER JOIN $SQL_tuteurs ";
        $SQL .= " LEFT OUTER JOIN $SQL_CM LEFT OUTER JOIN $SQL_TD LEFT OUTER JOIN $SQL_TP)".
               " ORDER BY diplome ASC, semestre ASC;";
               
        if(($requete = $DB->prepare($SQL)) && ($requete->execute([":etudiant" => $etudiant]))) {
            $result = array();
                
            while($row = $requete->fetch()) {
                $result[] = $row;
            }
            return $result;
        }
        else
            return [];
    }
    
    /**
     * Retourne la liste des étudiants avec leurs inscriptions dans leurs groupes.
     * @param diplome l'identifiant du diplome
     * @param semestre le numéro du semestre (ou -1 pour tous)
     * @param tuteur si 'true' affiche le tuteur
     * @return la liste des étudiants ou null en cas d'erreur
     */
    public static function getListeInscriptions(int $diplome, int $semestre, bool $tuteur = false) : array {
        $DB = MyPDO::getInstance();
        
        $SQL_etu = "(SELECT * FROM ".UserModel::DB.", ".
                                     EtudiantModel::DB.", ".
                                     InscriptionDiplomeModel::DB." WHERE ".
                   UserModel::DBF_ID."=".EtudiantModel::DBF_USER." AND ".
                   UserModel::DBF_ID."=".InscriptionDiplomeModel::DBF_ETUDIANT." AND ".
                   InscriptionDiplomeModel::DBF_DIPLOME."=:diplome AND ".
                   InscriptionDiplomeModel::DBF_SEMESTRE."=:semestre) AS A";
        $SQL_tuteurs = "(SELECT CONCAT(".UserModel::DBF_NAME.", ' ', ".UserModel::DBF_FIRSTNAME.") as tuteur, ".
                                InscriptionTuteurModel::DBF_ETUDIANT.
                       " FROM ".InscriptionTuteurModel::DB.", ".UserModel::DB.
                       " WHERE ".InscriptionTuteurModel::DBF_DIPLOME."=:diplome AND ".
                                 UserModel::DBF_ID."=".InscriptionTuteurModel::DBF_TUTEUR.") AS T".
                       " ON A.".UserModel::DBF_ID."=T.".InscriptionTuteurModel::DBF_ETUDIANT;        
        $SQL_CM = "(SELECT * FROM ".self::DB.", ".GroupeModel::DB.
                  " WHERE ".self::DBF_GROUPE."=".GroupeModel::DBF_ID." AND ".
                            GroupeModel::DBF_TYPE."=".Groupe::GRP_CM." AND ".
                            GroupeModel::DBF_DIPLOME."=:diplome AND ".
                            GroupeModel::DBF_SEMESTRE."=:semestre) AS B ".
                  "ON B.".self::DBF_ETUDIANT."=A.".UserModel::DBF_ID;
        $SQL_TD = "(SELECT * FROM ".self::DB.", ".GroupeModel::DB.
                  " WHERE ".self::DBF_GROUPE."=".GroupeModel::DBF_ID." AND ".
                            GroupeModel::DBF_TYPE."=".Groupe::GRP_TD." AND ".
                            GroupeModel::DBF_DIPLOME."=:diplome AND ".
                            GroupeModel::DBF_SEMESTRE."=:semestre) AS C ".
                  "ON C.".self::DBF_ETUDIANT."=A.".UserModel::DBF_ID;
        $SQL_TP = "(SELECT * FROM ".self::DB.", ".GroupeModel::DB.
                  " WHERE ".self::DBF_GROUPE."=".GroupeModel::DBF_ID." AND ".
                            GroupeModel::DBF_TYPE."=".Groupe::GRP_TP." AND ".
                            GroupeModel::DBF_DIPLOME."=:diplome AND ".
                            GroupeModel::DBF_SEMESTRE."=:semestre) AS D ".
                  "ON D.".self::DBF_ETUDIANT."=A.".UserModel::DBF_ID;
                  
        $SQL = "SELECT ".EtudiantModel::DBF_USER." AS id, ".
                         EtudiantModel::DBF_NUMERO." AS numero, ".
                         UserModel::DBF_NAME." AS nom, ".
                         UserModel::DBF_FIRSTNAME." AS prenom, ".
                         UserModel::DBF_MAIL." AS email, ";
        if($tuteur) $SQL .= "tuteur, ";
        $SQL .=          "B.".GroupeModel::DBF_ID." AS groupeCM, ".
                         "B.".GroupeModel::DBF_INTITULE." AS intituleCM, ".
                         "C.".GroupeModel::DBF_ID." AS groupeTD, ".
                         "C.".GroupeModel::DBF_INTITULE." AS intituleTD, ".
                         "D.".GroupeModel::DBF_ID." AS groupeTP, ".
                         "D.".GroupeModel::DBF_INTITULE." AS intituleTP".
               " FROM ".
               "($SQL_etu ";
        if($tuteur)
            $SQL .= "LEFT OUTER JOIN $SQL_tuteurs ";
        $SQL .= " LEFT OUTER JOIN $SQL_CM LEFT OUTER JOIN $SQL_TD LEFT OUTER JOIN $SQL_TP)".
               " ORDER BY nom ASC, prenom ASC;";

        if(($requete = $DB->prepare($SQL)) && ($requete->execute([":diplome" => $diplome, ":semestre" => $semestre]))) {
            $result = array();
                
            while($row = $requete->fetch()) {
                $result[] = $row;
            }
            return $result;
        }
        else
            return [];
    }
    
    /**
     * Inscrire un étudiant à un groupe donné.
     * @param groupe l'identifiant du groupe (ou -1 pour aucun)
     * @param etudiant l'identifiant de l'étudiant
     * @param diplome l'identifiant du diplôme
     * @param semestre le numéro du semestre
     * @param type le type de groupe
     * @return 'true' on success or 'false' on error
     */
    public static function inscrire(int $groupe, int $etudiant, int $diplome, int $semestre, int $type) : bool {
        $DB = MyPDO::getInstance();
        
        // Est-il déjà inscrit à un groupe de ce type ? Si oui => supprimer !
        $SQL1 = "DELETE `".self::DB."` FROM `".self::DB."`, `".GroupeModel::DB."` WHERE `".
                self::DBF_GROUPE."`=`".GroupeModel::DBF_ID."` AND `".
                self::DBF_ETUDIANT."`=:etudiant AND `".
                GroupeModel::DBF_DIPLOME."`=:diplome AND `".
                GroupeModel::DBF_SEMESTRE."`=:semestre AND `".
                GroupeModel::DBF_TYPE."`=:type";
                
        // Inscription au groupe si le groupe est différent de -1 !
        $SQL2 = "INSERT INTO `".self::DB."` (`".self::DBF_GROUPE."`, `".self::DBF_ETUDIANT.
                "`) VALUES (:groupe, :etudiant);";
        
        if(($requete1 = $DB->prepare($SQL1)) &&
           $requete1->execute([':etudiant' => $etudiant, ':diplome' => $diplome, ':semestre' => $semestre, ':type' => $type])) {
            if($groupe != -1) {
                if(($requete2 = $DB->prepare($SQL2)) &&
                   $requete2->execute([':groupe' => $groupe, ':etudiant' => $etudiant]))
                    return true;
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
     * Désinscrire un étudiant d'un groupe donné.
     * @param groupe l'identifiant du groupe
     * @param etudiant l'identifiant de l'étudiant
     * @return 'true' on success or 'false' on error
     */
    public static function desinscrire(int $groupe, int $etudiant) : bool {
        $DB = MyPDO::getInstance();
        
        $SQL1 = "DELETE FROM `".self::DB."` WHERE `".self::DBF_GROUPE."`=:groupe AND `".
                self::DBF_ETUDIANT."`=:etudiant LIMIT 1";
        
        if($requete1 = $DB->prepare($SQL1)) {
            return ($requete1->execute(array(":groupe" => $groupe, ":etudiant" => $etudiant)));
        }
        else
            return false;
    }    
    
} // class InscriptionGroupeModel