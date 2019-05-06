<?php
// *************************************************************************************************
// * Modèle pour les séances importées de l'outil Celcat.
// *************************************************************************************************

class CCSeanceModel {

    // Constantes pour la table des séances
    const DB = "inf_cc_sean";                       // Table des séances
    const DBF_ID = "cc_sean_id";                    // Identifiant
    const DBF_EC = "cc_sean_ec";                    // EC de la séance
    const DBF_GROUPE = "cc_sean_groupe";            // Groupe de la séance
    const DBF_TYPE = "cc_sean_type";                // Type de la séance
    const DBF_SALLE = "cc_sean_salle";              // Salle de la séance
    const DBF_DEBUT = "cc_sean_debut";              // Date du début de la séance
    const DBF_FIN = "cc_sean_fin";                  // Date de la fin de la séance

    public static function create(CelcatEvent $seance) : bool {
        $DB = MyPDO::getInstance();

        $dbName = self::DB;
        $dbfID = self::DBF_ID;
        $dbfEC = self::DBF_EC;
        $dbfGrp = self::DBF_GROUPE;
        $dbfType = self::DBF_TYPE;
        $dbfSalle = self::DBF_SALLE;
        $dbfDebut = self::DBF_DEBUT;
        $dbfFin = self::DBF_FIN;

        $idType = TypeSeanceModel::getOrCreate($seance->getType(), $seance->getCouleur());
        $idSalle = SalleModel::getOrCreate($seance->getSalle());

        $ecs = array(array('code' => $seance->getEC(), 'id' => null));
        ECModel::getListFromCode($ecs);
        $idEC = $ecs[0]['id'];

        $SQL = "INSERT INTO `$dbName`(`$dbfID`, `$dbfEC`, `$dbfGrp`, `$dbfType`, `$dbfSalle`, `$dbfDebut`, `$dbfFin`) 
                VALUES (NULL, :ec, :groupe, :type, :salle, :debut, :fin);";

        if(($requete = $DB->prepare($SQL)) &&
            $requete->execute(array(":ec" => $idEC,
                ":groupe" => $seance->getGroupeID(),
                ":type" => $idType,
                ":salle" => $idSalle,
                ":debut" => $seance->getDateDebut(),
                ":fin" => $seance->getDateFin()))) {
            $seance->setId($DB->lastInsertId());
            return true;
        }
        else
            return false;
    }

    /**
     * Supprime toutes les séances associées à un groupe
     * @param idGroupe ID du groupe
     * @return bool 'true' on success or 'false' on error
     */
    public static function deleteSeancesFromGroupe(int $idGroupe) : bool {
        $DB = MyPDO::getInstance();
        $SQL = "DELETE FROM `".self::DB."` WHERE `".self::DBF_GROUPE."`=:groupe;";

        if(($requete = $DB->prepare($SQL)) && $requete->execute(array(":groupe" => $idGroupe)))
            return true;
        else
            return false;
    }

    /**
     * Récupère toutes les séances du groupe sur une semaine
     * @param int $week
     * @param $intituleGroupe string intitulé du groupe à récupérer
     * @return la liste des séances (ou null en cas d'erreur)
     */
    public static function getSeancesOfTheWeek(int $week, string $idGroupe) : ?array {
        $DB = MyPDO::getInstance();

        list($debut, $fin) = self::getWeekTime($week);
        $SQL = 'SELECT ' . self::DB . '.' . self::DBF_ID . ' AS id' .
               ', ' . self::DBF_DEBUT . ' AS debut ' .
               ', ' . self::DBF_FIN . ' AS fin ' .
               ', ' . ECModel::DBF_CODE . ' AS ec' .
               ', ' . TypeSeanceModel::DBF_INTITULE . ' AS type' .
               ', ' . TypeSeanceModel::DBF_COULEUR . ' AS couleur' .
               ', ' . SalleModel::DBF_INTITULE . ' AS salle' .
               ' from ' . self::DB .
               ' LEFT JOIN ' . GroupeModel::DB .
                   ' ON ' . self::DB . '.' . self::DBF_GROUPE . ' = ' . GroupeModel::DB . '.' . GroupeModel::DBF_ID .
               ' LEFT JOIN ' . SalleModel::DB .
                   ' ON ' . self::DB . '.' . self::DBF_SALLE . ' = ' . SalleModel::DB . '.' . SalleModel::DBF_ID .
               ' LEFT JOIN ' . TypeSeanceModel::DB .
                   ' ON ' . self::DB . '.' . self::DBF_TYPE . ' = ' . TypeSeanceModel::DB . '.' . TypeSeanceModel::DBF_ID .
               ' LEFT JOIN ' . ECModel::DB .
               ' ON ' . self::DB . '.' . self::DBF_EC . ' = ' . ECModel::DB . '.' . ECModel::DBF_ID .
               ' WHERE ' . self::DBF_DEBUT . ' >= :debut'.
               ' AND ' . self::DBF_FIN . ' <= :fin'.
               ' AND ' . self::DBF_GROUPE . ' = :idGroupe';

        if(($requete = $DB->prepare($SQL)) && $requete->execute(array(
                ":idGroupe" => $idGroupe,
                ":debut" => $debut,
                ":fin" => $fin))) {
            $result = array();

            while($row = $requete->fetch()) {
                $result[] = $row;
            }

            return $result;
        }
        else
            return null;
    }

    /**
     * Donne les timestamp de la semaine
     * @param week int le numéro de la semaine
     * @return array [debut semaine (Lundi), fin semaine (Samedi)]
     */
    public static function getWeekTime(int $week, int $year = -1) : array {

        if($year == -1)
            $year = intval(date('Y'));

        $date = new DateTime();
        $date->setISODate(date('Y'), $week);
        $debut = $date->getTimestamp();
        $fin = $debut + 5 * 24 * 3600;

        return array(
            $debut,
            $fin
        );
    }

} // class CCSeanceModel