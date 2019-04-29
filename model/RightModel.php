<?php
// *************************************************************************************************
// * Model for users rights.
// *************************************************************************************************
class RightModel {
    
    // Constants for rights table
    const DB_RIGHT = "inf_right";                // Table of rights
    
    const DBF_RIG_ID = "rig_id";                 // Identifier
    const DBF_RIG_DESCRIPTION = "rig_description"; // Description
    const DBF_RIG_CODE = "rig_code";             // Code

    // Constants for user rights table
    const DB_USERRIGHT = "inf_userright";        // Table of user rights
    const DBF_URI_USER = "uri_user";             // User identifier
    const DBF_URI_RIGHT = "uri_right";           // Right identifier
    
    /**
     * Get the rights of an user.
     * @param user the user
     * @return 'true' on success or 'false' on error
     */
    public static function read(User $user) {
        $DB = MyPDO::getInstance();
        
        // Load the rights
        $SQL = "SELECT `".self::DBF_RIG_CODE."` FROM `".self::DB_USERRIGHT."`, `".self::DB_RIGHT.
               "` WHERE `".self::DBF_URI_USER."`=:id AND `".self::DBF_URI_RIGHT."`=`".self::DBF_RIG_ID."`";
        if(($request = $DB->prepare($SQL)) && $request->execute(array(':id' => $user->getId()))) {
            while($row = $request->fetch()) {
                $user->addRight($row[self::DBF_RIG_CODE]);
            }
            return true;
        }
        else 
            return false;
    }
    
} // class RightModel