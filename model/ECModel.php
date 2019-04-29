<?php
// *************************************************************************************************
// * Modèle pour les ECS
// *************************************************************************************************
class ECModel {
    
    // Constantes pour la table des EC
    const DB = "inf_ec";                         // Table des EC
    const DBF_ID = "eco_id";                     // Identifiant
    const DBF_CODE = "eco_code";                 // Code
    const DBF_INTITULE = "eco_intitule";         // Intitulé

    /**
     * Retourne la liste des ECS.
     * @return la liste des ECS (ou null en cas d'erreur)
     */
    public static function getList() : array {
        $DB = MyPDO::getInstance();
        $SQL = "SELECT ".self::DBF_ID." as id, ".
                         self::DBF_CODE." as code, ".
                         self::DBF_INTITULE." as intitule, ".
                         "CONCAT(".self::DBF_CODE.",' - ',".self::DBF_INTITULE.") as nom ".
               " FROM ".self::DB." ORDER BY ".self::DBF_CODE." ASC;";
        
        if(($request = $DB->prepare($SQL)) && $request->execute()) {
            $result = array();
                
            while($row = $request->fetch())
                $result[] = $row;
            return $result;
        }
        else
            return null;
    }  

    /**
     * Retourne la liste des ECs à partir de leur code.
     * @param[in,out] liste la liste des codes des ECs
     * @return 'true' en cas de réussite, 'false' en cas d'erreur
     */
    public static function getListFromCode(array &$liste) : bool {
        $DB = MyPDO::getInstance();
        
        $SQL = "SELECT `".self::DBF_ID."` FROM `".self::DB."` WHERE `".self::DBF_CODE."`=:code;";
        if($request = $DB->prepare($SQL)) {
            $result = true;
            for($i = 0; $i < count($liste); $i++) {
                if($request->execute([':code' => $liste[$i]['code']])) {
                    if($row = $request->fetch()) {
                        $liste[$i]['id'] = $row[self::DBF_ID];
                    }
                    else {
                        $result = false;
                        $liste[$i]['id'] = -1;
                    }
                }
                else {
                    $result = false;
                    $liste[$i]['id'] = -1;
                }
            }
            return $result;
        }
        else
            return false;
    }    
    
    /**
     * Retourne une EC à partir d'un tableau associatif.
     * @param array le tableau associatif
     * @return l'EC
     */
    public static function fromArray(array $array) : EC {
        return new EC($array[self::DBF_ID], 
                      $array[self::DBF_CODE], 
                      $array[self::DBF_INTITULE]);
    } 

    /**
	 * Ajoute une EC dans la base.
     * @param EC l'EC à ajouter dans la base
	 * @return 'true' on success or 'false' on error
	 */
	public static function create(EC $EC) : bool {
        $DB = MyPDO::getInstance();
        $SQL = "INSERT INTO `".self::DB."` (`".self::DBF_ID."`, `".self::DBF_CODE."`, `".self::DBF_INTITULE.
               "`) VALUES (NULL, :code, :intitule);";

        if(($requete = $DB->prepare($SQL)) &&
           $requete->execute(array(":code" => $EC->getCode(), 
                                   ":intitule" => $EC->getIntitule()))) {
            $EC->setId($DB->lastInsertId());
            return true;
        }
        else
            return false;
    }
    
    /**
	 * Récupère une EC depuis la base de données.
     * @param id l'identifiant de l'EC
	 * @return l'EC ou 'null' en cas d'erreur
	 */
	public static function read(int $id) : EC {
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
	 * Sauve les modifications d'une EC dans la base.
     * @param EC l'EC
	 * @return 'true' on success or 'false' on error
	 */
	public static function update(EC $EC) : bool {
        $data = array(array(self::DBF_CODE, $EC->getCode()),
                      array(self::DBF_INTITULE, $EC->getIntitule()));
        
        return MyPDO::update(self::DB, $data, $EC->getId(), self::DBF_ID);
	}

    /**
     * Supprime une EC de la base.
     * @param id l'identifiant de l'EC
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
    
} // class ECModel