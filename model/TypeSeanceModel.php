<?php

class TypeSeanceModel {
    const DB = "inf_type_sean";
    const DBF_ID = "type_sean_id";
    const DBF_INTITULE = "type_sean_intitule";
    const DBF_COULEUR = "type_sean_couleur";

    /**
     * Créé un nouveau type de séance
     * @param string $type
     * @return l'ID du type (ou -1 en cas d'erreur)
     */
    public static function create(string $type, string $couleur) : int {
        $DB = MyPDO::getInstance();
        $SQL = "INSERT INTO `".self::DB."` (`".self::DBF_ID."`, `".self::DBF_INTITULE."`, `".self::DBF_COULEUR.
            "`) VALUES (NULL, :type, :couleur);";

        if(($requete = $DB->prepare($SQL)) &&
            $requete->execute(array(":type" => $type,
                ":couleur" => $couleur))) {
            return $DB->lastInsertId();
        }
        else
            return -1;
    }

    /**
     * Récupère l'ID du type de séance ou en créé un s'il n'existe pas
     * @param string $type
     * @param string $couleur
     * @return int
     */
    public static function getOrCreate(string $type, string $couleur) : int {
        $DB = MyPDO::getInstance();
        $SQL = 'SELECT * FROM ' . self::DB.
               ' WHERE ' . self::DBF_INTITULE . ' = :type;';

        if(($requete = $DB->prepare($SQL)) &&
            $requete->execute(array(":type" => $type)) &&
            ($row = $requete->fetch()))
            return $row[self::DBF_ID];
        else {
            return self::create($type, $couleur);
        }
    }
}