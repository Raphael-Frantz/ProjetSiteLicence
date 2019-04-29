<?php
// *************************************************************************************************
// * Class used to manage the PDO instance.
// *************************************************************************************************
final class MyPDO {

    private static $instance = null;

    private static $driverOptions = array(
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    );
    
    /**
     * Get instance of the PDO.
     * @return the PDO object
     */
    public static function getInstance() : PDO {
        if(self::$instance === null)
            self::$instance = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME.";charset=utf8", 
                                      DB_LOGIN, DB_PASSWORD, self::$driverOptions);
        return self::$instance;
    }
    
    /**
     * Update an element.
     * @param table the table
     * @param data the data to modify
     * @param id the identifier of the element
     * @param idField the name of the identifier field
     * @return 'true' on success else 'false'
     */
    public static function update(string $table, array $data, int $id, string $idField) : bool {
        $DB = self::getInstance();
        
        $SQL = "UPDATE `$table` SET";
        for($i = 0; $i < sizeof($data); $i++) {
            $SQL .= " `".$data[$i][0]."` = ".$DB->quote($data[$i][1]);
            if($i < sizeof($data) - 1) $SQL .= ", ";
        }
        $SQL .= " WHERE `$idField` = $id";
        
        return $DB->query($SQL) !== false;
    }
    
} // class MyPDO