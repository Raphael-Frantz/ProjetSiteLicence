<?php
// *************************************************************************************************
// * Modèle pour les groupes d'EC.
// *************************************************************************************************
class GroupeECModel {
    
    // Constantes pour la table des groupes d'EC
    const DB = "inf_groupeec";                   // Table des groupes d'EC
    const DBF_ID = "gre_id";                     // Identifiant 
    const DBF_INTITULE = "gre_intitule";         // Intitulé
    const DBF_TYPE = "gre_type";                 // Type
    const DBF_EC = "gre_ec";                     // EC

    /**
     * Retourne la liste des groupes d'un EC
     * @param EC l'identifiant de l'EC
     * @param type le type du groupe (ou GRP_UNDEF)
     * @param ordre si 'true' ordonne les groupes par l'intitulé, sinon indexe par leur identifiant
     * @return la liste des groupes (ou null en cas d'erreur)
     */
    public static function getList(int $EC, int $type = Groupe::GRP_UNDEF, bool $ordre = true) : array {
        $DB = MyPDO::getInstance();
        
        $data = array(":EC" => $EC);
        $SQL = "SELECT ".self::DBF_ID." as id, ".
                         self::DBF_INTITULE." as intitule, ".
                         self::DBF_TYPE." as type".
               " FROM ".self::DB.
               " WHERE ".self::DBF_EC."=:EC";

        if($type != Groupe::GRP_UNDEF) {
            $SQL .= " AND ".self::DBF_TYPE."=:type";
            $data[":type"] = $type;
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
     * Retourne un groupe d'EC à partir d'un tableau associatif.
     * @param array le tableau associatif
     * @return le groupe d'EC
     */
    public static function fromArray(array $array) : GroupeEC {
        return new GroupeEC($array[self::DBF_ID], $array[self::DBF_INTITULE], 
                            $array[self::DBF_TYPE], $array[self::DBF_EC]);
    }
    
    /**
	 * Ajoute un groupe d'EC dans la base.
     * @param groupe le groupe à ajouter dans la base
	 * @return 'true' on success or 'false' on error
	 */
	public static function create(GroupeEC $groupe) : bool {
        $DB = MyPDO::getInstance();
        $SQL = "INSERT INTO ".self::DB." (".self::DBF_ID.", ".self::DBF_INTITULE.", ".
               self::DBF_TYPE.", ".self::DBF_EC.") ".
               "VALUES (NULL, :intitule, :type, :EC);";

        if(($requete = $DB->prepare($SQL)) &&
           $requete->execute(array(":intitule" => $groupe->getIntitule(), 
                                   ":type" => $groupe->getType(),
                                   ":EC" => $groupe->getEC()))) {
            $groupe->setId($DB->lastInsertId());
            return true;
        }
        else
            return false;
    }
    
    /**
	 * Récupère un groupe d'EC depuis la base de données.
     * @param id l'identifiant du groupe
	 * @return le diplôme ou 'null' en cas d'erreur
	 */
	public static function read(int $id) : ?GroupeEC {
        $DB = MyPDO::getInstance();
        $SQL = "SELECT * FROM ".self::DB." WHERE ".self::DBF_ID."=:id";

        if(($requete = $DB->prepare($SQL)) &&
           $requete->execute(array(":id" => $id)) && 
           ($row = $requete->fetch()))
            return self::fromArray($row);
        else
            return null;
	}
    
    /**
	 * Sauve les modifications d'un groupe d'EC dans la base.
     * @param groupe le groupe
	 * @return 'true' on success or 'false' on error
	 */
	public static function update(GroupeEC $groupe) : bool {
        $data = array(array(self::DBF_INTITULE, $groupe->getIntitule()));
        
        return MyPDO::update(self::DB, $data, $groupe->getId(), self::DBF_ID);
	}	
    
    /**
     * Supprime un groupe d'EC de la base.
     * @param id l'identifiant du groupe
     * @return 'true' on success or 'false' on error
     */
    public static function delete(int $id) : bool {
        $DB = MyPDO::getInstance();
        $SQL = "DELETE FROM ".self::DB." WHERE ".self::DBF_ID."=:id LIMIT 1";

        if(($requete = $DB->prepare($SQL)) && $requete->execute(array(":id" => $id)))
            return true;
        else
            return false;
    }    
    
} // class GroupeModel