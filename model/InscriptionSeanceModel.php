<?php
// *************************************************************************************************
// * Modèle pour les inscriptions aux séances.
// *************************************************************************************************
class InscriptionSeanceModel {
    
    // Constantes pour la table des séances
    const DB = "inf_insseance";                  // Table des tuteurs
    const DBF_SEANCE = "ins_seance";             // Identifiant séance
    const DBF_ETUDIANT = "ins_etudiant";         // Identifiant étudiant
    const DBF_TYPE = "ins_type";                 // Type (constante TYPE_*)
        
    const TYPE_NON_SPECIFIE = 0;                 // Non spécifié
    const TYPE_PRESENT = 1;                      // Présent
    const TYPE_ABSENT = 2;                       // Absent
    const TYPE_JUSTIFIE = 3;                     // Absence justifiée
    const TYPE_RATTRAPAGE = 4;                   // Rattrapage
    
    /**
     * Retourne la liste des présences des étudiants pour un groupe.
     * @param groupe l'identifiant du groupe
     * @param seance l'identifiant de la séance (ou -1 pour toutes les séances)
     * @return la liste des présences ou null
     */
    public static function getListePresencesGroupe(int $groupe, int $seance = -1) : ?array {
        $DB = MyPDO::getInstance();
        
        // Récupération de la liste des étudiants du groupe
        $etudiants = InscriptionGroupeECModel::getListe($groupe);
        
        // Sélection du présentiel correspondant au groupe
        $SQL = "SELECT ".self::DBF_ETUDIANT.", ".
                         UserModel::DBF_NAME.", ".
                         UserModel::DBF_FIRSTNAME.", ".
                         EtudiantModel::DBF_NUMERO.", ".
                         self::DBF_SEANCE.", ".
                         self::DBF_TYPE.", ".
                         SeanceModel::DBF_DEBUT.", ".
                         SeanceModel::DBF_FIN.
               " FROM ".self::DB.", ".SeanceModel::DB.", ".UserModel::DB.", ".EtudiantModel::DB.
               " WHERE ".self::DBF_SEANCE."=".SeanceModel::DBF_ID." AND ".
                         SeanceModel::DBF_GROUPE."=:groupe AND ".
                         UserModel::DBF_ID."=".EtudiantModel::DBF_USER." AND ".
                         UserModel::DBF_ID."=".self::DBF_ETUDIANT;
        $data = [ ':groupe' => $groupe ];
        
        // Présentiel spécifique à la séance
        if($seance != -1) {
            $SQL .= " AND ".self::DBF_SEANCE."=:seance";
            $data[':seance'] = $seance;
        }
        
        // Ordonner pour les étudiants qui rattrapent
        $SQL .= " ORDER BY ".UserModel::DBF_NAME." ASC, ".UserModel::DBF_FIRSTNAME." ASC";
        
        // Exécution de la requête
        if(!($request = $DB->prepare($SQL)) || !$request->execute($data))
            return null;

        // Création de chaque étudiant du groupe
        $result = [];
        foreach($etudiants as $etudiant) {
            $result[$etudiant['id']] = ['nom' => $etudiant['nom'],
                                        'prenom' => $etudiant['prenom'],
                                        'numero' => $etudiant['numero'],
                                        'id' => $etudiant['id'],
                                        'pres' => [],
                                        'groupe' => true ];
        }
        
        // Association du présentiel aux séances pour chaque étudiant
        while($row = $request->fetch()) {
            // Si l'étudiant n'existe pas, on l'ajoute...
            if(!isset($result[$row[self::DBF_ETUDIANT]]))
                $result[$row[self::DBF_ETUDIANT]] = ['nom' => $row[UserModel::DBF_NAME],
                                                     'prenom' => $row[UserModel::DBF_FIRSTNAME],
                                                     'numero' => $row[EtudiantModel::DBF_NUMERO],
                                                     'id' => $row[self::DBF_ETUDIANT],
                                                     'pres' => [], 
                                                     'groupe' => false ];

            $result[$row[self::DBF_ETUDIANT]]['pres'][$row[self::DBF_SEANCE]] = $row[self::DBF_TYPE];
        }
        
        return $result;
    }
    
