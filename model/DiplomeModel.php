<?php
// *************************************************************************************************
// * Modèle pour les diplômes.
// *************************************************************************************************
class DiplomeModel {
    
    // Constantes pour la table des diplômes
    const DB = "inf_diplome";                    // Table des diplômes
    const DBF_ID = "dip_id";                     // Identifiant
    const DBF_INTITULE = "dip_intitule";         // Intitulé
    const DBF_MINSEMESTRE = "dip_minsemestre";   // Semestre min.
    const DBF_NBSEMESTRES = "dip_nbsemestres";   // Nombre de semestres
    
    /**
     * Retourne la liste des diplômes.
     * @return la liste des diplômes (ou null en cas d'erreur)
     */
    public static function getList() : array {
        $DB = MyPDO::getInstance();
        $SQL = "SELECT ".self::DBF_ID." as id, ".
                         self::DBF_INTITULE." as intitule, ".
                         self::DBF_MINSEMESTRE." as minSemestre, ".
                         self::DBF_NBSEMESTRES." as nbSemestres".
               " FROM ".self::DB.
               " ORDER BY ".self::DBF_INTITULE." ASC;";
        
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
     * Retourne un diplôme à partir d'un tableau associatif.
     * @param array le tableau associatif
     * @return le diplôme
     */
    public static function fromArray(array $array) : ?Diplome {
        return new Diplome($array[self::DBF_ID], 
                           $array[self::DBF_INTITULE],
                           $array[self::DBF_MINSEMESTRE],
                           $array[self::DBF_NBSEMESTRES]);
    }

    /**
	 * Ajoute un diplôme dans la base.
     * @param diplome le diplôme à ajouter dans la base
	 * @return 'true' on success or 'false' on error
	 */
	public static function create(Diplome $diplome) : bool {
        $DB = MyPDO::getInstance();
        $SQL = "INSERT INTO `".self::DB."` (`".self::DBF_ID."`, `".self::DBF_INTITULE."`, `".
               self::DBF_NBSEMESTRES."`) VALUES (NULL, :intitule, :minsemestre, :nbsemestres);";

        if(($requete = $DB->prepare($SQL)) &&
           $requete->execute(array(":intitule" => $diplome->getIntitule(),
                                   ":minsemestre" => $diplome->getMinSemestre(),
                                   ":nbsemestres" => $diplome->getNbSemestres()))) {
            $diplome->setId($DB->lastInsertId());
            return true;
        }
        else
            return false;
    }
    
    /**
	 * Récupère un diplôme depuis la base de données.
     * @param id l'identifiant du diplôme
	 * @return le diplôme ou 'null' en cas d'erreur
	 */
	public static function read(int $id) : ?Diplome {
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
	 * Sauve les modifications d'un diplôme dans la base.
     * @param diplome le diplôme
	 * @return 'true' on success or 'false' on error
	 */
	public static function update(Diplome $diplome) : bool {
        $data = [ [ self::DBF_INTITULE, $diplome->getIntitule() ],
                  [ self::DBF_MINSEMESTRE, $diplome->getMinSemestre() ] ];
        
        return MyPDO::update(self::DB, $data, $diplome->getId(), self::DBF_ID);
	}

    /**
     * Supprime un diplôme de la base.
     * @param id l'identifiant du diplôme
     * @return 'true' on success or 'false' on error
     */
    public static function delete(int $id) : bool {
        $DB = MyPDO::getInstance();
        
        // Suppression de toutes les UE
        if(!UEECModel::supprimerSemestre($id))
            return false;
        
        // Suppression du diplôme
        $SQL = "DELETE FROM `".self::DB."` WHERE `".self::DBF_ID."`=:id LIMIT 1";

        if(($requete = $DB->prepare($SQL)) && $requete->execute(array(":id" => $id)))
            return true;
        else
            return false;
    }

    /**
     * Ajoute un semestre au diplôme.
     * @param id l'identifiant du diplôme
     * @return 'true' on success or 'false' on error
     */
    public static function ajouterSemestre(int $id) : bool {
        $DB = MyPDO::getInstance();
        
        $SQL = "UPDATE `".self::DB."` SET `".self::DBF_NBSEMESTRES."`=`".
               self::DBF_NBSEMESTRES."`+1 WHERE `".self::DBF_ID."`=:id";
        if(($requete = $DB->prepare($SQL)) && $requete->execute(array(":id" => $id)))
            return true;
        else
            return false;
    }
    
    /**
     * Supprime un semestre du diplôme.
     * @param id l'identifiant du diplôme
     * @param num le numéro du semestre
     * @return 'true' on success or 'false' on error
     */
    public static function supprimerSemestre(int $id, int $num) : bool {
        $DB = MyPDO::getInstance();
        
        if(!UEECModel::supprimerSemestre($id, $num))
            return false;
                
        $SQL = "UPDATE ".self::DB." SET ".self::DBF_NBSEMESTRES."=".
               self::DBF_NBSEMESTRES."-1 WHERE ".self::DBF_ID."=:id";
        if(($requete = $DB->prepare($SQL)) && $requete->execute(array(":id" => $id)))
            return true;
        else
            return false;
    }
    
    /**
     * Supprime une UE d'un diplôme.
     * @param idDiplome l'identifiant du diplôme
     * @param idUE l'identifiant de l'UE
     * @return 'true' on success or 'false' on error
     */
    public static function supprimerUE(int $idDiplome, int $idUE) : bool {
        if(($UE = UEModel::read($idUE)) === false)
            return false;
        
        if($UE->getDiplome() == $idDiplome)
            return UEModel::delete($UE);
        else
            return false;
    }
    
    /**
     * Monter une UE d'un diplôme.
     * @param idDiplome l'identifiant du diplôme
     * @param idUE l'identifiant de l'UE
     * @return 'true' on success or 'false' on error
     */
    public static function monterUE(int $idDiplome, int $idUE) : bool {
        if(($UE = UEModel::read($idUE)) === false)
            return false;
        
        if($UE->getDiplome() == $idDiplome)
            return UEModel::monterUE($UE);
        else
            return false;
    }
    
    /**
     * Descendre une UE d'un diplôme.
     * @param idDiplome l'identifiant du diplôme
     * @param idUE l'identifiant de l'UE
     * @return 'true' on success or 'false' on error
     */
    public static function descendreUE(int $idDiplome, int $idUE) : bool {
        if(($UE = UEModel::read($idUE)) === false)
            return false;
        
        if($UE->getDiplome() == $idDiplome)
            return UEModel::descendreUE($UE);
        else
            return false;
    }
    
    /**
     * Ajoute une EC à une UE d'un diplôme.
     * @param idDiplome l'identifiant du diplôme
     * @param idUE l'identifiant de l'UE
     * @param idEC l'identifiant de l'EC
     * @return 'true' on success or 'false' on error
     */
    public static function ajouterEC(int $idDiplome, int $idUE, int $idEC) : bool {
        if(($UE = UEModel::read($idUE)) === false)
            return false;
        
        if($UE->getDiplome() == $idDiplome)
            return UEECModel::ajouterEC($UE, $idEC);
        else
            return false;
    }
    
    /**
     * Monter une EC d'une UE d'un diplôme.
     * @param idDiplome l'identifiant du diplôme
     * @param idUE l'identifiant de l'UE
     * @param idEC l'identifiant de l'EC
     * @return 'true' on success or 'false' on error
     */
    public static function monterEC(int $idDiplome, int $idUE, int $idEC) : bool {
        if(($UE = UEModel::read($idUE)) === false)
            return false;
        
        if($UE->getDiplome() == $idDiplome)
            return UEECModel::monterEC($UE, $idEC);
        else
            return false;
    }
    
    /**
     * Descendre une EC d'une UE d'un diplôme.
     * @param idDiplome l'identifiant du diplôme
     * @param idUE l'identifiant de l'UE
     * @param idEC l'identifiant de l'EC
     * @return 'true' on success or 'false' on error
     */
    public static function descendreEC(int $idDiplome, int $idUE, int $idEC) : bool {
        if(($UE = UEModel::read($idUE)) === false)
            return false;
        
        if($UE->getDiplome() == $idDiplome)
            return UEECModel::descendreEC($UE, $idEC);
        else
            return false;
    }    
    
    /**
     * Supprime une UE d'un diplôme.
     * @param idDiplome l'identifiant du diplôme
     * @param idUE l'identifiant de l'UE
     * @param idEC l'identifiant de l'EC
     * @return 'true' on success or 'false' on error
     */
    public static function supprimerEC(int $idDiplome, int $idUE, int $idEC) : bool {
        if(($UE = UEModel::read($idUE)) === false)
            return false;
        
        if($UE->getDiplome() == $idDiplome)
            return UEECModel::supprimerEC($UE, $idEC);
        else
            return false;
    }    
    
} // class DiplomeModel