<?php
// *****************************************************************************************************
// * General constants
// *****************************************************************************************************
const MONTHS_LIST =  array("Janvier", "Février", "Mars", "Avril", "Mai", "Juin", "Juillet", "Août", 
                           "Septembre", "Octobre", "Novembre", "Décembre");
const DAYS_LIST = array("Dimanche", "Lundi", "Mardi", "Mercredi", "Jeudi", "Vendredi", "Samedi", "Dimanche");

// *****************************************************************************************************
// * Class used to handling dates.
// *****************************************************************************************************
class DateTools {

    /**
     * Convert a timestamp to a long string depending on current selected language: "Monday, January 1 2017 at 8:00".
     * @param date the date to convert
     * @param hour if 'true', specify the hour
     * @return a string that contains the date
     **/
    public static function timestamp2LongDate(int $date, bool $hour = false) : int {
        $result = DAYS_LIST[date("w", $date)]." ".date("j", $date)." ".MONTHS_LIST[date("n", $date) - 1]." ".date("Y", $date);
        if($hour)
            $result .= " à ".date("H", $date).":".date("i", $date);
        
        return $result;
    }
    
    /**
     * Convert a timestamp to a string in format "01/01/2019 8:00".
     * @param date the date to convert
     * @param hour if 'true', specify the hour
     * @return a string that contains the date
     **/
    public static function timestamp2Date(int $date, bool $hour = false) : string {
        if($hour)
            $result = date("d/m/Y H:i", $date);
        else
            $result = date("d/m/Y", $date);

        return $result;
    }    
        
    /**
     * Convert a timestamp to a string in format "2019-01-01 8:00"
     * @param timestamp the timestamp
     * @return the string or 'false' on error
     */
    public static function timestamp2USDate(int $date, bool $hour = false) : string {
        if($hour)
            $result = date("Y-m-d H:i", $date);
        else
            $result = date("Y-m-d", $date);

        return $result;
    }   
    
    /**
     * Convert a string in format 2017/01/01 8:00 to a timestamp.
     * @param date the string
     * @param hour if 'true', the date contains hour
     * @return the timestamp or 'false' on error
     */
    public static function date2Timestamp(string $date, bool $hour = true) {
        if($hour) {        
            if(!preg_match("$(\d{1,2})/(\d{1,2})/(\d{1,4})\ (\d{1,2}):(\d{1,2})$", $date, $matches))
                return false;
            else
                return mktime($matches[4], $matches[5], 0, $matches[2], $matches[1], $matches[3]);               
        }
        else {
            if(!preg_match("$(\d{1,2})/(\d{1,2})/(\d{1,4})$", $date, $matches))
                return false;
            else
                return mktime(0, 0, 0, $matches[2], $matches[1], $matches[3]);
        }
    }    

    /**
     * Convert a date in format 2017-01-01 to a timestamp.
     * @param date the date
     * @return the timestamp or 'false' on error
     */
    public static function USDate2Timestamp(string $date, bool $hour = true) {
        if($hour) {
            if(!preg_match("$(\d{1,4})-(\d{1,2})-(\d{1,2}) (\d{1,2}):(\d{1,2})$", $date, $matches))
                return false;
            else
                return mktime($matches[4], $matches[5], 0, $matches[2], $matches[3], $matches[1]);
        }
        else {
            if(!preg_match("$(\d{1,4})-(\d{1,2})-(\d{1,2})$", $date, $matches))
                return false;
            else
                return mktime(0, 0, 0, $matches[2], $matches[3], $matches[1]);
        }
    }    
    
    /**
     * Add a number of days to a timestamp.
     * @param date the timestamp
     * @param daysNb the number of days to add
     * @return the new timestamp
     */
    public static function addDays($date, $daysNb) {
       return mktime(date("H", $date), date("i", $date), 0, date("n", $date), date("j", $date) + $daysNb,  date("Y", $date)); 
    }
  
} // class DateTools