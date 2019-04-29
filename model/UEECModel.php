<?php
// *************************************************************************************************
// * Modèle pour les liaisons EC dans les UE.
// *************************************************************************************************
class UEECModel {
    
    // Constantes pour la table de relation UE-EC
    const DB = "inf_ueec";                          // Table des UE/EC
    const DBF_UE = "uee_ue";                        // UE
    const DBF_EC = "uee_ec";                        // EC
    const DBF_POSITION = "uee_position";            // Position   
    
    /**
     * Suppression des EC d'une UE
     * @param UE l'UE
     * @return 'true' on success or 'false' on error
     */
    public static function delete(UE $UE) : bool {
        $DB = MyPDO::getInstance();
        
        $SQL = "DELETE FROM ".self::DB." WHERE ".self::DBF_UE."=:idUE";
        return (($requete = $DB->prepare($SQL)) && $requete->execute([ ":idUE" => $UE->getId()]));
    }
    
    /**
     * Supprime un semestre du diplôme.
     * @param id l'identifiant du diplôme
     * @param num le numéro du semestre (ou -1 si suppression de tous les semestres)
     * @return 'true' on success or 'false' on error
     */
    public static function supprimerSemestre(int $id, int $num = -1) : bool {
        $DB = MyPDO::getInstance();
        
        // Supprimer toutes les liaisons avec les UEs de ce semestre
        $SQL = "DELETE ".self::DB." FROM ".self::DB." LEFT JOIN ".UEModel::DB." ON (".
               self::DBF_UE."=".UEModel::DBF_ID.") WHERE ".
               UEModel::DBF_DIPLOME."=:diplome";
        $data = [ ":diplome" => $id ];
        if($num != -1) {
            $SQL .= " AND ".UEModel::DBF_SEMESTRE."=:semestre";
            $data[':semestre'] = $num;
        }
        if(!(($requete = $DB->prepare($SQL)) && 
             $requete->execute($data)))
            return false;
            
        // Supprimer toutes les UEs de ce semestre
        $SQL = "DELETE FROM ".UEModel::DB." WHERE ".
               UEModel::DBF_DIPLOME."=:diplome";
        if($num != -1)
            $SQL .= " AND ".UEModel::DBF_SEMESTRE."=:semestre";
        if(!(($requete = $DB->prepare($SQL)) && 
             $requete->execute($data)))
            return false;
            
        // Mise-à-jour du numéro de semestre pour les autres UE
        if($num != -1) {
            $SQL = "UPDATE ".UEModel::DB." SET ".UEModel::DBF_SEMESTRE."=".UEModel::DBF_SEMESTRE."-1 WHERE ".
                   UEModel::DBF_SEMESTRE.">:semestre AND ".UEModel::DBF_DIPLOME."=:diplome";
            if(!(($requete = $DB->prepare($SQL)) && $requete->execute($data)))
                return false;
        } 
        
        return true;
    }
    
    /**
     * Retourne la liste des ECs d'un diplôme.
     * @param diplome l'identifiant du diplôme
     * @return la liste des ECs
     */
    public static function getListEC(int $diplome) : ?array {
        $DB = MyPDO::getInstance();
        
        $SQL = "SELECT ".ECModel::DBF_ID." as id, ".
                         ECModel::DBF_CODE." as code, ".
                         ECModel::DBF_INTITULE." as intitule".
               " FROM ".ECModel::DB.", ".UEModel::DB.", ".self::DB.
               " WHERE ".ECModel::DBF_ID."=".self::DBF_EC." AND ".
                         UEModel::DBF_ID."=".self::DBF_UE." AND ".
                         UEModel::DBF_DIPLOME."=:diplome".
               " ORDER BY code ASC";
               
        if(($requete = $DB->prepare($SQL)) && $requete->execute([':diplome' => $diplome])) {
            $result = [];
            while($row = $requete->fetch())
                $result[] = $row;
            return $result;
        }
        else
            return null;
    }
    
