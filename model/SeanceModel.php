<?php
// *************************************************************************************************
// * Modèle pour les séances.
// *************************************************************************************************
class SeanceModel {
    
    // Constantes pour la table des séances
    const DB = "inf_seance";                     // Table des tuteurs
    const DBF_ID = "sea_id";                     // Identifiant
    const DBF_GROUPE = "sea_groupe";             // Identifiant groupe EC
    const DBF_DEBUT = "sea_debut";               // Date de début
    const DBF_FIN = "sea_fin";                   // Date de fin
    
    /**
     * Retourne la liste des séances d'un groupe.
     * @param groupe l'identifiant du groupe
     * @return la liste des séances ou null
     */
    public static function getListeSeancesGroupe(int $groupe) : ?array {
        $DB = MyPDO::getInstance();
        
        $SQL = "SELECT ".self::DBF_ID." as id, ".
                         self::DBF_DEBUT." as debut, ".
                         self::DBF_FIN." as fin".
               " FROM ".self::DB.
               " WHERE ".self::DBF_GROUPE."=:groupe".
               " ORDER BY ".self::DBF_DEBUT." ASC;";
        
        if(($request = $DB->prepare($SQL)) && $request->execute([':groupe' => $groupe])) {
            $result = [];
            while($row = $request->fetch()) {
                $row['debut'] = DateTools::timestamp2Date($row['debut'], true);
                $row['fin'] = DateTools::timestamp2Date($row['fin'], true);
                $result[] = $row;
            }
            return $result;
        }
        else
            return null;
    }
    
    /**
     * Retourne une séance à partir d'un tableau associatif.
     * @param array le tableau associatif
     * @return la séance
     */
    public static function fromArray(array $array) : ?Seance {
        return new Seance($array[self::DBF_ID], 
                          $array[self::DBF_GROUPE],
                          $array[self::DBF_DEBUT],
                          $array[self::DBF_FIN]);
    }    
    
    /**
	 * Ajoute une séance dans la base.
     * @param seance la séance à ajouter dans la base
	 * @return 'true' on success or 'false' on error
	 */
	public static function create(Seance $seance) : bool {
        $DB = MyPDO::getInstance();
        $SQL = "INSERT INTO ".self::DB." (".self::DBF_ID.", ".self::DBF_GROUPE.", ".self::DBF_DEBUT.", ".self::DBF_FIN.
               ") VALUES (NULL, :groupe, :debut, :fin);";

        if(($requete = $DB->prepare($SQL)) &&
           $requete->execute([":groupe" => $seance->getGroupe(),
                              ":debut" => $seance->getDebut(),
                              ":fin" => $seance->getFin()])) {
            $seance->setId($DB->lastInsertId());
            return true;
        }
        else
            return false;
    }
    
    /**
	 * Récupère une séance depuis la base de données.
     * @param id l'identifiant de la séance
	 * @return la séance ou 'null' en cas d'erreur
	 */
	public static function read(int $id) : ?Seance {
        $DB = MyPDO::getInstance();
        $SQL = "SELECT * FROM ".self::DB." WHERE ".self::DBF_ID."=:id;";

        if(($requete = $DB->prepare($SQL)) &&
           $requete->execute(array(":id" => $id)) && 
           ($row = $requete->fetch()))
            return self::fromArray($row);
        else
            return null;
	}
    
    /**
	 * Récupère une séance depuis la base de données à partir d'une date (timestamp).
     * @param groupe l'identifiant du groupe
     * @param debut la date de début de la séance
     * @param fin la date de fin de la séance
	 * @return la séance ou 'null' en cas d'erreur
	 */
	public static function readFromDate(int $groupe, int $debut, int $fin) : ?Seance {
        $DB = MyPDO::getInstance();
        
        $SQL = "SELECT * FROM ".self::DB." WHERE ".self::DBF_GROUPE."=:groupe AND ".self::DBF_DEBUT."=:debut AND ".self::DBF_FIN."=:fin;";

        if(($requete = $DB->prepare($SQL)) &&
           $requete->execute([":groupe" => $groupe, ":debut" => $debut, ':fin' => $fin]) && 
           ($row = $requete->fetch()))
            return self::fromArray($row);
        else
            return null;
	}    
    
    /**
	 * Sauve les modifications d'une séance dans la base.
     * @param id l'identifiant de la séance
     * @param debut la date de début
     * @param fin la date de fin
	 * @return 'true' on success or 'false' on error
	 */
	public static function update(int $id, int $debut, int $fin) : bool {
        $data = [ [self::DBF_DEBUT, $debut],
                  [self::DBF_FIN, $fin] ];

        return MyPDO::update(self::DB, $data, $id, self::DBF_ID);
	}
    
    /**
	 * Supprime une séance de la base.
     * @param id l'identifiant de la séance
	 * @return 'true' on success or 'false' on error
	 */
	public static function delete(int $id) : bool {
        $DB = MyPDO::getInstance();
        
        // Suppression du présentiel associé
        if(InscriptionSeanceModel::suppression($id)) {
            // Suppression de la séance
            $SQL = "DELETE FROM ".self::DB." WHERE ".self::DBF_ID."=:id LIMIT 1;";

            if(($requete = $DB->prepare($SQL)) && $requete->execute([":id" => $id]))
                return true;
            else
                return false;
        }
        else
            return false;
	}
    
} // class SeanceModel