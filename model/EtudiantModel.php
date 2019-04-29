<?php
// *************************************************************************************************
// * Modèle pour les étudiants.
// *************************************************************************************************
class EtudiantModel {
    
    // Constantes pour la table des étudiants
    const DB = "inf_etudiant";                   // Table des diplômes
    const DBF_USER = "etu_user";                 // Identifiant utilisateur
    const DBF_NUMERO = "etu_numero";             // Numéro d'étudiant

    /**
     * Recherche un étudiant en fonction de son nom.
     * @param nom le nom de l'étudiant
     * @return un tableau d'étudiant
     */
    public static function rechercher(string $nom) : array {
        $DB = MyPDO::getInstance();
        $SQL = "SELECT ".UserModel::DBF_ID." as id, ".
                         self::DBF_NUMERO." as numero, ".
                         UserModel::DBF_NAME." as nom, ".
                         UserModel::DBF_FIRSTNAME." as prenom, ".
                         UserModel::DBF_MAIL." as email ".
               " FROM ".self::DB.", ".UserModel::DB.
               " WHERE ".self::DBF_USER."=".UserModel::DBF_ID." AND ".
                         "(".UserModel::DBF_NAME." LIKE :nom OR ".
                             UserModel::DBF_FIRSTNAME." LIKE :nom)".
               " ORDER BY ".UserModel::DBF_NAME." ASC, ".UserModel::DBF_FIRSTNAME." ASC".
               " LIMIT 10";
        
        if(($request = $DB->prepare($SQL)) && $request->execute([':nom' => "%$nom%"])) {
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
     * Retourne la liste des étudiants.
     * @return la liste des étudiants (ou null en cas d'erreur)
     */
    public static function getList() : array {
        $DB = MyPDO::getInstance();
        $SQL = "SELECT ".UserModel::DBF_ID." as id, ".
                         self::DBF_NUMERO." as numero, ".
                         UserModel::DBF_NAME." as nom, ".
                         UserModel::DBF_FIRSTNAME." as prenom, ".
                         UserModel::DBF_MAIL." as email ".
               " FROM ".self::DB.", ".UserModel::DB.
               " WHERE ".self::DBF_USER."=".UserModel::DBF_ID.
               " ORDER BY ".UserModel::DBF_NAME." ASC, ".UserModel::DBF_FIRSTNAME." ASC;";
        
        if(($request = $DB->prepare($SQL)) && $request->execute()) {
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
     * Retourne un étudiant à partir d'un tableau associatif.
     * @param array le tableau associatif
     * @return l'étudiant
     */
    public static function fromArray(array $array) : Etudiant {
        return new Etudiant($array[self::DBF_USER],
                            $array[UserModel::DBF_NAME],
                            $array[UserModel::DBF_FIRSTNAME],
                            $array[self::DBF_NUMERO],
                            $array[UserModel::DBF_MAIL]);
    }
    
    /**
	 * Ajoute un étudiant dans la base.
     * @param etudiant l'étudiant à ajouter dans la base
	 * @return 'true' on success or 'false' on error
	 */
	public static function create(Etudiant $etudiant) : bool {
        $DB = MyPDO::getInstance();
        
        // Création de l'utilisateur associé ?
        $user = $etudiant->creerUtilisateur();
        if(!UserModel::create($user))
            return false;
        $etudiant->setIdUtilisateur($user->getId());
        
        // Création de l'étudiant
        $SQL = "INSERT INTO `".self::DB."` (`".self::DBF_USER."`, `".self::DBF_NUMERO.
               "`) VALUES (:id, :numero);";

        if(($requete = $DB->prepare($SQL)) &&
           $requete->execute(array(":id" => $etudiant->getIdUtilisateur(),
                                   ":numero" => $etudiant->getNumero()))) {
            return true;
        }
        else
            return false;
    }

    /**
	 * Récupère un étudiant depuis la base de données.
     * @param id l'identifiant de l'étudiant
	 * @return l'étudiant ou 'null' en cas d'erreur
	 */
	public static function read(int $id) : ?Etudiant {
        $DB = MyPDO::getInstance();
        $SQL = "SELECT * FROM `".self::DB."`, `".UserModel::DB."` WHERE `".
               self::DBF_USER."`=`".UserModel::DBF_ID."` AND `".
               self::DBF_USER."`=:id";

        if(($requete = $DB->prepare($SQL)) &&
           $requete->execute(array(":id" => $id)) && 
           ($row = $requete->fetch()))
            return self::fromArray($row);
        else
            return null;
	}
    
    /**
	 * Sauve les modifications d'un étudiant dans la base.
     * @param etudiant l'étudiant
	 * @return 'true' on success or 'false' on error
	 */
	public static function update(Etudiant $etudiant) : bool {
        // Modification des données utilisateur
        $user = $etudiant->creerUtilisateur();
        if(!UserModel::update($user))
            return false;
        
        // Modification des données étudiant
        $data = array(array(self::DBF_NUMERO, $etudiant->getNumero()));
        
        return MyPDO::update(self::DB, $data, $etudiant->getIdUtilisateur(), self::DBF_USER);
	}	
    
    /**
     * Supprime un étudiant de la base (et l'utilisateur associé).
     * @param id l'identifiant de l'étudiant
     * @return 'true' on success or 'false' on error
     */
    public static function delete(int $id) : bool {
        $DB = MyPDO::getInstance();
        
        $SQL = "DELETE FROM `".self::DB."` WHERE `".self::DBF_USER."`=:id LIMIT 1";

        if(($requete = $DB->prepare($SQL)) && $requete->execute(array(":id" => $id)))
            return UserModel::delete($id);
        else
            return false;
    }
    
    /**
     * Ajoute une liste d'étudiants à la base ou récupère leur identifiant.
     * @param[in,out] liste la liste des étudiants (numéro, nom, prénom et email)
     *                      ajoute 'id' et 'statut' (CREE, EXISTE, ERREUR_CREATION_USER, ERREUR_CREATION_ETUDIANT, INEXISTANT)
     * @param creation si 'true' les étudiants sont créés
     */
    public static function updateList(array &$liste, bool $creation = true) : void {
        $DB = MyPDO::getInstance();
        
        $SQL1 = "SELECT `".self::DBF_USER."` FROM `".self::DB."` WHERE ".self::DBF_NUMERO."=:num";
        
        $SQL2 = "INSERT INTO `".self::DB."` (`".self::DBF_USER."`, `".self::DBF_NUMERO.
                "`) VALUES (:id, :numero);";
                
        if(($requete1 = $DB->prepare($SQL1)) && ($requete2 = $DB->prepare($SQL2))) {
            for($i = 0; $i < count($liste); $i++) {
                if($requete1->execute(array(":num" => $liste[$i]['numero'])) && ($requete1->rowCount() == 0)) {
                    if($creation) {
                        $user = new User(-1, $liste[$i]['nom'], $liste[$i]['prenom'], $liste[$i]['email'], User::generatePassword(), User::TYPE_ETUDIANT);
                        if(!UserModel::create($user)) {
                            $liste[$i]['id'] = -1;
                            $liste[$i]['statut'] = 'ERREUR_CREATION_USER';
                        }
                        else {
                            $liste[$i]['id'] = $user->getId();
                            
                            // Création de l'étudiant
                            if($requete2->execute(array(":id" => $user->getId(),
                                                        ":numero" => $liste[$i]['numero'])))
                                $liste[$i]['statut'] = 'CREE';
                            else
                                $liste[$i]['statut'] = 'ERREUR_CREATION_ETUDIANT';
                        }
                    }
                    else {
                        $liste[$i]['id'] = -1;
                        $liste[$i]['statut'] = 'INEXISTANT';
                    }
                }
                else {            
                    $row = $requete1->fetch();
                    $liste[$i]['id'] = $row[self::DBF_USER];
                    $liste[$i]['statut'] = 'EXISTE';
                }
            }
        }
    }
    
} // class EtudiantModel