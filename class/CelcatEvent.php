<?php
/**
 * Class SeanceXML Représente une séance dans l'emploi du temps XML
 */
class SeanceXML {

    private $id = '';
    private $date = '';
    private $day = '';
    private $starttime = '';
    private $endtime = '';
    private $category = '';
    private $modules = array();
    private $rooms = array();
    private $groups = array();

    public static function fromEvent(DOMElement $element) : SeanceXML {
        $seance = new SeanceXML();
        $resources = $element->getElementsByTagName('resources');

        if($resources->count() != 0) {
            $resource = $resources->item(0);
            $seance->modules = CelcatFinder::listerItemsXML($resource, 'module');
            $seance->rooms = CelcatFinder::listerItemsXML($resource, 'room');
            $seance->groups = CelcatFinder::listerItemsXML($resource, 'group');
        }

        $seance->id = trim($element->getAttribute('id'));
        $seance->date = trim($element->getAttribute('date'));
        $seance->day = trim($element->getElementsByTagName('day')->item(0)->textContent);
        $seance->starttime = trim($element->getElementsByTagName('starttime')->item(0)->textContent);
        $seance->endtime = trim($element->getElementsByTagName('endtime')->item(0)->textContent);
        $seance->category = trim($element->getElementsByTagName('category')->item(0)->textContent);
        return $seance;
    }

    public function __construct(string $id = '', string $date = '',
                                string $day = '', string $starttime = '', string $endtime = '',
                                string $category = '',
                                array $modules = array(), array $rooms = array(), array $groups = array()) {
        $this->id = $id;
        $this->date = $date;
        $this->day = $day;
        $this->starttime = $starttime;
        $this->endtime = $endtime;
        $this->category = $category;
        $this->modules = $modules;
        $this->rooms = $rooms;
        $this->groups = $groups;
    }

    /**
     * @return L'identifiant Celcat
     */
    public function getId(): int {
        return intval($this->id);
    }

    /**
     * @return La date du Lundi de la semaine
     */
    public function getWeekDate(): int {
        list($jour, $mois, $annee) = sscanf($this->date, "%d/%d/%d");
        $date = strtotime("$mois/$jour/$annee");
        return $date;
    }

    /**
     * @return le numéro du jour de la semaine
     * Lundi=0, Mardi=1, Mercredi=2, Jeudi=3, Vendredi=4, Samedi=5
     */
    public function getDay(): string {
        return intval($this->day);
    }

    /**
     * @return le timestamp du début de la séance
     */
    public function getStarttime(): int {
        list($heureDebut, $minutesDebut) = sscanf($this->starttime, "%d:%d");
        $weekTime = $this->getWeekDate();
        $day = $this->getDay();
        $time = strtotime( '+ ' . $day . ' days', $weekTime);
        $time += $heureDebut * 60;
        $time += $minutesDebut;
        return $time;
    }

    /**
     * @return le timestamp de fin de la séance
     */
    public function getEndtime(): string {
        list($heureFin, $minutesFin) = sscanf($this->endtime, "%d:%d");
        $weekTime = $this->getWeekDate();
        $day = $this->getDay();
        $time = strtotime( '+ ' . $day . ' days', $weekTime);
        $time += $heureFin * 60;
        $time += $minutesFin;
        return $time;
    }

    /**
     * @return string La catégorie de la séance (TD, TP, CM)
     */
    public function getCategory(): string {
        return $this->category;
    }

    /**
     * @return L'ID du module (ou -1 si non trouvé).
     */
    public function getModuleID(): int {
        $id = -1;
        if(count($this->modules) != 0) {
            $list = array(array('code' => $this->modules[0]));
            if(ECModel::getListFromCode($list)) {
                $id = $list[0]['id'];
            }
        }

        return $id;
    }

    /**
     * @return Le nom de la salle
     */
    public function getRoom(): string {
        return $this->rooms[0];
    }
}