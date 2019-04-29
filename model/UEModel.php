<?php
// *************************************************************************************************
// * Modèle pour les UE.
// *************************************************************************************************
class UEModel {

    // Constantes pour la table des UE
    const DB = "inf_ue";                           // Table des UE
    const DBF_ID = "uen_id";                       // Identifiant
    const DBF_DIPLOME = "uen_diplome";             // Diplôme
    const DBF_SEMESTRE = "uen_semestre";           // Semestre
    const DBF_POSITION = "uen_position";           // Position
    
    /**
     * Retourne une UE à partir d'un tableau associatif.
     * @param array le tableau associatif
     * @return l'UE
     */
    public static function fromArray(array $array) : ?UE {
        return new UE($array[self::DBF_ID], 
                      $array[self::DBF_DIPLOME],
                      $array[self::DBF_SEMESTRE],
                      $array[self::DBF_POSITION]);
    }    
    
    /**
	 * Récupère une UE depuis la base de données.
     * @param id l'identifiant de l'UE
	 * @return l'UE ou 'null' en cas d'erreur
	 */
	public static function read(int $id) : ?UE {
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
     * Supprime une UE d'un diplôme.
     * @param UE l'UE à supprimer
     * @return 'true' on success or 'false' on error
     */
    public static function delete(UE $UE) : bool {
        $DB = MyPDO::getInstance();
        
        // Suppression des EC de l'UE
        if(!UEECModel::delete($UE))
            return false;
        
        // Suppression de l'UE
        $SQL = "DELETE FROM ".self::DB." WHERE ".self::DBF_ID."=:idUE LIMIT 1;";
        if(!(($requete = $DB->prepare($SQL)) && 
             $requete->execute([ ":idUE" => $UE->getId()])))
            return false;
            
        // Décalage des autres UEs si nécessaire
        $SQL = "UPDATE ".self::DB." SET ".self::DBF_POSITION."=".self::DBF_POSITION."-1 WHERE ".
               self::DBF_POSITION.">:position AND ".self::DBF_DIPLOME."=:diplome AND ".
               self::DBF_SEMESTRE."=:semestre";
        if(($requete = $DB->prepare($SQL)) && 
           $requete->execute([ ":diplome" => $UE->getDiplome(), ":semestre" => $UE->getSemestre(), ":position" => $UE->getPosition()]))
            return true;
        else
            return false;
    }
    
    /**
     * Ajoute une UE à la dernière position d'un semestre d'un diplôme.
     * @param diplome l'identifiant du diplôme
     * @param semestre le numéro du semestre
     * @return 'true' on success or 'false' on error
     */
    public static function ajouterUE(int $diplome, int $semestre) : bool {
        $DB = MyPDO::getInstance();
        
        // Récupère la position max.
        $SQL = "SELECT MAX(".self::DBF_POSITION.") AS max FROM ".self::DB." WHERE ".
               self::DBF_DIPLOME."=:diplome AND ".self::DBF_SEMESTRE."=:semestre;";
        if(($request = $DB->prepare($SQL)) && $request->execute([':diplome' => $diplome, ':semestre' => $semestre])) {
            if($row = $request->fetch())
                $position = $row['max'] + 1;
            else
                $position = 1;
        }
        else
            return false;
        
        // Ajoute l'UE
        $SQL = "INSERT INTO ".self::DB." (".self::DBF_ID.", ".
                                            self::DBF_DIPLOME.", ".
                                            self::DBF_SEMESTRE.", ".
                                            self::DBF_POSITION.
                                       ") VALUES (NULL, :diplome, :semestre, :position);";
        
        if(($requete = $DB->prepare($SQL)) &&
           $requete->execute(array(":diplome" => $diplome, ':semestre' => $semestre, ':position' => $position)))
            return true;
        else
            return false;
    }
    
    /**
     * Remonte l'UE (ie diminue la position de 1).
     * @param UE l'UE à monter
     * @return 'true' on success or 'false' on error
     */
    public static function monterUE(UE $UE) : bool {
        $DB = MyPDO::getInstance();
        
        if($UE->getPosition() > 1) {
            // Descend l'UE précédente
            $SQL = "UPDATE ".self::DB." SET ".self::DBF_POSITION."=".self::DBF_POSITION."+1 WHERE ".
                   self::DBF_POSITION."=:position AND ".self::DBF_DIPLOME."=:diplome AND ".
                   self::DBF_SEMESTRE."=:semestre";

            if(!(($requete = $DB->prepare($SQL)) &&
                 $requete->execute([ ':position' => $UE->getPosition()-1, 
                                     ':diplome' => $UE->getDiplome(),
                                     ':semestre' => $UE->getSemestre() ])))
                return false;
                
            // Monte l'UE
            $SQL = "UPDATE ".self::DB." SET ".self::DBF_POSITION."=".self::DBF_POSITION."-1 WHERE ".
                   self::DBF_ID."=:id";
            
            if(!(($requete = $DB->prepare($SQL)) &&
                 $requete->execute([ ':id' => $UE->getId() ])))
                return false;
            else
                return true;
        }
        else
            return false;
    }
    
    /**
     * Descend l'UE (ie augmente la position de 1).
     * @param UE l'UE à descendre
     * @return 'true' on success or 'false' on error
     */
    public static function descendreUE(UE $UE) : bool {
        $DB = MyPDO::getInstance();
        
        // Monte l'UE suivante
        $SQL = "UPDATE ".self::DB." SET ".self::DBF_POSITION."=".self::DBF_POSITION."-1 WHERE ".
               self::DBF_POSITION."=:position AND ".self::DBF_DIPLOME."=:diplome AND ".
               self::DBF_SEMESTRE."=:semestre";

        if(!(($requete = $DB->prepare($SQL)) &&
             $requete->execute([ ':position' => $UE->getPosition()+1, 
                                 ':diplome' => $UE->getDiplome(),
                                 ':semestre' => $UE->getSemestre() ])))
            return false;
        
        if($requete->rowCount() == 0)
            return false;
            
        // Descend l'UE
        $SQL = "UPDATE ".self::DB." SET ".self::DBF_POSITION."=".self::DBF_POSITION."+1 WHERE ".
               self::DBF_ID."=:id";
        
        if(!(($requete = $DB->prepare($SQL)) &&
             $requete->execute([ ':id' => $UE->getId() ])))
            return false;
        else
            return true;
    }
    
} // class UEModel