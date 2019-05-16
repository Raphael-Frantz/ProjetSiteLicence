<?php
/**
 * Class CelcatEvent Représente une séance dans l'emploi du temps
 */

class CelcatEvent {

    private $id = -1;               // Identifiant
    private $groupeID = -1;         // ID du groupe
    private $debut = -1;            // Date de début
    private $fin = -1;              // Date de fin
    private $type = '';             // Nom du type
    private $couleur = '';          // Couleur
    private $ecs = array();         // Nom des EC
    private $salles = array();      // Nom des salle

    /**
     * Parsing du noeud <event> celcat
     * Structure typique:
     *
     * <event id="196817" timesort="10151215" colour="FFFFBF" date="03/09/2018" ecs="26" ecc="16" er="0" sca="2" scb="2">
     *      <day>0</day>
     *      <prettytimes>10:15-12:15 [TD]</prettytimes>
     *      <starttime>10:15</starttime>
     *      <endtime>12:15</endtime>
     *      <category>[TD]</category>
     *      <prettyweeks></prettyweeks>
     *      <rawweeks>NYNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNN</rawweeks>
     *      <resources>
     *          <module>
     *              <item>
     *                  <a href="m88888.html">INFO0101</a>
     *              </item>
     *          </module>
     *          <room>
     *              <item>salle 1719 (Moulin de la Housse (Sciences et STAPS))</item>
     *          </room>
     *      </resources>
     *  </event>
     *
     * @param DOMElement $element
     * @param int $idGroupe
     * @return CelcatEvent
     */
    public static function fromTag(DOMElement $element, int $idGroupe) : CelcatEvent {
        $seance = new CelcatEvent($idGroupe);
        $seance->setTimeFromTag($element);
        $seance->setTypeFromTag($element);
        $seance->setCouleurFromTag($element);
        $seance->setResourcesFromTag($element);

        return $seance;
    }


    /**
     * @param DOMElement $resource <event>
     * @param nomElement balise html à rechercher
     * @return array Un tableau du contenu des éléments <item> dans la balise à rechercher.
     *               Si l'élément contenu dans <item> est un <a>, récupère uniquement le contenu texte.
     */
    public static function listerItemsXML(DOMElement $resource, string $nomElement) : array {
        $resultat = array();
        $list = $resource->getElementsByTagName($nomElement);

        if(!is_null($list) && $list->count() != 0) {
            foreach ($list->item(0)->getElementsByTagName('item') as $item) {

                if(!is_null($item)) {

                    $as = $item->getElementsByTagName('a');

                    if (!is_null($as) && $as->count() != 0) {
                        $resultat[] = trim($as->item(0)->textContent);
                    } else {
                        $resultat[] = trim($item->textContent);
                    }
                }
            }
        }

        return $resultat;
    }

    public function __construct(int $idGroupe) {
        $this->groupeID = $idGroupe;
    }

    public function setTimeFromTag(DOMElement $element) : void {

        $date = trim($element->getAttribute('date'));
        $day = trim($element->getElementsByTagName('day')->item(0)->textContent);
        $debut = trim($element->getElementsByTagName('starttime')->item(0)->textContent);
        $fin = trim($element->getElementsByTagName('endtime')->item(0)->textContent);
        list($heureDebut, $minutesDebut) = sscanf($debut, "%d:%d");
        list($heureFin, $minutesFin) = sscanf($fin, "%d:%d");
        list($jour, $mois, $annee) = sscanf($date, "%d/%d/%d");

        $time = strtotime($mois . '/' . $jour . '/' . $annee . ' + ' . $day . ' days');

        $timeDebut = $time;
        $timeDebut += $heureDebut * 60 * 60 + $minutesDebut * 60;
        $this->debut = $timeDebut;

        $timeFin = $time;
        $timeFin += $heureFin * 60 * 60 + $minutesFin * 60;
        $this->fin = $timeFin;
    }

    public function setTypeFromTag(DOMElement $element) : void {
        $this->type = trim($element->getElementsByTagName('category')->item(0)->textContent);
    }

    public function setCouleurFromTag(DOMElement $element) : void {
        $this->couleur = '#' . trim($element->getAttribute('colour'));
    }

    public function setResourcesFromTag(DOMElement $element) : void {
        // Récpupère les ressources (modules, salles)
        $resources = $element->getElementsByTagName('resources');

        if($resources->count() != 0) {
            $resource = $resources->item(0);
            $this->ecs = self::listerItemsXML($resource, 'module');
            $this->salles = self::listerItemsXML($resource, 'room');
        }
    }

    /**
     * @return le timestamp du début de la séance
     */
    public function getDateDebut(): int {
        return $this->debut;
    }

    /**
     * @return le timestamp de fin de la séance
     */
    public function getDateFin(): int {
        return $this->fin;
    }

    /**
     * @return string Le type de la séance (TD, TP, CM)
     */
    public function getType(): string {
        return $this->type;
    }

    /**
     * @return Le nom de l'EC
     */
    public function getEC() : string {
        return $this->ecs[0] ?? '';
    }

    /**
     * @return Le nom de la salle
     */
    public function getSalle(): string {
        return $this->salles[0] ?? '';
    }

    /**
     * @return L'ID du groupe
     */
    public function getGroupeId() : string {
        return $this->groupeID;
    }

    /**
     * @return La couleur de la séance
     */
    public function getCouleur() : string {
        return $this->couleur ?? '';
    }

    public function setId(int $id) : void {
        $this->id = $id;
    }
}