    /**
     * Retourne la liste des rattrapages d'un groupe d'étudiants.
     * @param groupe le groupe
     * @param seance la séance
     */
    public static function getListeRattrapagesGroupe(int $groupe, int $seance = -1) : ?array {
        $DB = MyPDO::getInstance();
        
        // Chargement du groupe pour récupérer son type
        $tmp = GroupeECModel::read($groupe);
            
        // Sélection du présentiel correspondant au groupe
        $SQL = "SELECT ".self::DBF_ETUDIANT." as id, ".
                         UserModel::DBF_NAME." as nom, ".
                         UserModel::DBF_FIRSTNAME." as prenom, ".
                         EtudiantModel::DBF_NUMERO." as numero, ".
                         self::DBF_SEANCE." as idSeance, ".
                         self::DBF_TYPE." as type, ".
                         SeanceModel::DBF_DEBUT." as debut, ".
                         SeanceModel::DBF_FIN." as fin".
           " FROM ".self::DB.", ".SeanceModel::DB.", ".UserModel::DB.", ".EtudiantModel::DB.", ".GroupeECModel::DB.", ".
                    InscriptionGroupeECModel::DB.
           " WHERE ".self::DBF_SEANCE."=".SeanceModel::DBF_ID." AND ".
                     InscriptionGroupeECModel::DBF_ETUDIANT."=".self::DBF_ETUDIANT." AND ".
                     InscriptionGroupeECModel::DBF_GROUPE."=:groupe AND ".
                     UserModel::DBF_ID."=".EtudiantModel::DBF_USER." AND ".
                     UserModel::DBF_ID."=".self::DBF_ETUDIANT." AND ".
                     GroupeECModel::DBF_ID."!=:groupe AND ".
                     SeanceModel::DBF_GROUPE."=".GroupeECModel::DBF_ID." AND ".
                     GroupeECModel::DBF_EC."=:EC AND ".
                     GroupeECModel::DBF_TYPE."=:type;";
            
        if(!($request = $DB->prepare($SQL)) || !$request->execute([':groupe' => $groupe, 
                                                                   ':type' => $tmp->getType(), 
                                                                   ':EC' => $tmp->getEC() ]))
            return null;
        
        $result = [];
        while($row = $request->fetch()) {
            if(!isset($result[$row['id']])) $result[$row['id']] = [];
            $result[$row['id']][] = $row;
        }
        return $result;
    }
    
