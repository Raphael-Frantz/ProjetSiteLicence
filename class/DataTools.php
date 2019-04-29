<?php
// *************************************************************************************************
// * Class used to manipulate data.
// *************************************************************************************************
class DataTools {

    // Constants used to generate random keys
    const KEYSPACE = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

    /**
     * Convert a boolean to integer (0 for 'false' or 1 for 'true').
     * @param value the boolean
     * @return 0 or 1
     */
    public static function boolean2Int(bool $value) : int {
        if($value)
            return 1;
        else
            return 0;
    }
    
    /**
     * Convert an integer (1 for 'true' else 'false') to boolean.
     * @param value the integer
     * @return 'true' or 'false'
     */
    public static function int2Boolean(int $value) : bool {
        return ($value == 1);
    }
    
    /**
     * Generate a random key.
     * @param length the length of the key
     * @return the key
     */
    public static function generateKey(int $length) : string {
        $result = '';
        $max = strlen(self::KEYSPACE) - 1;
        for ($i = 0; $i < $length; ++$i)
            $result .= self::KEYSPACE[random_int(0, $max)];
        return $result;
    }
    
    /**
     * Convert a string to valid filename.
     * @param str the string to convert
     * @return the string converted
     */
    public static function convert2Filename(string $string) : string {
        $result = mb_ereg_replace("([^\w\s\d\-_~,;\[\]\(\).])", '', $string);
        $result = mb_ereg_replace("([\.]{2,})", '', $result);
        
        return $result;
    }
    
    /**
     * Merge 2 arrays.
     * @param key the key on witch merges the arrays
     * @param array1 the first array
     * @param array2 the second array
     * @return a merged array
     */
    public static function mergeArray(string $key, array $array1, array $array2) : array {
        $result = [];
        $i = 0;
        $j = 0;
        while(($i < count($array1)) && ($j < count($array2))) {
            if($array1[$i][$key] == $array2[$j][$key]) {
                $result[] = $array1[$i];
                $i++;
                $j++;
            }
            elseif($array1[$i][$key] < $array2[$j][$key]) {
                $result[] = $array1[$i];
                $i++;
            }
            else {
                $result[] = $array2[$j];
                $j++;
            }
        }
        while($i < count($array1)) {
            $result[] = $array1[$i];
            $i++;
        }
        while($j < count($array2)) {
            $result[] = $array2[$j];
            $j++;
        }
        
        return $result;
    }
    
} // class DataTools