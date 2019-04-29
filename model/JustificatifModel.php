<?php
// *************************************************************************************************
// * Modèle pour les justificatifs d'absence.
// *************************************************************************************************
class JustificatifModel {
    
    // Constantes pour la table des notes
    const DB = "inf_justificatif";               // Table des justificatifs d'absence
    const DBF_ID = "jus_id";                     // Identifiant
    const DBF_ETUDIANT = "jus_etudiant";         // Identifiant de l'étudiant
    const DBF_DEBUT = "jus_debut";               // Date de début
    const DBF_FIN = "jus_fin";                   // Date de fin
    const DBF_MOTIF = "jus_motif";               // Motif
    const DBF_REMARQUE = "jus_remarque";         // Remarque
    const DBF_EDITEUR = "jus_editeur";           // Identifiant de l'éditeur
    const DBF_DATESAISIE = "jus_datesaisie";     // Date de saisie

    /**
     * Retourne la liste des jutificatifs pour un diplôme.
     * @param diplome l'identifiant du diplôme
     * @return la liste des justificatifs (ou null en cas d'erreur)
     */
    public static function getList(int $diplome) : ?array {
        $DB = MyPDO::getInstance();
        
        $SQL = "SELECT ".self::DBF_ID." as id, ".
                         EtudiantModel::DBF_NUMERO." as numero, ".
                         UserModel::DBF_ID." as idEtu, ".
                         UserModel::DBF_NAME." as nom, ".
                         UserModel::DBF_FIRSTNAME." as prenom, ".
                         "CONCAT(".UserModel::DBF_NAME.", ' ',".UserModel::DBF_FIRSTNAME.") as nomcomplet, ".
                         UserModel::DBF_MAIL." as email, ".
                         self::DBF_DEBUT." as debut, ".
                         self::DBF_FIN." as fin, ".
                         self::DBF_MOTIF." as motif, ".
                         self::DBF_REMARQUE." as remarque".
               " FROM ".self::DB.", ".UserModel::DB.", ".EtudiantModel::DB.", ".InscriptionDiplomeModel::DB.
               " WHERE ".UserModel::DBF_ID."=".EtudiantModel::DBF_USER." AND ".
                         self::DBF_ETUDIANT."=".EtudiantModel::DBF_USER." AND ".
                         UserModel::DBF_ID."=".InscriptionDiplomeModel::DBF_ETUDIANT." AND ".
                         InscriptionDiplomeModel::DBF_DIPLOME."=:diplome".
               " GROUP BY ".self::DBF_ID.
               " ORDER BY ".self::DBF_DEBUT." DESC, ".UserModel::DBF_NAME." ASC, ".UserModel::DBF_FIRSTNAME." ASC;";
        
        if(($request = $DB->prepare($SQL)) && $request->execute([':diplome' => $diplome])) {
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
     * Retourne la liste des jutificatifs d'un étudiant.
     * @param diplome l'identifiant du diplôme
     * @return la liste des justificatifs (ou null en cas d'erreur)
     */
    public static function getListEtudiant(int $etudiant) : ?array {
        $DB = MyPDO::getInstance();
        
        $SQL = "SELECT ".self::DBF_DEBUT." as debut, ".
                         self::DBF_FIN." as fin, ".
                         self::DBF_MOTIF." as motif".
               " FROM ".self::DB.", ".UserModel::DB.", ".EtudiantModel::DB.
               " WHERE ".UserModel::DBF_ID."=".EtudiantModel::DBF_USER." AND ".
                         UserModel::DBF_ID."=:id AND ".
                         self::DBF_ETUDIANT."=".EtudiantModel::DBF_USER.
               " ORDER BY ".self::DBF_DEBUT." ASC, ".UserModel::DBF_NAME." ASC, ".UserModel::DBF_FIRSTNAME." ASC;";
        
        if(($request = $DB->prepare($SQL)) && $request->execute([':id' => $etudiant])) {
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
     * Retourne les justificatifs correspondant à une séance.
     * @param seances les séances
     * @return la liste des justificatifs (ou null en cas d'erreur)
     */
    public static function getListSeances(array $seances) : ?array {
        $DB = MyPDO::getInstance();

        $SQL = "SELECT *".
               " FROM ".
               "(SELECT ".UserModel::DBF_ID.
               " FROM ".EtudiantModel::DB.", ".
                        UserModel::DB.", ".
                        GroupeECModel::DB.", ".
                        InscriptionGroupeECModel::DB.", ".
                        SeanceModel::DB.
               " WHERE ".EtudiantModel::DBF_USER."=".UserModel::DBF_ID." AND ".
                         GroupeECModel::DBF_ID."=".InscriptionGroupeECModel::DBF_GROUPE." AND ".
                         UserModel::DBF_ID."=".InscriptionGroupeECModel::DBF_ETUDIANT." AND ".
                         SeanceModel::DBF_ID."=:seance AND ".
                         GroupeECModel::DBF_ID."=".SeanceModel::DBF_GROUPE.
               ") AS A LEFT OUTER JOIN ".
               "(SELECT * FROM ".self::DB." WHERE :debut>=".self::DBF_DEBUT." AND ".
                                                 ":fin<=".self::DBF_FIN.") AS B".
               " ON A.".UserModel::DBF_ID."=B.".self::DBF_ETUDIANT;
        
        if($request = $DB->prepare($SQL)) {
            $result = [];
            foreach($seances as $seance) {
                if($request->execute([':seance' => $seance['id'], 
                                      ':debut' => DateTools::date2Timestamp($seance['debut'], true), 
                                      ':fin' => DateTools::date2Timestamp($seance['fin'], true)])) {
                    $result[$seance['id']] = [];
                    while($row = $request->fetch()) {
                        if($row[self::DBF_ID] != null) {
                            $heure = (date('G', $row[self::DBF_DEBUT]) != 0);
                            if(!$heure)
                                $fin = strtotime('-1 day', $row[self::DBF_FIN]);
                            else
                                $fin = $row[self::DBF_FIN];
                            
                            $result[$seance['id']][$row[UserModel::DBF_ID]] = [
                                'debut' => DateTools::timestamp2Date($row[self::DBF_DEBUT], $heure), 
                                'fin' => DateTools::timestamp2Date($fin, $heure)];
                        }
                    }
                }
                else
                    return null;
            }
            return $result;
        }
        else
            return null;
    }
    
    /**
     * Retourne un justificatif à partir d'un tableau associatif.
     * @param array le tableau associatif
     * @return le justificatif
     */
    public static function fromArray(array $array) : ?Justificatif {
        return new Justificatif($array[self::DBF_ID], 
                                $array[self::DBF_ETUDIANT],
                                $array[self::DBF_DEBUT],
                                $array[self::DBF_FIN],
                                $array[self::DBF_MOTIF],
                                $array[self::DBF_REMARQUE],
                                $array[self::DBF_EDITEUR],
                                $array[self::DBF_DATESAISIE]);
    }

    /**
	 * Ajoute un justificatif dans la base.
     * @param justificatif le justificatif à ajouter dans la base
	 * @return 'true' on success or 'false' on error
	 */
	public static function create(Justificatif $justificatif) : bool {
        $DB = MyPDO::getInstance();
        
        $SQL = "INSERT INTO ".self::DB.
               " (".self::DBF_ID.", ".  
                    self::DBF_ETUDIANT.", ".
                    self::DBF_DEBUT.", ".
                    self::DBF_FIN.", ".
                    self::DBF_MOTIF.", ".
                    self::DBF_REMARQUE.", ".
                    self::DBF_EDITEUR.", ".
                    self::DBF_DATESAISIE.
               ") VALUES (NULL, :etudiant, :debut, :fin, :motif, :remarque, :editeur, :datesasie);";

        // Pour les vérifications dans la base, il faut faire en sorte que la date de fin soit +1 jour si justif sur une journée
        if(date('G', $justificatif->getDateDebut()) == 0)
            $fin = strtotime('+1 day', $justificatif->getDateFin());
        else
            $fin = $justificatif->getDateFin();
               
        if(($requete = $DB->prepare($SQL)) &&
           $requete->execute(array(":etudiant" => $justificatif->getEtudiant(),
                                   ":debut" => $justificatif->getDateDebut(),
                                   ":fin" => $fin,
                                   ":motif" => $justificatif->getMotif(),
                                   ":remarque" => $justificatif->getRemarque(),
                                   ":editeur" => $justificatif->getEditeur(),
                                   ":datesasie" => $justificatif->getDateSaisie()))) {
            $justificatif->setId($DB->lastInsertId());
            return true;
        }
        else
            return false;
    }
    
    /**
	 * Récupère un justificatif depuis la base de données.
     * @param id l'identifiant du justificatif
	 * @return le justificatif ou 'null' en cas d'erreur
	 */
	public static function read(int $id) : ?Justificatif {
        $DB = MyPDO::getInstance();
        
        $SQL = "SELECT * FROM ".self::DB." WHERE ".self::DBF_ID."=:id;";

        if(($requete = $DB->prepare($SQL)) &&
           $requete->execute(array(":id" => $id)) && 
           ($row = $requete->fetch())) {
            // Pour les vérifications dans la base, il faut faire en sorte que la date de fin soit +1 jour si justif sur une journée
            if(date('G', $row[self::DBF_DEBUT]) == 0)
                $row[self::DBF_FIN] = strtotime('-1 day', $row[self::DBF_FIN]);
        
            return self::fromArray($row);
        }
        else
            return null;
	}
    
    /**
	 * Sauve les modifications d'un justificatif dans la base.
     * @param justificatif le justificatif
	 * @return 'true' on success or 'false' on error
	 */
	public static function update(Justificatif $justificatif) : bool {
        // Pour les vérifications dans la base, il faut faire en sorte que la date de fin soit +1 jour si justif sur une journée
        if(date('G', $justificatif->getDateDebut()) == 0)
            $fin = strtotime('+1 day', $justificatif->getDateFin());
        else
            $fin = $justificatif->getDateFin();
        
        $data = [ [self::DBF_ETUDIANT, $justificatif->getEtudiant()],
                  [self::DBF_DEBUT, $justificatif->getDateDebut()],
                  [self::DBF_FIN, $fin],
                  [self::DBF_MOTIF, $justificatif->getMotif()],
                  [self::DBF_REMARQUE, $justificatif->getRemarque()],
                  [self::DBF_EDITEUR, $justificatif->getEditeur()],
                  [self::DBF_DATESAISIE, time() ] ];                  
        
        return MyPDO::update(self::DB, $data, $justificatif->getId(), self::DBF_ID);
	}

    /**
     * Supprime un justificatif de la base.
     * @param id l'identifiant du justificatif
     * @return 'true' on success or 'false' on error
     */
    public static function delete(int $id) : bool {
        $DB = MyPDO::getInstance();
        
        // Suppression du justificatif
        $SQL = "DELETE FROM ".self::DB." WHERE ".self::DBF_ID."=:id LIMIT 1;";

        if(($requete = $DB->prepare($SQL)) && $requete->execute(array(":id" => $id)))
            return true;
        else
            return false;
    }
    
} // class JustificatifModel