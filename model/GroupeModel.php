<?php
// *************************************************************************************************
// * Modèle pour les groupes.
// *************************************************************************************************
class GroupeModel {
    
    // Constantes pour la table des groupes
    const DB = "inf_groupe";                     // Table des groupes
    const DBF_ID = "gro_id";                     // Identifiant 
    const DBF_INTITULE = "gro_intitule";         // Intitulé
    const DBF_TYPE = "gro_type";                 // Type
    const DBF_DIPLOME = "gro_diplome";           // Diplôme
    const DBF_SEMESTRE = "gro_semestre";         // Semestre

    /**
     * Retourne la liste des groupes d'un diplôme.
     * @param diplome l'identifiant du diplôme
     * @param semestre le numéro du semestre (ou -1)
     * @param type le type du groupe (ou GRP_UNDEF)
     * @param ordre si 'true' ordonne les groupes par l'intitulé, sinon indexe par leur identifiant
     * @return la liste des groupes (ou null en cas d'erreur)
     */
    public static function getList(int $diplome, int $semestre, int $type = Groupe::GRP_UNDEF, bool $ordre = true) : array {
        $DB = MyPDO::getInstance();
        
        $data = array(":diplome" => $diplome);
        $SQL = "SELECT ".self::DBF_ID." as id, ".
                         self::DBF_INTITULE." as intitule, ".
                         self::DBF_TYPE." as type".
               " FROM ".self::DB.
               " WHERE ".self::DBF_DIPLOME."=:diplome";

        if($type != Groupe::GRP_UNDEF) {
            $SQL .= " AND ".self::DBF_TYPE."=:type";
            $data[":type"] = $type;
        }
        if($semestre != -1) {
            $SQL .= " AND ".self::DBF_SEMESTRE."=:semestre";
            $data[":semestre"] = $semestre;
        }
        $SQL .= " ORDER BY ".self::DBF_INTITULE." ASC;";
        
        if(($request = $DB->prepare($SQL)) && $request->execute($data)) {
            $result = array();

            if($ordre) {
                while($row = $request->fetch())
                    $result[] = $row;
            }
            else {
                while($row = $request->fetch())
                    $result[$row['id']] = $row['intitule'];
            }
            return $result;
        }
        else
            return null;
    }    
    
    /**
     * Retourne un groupe à partir d'un tableau associatif.
     * @param array le tableau associatif
     * @return le groupe
     */
    public static function fromArray(array $array) : Groupe {
        return new Groupe($array[self::DBF_ID], $array[self::DBF_INTITULE], $array[self::DBF_TYPE],
                          $array[self::DBF_DIPLOME], $array[self::DBF_SEMESTRE]);
    }
    
    /**
	 * Ajoute un groupe dans la base.
     * @param groupe le groupe à ajouter dans la base
	 * @return 'true' on success or 'false' on error
	 */
	public static function create(Groupe $groupe) : bool {
        $DB = MyPDO::getInstance();
        $SQL = "INSERT INTO `".self::DB."` (`".self::DBF_ID."`, `".self::DBF_INTITULE."`, `".
               self::DBF_TYPE."`, `".self::DBF_DIPLOME."`, `".self::DBF_SEMESTRE."`) ".
               "VALUES (NULL, :intitule, :type, :diplome, :semestre);";

        if(($requete = $DB->prepare($SQL)) &&
           $requete->execute(array(":intitule" => $groupe->getIntitule(), 
                                   ":type" => $groupe->getType(),
                                   ":diplome" => $groupe->getDiplome(),
                                   ":semestre" => $groupe->getSemestre()))) {
            $groupe->setId($DB->lastInsertId());
            return true;
        }
        else
            return false;
    }
    
    /**
	 * Récupère un groupe depuis la base de données.
     * @param id l'identifiant du groupe
	 * @return le diplôme ou 'null' en cas d'erreur
	 */
	public static function read(int $id) : Groupe {
        $DB = MyPDO::getInstance();
        $SQL = "SELECT * FROM `".self::DB."` WHERE `".self::DBF_ID."`=:id";

        if(($requete = $DB->prepare($SQL)) &&
           $requete->execute(array(":id" => $id)) && 
           ($row = $requete->fetch()))
            return self::fromArray($row);
        else
            return null;
	}
    
    /**
	 * Sauve les modifications d'un groupe dans la base.
     * @param groupe le groupe
	 * @return 'true' on success or 'false' on error
	 */
	public static function update(Groupe $groupe) : bool {
        $data = array(array(self::DBF_INTITULE, $groupe->getIntitule()));
        
        return MyPDO::update(self::DB, $data, $groupe->getId(), self::DBF_ID);
	}	
    
    /**
     * Supprime un groupe de la base.
     * @param id l'identifiant du groupe
     * @return 'true' on success or 'false' on error
     */
    public static function delete(int $id) : bool {
        $DB = MyPDO::getInstance();
        $SQL = "DELETE FROM `".self::DB."` WHERE `".self::DBF_ID."`=:id LIMIT 1";

        if(($requete = $DB->prepare($SQL)) && $requete->execute(array(":id" => $id)))
            return true;
        else
            return false;
    }

} // class GroupeModel