    /**
     * Retourne la liste des UE/EC d'un diplôme struturée par UE.
     * @param diplome l'identifiant du diplôme
     * @param semestre le numéro du semestre (-1 si les deux)
     * @return la liste des UE/EC
     */
    public static function getUEEC(int $diplome, int $semestre = -1) : ?array {
        $DB = MyPDO::getInstance();
        
        $SQL_UE = "SELECT * FROM ".UEModel::DB." WHERE ".UEModel::DBF_DIPLOME."=:diplome";
        $data_UE = array(":diplome" => $diplome);
        if($semestre != -1) {
            $SQL_UE .= " AND ".UEModel::DBF_SEMESTRE."=:semestre";
            $data_UE[":semestre"] = $semestre;
        }
        $SQL_UE .= " ORDER BY ".UEModel::DBF_POSITION." ASC";
        
        $SQL_EC = "SELECT * FROM ".ECModel::DB.", ".self::DB." WHERE ".
                  self::DBF_EC."=".ECModel::DBF_ID." AND ".
                  self::DBF_UE."=:UE".
                  " ORDER BY ".self::DBF_POSITION." ASC";
        
        if(($request_UE = $DB->prepare($SQL_UE)) &&
           ($request_EC = $DB->prepare($SQL_EC)) &&
           $request_UE->execute($data_UE)) {
            $result = array();
            if($semestre != -1)
                $result['semestre'] = $semestre;
            
            while($UE = $request_UE->fetch()) {
                if(!isset($result[$UE[UEModel::DBF_SEMESTRE]]))
                    $result[$UE[UEModel::DBF_SEMESTRE]] = [];
                
                if($request_EC->execute([':UE' => $UE[UEModel::DBF_ID]])) {
                    $result[$UE[UEModel::DBF_SEMESTRE]][$UE[UEModel::DBF_POSITION]] = 
                        [ 'id' => $UE[UEModel::DBF_ID], 'EC' => [] ];
                    
                    while($EC = $request_EC->fetch()) {
                        $result[$UE[UEModel::DBF_SEMESTRE]][$UE[UEModel::DBF_POSITION]]['EC'][] =
                            [ "id" => $EC[ECModel::DBF_ID],
                              "code" => $EC[ECModel::DBF_CODE],
                              "intitule" => $EC[ECModel::DBF_INTITULE]
                            ];
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
     * Ajouter une EC à une UE.
     * @param UE l'UE
     * @param EC l'EC
     * @return 'true' on success or 'false' on error
     */
    public static function ajouterEC(UE $UE, int $EC) {
        $DB = MyPDO::getInstance();
        
        // Récupère la position max.
        $SQL = "SELECT MAX(".self::DBF_POSITION.") AS max FROM ".self::DB." WHERE ".
               self::DBF_UE."=:id";
        if(($request = $DB->prepare($SQL)) && $request->execute([':id' => $UE->getId()])) {
            if($row = $request->fetch())
                $position = $row['max'] + 1;
            else
                $position = 1;
        }
        else
            return false;
        
        // Ajoute l'EC
        $SQL = "INSERT INTO ".self::DB." (".self::DBF_UE.", ".self::DBF_EC.", ".
               self::DBF_POSITION.") VALUES(:ue, :ec, :position);";
        
        if(($requete = $DB->prepare($SQL)) &&
           $requete->execute(array(":ue" => $UE->getId(), ':ec' => $EC, ':position' => $position)))
            return true;
        else
            return false;
    }

    /**
     * Remonte l'EC d'une UE (ie diminue la position de 1).
     * @param UE l'UE
     * @param idEC l'identifiant de l'EC
     * @return 'true' on success or 'false' on error
     */
    public static function monterEC(UE $UE, int $idEC) : bool {
        $DB = MyPDO::getInstance();
        
        // Récupérer la position
        $SQL = "SELECT * FROM ".self::DB." WHERE ".self::DBF_UE."=:ue AND ".self::DBF_EC."=:ec;";
        if(!(($requete = $DB->prepare($SQL)) &&
             $requete->execute([ ':ue' => $UE->getId(), 
                                 ':ec' => $idEC ])))
            return false;

        $position = 1;
        if($row = $requete->fetch())
            $position = $row[self::DBF_POSITION];
               
        if($position > 1) {
            // Descend l'EC précédente
            $SQL = "UPDATE ".self::DB." SET ".self::DBF_POSITION."=".self::DBF_POSITION."+1 WHERE ".
                   self::DBF_UE."=:ue AND ".self::DBF_POSITION."=:position;";

            if(!(($requete = $DB->prepare($SQL)) &&
                 $requete->execute([ ':position' => $position - 1, 
                                     ':ue' => $UE->getId() ])))
                return false;
                
            // Monte l'EC
            $SQL = "UPDATE ".self::DB." SET ".self::DBF_POSITION."=".self::DBF_POSITION."-1 WHERE ".
                   self::DBF_UE."=:ue AND ".self::DBF_EC."=:ec;";
            
            if(!(($requete = $DB->prepare($SQL)) &&
                 $requete->execute([ ':ue' => $UE->getId(), ':ec' => $idEC ])))
                return false;
            else
                return true;
        }
        else
            return false;
    }
    
    /**
     * Descend l'EC d'une UE (ie augmente la position de 1).
     * @param UE l'UE
     * @param idEC l'identifiant de l'EC
     * @return 'true' on success or 'false' on error
     */
    public static function descendreEC(UE $UE, int $idEC) : bool {
        $DB = MyPDO::getInstance();
        
        // Récupérer la position
        $SQL = "SELECT * FROM ".self::DB." WHERE ".self::DBF_UE."=:ue AND ".self::DBF_EC."=:ec;";
        if(!(($requete = $DB->prepare($SQL)) &&
             $requete->execute([ ':ue' => $UE->getId(), 
                                 ':ec' => $idEC ])))
            return false;

        $position = 1;
        if($row = $requete->fetch())
            $position = $row[self::DBF_POSITION];
               
        // Monte l'EC suivante
        $SQL = "UPDATE ".self::DB." SET ".self::DBF_POSITION."=".self::DBF_POSITION."-1 WHERE ".
               self::DBF_POSITION."=:position AND ".self::DBF_UE."=:ue;";

        if(!(($requete = $DB->prepare($SQL)) &&
             $requete->execute([ ':position' => $position + 1,
                                 ':ue' => $UE->getId() ])))
            return false;
        
        if($requete->rowCount() == 0)
            return false;
            
        // Descend l'EC
        $SQL = "UPDATE ".self::DB." SET ".self::DBF_POSITION."=".self::DBF_POSITION."+1 WHERE ".
               self::DBF_UE."=:ue AND ".self::DBF_EC."=:ec;";
        
        if(!(($requete = $DB->prepare($SQL)) &&
                 $requete->execute([ ':ue' => $UE->getId(), ':ec' => $idEC ])))
            return false;
        else
            return true;
    }     
    
    /**
     * Supprime une EC d'une UE.
     * @param UE l'UE
     * @param idEC l'identifiant de l'EC
     * @return 'true' on success or 'false' on error
     */
    public static function supprimerEC(UE $UE, int $idEC) : bool {
        $DB = MyPDO::getInstance();
        
        // Récupérer la position
        $SQL = "SELECT * FROM ".self::DB." WHERE ".self::DBF_UE."=:ue AND ".self::DBF_EC."=:ec;";
        if(!(($requete = $DB->prepare($SQL)) &&
             $requete->execute([ ':ue' => $UE->getId(), 
                                 ':ec' => $idEC ])))
            return false;
        
        $position = 1;
        if($row = $requete->fetch())
            $position = $row[self::DBF_POSITION];        
        
        // Suppression de l'EC
        $SQL = "DELETE FROM ".self::DB." WHERE ".self::DBF_UE."=:ue AND ".self::DBF_EC."=:ec LIMIT 1;";
        if(!(($requete = $DB->prepare($SQL)) && 
             $requete->execute([ ":ue" => $UE->getId(), ':ec' => $idEC ])))
            return false;
            
        // Décalage des autres UEs si nécessaire
        $SQL = "UPDATE ".self::DB." SET ".self::DBF_POSITION."=".self::DBF_POSITION."-1 WHERE ".
               self::DBF_POSITION.">:position AND ".self::DBF_UE."=:ue;";
        if(($requete = $DB->prepare($SQL)) && 
           $requete->execute([ ":ue" => $UE->getId(), ":position" => $position ]))
            return true;
        else
            return false;
    }
            
    /**
     * Indique si une EC existe dans une liste de diplômes.
     * @param id l'identifiant de l'EC
     * @param diplomes la liste des identifiants de diplômes
     * @return 'true' si oui, 'false' sinon
     */
    public static function existeDiplome(int $id, array $diplomes) : bool {
        $DB = MyPDO::getInstance();
        
        $data = [];
        $data[':EC'] = $id;
        $SQL = "SELECT COUNT(*) as nb FROM ".UEModel::DB.", ".self::DB.
               " WHERE ".self::DBF_EC."=:EC AND ".
                         self::DBF_UE."=".self::DBF_UE_ID." AND (";
        $i = 0;
        for($i = 0; $i < count($diplomes); $i++) {
            $SQL .= UEModel::DBF_DIPLOME."=:diplome$i";
            if($i < count($diplomes) - 1) $SQL .= " OR ";
            $data[":diplome$i"] = $i;
        }
        $SQL .= ")";
        if(($requete = $DB->prepare($SQL)) && $requete->execute($data) && ($row = $requete->fetch()))
            return $row['nb'] != 0;
        else
            return false;
    }
    
} // class UEECModel