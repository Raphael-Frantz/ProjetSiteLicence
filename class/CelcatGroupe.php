<?php
/**
 * Class GroupeXML Représente un groupe dans l'emploi du temps
 */
class GroupeXML {

    const INTITULE_PREFIX = "Groupe:";

    private $intitule;
    private $fichier; // fichier html

    /**
     * CelcatGroupe constructor.
     * @param $intitule
     * @param $fichier
     */
    public function __construct($intitule, $fichier) {
        $this->intitule = $intitule;
        $this->fichier = $fichier;
    }

    public function getCode() : string {
        return trim(substr($this->intitule, strlen(self::INTITULE_PREFIX)));
    }

    public function getPlanningXML() : string {
        return CelcatFinder::CELCAT_URL_PREFIX . substr($this->fichier, 0, -strlen(".html")) . ".xml";
    }
}