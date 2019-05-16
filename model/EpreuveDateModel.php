<?php
/**
 * Created by PhpStorm.
 * User: Raphael
 * Date: 16/05/2019
 * Time: 03:35
 *
 *
 */

class EpreuveDateModel
{
    const DB = 'inf_epreuve_date';
    const DBF_ID = 'eprp_id';
    const DBF_BEG = 'eprp_debut';
    const DBF_END = 'eprp_fin';
    const DBF_EPR = 'eprp_epreuve';
    const DBF_GRP = 'eprp_groupe';

    /**
     * Récupère la liste des épreuves pour lesquelles il y a une date pour un EC donné
     * @param int $EC
     * @return array|null
     */
    public static function getList(int $EC) : ?array {

        $DB = MyPDO::getInstance();

        // begin est un mot clé reservé

        $SQL = 'SELECT ' . self::DBF_BEG . ' AS debut, ' .
               self::DBF_END . ' AS fin, ' .
               self::DBF_GRP . ' AS groupe, ' .
               EpreuveModel::DBF_INTITULE . ' AS type ' .
               ' FROM ' . self::DB .
               ' JOIN ' . EpreuveModel::DB . ' ON ' . self::DBF_EPR . ' = ' . EpreuveModel::DBF_ID .
               ' JOIN ' . ECModel::DB . ' ON ' . EpreuveModel::DBF_EC . ' = ' . ECModel::DBF_ID .
               ' WHERE ' . ECModel::DBF_ID  . ' = :EC';

        $input = array(
            ':EC' => $EC
        );

        return self::fetch($SQL, $input);
    }

    /**
     * Récupère la liste des dates associées à l'épreuve pour chaque groupe.
     * Si le groupe n'a pas de date prévu, le champ vaut NULL.
     * @param int $epreuve
     * @return array|null
     */
    public static function getGroupList(int $epreuve) : ?array {

        $SQL =  'SELECT ' . GroupeModel::DB . '.' . GroupeModel::DBF_ID . ' AS id, ' .
                GroupeModel::DBF_INTITULE . ' AS intitule, ' .
                self::DBF_BEG . ' AS debut, ' .
                self::DBF_END . ' AS fin ' .
                ' FROM ' . EpreuveModel::DB .
                ' JOIN ' . UEECModel::DB . ' ON ' . UEECModel::DBF_EC . ' = ' . EpreuveModel::DBF_EC .
                ' JOIN ' . UEModel::DB . ' ON ' . UEModel::DBF_ID . ' = ' . UEECModel::DBF_UE .
                ' JOIN ' . GroupeModel::DB .
                ' LEFT JOIN ' . self::DB . ' ON ' . self::DBF_EPR . ' = :epreuve ' .
                ' AND ' . self::DBF_GRP . ' = ' . GroupeModel::DBF_ID .
                ' WHERE ' . GroupeModel::DBF_TYPE . ' = ' . Groupe::GRP_TD .
                ' AND ' . GroupeModel::DBF_DIPLOME . ' = ' . UEModel::DBF_DIPLOME .
                ' AND ' . GroupeModel::DBF_SEMESTRE . ' = ' . UEModel::DBF_SEMESTRE .
                ' AND ' . EpreuveModel::DBF_ID . ' = :epreuve ';

        $input = array(
            ':epreuve' => $epreuve
        );

        return self::fetch($SQL, $input);
    }

    private static function fetch(string $SQL, array $input) : ?array {

        $DB = MyPDO::getInstance();

        if(($request = $DB->prepare($SQL)) && $request->execute($input)) {
            $result = array();

            while($row = $request->fetch()) {
                $result[] = $row;
            }
            return $result;
        }
        else
            return null;
    }

    public static function read(int $epreuve, int $groupe) : int {

        $DB = MyPDO::getInstance();
        $SQL = 'SELECT ' . self::DBF_ID . ' FROM ' . self::DB . ' WHERE ' . self::DBF_EPR . ' = :epreuve' .
               ' AND ' . self::DBF_GRP . ' = :groupe';
        $input = array(
            ':epreuve' => $epreuve,
            ':groupe' => $groupe
        );

        if(($request = $DB->prepare($SQL)) && $request->execute($input)) {
            $row = $request->fetch();

            if($row) {
                return $row[self::DBF_ID];
            }
        }

        return -1;
    }

    public static function create(int $epreuve, int $debut, int $fin, int $groupe) {

        $DB = MyPDO::getInstance();
        $SQL = 'INSERT INTO ' . self::DB . '(' . self::DBF_ID . ', ' .
               self::DBF_EPR . ', ' . self::DBF_GRP . ', ' .
               self::DBF_BEG . ', ' . self::DBF_END . ') VALUES(NULL, :epreuve, :groupe, :debut, :fin)';

        $input = array(
            ':epreuve' => $epreuve,
            ':groupe' => $groupe,
            ':debut' => $debut,
            ':fin' => $fin
        );

        $request = $DB->prepare($SQL);
        return $request && $request->execute($input);
    }

    public static function update(int $epreuve, int $debut, int $fin, int $groupe) {

        $id = self::read($epreuve, $groupe);

        if($id != -1) {

            $input = [
                [self::DBF_EPR, $epreuve],
                [self::DBF_GRP, $groupe],
                [self::DBF_BEG, $debut],
                [self::DBF_END, $fin]
            ];
            MyPDO::update(self::DB, $input, $id, self::DBF_ID);
        }
        else {

            self::create($epreuve, $debut, $fin, $groupe);
        }
    }
}