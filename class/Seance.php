<?php
// *****************************************************************************************************
// * Classe pour représenter une séance
// *****************************************************************************************************
class Seance {
    
    private $id;                                 // Identifiant
    private $groupe;                             // Identifiant du groupe de l'EC
    private $debut;                              // Début de la séance
    private $fin;                                // Fin de la séance

    /**
     * Crée une nouvelle séance.
     * @param id l'identifiant
     * @param groupe l'identifiant du groupe de l'EC
     * @param debut la date de début
     * @param fin la date de fin
     */
	function __construct(int $id = -1, int $groupe = -1, int $debut = 0, int $fin = 0) {
		$this->id = $id;
        $this->groupe = $groupe;
        $this->debut = $debut;
        $this->fin = $fin;
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
     * Retourne l'identifiant du groupe.
     * @return l'identifiant du groupe
     */
    public function getGroupe() : int {
        return $this->groupe;
    }
    
    /**
     * Retourne la date de début.
     * @return la date de début
     */
    public function getDebut() : int {
        return $this->debut;
    }
    
    /**
     * Retourne la date de fin.
     * @return la date de fin
     */
    public function getFin() : int {
        return $this->fin;
    }
    
} // class Seance