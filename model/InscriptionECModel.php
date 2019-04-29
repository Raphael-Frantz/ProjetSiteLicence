<?php
// *************************************************************************************************
// * Modèle pour les inscriptions des étudiants dans des ECs
// *************************************************************************************************
class InscriptionECModel {

    // Constantes pour les types d'inscription
    const TYPE_NONINSCRIT = -1;
    const TYPE_INSCRIT = 1;
    const TYPE_VALIDE = 2;

    // Constantes pour la tables des inscriptions à un EC
    const DB = "inf_insec";                      // Table des inscriptions aux ECs
    const DBF_EC = "ine_ec";                     // Identifiant de l'EC
    const DBF_ETUDIANT = "ine_etudiant";         // Identifiant de l'étudiant
    const DBF_TYPE = "ine_type";                 // Type de l'inscription (constantes TYPE_*)
    const DBF_NOTE = "ine_note";                 // Note (en cas de validation)
    const DBF_BAREME = "ine_bareme";             // Barème (en cas de validation)

    /**
     * Retourne l'inscription d'un étudiant à un EC.
     * @param etudiant l'identifiant de l'étudiant
     * @param EC l'identifiant de l'EC
     * @return l'inscription ou un tableau vide en cas d'erreur
     */
    public static function getInscription(int $etudiant, int $EC) : array {
        $DB = MyPDO::getInstance();
        
        $SQL = "SELECT ".self::DBF_TYPE." as type, ".
                         self::DBF_NOTE." as note, ".
                         self::DBF_BAREME." as bareme".
               " FROM ".self::DB.
               " WHERE ".self::DBF_EC."=:EC AND ".self::DBF_ETUDIANT."=:etudiant;";
        if(($request = $DB->prepare($SQL)) && 
           $request->execute([ ':EC' => $EC, 'etudiant' => $etudiant])) {
            if($request->rowCount() == 0)
                return [ 'type' => self::TYPE_NONINSCRIT, 'note' => 0.0 ];
            else
                return $request->fetch();
        }
        else {
            return [];
        }
    }
    
    /**
     * Retourne la liste des étudiants inscrits à un EC.
     * @param EC l'identifiant de l'EC
     * @return la liste des étudiants ou un tableau vide en cas d'erreur
     */
    public static function getListeEtudiants(int $EC) : array {
        $DB = MyPDO::getInstance();
        
        $SQL = "SELECT ".UserModel::DBF_ID." as id, ".
                         EtudiantModel::DBF_NUMERO." as numero, ".
                         UserModel::DBF_NAME." as nom, ".
                         UserModel::DBF_FIRSTNAME." as prenom, ".
                         UserModel::DBF_MAIL." as email".
               " FROM ".self::DB.", ".UserModel::DB.", ".EtudiantModel::DB.
               " WHERE ".self::DBF_EC."=:EC AND ".
                         self::DBF_ETUDIANT."=".UserModel::DBF_ID." AND ".
                         EtudiantModel::DBF_USER."=".UserModel::DBF_ID." AND ".
                         self::DBF_TYPE."!=".self::TYPE_VALIDE.
               " ORDER BY ".UserModel::DBF_NAME." ASC, ".UserModel::DBF_FIRSTNAME." ASC";
        if(($request = $DB->prepare($SQL)) && 
           $request->execute([ ':EC' => $EC])) {
            $result = [];
            while($row = $request->fetch())
                $result[] = $row;
            return $result;
        }
        else {
            return null;
        }
    }
    
