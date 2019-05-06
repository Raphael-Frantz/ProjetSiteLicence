<?php
// *****************************************************************************************************
// * Class used to parse the Celcat groups
// *****************************************************************************************************



class CelcatFinder
{
    const CELCAT_URL_PREFIX = "http://ebureau.univ-reims.fr/celcat/910913/";
    const CELCAT_FINDER_URL = self::CELCAT_URL_PREFIX . "finder2.html";

    public static function getAllGroups(string $url = self::CELCAT_FINDER_URL) : array {
        $dom = new DOMDocument();
        $modules = array();

        if(!@$dom->loadHTMLFile($url)) {
            return $modules;
        }

        $prefixe = 'group';
        $len = strlen($prefixe);

        foreach($dom->getElementsByTagName('tr') as $tagTr) {

            $id = $tagTr->getAttribute('id');

            if(substr($id, 0, $len) == $prefixe) {

                $tagA = $tagTr->getElementsByTagName('td')->item(0)->getElementsByTagName('a')->item(0);
                $modules[] = new CelcatGroupe($tagA->textContent, $tagA->getAttribute('href'));
            }
        }

        return $modules;
    }

    /**
     * Récupère l'url d'un groupe
     * @param string $intituleGroupe
     * @return l'URL de l'emploi du temps du groupe ou une chaîne vide en cas d'erreur
     */
    public static function getGroupeURL(string $intituleGroupe) : string {

        $modules = self::getAllModulesURL();

        foreach($modules as $mod) {

            if($mod->getCode() == $intituleGroupe) {
                return $mod->getPlanningXML();
            }
        }

        return '';
    }



    /**
     * Récupère toutes les séances depuis le planing
     * @param string $url url du planning
     * @return La liste de tous les évènements (ou null en cas d'erreur).
     */
    public static function getAllSeances(string $url, int $group) : ?array {

        $content = @file_get_contents($url);
        $dom = new DOMDocument();
        $events = array();

        if(!$content) {
            return null;
        }
        if(!@$dom->loadXML($content))
            return null;

        $tags = $dom->getElementsByTagName('event');

        foreach($tags as $tag) {
            $events[] = CelcatEvent::fromTag($tag, $group);
        }

        return $events;
    }
}