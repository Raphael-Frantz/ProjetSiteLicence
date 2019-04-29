<?php
// *************************************************************************************************
// * Modèle pour les inscriptions des étudiants dans un groupe d'EC
// *************************************************************************************************
class InscriptionGroupeECModel {
        
    // Constantes pour la tables des inscriptions à un diplôme
    const DB = "inf_insgroec";                   // Table des inscriptions aux groupes d'EC
    const DBF_GROUPE = "ige_groupe";             // Identifiant du groupe
    const DBF_ETUDIANT = "ige_etudiant";         // Identifiant de l'étudiant
  
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
                         UserModel::DBF_FIRSTNAME." as prenom, ".
                         UserModel::DBF_MAIL." as email ".
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
     * Retourne la liste des étudiants non inscrit d'un groupe d'EC mais inscrit dans d'autres groupes.
     * @param EC l'identifiant de l'EC
     * @param groupe l'identifiant du groupe
     * @param type le type du groupe
     * @return un tableau d'étudiant
     */
    public static function getListeNonInscritsGrp(int $EC, int $groupe, int $type) : ?array {
        $DB = MyPDO::getInstance();
        
        // Les étudiants inscrits dans l'EC
        $SQL = "SELECT ".UserModel::DBF_ID." as id, ".
                         EtudiantModel::DBF_NUMERO." as numero, ".
                         UserModel::DBF_NAME." as nom, ".
                         UserModel::DBF_FIRSTNAME." as prenom, ".
                         UserModel::DBF_MAIL." as email ".
                   " FROM ".UserModel::DB.", ".
                            EtudiantModel::DB.", ".
                            InscriptionECModel::DB.", ".
                            self::DB.", ".
                            GroupeECModel::DB.
                   " WHERE ".UserModel::DBF_ID."=".EtudiantModel::DBF_USER." AND ".
                             UserModel::DBF_ID."=".InscriptionECModel::DBF_ETUDIANT." AND ".
                             self::DBF_GROUPE."=".GroupeECModel::DBF_ID." AND ".
                             self::DBF_ETUDIANT."=".EtudiantModel::DBF_USER." AND ".
                             GroupeECModel::DBF_EC."=".InscriptionECModel::DBF_EC." AND ".
                             GroupeECModel::DBF_TYPE."=:type AND ".
                             InscriptionECModel::DBF_EC."=:EC AND ".
                             GroupeECModel::DBF_ID."!=:groupe".
                   " ORDER BY ".UserModel::DBF_NAME." ASC, ".UserModel::DBF_FIRSTNAME." ASC";
        
        if(($request = $DB->prepare($SQL)) && $request->execute([':EC' => $EC, 'groupe' => $groupe, 'type' => $type])) {
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
     * Retourne la liste des étudiants non inscrit d'un groupe d'EC et non inscrit dans un groupe du même type.
     * @param EC l'identifiant du diplôme
     * @param groupe l'identifiant du groupe
     * @param type le type du groupe
     * @return la liste des étudiants ou null en cas d'erreur
     */
    public static function getListeNonInscrits(int $EC, int $groupe, int $type) : array {
        $DB = MyPDO::getInstance();
        
        // Les étudiants inscrits dans l'EC
        $SQL_etu = "SELECT * FROM ".UserModel::DB.", ".
                                    EtudiantModel::DB.", ".
                                    InscriptionECModel::DB.
                   " WHERE ".UserModel::DBF_ID."=".EtudiantModel::DBF_USER." AND ".
                             UserModel::DBF_ID."=".InscriptionECModel::DBF_ETUDIANT." AND ".
                             InscriptionECModel::DBF_EC."=:EC AND ".
                             InscriptionECModel::DBF_TYPE."!=".InscriptionECModel::TYPE_VALIDE;
                             
        // Les groupes de l'EC du type défini
        $SQL_grp = "SELECT * ".
                   " FROM ".self::DB." LEFT OUTER JOIN ".GroupeECModel::DB." ".
                   " ON ".self::DBF_GROUPE."=".GroupeECModel::DBF_ID.
                   " WHERE ".GroupeECModel::DBF_TYPE."=:type AND ".
                             GroupeECModel::DBF_EC."=:EC";
                           
                   GroupeModel::DBF_SEMESTRE."`=:semestre";
                  
        $SQL = "SELECT ".EtudiantModel::DBF_USER." AS id, ".
                         EtudiantModel::DBF_NUMERO." AS numero, ".
                         UserModel::DBF_NAME." AS nom, ".
                         UserModel::DBF_FIRSTNAME." AS prenom FROM ".
               "(($SQL_etu) as A LEFT OUTER JOIN ($SQL_grp) as B ON B.".self::DBF_ETUDIANT."=A.".UserModel::DBF_ID.")".
               " WHERE ".GroupeECModel::DBF_ID." IS NULL ".
               " ORDER BY nom ASC, prenom ASC;";

        if(($requete = $DB->prepare($SQL)) && $requete->execute([":EC" => $EC, ":groupe" => $groupe, ":type" => $type])) {
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
     * Retourne la liste des groupes d'un étudiant.
     * @param etudiant l'identifiant de l'étudiant
     * @return la liste des groupes ou null en cas d'erreur
     */
    public static function getListeGroupes(int $etudiant) : array {
        $DB = MyPDO::getInstance();
        
        $SQL = "SELECT ".DiplomeModel::DBF_ID." as idDiplome, ".
                         DiplomeModel::DBF_INTITULE." as diplome, ".
                         DiplomeModel::DBF_MINSEMESTRE." as minSemestre, ".
                         UEModel::DBF_SEMESTRE." as semestre, ".
                         ECModel::DBF_ID." as idEC, ".
                         ECModel::DBF_CODE." as code, ".
                         "CONCAT('(',".ECModel::DBF_CODE.",') ',".ECModel::DBF_INTITULE.") as intitule, ".
                         GroupeECModel::DBF_ID." as idGrp, ".
                         GroupeECModel::DBF_INTITULE." as groupe, ".
                         GroupeECModel::DBF_TYPE." as type".
               " FROM ".DiplomeModel::DB.", ".ECModel::DB.", ".GroupeECModel::DB.
                        ", ".self::DB.", ".UEModel::DB.", ".UEECModel::DB.", ".InscriptionDiplomeModel::DB.
               " WHERE ".self::DBF_ETUDIANT."=:etudiant AND ".
                         self::DBF_GROUPE."=".GroupeECModel::DBF_ID." AND ".
                         GroupeECModel::DBF_EC."=".ECModel::DBF_ID." AND ".
                         UEECModel::DBF_EC."=".ECModel::DBF_ID." AND ".
                         UEECModel::DBF_UE."=".UEModel::DBF_ID." AND ".
                         UEModel::DBF_DIPLOME."=".DiplomeModel::DBF_ID." AND ".
                         DiplomeModel::DBF_ID."=".InscriptionDiplomeModel::DBF_DIPLOME." AND ".
                         self::DBF_ETUDIANT."=".InscriptionDiplomeModel::DBF_ETUDIANT.
               " GROUP BY diplome, semestre, code, idGrp".
               " ORDER BY ".DiplomeModel::DBF_INTITULE." ASC,".
                            UEModel::DBF_SEMESTRE." ASC, ".
                            UEModel::DBF_POSITION." ASC, ".
                            UEECModel::DBF_POSITION." ASC, ".
                            ECModel::DBF_INTITULE." ASC, ".
                            GroupeECModel::DBF_TYPE." ASC;";
        if(($requete = $DB->prepare($SQL)) && $requete->execute([":etudiant" => $etudiant])) {
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
     * Retourne la liste des groupes d'un étudiant (null si pas de groupe associé).
     * @param etudiant l'identifiant de l'étudiant
     * @return la liste des groupes ou null en cas d'erreur
     */
    public static function getListeGroupesComplete(int $etudiant) : array {
        $DB = MyPDO::getInstance();
        
        // Récupération de la liste des EC de chaque diplôme dans lequel est inscrit l'étudiant
        $SQL_etu = "(SELECT ".DiplomeModel::DBF_ID.", ".
                              DiplomeModel::DBF_INTITULE.", ".
                              DiplomeModel::DBF_MINSEMESTRE.", ".
                              UEModel::DBF_SEMESTRE.", ".
                              UEModel::DBF_POSITION.", ".
                              ECModel::DBF_ID.", ".
                              ECModel::DBF_CODE.", ".
                              ECModel::DBF_INTITULE.", ".
                              UEECModel::DBF_POSITION.", ".
                              InscriptionDiplomeModel::DBF_ETUDIANT.
                   " FROM ".InscriptionDiplomeModel::DB.", ".
                           UEModel::DB.", ".
                           ECModel::DB.", ".
                           UEECModel::DB.", ".
                           DiplomeModel::DB.", ".
                           InscriptionECModel::DB.
                   " WHERE ".UEModel::DBF_DIPLOME."=".DiplomeModel::DBF_ID." AND ".
                             ECModel::DBF_ID."=".UEECModel::DBF_EC." AND ".
                             UEModel::DBF_ID."=".UEECModel::DBF_UE." AND ".
                             InscriptionECModel::DBF_ETUDIANT."=:etudiant AND ".
                             InscriptionECModel::DBF_TYPE."=".InscriptionECModel::TYPE_INSCRIT." AND ".
                             InscriptionECModel::DBF_EC."=".ECModel::DBF_ID." AND ".
                             InscriptionDiplomeModel::DBF_DIPLOME."=".UEModel::DBF_DIPLOME." AND ".
                             InscriptionDiplomeModel::DBF_SEMESTRE."=".UEModel::DBF_SEMESTRE." AND ".
                             InscriptionDiplomeModel::DBF_ETUDIANT."=:etudiant) AS A";

        // Récupération des groupes de CM, TD et TP
        $SQL_CM = "(SELECT * FROM ".self::DB.", ".GroupeECModel::DB.
                  " WHERE ".self::DBF_GROUPE."=".GroupeECModel::DBF_ID." AND ".
                            GroupeECModel::DBF_TYPE."=".Groupe::GRP_CM." AND ".
                            self::DBF_ETUDIANT."=:etudiant) AS B ".
                  "ON "."B.".GroupeECModel::DBF_EC."=A.".ECModel::DBF_ID;
        $SQL_TD = "(SELECT * FROM ".self::DB.", ".GroupeECModel::DB.
                  " WHERE ".self::DBF_GROUPE."=".GroupeECModel::DBF_ID." AND ".
                            GroupeECModel::DBF_TYPE."=".Groupe::GRP_TD." AND ".
                            self::DBF_ETUDIANT."=:etudiant) AS C ".
                  "ON "."C.".GroupeECModel::DBF_EC."=A.".ECModel::DBF_ID;
        $SQL_TP = "(SELECT * FROM ".self::DB.", ".GroupeECModel::DB.
                  " WHERE ".self::DBF_GROUPE."=".GroupeECModel::DBF_ID." AND ".
                            GroupeECModel::DBF_TYPE."=".Groupe::GRP_TP." AND ".
                            self::DBF_ETUDIANT."=:etudiant) AS D ".
                  "ON "."D.".GroupeECModel::DBF_EC."=A.".ECModel::DBF_ID;
        
        $SQL = "SELECT ".DiplomeModel::DBF_ID." as idDiplome, ".
                         DiplomeModel::DBF_INTITULE." as diplome, ".
                         DiplomeModel::DBF_MINSEMESTRE." as minSemestre, ".
                         UEModel::DBF_SEMESTRE." as semestre, ".
                         UEModel::DBF_POSITION.", ".
                         ECModel::DBF_ID." as idEC, ".
                         ECModel::DBF_CODE." as code, ".
                         ECModel::DBF_INTITULE." as intitule, ".
                         UEECModel::DBF_POSITION.", ".
                         "B.".GroupeECModel::DBF_ID." as groupeCM, ".
                         "B.".GroupeECModel::DBF_INTITULE." as intituleCM, ".
                         "C.".GroupeECModel::DBF_ID." as groupeTD, ".
                         "C.".GroupeECModel::DBF_INTITULE." as intituleTD, ".
                         "D.".GroupeECModel::DBF_ID." as groupeTP, ".
                         "D.".GroupeECModel::DBF_INTITULE." as intituleTP".
               " FROM ".
               "($SQL_etu ";
        $SQL .= " LEFT OUTER JOIN $SQL_CM LEFT OUTER JOIN $SQL_TD LEFT OUTER JOIN $SQL_TP)".
                " ORDER BY diplome ASC, semestre ASC, ".UEModel::DBF_POSITION." ASC, ".UEECModel::DBF_POSITION." ASC";

        if(($requete = $DB->prepare($SQL)) && ($requete->execute([":etudiant" => $etudiant]))) {
            $result = [];
                
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
     * @param EC l'identifiant de l'EC
     * @return la liste des étudiants ou un tableau vide en cas d'erreur
     */
    public static function getListeInscriptions(int $EC) : array {
        $DB = MyPDO::getInstance();
        
        $SQL_etu = "(SELECT * FROM ".UserModel::DB.", ".
                                     EtudiantModel::DB.", ".
                                     InscriptionECModel::DB.
                   " WHERE ".UserModel::DBF_ID."=".EtudiantModel::DBF_USER." AND ".
                             UserModel::DBF_ID."=".InscriptionECModel::DBF_ETUDIANT." AND ".
                             InscriptionECModel::DBF_TYPE."=".InscriptionECModel::TYPE_INSCRIT." AND ".
                             InscriptionECModel::DBF_EC."=:ec) AS A";
        $SQL_CM = "(SELECT ".GroupeECModel::DBF_ID.", ".self::DBF_ETUDIANT.
                  " FROM ".self::DB.", ".GroupeECModel::DB.
                  " WHERE ".self::DBF_GROUPE."=".GroupeECModel::DBF_ID." AND ".
                            GroupeECModel::DBF_TYPE."=".Groupe::GRP_CM." AND ".
                            GroupeECModel::DBF_EC."=:ec) AS B".
                  " ON B.".self::DBF_ETUDIANT."=A.".UserModel::DBF_ID;
        $SQL_TD = "(SELECT ".GroupeECModel::DBF_ID.", ".self::DBF_ETUDIANT.
                  " FROM ".self::DB.", ".GroupeECModel::DB.
                  " WHERE ".self::DBF_GROUPE."=".GroupeECModel::DBF_ID." AND ".
                            GroupeECModel::DBF_TYPE."=".Groupe::GRP_TD." AND ".
                            GroupeECModel::DBF_EC."=:ec) AS C".
                  " ON C.".self::DBF_ETUDIANT."=A.".UserModel::DBF_ID;
        $SQL_TP = "(SELECT ".GroupeECModel::DBF_ID.", ".self::DBF_ETUDIANT.
                  " FROM ".self::DB.", ".GroupeECModel::DB.
                  " WHERE ".self::DBF_GROUPE."=".GroupeECModel::DBF_ID." AND ".
                            GroupeECModel::DBF_TYPE."=".Groupe::GRP_TP." AND ".
                            GroupeECModel::DBF_EC."=:ec) AS D".
                  " ON D.".self::DBF_ETUDIANT."=A.".UserModel::DBF_ID;
                  
        $SQL = "SELECT ".EtudiantModel::DBF_USER." AS id, ".
                         EtudiantModel::DBF_NUMERO." AS numero, ".
                         UserModel::DBF_NAME." AS nom, ".
                         UserModel::DBF_FIRSTNAME." AS prenom, ".
                         UserModel::DBF_MAIL." AS email, ".
                         "B.".GroupeECModel::DBF_ID." AS groupeCM, ".
                         "C.".GroupeECModel::DBF_ID." AS groupeTD, ".
                         "D.".GroupeECModel::DBF_ID." AS groupeTP FROM ".
               "($SQL_etu LEFT OUTER JOIN $SQL_CM LEFT OUTER JOIN $SQL_TD LEFT OUTER JOIN $SQL_TP)".
               " ORDER BY nom ASC, prenom ASC;";
        
        if(($requete = $DB->prepare($SQL)) && ($requete->execute([":ec" => $EC]))) {
            $result = [];
                
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
     * @param EC l'identifiant de l'EC
     * @param type le type de groupe
     * @return 'true' on success or 'false' on error
     */
    public static function inscrire(int $groupe, int $etudiant, int $EC, int $type) : bool {
        $DB = MyPDO::getInstance();
        
        // Est-il déjà inscrit à un groupe de ce type ? Si oui => supprimer !
        $SQL1 = "DELETE ".self::DB.
                " FROM ".self::DB.", ".GroupeECModel::DB.
                " WHERE ".self::DBF_GROUPE."=".GroupeECModel::DBF_ID." AND ".
                          self::DBF_ETUDIANT."=:etudiant AND ".
                          GroupeECModel::DBF_EC."=:EC AND ".
                          GroupeECModel::DBF_TYPE."=:type;";
                
        // Inscription au groupe si le groupe est différent de -1 !
        $SQL2 = "INSERT INTO ".self::DB." (".self::DBF_GROUPE.", ".self::DBF_ETUDIANT.
                ") VALUES (:groupe, :etudiant);";
        
        if(($requete1 = $DB->prepare($SQL1)) &&
           $requete1->execute([':etudiant' => $etudiant, ':EC' => $EC, ':type' => $type])) {
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
     * Inscrire une liste d'étudiants à un groupe donné.
     * @param groupeEC l'identifiant du groupe EC
     * @param groupe l'identifiant du groupe
     * @param EC l'identifiant de l'EC
     * @param type le type de groupe
     * @return 'true' on success or 'false' on error
     */
    public static function inscrireListeFromGroupe(int $groupeEC, int $groupe, int $EC, int $type) : bool {
        $DB = MyPDO::getInstance();
                          
        // Suppresion des étudiants du groupe des groupes de l'EC de même type
        $SQL1 = "DELETE ".self::DB.
                " FROM ".InscriptionGroupeModel::DB.", ".GroupeECModel::DB.", ".self::DB.
                " WHERE ".GroupeECModel::DBF_ID."=".self::DBF_GROUPE." AND ".
                          GroupeECModel::DBF_TYPE."=:type AND ".
                          GroupeECModel::DBF_EC."=:EC AND ".
                          InscriptionGroupeModel::DBF_GROUPE."=:groupe AND ".
                          InscriptionGroupeModel::DBF_ETUDIANT."=".self::DBF_ETUDIANT.";";
        
        // Inscription des étudiants du groupe
        $SQL2 = "INSERT INTO ".self::DB." (".self::DBF_GROUPE.", ".self::DBF_ETUDIANT.")".
                " SELECT :groupeEC, ".InscriptionGroupeModel::DBF_ETUDIANT.
                " FROM ".InscriptionGroupeModel::DB.", ".
                         InscriptionECModel::DB.
                " WHERE ".InscriptionGroupeModel::DBF_ETUDIANT."=".InscriptionECModel::DBF_ETUDIANT." AND ".
                          InscriptionECModel::DBF_EC."=:EC AND ".
                          InscriptionECModel::DBF_TYPE."!=".InscriptionECModel::TYPE_VALIDE." AND ".
                          InscriptionGroupeModel::DBF_GROUPE."=:groupe;";
        
        if(($requete1 = $DB->prepare($SQL1)) && ($requete2 = $DB->prepare($SQL2)) &&
            $requete1->execute([':EC' => $EC, ':type' => $type, ':groupe' => $groupe ]) &&
            $requete2->execute([':EC' => $EC, ':groupe' => $groupe, 'groupeEC' => $groupeEC]))
            return true;
        else
            return false;
    }
    
    /**
     * Inscrire une liste d'étudiants à un groupe donné depuis un groupe d'EC.
     * @param groupeEC l'identifiant du groupe EC
     * @param groupeOrigine l'identifiant du groupe d'EC d'origine
     * @param EC l'identifiant de l'EC
     * @param type le type de groupe
     * @return 'true' on success or 'false' on error
     */
    public static function inscrireListeFromGroupeEC(int $groupeEC, int $groupeOrigine, int $EC, int $type) : bool {
        $DB = MyPDO::getInstance();

        $SQL = "SELECT ".self::DBF_ETUDIANT." FROM ".self::DB.", ".InscriptionECModel::DB.
               " WHERE ".self::DBF_ETUDIANT."=".InscriptionECModel::DBF_ETUDIANT." AND ".
                         InscriptionECModel::DBF_EC."=:EC AND ".
                         self::DBF_GROUPE."=:groupeOrigine";
        if(($request1 = $DB->prepare($SQL)) &&
           $request1->execute([':EC' => $EC, ':groupeOrigine' => $groupeOrigine ])) {
            while($row = $request1->fetch()) {
                self::inscrire($groupeEC, $row[self::DBF_ETUDIANT], $EC, $type);
            }
            return true;
        }
        else
            return false;
    }
    
    /**
     * Désinscrire un étudiant d'un groupe d'EC donné.
     * @param groupe l'identifiant du groupe d'EC
     * @param etudiant l'identifiant de l'étudiant (ou -1 pour supprimer tous les étudiants)
     * @return 'true' on success or 'false' on error
     */
    public static function desinscrire(int $groupe, int $etudiant) : bool {
        $DB = MyPDO::getInstance();
        
        $data[':groupe'] = $groupe;
        if($etudiant != -1) {
            $SQL1 = "DELETE FROM ".self::DB.
                    " WHERE ".self::DBF_GROUPE."=:groupe AND ".
                              self::DBF_ETUDIANT."=:etudiant LIMIT 1;";
            $data[':etudiant'] = $etudiant;
        }
        else {
            $SQL1 = "DELETE FROM ".self::DB.
                    " WHERE ".self::DBF_GROUPE."=:groupe;";
        }
        
        if($requete1 = $DB->prepare($SQL1)) {
            return ($requete1->execute($data));
        }
        else
            return false;
    }
    
    /**
     * Récupère les identifiants des étudiants inscrits à un groupe d'EC.
     * @param groupe l'identifiant du groupe
     * @param[in,out] liste la liste des étudiants (numéro, nom, prénom et email)
     *                      ajoute 'id' et 'statut' (EXISTE, INEXISTANT)
     */
    public static function updateList(int $groupe, array &$liste) : void {
        $DB = MyPDO::getInstance();
        
        $SQL = "SELECT ".EtudiantModel::DBF_USER.
               " FROM ".EtudiantModel::DB.", ".self::DB.
               " WHERE ".EtudiantModel::DBF_NUMERO."=:numero AND ".
                         EtudiantModel::DBF_USER."=".self::DBF_ETUDIANT." AND ".
                         self::DBF_GROUPE."=:groupe;";

        if($requete1 = $DB->prepare($SQL)) {
            for($i = 0; $i < count($liste); $i++) {
                if($requete1->execute([ ':numero' => $liste[$i]['numero'], ':groupe' => $groupe]) &&
                   ($requete1->rowCount() == 0)) {
                    $liste[$i]['id'] = -1;
                    $liste[$i]['statut'] = 'INEXISTANT';
                }
                else {            
                    $row = $requete1->fetch();
                    $liste[$i]['id'] = $row[EtudiantModel::DBF_USER];
                    $liste[$i]['statut'] = 'EXISTE';
                }
            }
        }
    }
    
} // class InscriptionGroupeECModel