    /**
     * Retourne le bilan de présentiel d'un EC.
     * @param EC l'EC
     * @return le bilan (ou null en cas d'erreur)
     */
    public static function getBilan(int $EC) : ?array {
        $DB = MyPDO::getInstance();
        
        // Récupération de la liste des étudiants de l'EC
        $result = InscriptionECModel::getListeEtudiants($EC);
        for($i = 0; $i < count($result); $i++) {
            $result[$i]['pres'] = [ 
                    Groupe::GRP_CM => [self::TYPE_PRESENT => 0, self::TYPE_ABSENT => 0, self::TYPE_JUSTIFIE => 0, self::TYPE_RATTRAPAGE => 0], 
                    Groupe::GRP_TD => [self::TYPE_PRESENT => 0, self::TYPE_ABSENT => 0, self::TYPE_JUSTIFIE => 0, self::TYPE_RATTRAPAGE => 0],
                    Groupe::GRP_TP => [self::TYPE_PRESENT => 0, self::TYPE_ABSENT => 0, self::TYPE_JUSTIFIE => 0, self::TYPE_RATTRAPAGE => 0]
                ];
        }
        
        // Récupération du présentiel (présent/absent)
        $SQL = "SELECT A.id, B.id as id2, A.type, A.typeS, COUNT(*) as nb FROM ".
               "(SELECT ".UserModel::DBF_ID." as id, ".
                         UserModel::DBF_NAME.", ".
                         UserModel::DBF_FIRSTNAME.", ".
                         InscriptionSeanceModel::DBF_TYPE." as type, ".
                         GroupeECModel::DBF_TYPE." as typeS, ".
                         SeanceModel::DBF_ID.
               " FROM ".UserModel::DB.", ".
                        InscriptionECModel::DB.", ".
                        SeanceModel::DB.", ".
                        InscriptionSeanceModel::DB.", ".
                        GroupeECModel::DB.
               " WHERE ".UserModel::DBF_ID."=".InscriptionECModel::DBF_ETUDIANT." AND ".
                         InscriptionECModel::DBF_EC."=:EC AND ".
                         InscriptionSeanceModel::DBF_ETUDIANT."=".UserModel::DBF_ID." AND ".
                         InscriptionSeanceModel::DBF_SEANCE."=".SeanceModel::DBF_ID." AND ".
                         SeanceModel::DBF_GROUPE."=".GroupeECModel::DBF_ID." AND ".
                         GroupeECModel::DBF_EC."=".InscriptionECModel::DBF_EC.
               ") AS A LEFT OUTER JOIN ".
               "(SELECT ".UserModel::DBF_ID." as id, ".
                         GroupeECModel::DBF_TYPE." as typeS, ".
                         SeanceModel::DBF_ID.
               " FROM ".UserModel::DB.", ".
                        EtudiantModel::DB.", ".
                        InscriptionECModel::DB.", ".
                        SeanceModel::DB.", ".
                        GroupeECModel::DB.", ".
                        InscriptionGroupeECModel::DB.", ".
                        JustificatifModel::DB.
               " WHERE ".UserModel::DBF_ID."=".EtudiantModel::DBF_USER." AND ".
                         UserModel::DBF_ID."=".InscriptionECModel::DBF_ETUDIANT." AND ".
                         InscriptionECModel::DBF_EC."=:EC AND ".
                         JustificatifModel::DBF_ETUDIANT."=".UserModel::DBF_ID." AND ".
                         JustificatifModel::DBF_DEBUT."<=".SeanceModel::DBF_DEBUT." AND ".
                         JustificatifModel::DBF_FIN.">=".SeanceModel::DBF_FIN." AND ".
                         SeanceModel::DBF_GROUPE."=".GroupeECModel::DBF_ID." AND ".
                         GroupeECModel::DBF_EC."=".InscriptionECModel::DBF_EC." AND ".
                         InscriptionGroupeECModel::DBF_ETUDIANT."=".UserModel::DBF_ID." AND ".
                         InscriptionGroupeECModel::DBF_GROUPE."=".GroupeECModel::DBF_ID.
               ") AS B ON A.id=B.id AND A.".SeanceModel::DBF_ID."=B.".SeanceModel::DBF_ID.
               " GROUP BY A.id, A.typeS, type, B.id".
               " ORDER BY ".UserModel::DBF_NAME." ASC, ".UserModel::DBF_FIRSTNAME.", A.typeS, type";

        if(!($request = $DB->prepare($SQL)) || !$request->execute([ ':EC' => $EC ]))
            return null;
        
        $i = 0;
        while(($row = $request->fetch()) && ($i < count($result))) {
            // Recherche de l'étudiant
            while(($i < count($result)) && ($result[$i]['id'] != $row['id'])) $i++;
            
            // Si l'étudiant existe
            if(($i < count($result)) && ($row['id2'] === null))
                $result[$i]['pres'][$row['typeS']][$row['type']] = $row['nb'];
        }
        
        // Récupération des rattrapages
        $SQL = "SELECT ".UserModel::DBF_ID." as id, ".
                         "COUNT(*) as nb, ".
                         "A.".GroupeECModel::DBF_TYPE." as type".
               " FROM ".InscriptionSeanceModel::DB.", ".
                        SeanceModel::DB.", ".
                        UserModel::DB.", ".
                        GroupeECModel::DB." as A, ".
                        GroupeECModel::DB." as B, ".
                        InscriptionGroupeECModel::DB.
               " WHERE "."A.".GroupeECModel::DBF_EC."=:EC AND ".
                         "B.".GroupeECModel::DBF_EC."=:EC AND ".
                         "A.".GroupeECModel::DBF_TYPE."=B.".GroupeECModel::DBF_TYPE." AND ".
                         InscriptionSeanceModel::DBF_ETUDIANT."=".UserModel::DBF_ID." AND ".
                         InscriptionSeanceModel::DBF_SEANCE."=".SeanceModel::DBF_ID." AND ".
                         SeanceModel::DBF_GROUPE."=A.".GroupeECModel::DBF_ID." AND ".
                         "A.".GroupeECModel::DBF_ID."!=".InscriptionGroupeECModel::DBF_GROUPE." AND ".
                         InscriptionGroupeECModel::DBF_ETUDIANT."=".UserModel::DBF_ID." AND ".
                         InscriptionGroupeECModel::DBF_GROUPE."=B.".GroupeECModel::DBF_ID.
               " GROUP BY ".UserModel::DBF_ID.", A.".GroupeECModel::DBF_TYPE.
               " ORDER BY ".UserModel::DBF_NAME." ASC, ".UserModel::DBF_FIRSTNAME." ASC;";
                            
        if(!($request = $DB->prepare($SQL)) || !$request->execute([ ':EC' => $EC ]))
            return null;
        
        $i = 0;
        while(($row = $request->fetch()) && ($i < count($result))) {
            // Recherche de l'étudiant
            while(($i < count($result)) && ($result[$i]['id'] != $row['id'])) $i++;
            
            // Si l'étudiant existe
            if($i < count($result)) {
                $result[$i]['pres'][$row['type']][self::TYPE_RATTRAPAGE] = $row['nb'];
            }
        }
        
        // Récupération des absences justifiées
        $SQL = "SELECT ".UserModel::DBF_ID." as id, ".
                         "COUNT(*) as nb, ".
                         GroupeECModel::DBF_TYPE." as typeS".
               " FROM ".UserModel::DB.", ".
                        EtudiantModel::DB.", ".
                        InscriptionECModel::DB.", ".
                        SeanceModel::DB.", ".
                        GroupeECModel::DB.", ".
                        InscriptionGroupeECModel::DB.", ".
                        JustificatifModel::DB.
               " WHERE ".UserModel::DBF_ID."=".EtudiantModel::DBF_USER." AND ".
                         UserModel::DBF_ID."=".InscriptionECModel::DBF_ETUDIANT." AND ".
                         InscriptionECModel::DBF_EC."=:EC AND ".
                         JustificatifModel::DBF_ETUDIANT."=".UserModel::DBF_ID." AND ".
                         JustificatifModel::DBF_DEBUT."<=".SeanceModel::DBF_DEBUT." AND ".
                         JustificatifModel::DBF_FIN.">=".SeanceModel::DBF_FIN." AND ".
                         SeanceModel::DBF_GROUPE."=".GroupeECModel::DBF_ID." AND ".
                         GroupeECModel::DBF_EC."=".InscriptionECModel::DBF_EC." AND ".
                         InscriptionGroupeECModel::DBF_ETUDIANT."=".UserModel::DBF_ID." AND ".
                         InscriptionGroupeECModel::DBF_GROUPE."=".GroupeECModel::DBF_ID.
               " GROUP BY ".UserModel::DBF_ID.", ".GroupeECModel::DBF_TYPE.
               " ORDER BY ".UserModel::DBF_NAME." ASC, ".
                            UserModel::DBF_FIRSTNAME." ASC, ".
                            GroupeECModel::DBF_TYPE." ASC";

        if(!($request = $DB->prepare($SQL)) || !$request->execute([ ':EC' => $EC ]))
            return null;
        
        $i = 0;
        while(($row = $request->fetch()) && ($i < count($result))) {
            // Recherche de l'étudiant
            while(($i < count($result)) && ($result[$i]['id'] != $row['id'])) $i++;
            
            // Si l'étudiant existe
            if($i < count($result)) {
                $result[$i]['pres'][$row['typeS']][self::TYPE_JUSTIFIE] = $row['nb'];
            }
        }
        
        return $result;
    }
    
