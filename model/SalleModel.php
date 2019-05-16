<?php
class SalleModel {

    const DB = "inf_salle";                                 // Table des salles
    const DBF_ID = "rom_id";
    const DBF_INTITULE = "rom_intitule";

    /**
     * Créé une nouvelle salle
     * @param string $type
     * @return l'ID de la salle (ou -1 en cas d'erreur)
     */
    public static function create(string $intitule) : int {
        $DB = MyPDO::getInstance();
        $SQL = "INSERT INTO `".self::DB."` (`".self::DBF_ID."`, `".self::DBF_INTITULE."`) VALUES (NULL, :intitule);";

        if(($requete = $DB->prepare($SQL)) &&
            $requete->execute(array(":intitule" => $intitule))) {
            return $DB->lastInsertId();
        }
        else
            return -1;
    }

    /**
     * Récupère l'ID de la salle ou en créé une si elle n'existe pas
     * @param string $intitule
     * @return int
     */
    public static function getOrCreate(string $intitule) : int {
        $DB = MyPDO::getInstance();
        $SQL = 'SELECT * FROM ' . self::DB.
            ' WHERE ' . self::DBF_INTITULE . ' = :intitule;';

        if(($requete = $DB->prepare($SQL)) &&
            $requete->execute(array(":intitule" => $intitule)) &&
            ($row = $requete->fetch()))
            return $row[self::DBF_ID];
        else {
            return self::create($intitule);
        }
    }
}