<?php
// *************************************************************************************************
// * Controller tool
// *************************************************************************************************
class Controller {

    /**
     * Load a view and push it to a template.
     * @param pageTitle the page title
     * @param data the data for the view
     * @param view the view
     * @param template the template (or "" if the view doesn't need a template)
     */
    public static function push(string $pageTitle, string $view, array $data = array(), 
                                string $template = "./view/template.php") : void {
        WebPage::setTitle($pageTitle);
        
        // Load the view and get the content
        if($view != "") {
            ob_start();
            require($view);
            WebPage::setContent(ob_get_contents());
            ob_end_clean();
        }
        
        // Push the content into the template
        if($template != "") 
            require($template);
        else
            echo WebPage::getContent();
    }
    
    /**
     * Push a JSON file.
     * @param data the JSON data
     */
    public static function JSONpush(array $data) : void {
        header("Content-type: application/json");
        echo json_encode($data);
        exit();
    }

    /**
     * Push a CSV file.
     * @param filename the filename
     * @param data the CSV data
     * @param header the header
     */
    public static function CSVpush($filename, array $data, array $header) : void {
        /* http header */
        header("Pragma: public");
        header("Expire: 0");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Cache-Control: private",false);
        header("Content-Type: text/csv; charset=UTF-8");
        header("Content-Disposition: attachment; filename=\"$filename.csv\"");
        //header("Content-Transfer-Encoding: text/csv");         
        flush();

        /* Document content */
        $out = fopen('php://output', 'w');
        
        $titles = array_keys($header);
        foreach($titles as &$title) $title = utf8_decode($title);
        fputcsv($out, $titles, ";");
        foreach($data as $row) {
            $line = [];
            foreach($header as $field)
                $line[] = utf8_decode($row[$field]);
            fputcsv($out, $line, ";");
        }
        fclose($out);

        exit();
    }
    
    /**
     * Redirect to a specified page.
     * @param page the page
     * @param message the message
     * @param error the error message
     */
    public static function goTo(string $page = "", string $message = "", string $error = "") : void {
        if($message != "")
            WebPage::setCurrentMsg($message);
        if($error != "")
            WebPage::setCurrentErrorMsg($error);
        
        header("Location: ".WEB_PATH.$page);
        exit();
    }

}