    /**
     * Retourne la liste des présences d'un étudiant.
     * @param etudiant l'identifiant de l'étudiant
     * @return la liste des présences (ou null en cas d'erreur)
     */
    public static function getListePresencesEtudiant(int $etudiant) : ?array {
        $DB = MyPDO::getInstance();
        
        // Liste de toutes les séances dans lesquelles l'étudiant est inscrit dans le groupe correspondant
        $SQL_seances = "(SELECT * FROM ".InscriptionGroupeECModel::DB.", ".GroupeECModel::DB.", ".SeanceModel::DB.", ".ECModel::DB.
               " WHERE ".InscriptionGroupeECModel::DBF_GROUPE."=".GroupeECModel::DBF_ID." AND ".
                         InscriptionGroupeECModel::DBF_ETUDIANT."=:etudiant AND ".
                         GroupeECModel::DBF_EC."=".ECModel::DBF_ID." AND ".
                         SeanceModel::DBF_GROUPE."=".GroupeECModel::DBF_ID.") AS A";
        
        $SQL = "SELECT ".ECModel::DBF_ID.", ".
                         ECModel::DBF_INTITULE.", ".
                         ECModel::DBF_CODE.", ".
                         GroupeECModel::DBF_ID.", ".
                         GroupeECModel::DBF_INTITULE.", ".
                         GroupeECModel::DBF_TYPE.", ".
                         SeanceModel::DBF_DEBUT.", ".
                         SeanceModel::DBF_FIN.", ".
                         self::DBF_TYPE.
               " FROM ".$SQL_seances.               
               " LEFT OUTER JOIN".
               "(SELECT * FROM ".self::DB." WHERE ".self::DBF_ETUDIANT."=:etudiant) AS B".
               " ON A.".SeanceModel::DBF_ID."=B.".self::DBF_SEANCE.
               " ORDER BY ".ECModel::DBF_CODE." ASC, ".
                            GroupeECModel::DBF_INTITULE." ASC, ".
                            SeanceModel::DBF_DEBUT." ASC;";
                            
        if(!($request = $DB->prepare($SQL)) || !$request->execute([':etudiant' => $etudiant]))
            return null;

        $result = [];
        while($row = $request->fetch()) {
            if(!isset($result[$row[ECModel::DBF_ID]])) {
                $result[$row[ECModel::DBF_ID]] = [ 'intitule' => $row[ECModel::DBF_INTITULE],
                                                   'code' => $row[ECModel::DBF_CODE],
                                                   'pres' => [],
                                                   'ratt' => [] ];
            }
            if(!isset($result[$row[ECModel::DBF_ID]]['pres'][$row[GroupeECModel::DBF_ID]]))
                $result[$row[ECModel::DBF_ID]]['pres'][$row[GroupeECModel::DBF_ID]] = 
                [ 'groupe' => $row[GroupeECModel::DBF_INTITULE],
                  'type' => $row[GroupeECModel::DBF_TYPE],
                  'seances' => [] ];
            $result[$row[ECModel::DBF_ID]]['pres'][$row[GroupeECModel::DBF_ID]]['seances'][] = 
                [ 'debut' => $row[SeanceModel::DBF_DEBUT], 
                  'fin' => $row[SeanceModel::DBF_FIN], 
                  'type' => $row[self::DBF_TYPE] ];
        }
        
        // Récupération des rattrapages
        $SQL_ratt = "SELECT ".
                         ECModel::DBF_ID.", ".
                         ECModel::DBF_INTITULE.", ".
                         ECModel::DBF_CODE.", ".
                         "A.".GroupeECModel::DBF_INTITULE.", ".
                         "A.".GroupeECModel::DBF_TYPE.", ".
                         SeanceModel::DBF_DEBUT.", ".
                         SeanceModel::DBF_FIN.
               " FROM ".InscriptionSeanceModel::DB.", ".
                        SeanceModel::DB.", ".
                        ECModel::DB.", ".
                        GroupeECModel::DB." as A, ".
                        GroupeECModel::DB." as B, ".
                        InscriptionGroupeECModel::DB.
               " WHERE ".ECModel::DBF_ID."=A.".GroupeECModel::DBF_EC." AND ".
                         "A.".GroupeECModel::DBF_EC."=B.".GroupeECModel::DBF_EC." AND ".
                         "A.".GroupeECModel::DBF_TYPE."=B.".GroupeECModel::DBF_TYPE." AND ".
                         InscriptionSeanceModel::DBF_ETUDIANT."=:etudiant AND ".
                         InscriptionSeanceModel::DBF_SEANCE."=".SeanceModel::DBF_ID." AND ".
                         SeanceModel::DBF_GROUPE."=A.".GroupeECModel::DBF_ID." AND ".
                         "A.".GroupeECModel::DBF_ID."!=".InscriptionGroupeECModel::DBF_GROUPE." AND ".
                         InscriptionGroupeECModel::DBF_ETUDIANT."=:etudiant AND ".
                         InscriptionGroupeECModel::DBF_GROUPE."=B.".GroupeECModel::DBF_ID.
               " ORDER BY ".SeanceModel::DBF_DEBUT." ASC;";

        if(!($request = $DB->prepare($SQL_ratt)) || !$request->execute([':etudiant' => $etudiant]))
            return null;       
        
        while($row = $request->fetch()) {
            if(!isset($result[$row[ECModel::DBF_ID]])) {
                $result[$row[ECModel::DBF_ID]] = [ 'intitule' => $row[ECModel::DBF_INTITULE],
                                                   'code' => $row[ECModel::DBF_CODE],
                                                   'pres' => [],
                                                   'ratt' => [] ];
            }
            if(!isset($result[$row[ECModel::DBF_ID]]['ratt'][$row[GroupeECModel::DBF_TYPE]]))
                $result[$row[ECModel::DBF_ID]]['ratt'][$row[GroupeECModel::DBF_TYPE]] = [];
            $result[$row[ECModel::DBF_ID]]['ratt'][$row[GroupeECModel::DBF_TYPE]][] = 
                [ 'intitule' => $row[GroupeECModel::DBF_INTITULE],
                  'debut' => $row[SeanceModel::DBF_DEBUT],
                  'fin' => $row[SeanceModel::DBF_FIN]
                ];
        }
        
        return $result;
    }
    
