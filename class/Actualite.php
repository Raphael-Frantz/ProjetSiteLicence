<?php
// *****************************************************************************************************
// * Classe pour représenter une actualité
// *****************************************************************************************************
class Actualite {
    
    private $id;                                 // Identifiant
    private $date;                               // Date
	private $titre;                              // Titre
    private $contenu;                            // Contenu
    private $titreLien;                          // Titre du lien
    private $lien;                               // Lien
    private $prioritaire;                        // Actualité prioritaire ou non
    private $annees;                             // Années concernées par l'actualité

    /**
     * Crée une nouvelle actualité.
     * @param id l'identifiant
     * @param date la date
     * @param titre le titre
     * @param contenu le contenu
     * @param titreLien le titre du lien
     * @param lien le lien
     */
	function __construct(int $id = -1, int $date = 0, string $titre = "", string $contenu = "",
                         string $titreLien = "", string $lien = "", bool $prioritaire = false,
                         string $annees = "", bool $active = true) {
		$this->id = $id;
        $this->date = $date;
		$this->titre= $titre;
        $this->contenu = $contenu;
        $this->titreLien = $titreLien;
        $this->lien = $lien;
        $this->prioritaire = $prioritaire;
        $this->annees = $annees;
        $this->active = $active;
	}
    
    /**
     * Retourne l'identifiant.
     * @return l'identifiant
     */
    public function getId() : int {
        return $this->id;
    }
    
    /**
     * Modifie l'identifiant.
     * @param id l'identifiant
     */
    public function setId(int $id) {
        $this->id = $id;
    }    
    
    /**
     * Retourne la date.
     * @return la date
     */
    public function getDate() : int{
        return $this->date;
    }
    
    /**
     * Retourne le titre
     * @return le titre
     */
    public function getTitre() : string {
        return $this->titre;
    }

    /**
     * Retourne le contenu.
     * @return le contenu
     */
    public function getContenu() : string {
        return $this->contenu;
    }
    
    /**
     * Retourne le titre du lien
     * @return le titre du lien
     */
    public function getTitreLien() : string {
        return $this->titreLien;
    }   
    
    /**
     * Retourne le lien
     * @return le lien
     */
    public function getLien() : string {
        return $this->lien;
    }   
    
    /**
     * Indique si l'actualité est prioritaire.
     * @return 'true' si l'actualité est prioritaire
     */
    public function estPrioritaire() : bool {
        return $this->prioritaire;
    }   

    /**
     * Retourne les années concernées par l'actualité.
     * @return les années
     */
    public function getAnnees() : string {
        return $this->annees;
    } 

    /**
     * Indique si l'actualité est active.
     * @return 'true' si l'actualité est active
     */
    public function estActive() : bool {
        return $this->active;
    }   
    
    /**
     * Convertit l'actualité en chaîne de caractères.
     * @return une chaîne de caractères
     */
    public function __toString() : string {
        return $this->titre." [".DateTools::timestamp2Date($this->date, false)."]";
    }
    
} // class Actualite