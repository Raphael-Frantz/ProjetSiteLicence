<?php
// *************************************************************************************************
// * Modèle pour les épreuves.
// *************************************************************************************************
class EpreuveModel {
    
    // Constantes pour la table des épreuves
    const DB = "inf_epreuve";            // Table des épreuves
    const DBF_ID = "epr_id";
    const DBF_INTITULE = "epr_intitule";
    const DBF_TYPE = "epr_type";
    const DBF_EC = "epr_EC";
    const DBF_MAX = "epr_max";
    const DBF_BLOQUEE = "epr_bloquee";
    const DBF_ACTIVE = "epr_active";
    const DBF_VISIBLE = "epr_visible";
    const DBF_SESSION1 = "epr_session1";
    const DBF_SESSION2= "epr_session2";
    const DBF_SESSION1D = "epr_session1disp";
    const DBF_SESSION2D = "epr_session2disp";

    /**
     * Retourne la liste des épreuves d'une EC.
     * @param EC l'identifiant de l'EC
     * @return la liste des épreuves (ou null en cas d'erreur)
     */
    public static function getList(int $EC) : array {
        $DB = MyPDO::getInstance();
        
        $SQL = "SELECT ".self::DBF_ID." as id, ".
                         self::DBF_INTITULE." as intitule, ".
                         self::DBF_TYPE." as type, ".
                         self::DBF_MAX." as max, ".
                         self::DBF_BLOQUEE." as bloquee, ".
                         self::DBF_ACTIVE." as active, ".
                         self::DBF_VISIBLE." as visible, ".
                         self::DBF_SESSION1." as session1, ".
                         self::DBF_SESSION2." as session2, ".
                         self::DBF_SESSION1D." as session1disp, ".
                         self::DBF_SESSION2D." as session2disp".
               " FROM ".EpreuveModel::DB.
               " WHERE ".self::DBF_EC."=:EC".
               " ORDER BY ".self::DBF_INTITULE;
        
        if(($request = $DB->prepare($SQL)) && $request->execute([ ":EC" => $EC ])) {
            $result = [];
            while($row = $request->fetch())
                $result[] = $row;

            return $result;
        }
        else
            return null;
    }
    
    /**
     * Retourne la liste des épreuves d'un diplôme
     * @param diplome l'identifiant du diplôme
     * @param semestre le semestre
     * @return la liste des épreuves (ou null en cas d'erreur)
     */
    public static function getListDiplome(int $diplome, int $semestre) : array {
        $DB = MyPDO::getInstance();
        
        $SQL = "SELECT ".self::DBF_ID." as id, ".
                         ECModel::DBF_CODE." as code, ".
                         ECModel::DBF_INTITULE." as EC, ".
                         self::DBF_INTITULE." as intitule, ".
                         self::DBF_TYPE." as type, ".
                         self::DBF_MAX." as max, ".
                         self::DBF_BLOQUEE." as bloquee, ".
                         self::DBF_ACTIVE." as active, ".
                         self::DBF_VISIBLE." as visible, ".
                         self::DBF_SESSION1." as session1, ".
                         self::DBF_SESSION2." as session2, ".
                         self::DBF_SESSION1D." as session1disp, ".
                         self::DBF_SESSION2D." as session2disp".
               " FROM ".UEModel::DB.", ".
                        UEECModel::DB.", ".
                        ECModel::DB.", ".
                        self::DB.
               " WHERE ".UEModel::DBF_DIPLOME."=:diplome AND ".
                         UEModel::DBF_SEMESTRE."=:semestre AND ".
                         UEECModel::DBF_UE."=".UEModel::DBF_ID." AND ".
                         UEECModel::DBF_EC."=".ECModel::DBF_ID." AND ".
                         self::DBF_EC."=".ECModel::DBF_ID.                         
               " ORDER BY ".UEModel::DBF_POSITION." ASC,".
                            UEECModel::DBF_POSITION." ASC,".
                            self::DBF_INTITULE." ASC;";
        
        if(($request = $DB->prepare($SQL)) && $request->execute([ ":diplome" => $diplome, ":semestre" => $semestre])) {
            $result = [];
            while($row = $request->fetch())
                $result[] = $row;

            return $result;
        }
        else
            return null;
    }
    
    /**
     * Retourne une épreuve à partir d'un tableau associatif.
     * @param array le tableau associatif
     * @return l'épreuve
     */
    public static function fromArray(array $array) : Epreuve {
        return new Epreuve($array[self::DBF_ID], 
                           $array[self::DBF_INTITULE],
                           $array[self::DBF_TYPE],
                           $array[self::DBF_EC],
                           $array[self::DBF_MAX],
                           DataTools::int2Boolean($array[self::DBF_BLOQUEE]),
                           DataTools::int2Boolean($array[self::DBF_ACTIVE]),
                           DataTools::int2Boolean($array[self::DBF_VISIBLE]),
                           $array[self::DBF_SESSION1],
                           $array[self::DBF_SESSION2],
                           $array[self::DBF_SESSION1D],
                           $array[self::DBF_SESSION2D]);
    }
    
