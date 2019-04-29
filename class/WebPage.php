<?php
// *****************************************************************************************************
// * Class used to create the current page.
// *****************************************************************************************************
class WebPage {

    const CURRENT_MSG = "CURRENT_MSG";
    const CURRENT_ERR = "CURRENT_ERR";

    private static $CSSScripts = array();        // CSS files to include
    private static $JSScripts = array();         // Javascript files to include
    private static $onlineScripts = array();     // Javascript scripts to add
    private static $onReady = array();           // Javascript scripts to add in 'onReady' JQuery method
    private static $pageTitle = SITE_TITLE;      // The title of the current page
    private static $pageContent = "";            // The content of the current page
    
    /**
     * Add a CSS file name to include in the current page.
     * @param filename the name of the CSS file
     */
    public static function addCSSScript(string $filename) {
        if(!in_array($filename, WebPage::$CSSScripts))
            WebPage::$CSSScripts[] = $filename;
    }
    
    /**
     * Add a Javascript file name to include in the current page.
     * @param filename the name of the script file
     */
    public static function addJSScript(string $filename) {
        if(!in_array($filename, WebPage::$JSScripts))
            WebPage::$JSScripts[] = $filename;
    }

    /**
     * Add an online Javascript script in the current page.
     * @param script the script to add
     */
    public static function addOnlineScript(string $script) {
        WebPage::$onlineScripts[] = $script;
    }

    /**
     * Add Javascript script to execute when the document is loaded.
     * @param script the script
     */
    public static function addOnReady(string $script) {
        WebPage::$onReady[] = $script;
    }
    
    /**
     * Display the JS scripts in the current page.
     */
    public static function displayCSS() { 
        for($i = 0; $i < sizeof(self::$CSSScripts); $i++)
            if(substr(self::$CSSScripts[$i], 0, 4) != "http")
                echo "<link href=\"".WEB_PATH.self::$CSSScripts[$i]."?v=".time()."\" rel=\"stylesheet\" type=\"text/css\">\n";
            else
                echo "<link href=\"".self::$CSSScripts[$i]."\" rel=\"stylesheet\" type=\"text/css\">\n";
    }
    
    /**
     * Display the JS scripts in the current page.
     */
    public static function displayJS() { 
        for($i = 0; $i < sizeof(self::$JSScripts); $i++)
            if(substr(self::$JSScripts[$i], 0, 4) != "http")
                echo "<script src=\"".WEB_PATH.self::$JSScripts[$i]."?v=".time()."\"></script>\n";
            else
                echo "<script src=\"".self::$JSScripts[$i]."?v=".time()."\"></script>\n";
        
        // Adding online scripts
        if((sizeof(self::$onReady) > 0) || (sizeof(self::$onlineScripts) > 0)) {
            echo "    <script>\n";
            for($i = 0; $i < sizeof(self::$onlineScripts); $i++)
                echo self::$onlineScripts[$i]."\n";               
            if(sizeof(self::$onReady) > 0) {
                echo "$(document).ready(function() {\n";
                for($i = 0; $i < sizeof(self::$onReady); $i++)
                    echo self::$onReady[$i]."\n";
                echo "});\n";
            }
            echo "</script>\n";
        }
    }
    
    /**
     * Get the current message.
     * @return the current message
     */
    public static function getCurrentMsg() : string {
        $result = "";
        
        if(isset($_SESSION[self::CURRENT_MSG])) {
            $result = $_SESSION[self::CURRENT_MSG];
            unset($_SESSION[self::CURRENT_MSG]);
        }
        return $result;
    }
    
    /**
     * Set the current message.
     * @param the current message
     */
    public static function setCurrentMsg(string $msg) {
        $_SESSION[self::CURRENT_MSG] = $msg;
    }
    
    /**
     * Get the current error message.
     * @return the current error message
     */
    public static function getCurrentErrorMsg() : string {
        $result = "";
        
        if(isset($_SESSION[self::CURRENT_ERR])) {
            $result = $_SESSION[self::CURRENT_ERR];
            unset($_SESSION[self::CURRENT_ERR]);
        }
        return $result;
    }

    /**
     * Set the current error message.
     * @param the current error message
     */
    public static function setCurrentErrorMsg(string $msg) {
        $_SESSION[self::CURRENT_ERR] = $msg;
    }
    
    /**
     * Get the page title.
     * @return the page title
     */
    public static function getTitle() : string {
        return self::$pageTitle;
    }
    
    /**
     * Set the page title.
     * @param pageTitle the new page title
     */
    public static function setTitle(string $pageTitle) : void {
        self::$pageTitle = $pageTitle;
    }
    
    /**
     * Get the page content.
     * @return the page content
     */
    public static function getContent() : string {
        return self::$pageContent;
    }
    
    /**
     * Set the page content.
     * @param pageContent the new page content
     * @param merge if 'true' the content is merge to the current one
     */
    public static function setContent(string $pageContent, bool $merge = false) : void {
        if(!$merge)
            self::$pageContent = $pageContent;
        else
            self::$pageContent .= $pageContent;
    }
}