    /**
     * Récupère les identifiants des étudiants inscrits à un EC.
     * @param EC l'identifiant de l'EC
     * @param[in,out] liste la liste des étudiants (numéro, nom, prénom et email)
     *                      ajoute 'id' et 'statut' (EXISTE, INEXISTANT)
     */
    public static function updateList(int $EC, array &$liste) : void {
        $DB = MyPDO::getInstance();
        
        $SQL = "SELECT ".EtudiantModel::DBF_USER.
               " FROM ".EtudiantModel::DB.", ".self::DB.
               " WHERE ".EtudiantModel::DBF_NUMERO."=:numero AND ".
                         EtudiantModel::DBF_USER."=".self::DBF_ETUDIANT." AND ".
                         self::DBF_EC."=:EC AND ".
                         self::DBF_TYPE."!=".self::TYPE_VALIDE.";";

        if($requete1 = $DB->prepare($SQL)) {
            for($i = 0; $i < count($liste); $i++) {
                if($requete1->execute([ ':numero' => $liste[$i]['numero'], ':EC' => $EC]) &&
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
    
    /**
     * Retourne la liste des inscriptions des ECs d'un étudiant.
     * @param etudiant l'identifiant de l'étudiant
     * @param diplome l'identifiant du diplome
     * @param semestre le numéro du semestre
     * @param semestreIns le numéro du semestre pour les ECs
     * @return la liste des étudiants ou un tableau vide en cas d'erreur
     */
    public static function getListeInscriptionsEtudiant(int $etudiant) : array {
        $DB = MyPDO::getInstance();
        
        $SQL = "SELECT ".DiplomeModel::DBF_ID." as idDiplome, ".
                         DiplomeModel::DBF_INTITULE." as diplome, ".
                         DiplomeModel::DBF_MINSEMESTRE." as minSemestre, ".
                         UEModel::DBF_SEMESTRE." as semestre, ".
                         ECModel::DBF_ID." as idEC, ".
                         ECModel::DBF_CODE." as code, ".
                         ECModel::DBF_INTITULE." as intitule, ".
                         self::DBF_TYPE." as type, ".
                         self::DBF_NOTE." as note, ".
                         self::DBF_BAREME." as bareme".
               " FROM ".DiplomeModel::DB.", ".UEModel::DB.", ".ECModel::DB.", ".UEECModel::DB.", ".self::DB.", ".
                        InscriptionDiplomeModel::DB.
               " WHERE ".InscriptionDiplomeModel::DBF_ETUDIANT."=:etudiant AND ".
                         InscriptionDiplomeModel::DBF_DIPLOME."=".DiplomeModel::DBF_ID." AND ".
                         UEModel::DBF_DIPLOME."=".DiplomeModel::DBF_ID." AND ".
                         UEECModel::DBF_EC."=".ECModel::DBF_ID." AND ".
                         UEECModel::DBF_UE."=".UEModel::DBF_ID." AND ".
                         self::DBF_EC."=".UEECModel::DBF_EC." AND ".
                         self::DBF_ETUDIANT."=:etudiant".
               " GROUP BY diplome, semestre, code".
               " ORDER BY ".DiplomeModel::DBF_INTITULE." ASC, ".
                            UEModel::DBF_SEMESTRE." ASC, ".
                            UEModel::DBF_POSITION." ASC, ".
                            UEECModel::DBF_POSITION." ASC";

         if(($request = $DB->prepare($SQL)) && 
           $request->execute([ 'etudiant' => $etudiant])) {
            $result = [];
            while($row = $request->fetch())
                $result[] = $row;
            return $result;
        }
        else {
            return [];
        }
    }
    
    /**
     * Retourne la liste des inscriptions des ECs d'un étudiant (ou null si non inscrit).
     * @param etudiant l'identifiant de l'étudiant
     * @param diplome l'identifiant du diplome
     * @param semestre le numéro du semestre
     * @param semestreIns le numéro du semestre pour les ECs
     * @return la liste des étudiants ou un tableau vide en cas d'erreur
     */
    public static function getListeInscriptionsEtudiantComplete(int $etudiant) : array {
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
                           DiplomeModel::DB.
                   " WHERE ".UEModel::DBF_DIPLOME."=".DiplomeModel::DBF_ID." AND ".
                             ECModel::DBF_ID."=".UEECModel::DBF_EC." AND ".
                             UEModel::DBF_ID."=".UEECModel::DBF_UE." AND ".
                             InscriptionDiplomeModel::DBF_DIPLOME."=".UEModel::DBF_DIPLOME." AND ".
                             InscriptionDiplomeModel::DBF_SEMESTRE."=".UEModel::DBF_SEMESTRE." AND ".
                             InscriptionDiplomeModel::DBF_ETUDIANT."=:etudiant) AS A";
                             
        // Récupération des IPs
        $SQL_IP = "(SELECT * FROM ".self::DB.", ".ECModel::DB.
                  " WHERE ".self::DBF_EC."=".ECModel::DBF_ID." AND ".
                            self::DBF_ETUDIANT."=:etudiant) AS B ".
                  "ON "."B.".ECModel::DBF_ID."=A.".ECModel::DBF_ID;
                  
        $SQL = "SELECT ".DiplomeModel::DBF_ID." as idDiplome, ".
                         DiplomeModel::DBF_INTITULE." as diplome, ".
                         DiplomeModel::DBF_MINSEMESTRE." as minSemestre, ".
                         UEModel::DBF_SEMESTRE." as semestre, ".
                         "A.".ECModel::DBF_ID." as idEC, ".
                         "A.".ECModel::DBF_CODE." as code, ".
                         "A.".ECModel::DBF_INTITULE." as intitule, ".
                         "B.".self::DBF_TYPE." as type, ".
                         "B.".self::DBF_NOTE." as note, ".
                         "B.".self::DBF_BAREME." as bareme".
               " FROM ($SQL_etu LEFT OUTER JOIN $SQL_IP)";

        if(($request = $DB->prepare($SQL)) && 
           $request->execute([ 'etudiant' => $etudiant])) {
            $result = [];
            while($row = $request->fetch())
                $result[] = $row;
            return $result;
        }
        else {
            return [];
        }
    }
    
    /**
     * Retourne la liste des étudiants avec leurs inscriptions dans leurs ECs.
     * @param diplome l'identifiant du diplome
     * @param semestre le numéro du semestre
     * @param semestreIns le numéro du semestre pour les ECs
     * @return la liste des étudiants ou un tableau vide en cas d'erreur
     */
    public static function getListeInscriptions(int $diplome, int $semestre, int $semestreIns) : array {
        $DB = MyPDO::getInstance();
        
        // Récupération des EC du diplome/semestre
        $SQL_ECs = "SELECT ".ECModel::DBF_ID." as id, ".ECModel::DBF_CODE." as code, ".ECModel::DBF_INTITULE." as intitule".
                   " FROM ".UEModel::DB.", ".ECModel::DB.", ".UEECModel::DB.
                   " WHERE ".UEECModel::DBF_EC."=".ECModel::DBF_ID." AND ".
                             UEECModel::DBF_UE."=".UEModel::DBF_ID." AND ".
                             UEModel::DBF_DIPLOME."=:diplome AND ".
                             UEModel::DBF_SEMESTRE."=:semestre".
                   " ORDER BY ".UEModel::DBF_POSITION." ASC, ".UEECModel::DBF_POSITION." ASC";
                
        // Récupération des étudiants avec leur inscription pour une EC
        $SQL_etu = "SELECT * ".
                   "FROM ".EtudiantModel::DB.", ".UserModel::DB.", ".
                           InscriptionDiplomeModel::DB.
                   " WHERE ".EtudiantModel::DBF_USER."=".UserModel::DBF_ID." AND ".
                             InscriptionDiplomeModel::DBF_ETUDIANT."=".EtudiantModel::DBF_USER." AND ".
                             InscriptionDiplomeModel::DBF_DIPLOME."=:diplome AND ".
                             InscriptionDiplomeModel::DBF_SEMESTRE."=:semestre";
        $SQL_ec = "SELECT * FROM ".self::DB." WHERE ".self::DBF_EC."=:EC";
        $SQL_ins = "SELECT ".EtudiantModel::DBF_USER.", ".
                             EtudiantModel::DBF_NUMERO.", ".
                             UserModel::DBF_NAME.", ".
                             UserModel::DBF_FIRSTNAME.", ".
                             self::DBF_TYPE.", ".
                             self::DBF_NOTE.", ".
                             self::DBF_BAREME.
                   " FROM ($SQL_etu) AS A LEFT OUTER JOIN ($SQL_ec) AS B".
                   " ON ".self::DBF_ETUDIANT."=".EtudiantModel::DBF_USER.
                   " ORDER BY ".UserModel::DBF_NAME." ASC, ".
                                UserModel::DBF_FIRSTNAME." ASC;";
        
        if(($requete_ECs = $DB->prepare($SQL_ECs)) && 
           ($requete_ins = $DB->prepare($SQL_ins)) &&
           ($requete_ECs->execute([":diplome" => $diplome, ":semestre" => $semestreIns]))) {
            $result = [ 'EC' => [], 'etudiants' => [], 'diplome' => $diplome, 'semestre' => $semestreIns ];
                
            while($EC = $requete_ECs->fetch()) {
                $result['EC'][] = $EC;
                
                if($requete_ins->execute([":diplome" => $diplome, ":semestre" => $semestre, ":EC" => $EC['id']])) {
                    while($etudiant = $requete_ins->fetch()) {
                        if(!isset($result['etudiants'][$etudiant[EtudiantModel::DBF_USER]])) {
                            $result['etudiants'][$etudiant[EtudiantModel::DBF_USER]] =
                                [ 'numero' => $etudiant[EtudiantModel::DBF_NUMERO], 
                                  'nom' => $etudiant[UserModel::DBF_NAME], 
                                  'prenom' => $etudiant[UserModel::DBF_FIRSTNAME],
                                  'EC' => [] ];
                        }
                        $result['etudiants'][$etudiant[EtudiantModel::DBF_USER]]['EC'][] = 
                            [ 'type' => $etudiant[self::DBF_TYPE], 
                              'note' => $etudiant[self::DBF_NOTE],
                              'bareme' => $etudiant[self::DBF_BAREME] ];
                    }
                }
            }
            return $result;
        }
        else
            return [];
    }
    
    /**
     * Retourne la liste des étudiants avec leurs inscriptions dans leurs ECs pour un export CSV.
     * @param diplome l'identifiant du diplome
     * @param semestre le numéro du semestre
     * @return la liste des étudiants ou un tableau vide en cas d'erreur
     */
    public static function getListeInscriptionsCSV(int $diplome, int $semestre) : array {
        $DB = MyPDO::getInstance();
        
        // Récupération des EC du diplome/semestre
        $modulo = $semestre % 2;
        $SQL_ECs = "SELECT ".ECModel::DBF_ID.", ".ECModel::DBF_CODE.", ".ECModel::DBF_INTITULE.
                   " FROM ".UEModel::DB.", ".ECModel::DB.", ".UEECModel::DB.
                   " WHERE ".UEECModel::DBF_EC."=".ECModel::DBF_ID." AND ".
                             UEECModel::DBF_UE."=".UEModel::DBF_ID." AND ".
                             UEModel::DBF_DIPLOME."=:diplome AND ".
                             "MOD(".UEModel::DBF_SEMESTRE.",2)=:modulo".
                   " ORDER BY ".UEModel::DBF_SEMESTRE." ASC, ".UEModel::DBF_POSITION." ASC, ".UEECModel::DBF_POSITION." ASC";
        
        // Récupération des étudiants avec leur inscription pour une EC
        $SQL_etu = "SELECT * ".
                   "FROM ".EtudiantModel::DB.", ".UserModel::DB.", ".
                           InscriptionDiplomeModel::DB.
                   " WHERE ".EtudiantModel::DBF_USER."=".UserModel::DBF_ID." AND ".
                             InscriptionDiplomeModel::DBF_ETUDIANT."=".EtudiantModel::DBF_USER." AND ".
                             InscriptionDiplomeModel::DBF_DIPLOME."=:diplome AND ".
                             InscriptionDiplomeModel::DBF_SEMESTRE."=:semestre";
        $SQL_ec = "SELECT * FROM ".self::DB." WHERE ".self::DBF_EC."=:EC";
        $SQL_ins = "SELECT ".EtudiantModel::DBF_USER.", ".
                             EtudiantModel::DBF_NUMERO.", ".
                             UserModel::DBF_NAME.", ".
                             UserModel::DBF_FIRSTNAME.", ".
                             UserModel::DBF_MAIL.", ".
                             self::DBF_TYPE.", ".
                             self::DBF_NOTE.", ".
                             self::DBF_BAREME.
                   " FROM ($SQL_etu) AS A LEFT OUTER JOIN ($SQL_ec) AS B".
                   " ON ".self::DBF_ETUDIANT."=".EtudiantModel::DBF_USER.
                   " ORDER BY ".UserModel::DBF_NAME." ASC, ".
                                UserModel::DBF_FIRSTNAME." ASC;";
        
        if(($requete_ECs = $DB->prepare($SQL_ECs)) && 
           ($requete_ins = $DB->prepare($SQL_ins)) &&
           ($requete_ECs->execute([":diplome" => $diplome, ':modulo' => $modulo]))) {
            $result = [ 'header' => [ 'numero' => 'numero', 'nom' => 'nom', 'prenom' => 'prenom', 'email' => 'email' ], 
                        'etudiants' => [] ];
            
            $index = 0;
            while($EC = $requete_ECs->fetch()) {
                $result['header'][$EC[ECModel::DBF_CODE]] = $index;
                
                if($requete_ins->execute([":diplome" => $diplome, ":semestre" => $semestre, ":EC" => $EC[ECModel::DBF_ID]])) {
                    while($etudiant = $requete_ins->fetch()) {
                        if(!isset($result['etudiants'][$etudiant[EtudiantModel::DBF_USER]])) {
                            $result['etudiants'][$etudiant[EtudiantModel::DBF_USER]] =
                                [ 'numero' => $etudiant[EtudiantModel::DBF_NUMERO], 
                                  'nom' => $etudiant[UserModel::DBF_NAME], 
                                  'prenom' => $etudiant[UserModel::DBF_FIRSTNAME],
                                  'email' => $etudiant[UserModel::DBF_MAIL] ];
                        }
                        switch($etudiant[self::DBF_TYPE]) {
                            case null:
                                $result['etudiants'][$etudiant[EtudiantModel::DBF_USER]][$index] = "";
                                break;
                            case self::TYPE_INSCRIT:
                                $result['etudiants'][$etudiant[EtudiantModel::DBF_USER]][$index] = "X";
                                break;
                            case self::TYPE_VALIDE:
                                $result['etudiants'][$etudiant[EtudiantModel::DBF_USER]][$index] = sprintf("%.2f",$etudiant[self::DBF_NOTE])."/".$etudiant[self::DBF_BAREME];
                        }
                    }
                }
                $index++;
            }
            return $result;
        }
        else
            return [];
    }
    
    /**
     * Inscrire/désincrire un étudiant à un EC donné.
     * @param EC l'identifiant de l'EC
     * @param etudiant l'identifiant de l'étudiant
     * @param type le type d'inscription (TYPE_NONINSCRIT pour la desincription)
     * @param note la note (en cas de validation)
     * @param bareme le bareme (en cas de validation)
     * @return 'true' on success or 'false' on error
     */
    public static function inscrire(int $EC, int $etudiant, int $type, float $note = 0.0, float $bareme = 20.0) : bool {
        $DB = MyPDO::getInstance();
        
        // Est-il déjà inscrit à cet EC ? Si oui => supprimer !
        $SQL1 = "DELETE ".self::DB." FROM ".self::DB." WHERE ".
                self::DBF_EC."=:EC AND ".
                self::DBF_ETUDIANT."=:etudiant";
                
        // Inscription à l'EC si le type est différent de -1 !
        $SQL2 = "INSERT INTO ".self::DB." (".self::DBF_EC.", ".self::DBF_ETUDIANT.
                ", ".self::DBF_TYPE.", ".self::DBF_NOTE.", ".self::DBF_BAREME.
                ") VALUES (:EC, :etudiant, :type, :note, :bareme);";
        
        if(($requete1 = $DB->prepare($SQL1)) &&
           $requete1->execute([':etudiant' => $etudiant, ':EC' => $EC ])) {
            if($type != self::TYPE_NONINSCRIT) {
                if(($requete2 = $DB->prepare($SQL2)) &&
                   $requete2->execute([':EC' => $EC, ':etudiant' => $etudiant, ':type' => $type, 'note' => $note, 'bareme' => $bareme ]))
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
    
} // class InscriptionECModel