    /**
     * Modifie le présentiel d'un étudiant pour une séance donnée.
     * @param seance l'identifiant de la séance
     * @param etudiant l'identifiant de l'étudiant
     * @param type le type de présentiel
     * @return 'true' en cas de réussite, 'false' sinon
     */
    public static function modifier(int $seance, int $etudiant, int $type) : bool {
        $DB = MyPDO::getInstance();

        // Ajout du présentiel (si le type est différent de 'non spécifié')
        $SQL = "INSERT INTO ".self::DB." (".self::DBF_SEANCE.", ".
               self::DBF_ETUDIANT.", ".self::DBF_TYPE.") VALUES (:seance, :etudiant, :type);";

        // Suppression du présentiel précédent
        if(self::suppression($seance, $etudiant)) {
            // Ajout du présentiel (si le type est différent de 'non spécifié')
            if($type != self::TYPE_NON_SPECIFIE) {
                return (($request = $DB->prepare($SQL)) && 
                        $request->execute([':seance' => $seance, ':etudiant' => $etudiant, ':type' => $type]));
            }
            else
                return true;
        }
        else
            return false;
    }
    
    /**
     * Modifie le présentiel des étudiants d'un groupe pour une séance donnée.
     * @param groupe l'identifiant du groupe
     * @param seance l'identifiant de la séance
     * @param type le type de présentiel
     * @return 'true' en cas de réussite, 'false' sinon
     */
    public static function modifierGroupe(int $groupe, int $seance, int $type) : bool {
        $DB = MyPDO::getInstance();

        // Insertion du présentiel (si le type est différent de 'non spécifié')
        $SQL = "INSERT INTO ".self::DB." (".self::DBF_SEANCE.", ".
               self::DBF_ETUDIANT.", ".self::DBF_TYPE.") VALUES (:seance, :etudiant, :type);";

        // Suppression de tout le présentiel précédent
        if(self::suppression($seance)) {
            // Ajout du présentiel si le type n'est pas 'non spécifié'
            if($type != self::TYPE_NON_SPECIFIE) {
                if($request = $DB->prepare($SQL)) {
                    // Récupération de la liste des étudiants du groupe d'EC
                    $etudiants = InscriptionGroupeECModel::getListe($groupe);

                    // Spécification du présentiel pour chaque étudiant
                    foreach($etudiants as $etudiant)
                        $request->execute([':seance' => $seance, ':etudiant' => $etudiant['id'], ':type' => $type]);

                    return true;
                }
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
     * Supprime le présentiel d'un ou des étudiants d'une séance donnée.
     * @param seance l'identifiant de la séance
     * @param etudiant l'identifiant de l'étudiant (ou -1 pour tous les étudiants)
     * @return 'true' en cas de réussite, 'false' sinon
     */
    public static function suppression(int $seance, int $etudiant = -1) : bool {
        $DB = MyPDO::getInstance();
        
        $SQL = "DELETE FROM ".self::DB.
               " WHERE ".self::DBF_SEANCE."=:seance";
        $data = [':seance' => $seance];
        
        if($etudiant != -1) {
            $SQL .= " AND ".self::DBF_ETUDIANT."=:etudiant";
            $data[':etudiant'] = $etudiant;
        }

        return (($request = $DB->prepare($SQL)) && $request->execute($data));
    }
    
    /**
     * Modifie le présentiel des étudiants d'un groupe pour une séance donnée.
     * @param seance l'identifiant de la séance
     * @param etudiants la liste des étudiants
     * @param presentiel le présentiel des étudiants
     * @return 'true' en cas de réussite, 'false' sinon
     */
    public static function modifierPresentielGroupe(int $seance, array $etudiants, array $presentiel) : bool {
        $DB = MyPDO::getInstance();

        // Insertion du présentiel
        $SQL = "INSERT INTO ".self::DB." (".self::DBF_SEANCE.", ".
               self::DBF_ETUDIANT.", ".self::DBF_TYPE.") VALUES (:seance, :etudiant, :type);";

        // Suppression de tout le présentiel précédent
        if(self::suppression($seance) && ($request = $DB->prepare($SQL))) {
            for($i = 0; $i < count($etudiants); $i++) {
                if($presentiel[$i] != self::TYPE_NON_SPECIFIE) {
                    $request->execute([':seance' => $seance, ':etudiant' => $etudiants[$i]['id'], ':type' => $presentiel[$i]]);
                }
            }
            return true;
        }
        else
            return false;
    }
    
} // class InscriptionSeanceModel