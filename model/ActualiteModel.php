<?php
// *************************************************************************************************
// * Modèle pour les actualités.
// *************************************************************************************************
class ActualiteModel {
    
    // Constantes pour la table des actualités
    const DB = "inf_actualite";                  // Table des actualités
    const DBF_ID = "act_id";                     // Identifiant
    const DBF_DATE = "act_date";                 // Date
    const DBF_TITRE = "act_titre";               // Titre
    const DBF_CONTENU = "act_contenu";           // Contenu
    const DBF_TITRELIEN = "act_titrelien";       // Titre du lien
    const DBF_LIEN = "act_lien";                 // Lien
    const DBF_PRIORITE = "act_priorite";         // Priorité
    const DBF_ANNEES = "act_annees";             // Années
    const DBF_ACTIVE = "act_active";             // Active
    
    /**
     * Retourne la liste des actualités.
     * @param priorite si 'true', liste uniquement les actualités prioritaires
     * @param active si 'true', liste uniquement les actualités actives
     * @return la liste des actualités (ou null en cas d'erreur)
     */
    public static function getList(bool $priorite = false, bool $active = true, string $annee = "") : array {
        $DB = MyPDO::getInstance();
        $SQL = "SELECT * FROM `".self::DB."` ";
        
        $where = array();
        if($priorite)
            $where[]= self::DBF_PRIORITE."=1";
        if($active)
            $where[]= self::DBF_ACTIVE."=1";
        if($annee != "")
            $where[]= self::DBF_ANNEES." LIKE '%$annee%'";
        if(count($where) > 0) {
            $SQL .= " WHERE ".$where[0];
            for($i = 1; $i < count($where); $i++)
                $SQL .= " AND ".$where[$i];
        }
        $SQL .= " ORDER BY ".self::DBF_DATE." DESC;";
        
        if(($request = $DB->prepare($SQL)) && $request->execute()) {
            $result = array();
                
            while($row = $request->fetch()) {
                $result[] = self::fromArray($row);
            }
            return $result;
        }
        else
            return null;
    }
    
    /**
     * Retourne une actualité à partir d'un tableau associatif.
     * @param array le tableau associatif
     * @return l'actualité
     */
    public static function fromArray(array $array) : Actualite {
        return new Actualite($array[self::DBF_ID], $array[self::DBF_DATE], $array[self::DBF_TITRE], 
                             $array[self::DBF_CONTENU], $array[self::DBF_TITRELIEN], $array[self::DBF_LIEN],
                             DataTools::int2Boolean($array[self::DBF_PRIORITE]),
                             $array[self::DBF_ANNEES],
                             DataTools::int2Boolean($array[self::DBF_ACTIVE]));
    }
    
    /**
	 * Ajoute une actualité dans la base.
     * @param actualite l'actualité à ajouter dans la base
	 * @return 'true' on success or 'false' on error
	 */
	public static function create(Actualite $actualite) : bool {
        $DB = MyPDO::getInstance();
        $SQL = "INSERT INTO `".self::DB."` (`".self::DBF_ID."`, `".self::DBF_DATE."`, `".self::DBF_TITRE.
               "`, `".self::DBF_CONTENU."`, `".self::DBF_TITRELIEN."`, `".self::DBF_LIEN."`, `".self::DBF_PRIORITE.
               "`, `".self::DBF_ANNEES."`, `".self::DBF_ACTIVE."`".
               ") VALUES (NULL, :date, :titre, :contenu, :titreLien, :lien, :priorite, :annees, :active);";

        if(($requete = $DB->prepare($SQL)) &&
           $requete->execute(array(":date" => $actualite->getDate(),
                                   ":titre" => $actualite->getTitre(),
                                   ":contenu" => $actualite->getContenu(),
                                   ":titreLien" => $actualite->getTitreLien(),
                                   ":lien" => $actualite->getLien(),
                                   ":priorite" => DataTools::boolean2Int($actualite->estPrioritaire()),
                                   ":annees" => $actualite->getAnnees(),
                                   ":active" => DataTools::boolean2Int($actualite->estActive())))) {
            $actualite->setId($DB->lastInsertId());
            return true;
        }
        else
            return false;
    }
    
    /**
	 * Récupère une actualité depuis la base de données.
     * @param id l'identifiant de l'actualité
	 * @return l'actualité ou 'null' en cas d'erreur
	 */
	public static function read(int $id) : Actualite {
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
	 * Sauve les modifications d'une actualité dans la base.
     * @param actualite l'actualité
	 * @return 'true' on success or 'false' on error
	 */
	public static function update(Actualite $actualite) : bool {
        $data = array(array(self::DBF_DATE,      $actualite->getDate()),
                      array(self::DBF_TITRE,     $actualite->getTitre()),
                      array(self::DBF_CONTENU,   $actualite->getContenu()),
                      array(self::DBF_TITRELIEN, $actualite->getTitreLien()),
                      array(self::DBF_LIEN,      $actualite->getLien()),
                      array(self::DBF_PRIORITE,  DataTools::boolean2Int($actualite->estPrioritaire())),
                      array(self::DBF_ANNEES,    $actualite->getAnnees()),
                      array(self::DBF_ACTIVE,    DataTools::boolean2Int($actualite->estActive())),
                      );
        
        return MyPDO::update(self::DB, $data, $actualite->getId(), self::DBF_ID);
	}	

    /**
     * Active ou désactive l'actualité.
     * @param id l'identifiant de l'actualité
     * @param active si 'true' active l'actualité sinon la désactive
     * @return 'true' on success or 'false' on error
     */
    public static function active(int $id, bool $active) : bool {
        $DB = MyPDO::getInstance();
        $SQL = "UPDATE `".self::DB."` SET `".self::DBF_ACTIVE."`=:active".
               " WHERE `".self::DBF_ID."`=:id LIMIT 1";

        if(($requete = $DB->prepare($SQL)) && $requete->execute(array(":id" => $id, ":active" => DataTools::boolean2Int($active))))
            return true;
        else
            return false;
    }
    
    /**
     * Supprime une actualité de la base.
     * @param id l'identifiant de l'actualité
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
    
} // class ActualiteModel