    /**
	 * Ajoute une épreuve dans la base.
     * @param epreuve l'épreuve à ajouter dans la base
	 * @return 'true' on success or 'false' on error
	 */
	public static function create(Epreuve $epreuve) : bool {
        $DB = MyPDO::getInstance();
        $SQL = "INSERT INTO ".self::DB." (".self::DBF_ID.", ".self::DBF_INTITULE.", ".
               self::DBF_TYPE.", ".self::DBF_EC.", ".self::DBF_MAX.", ".self::DBF_BLOQUEE.", ".self::DBF_ACTIVE.", ".
               self::DBF_VISIBLE.", ".self::DBF_SESSION1.", ".self::DBF_SESSION2.", ".self::DBF_SESSION1D.", ".self::DBF_SESSION2D.
               ") VALUES (NULL, :intitule, :type, :EC, :max, :bloquee, :active, :visible, :session1, :session2, :session1disp, :session2disp);";

        if(($requete = $DB->prepare($SQL)) &&
           $requete->execute([":intitule" => $epreuve->getIntitule(),
                              ":type" => $epreuve->getType(),
                              ":EC" => $epreuve->getIdEC(),
                              ":max" => $epreuve->getMax(),
                              ":bloquee" => DataTools::boolean2Int($epreuve->isBloquee()),
                              ":active" => DataTools::boolean2Int($epreuve->isActive()),
                              ":visible" => DataTools::boolean2Int($epreuve->isVisible()),
                              ":session1" => $epreuve->getSession1(),
                              ":session2" => $epreuve->getSession2(),
                              ":session1disp" => $epreuve->getSession1Disp(),
                              ":session2disp" => $epreuve->getSession2Disp()
                              ])) {
            $epreuve->setId($DB->lastInsertId());
            return true;
        }
        else
            return false;
    }
    
    /**
	 * Récupère une épreuve depuis la base de données.
     * @param id l'identifiant de l'épreuve
	 * @return l'épreuve ou 'null' en cas d'erreur
	 */
	public static function read(int $id) : ?Epreuve {
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
     * Bloque ou débloque l'épreuve.
     * @param id l'identifiant de l'épreuve
     * @param bloquee si 'true' bloque l'épreuve sinon la débloque
     * @return 'true' on success or 'false' on error
     */
    public static function bloquee(int $id, bool $bloquee) : bool {
        $DB = MyPDO::getInstance();
        
        $SQL = "UPDATE ".self::DB." SET ".self::DBF_BLOQUEE."=:bloquee".
               " WHERE ".self::DBF_ID."=:id LIMIT 1";

        if(($requete = $DB->prepare($SQL)) && $requete->execute(array(":id" => $id, ":bloquee" => DataTools::boolean2Int($bloquee))))
            return true;
        else
            return false;
    }
    
    /**
     * Active ou désactive l'épreuve.
     * @param id l'identifiant de l'épreuve
     * @param active si 'true' active l'épreuve sinon la désactive
     * @return 'true' on success or 'false' on error
     */
    public static function active(int $id, bool $active) : bool {
        $DB = MyPDO::getInstance();
        
        $SQL = "UPDATE ".self::DB." SET ".self::DBF_ACTIVE."=:active".
               " WHERE ".self::DBF_ID."=:id LIMIT 1";

        if(($requete = $DB->prepare($SQL)) && $requete->execute(array(":id" => $id, ":active" => DataTools::boolean2Int($active))))
            return true;
        else
            return false;
    }
    
    /**
     * Rend visible ou non l'épreuve.
     * @param id l'identifiant de l'épreuve
     * @param visible si 'true' rend visible l'épreuve sinon la rend invisible
     * @return 'true' on success or 'false' on error
     */
    public static function visible(int $id, bool $visible) : bool {
        $DB = MyPDO::getInstance();
        
        $SQL = "UPDATE ".self::DB." SET ".self::DBF_VISIBLE."=:active".
               " WHERE ".self::DBF_ID."=:id LIMIT 1";

        if(($requete = $DB->prepare($SQL)) && $requete->execute(array(":id" => $id, ":active" => DataTools::boolean2Int($visible))))
            return true;
        else
            return false;
    }    
    
    /**
	 * Sauve les modifications d'une épreuve dans la base.
     * @param epreuve l'épreuve
	 * @return 'true' on success or 'false' on error
	 */
	public static function update(Epreuve $epreuve) : bool {
        $data = [ [self::DBF_INTITULE, $epreuve->getIntitule()],
                  [self::DBF_TYPE, $epreuve->getType()],
                  [self::DBF_INTITULE, $epreuve->getIntitule()],
                  [self::DBF_MAX, $epreuve->getMax()],
                  [self::DBF_BLOQUEE, DataTools::boolean2Int($epreuve->isBloquee())],
                  [self::DBF_ACTIVE, DataTools::boolean2Int($epreuve->isActive())],
                  [self::DBF_VISIBLE, DataTools::boolean2Int($epreuve->isVisible())],
                  [self::DBF_SESSION1, $epreuve->getSession1()],
                  [self::DBF_SESSION2, $epreuve->getSession2()],
                  [self::DBF_SESSION1D, $epreuve->getSession1Disp()],
                  [self::DBF_SESSION2D, $epreuve->getSession2Disp()] ];

        return MyPDO::update(self::DB, $data, $epreuve->getId(), self::DBF_ID);
	}

    /**
     * Supprime une épreuve de la base.
     * @param id l'identifiant de l'épreuve
     * @return 'true' on success or 'false' on error
     */
    public static function delete(int $id) : bool {
        $DB = MyPDO::getInstance();
        
        // #TODO# : suppression de toutes les notes saisies, des droits associés aux épreuves
        
        // Suppression de l'épreuve
        $SQL = "DELETE FROM ".self::DB." WHERE ".self::DBF_ID."=:id LIMIT 1";

        if(($requete = $DB->prepare($SQL)) && $requete->execute(array(":id" => $id)))
            return true;
        else
            return false;
    }    
    
} // class EpreuveModel