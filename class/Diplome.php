<?php
// *****************************************************************************************************
// * Classe pour représenter un diplôme
// *****************************************************************************************************
class Diplome {
    
    private $id;                                 // Identifiant
    private $intitule;                           // Intitulé
    private $minSemestre;                        // Semestre min.
    private $nbSemestres;                        // Nombre de semestres

    /**
     * Crée un nouveau diplôme.
     * @param id l'identifiant
     * @param intitule l'intitulé
     * @param nbSemestres le nombre de semestres
     */
	function __construct(int $id = -1, string $intitule = "", int $minSemestre = 1, int $nbSemestres = 1) {
		$this->id = $id;
        $this->intitule = $intitule;
        $this->minSemestre = $minSemestre;
        $this->nbSemestres = $nbSemestres;
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
     * Retourne l'intitulé.
     * @return l'intitulé
     */
    public function getIntitule() : string {
        return $this->intitule;
    }

    /**
     * Retourne le semestre minimum.
     * @return le semestre minimum
     */
    public function getMinSemestre() : int {
        return $this->minSemestre;
    }
    
    /**
     * Retourne le nombre de semestres.
     * @return le nombre de semestres
     */
    public function getNbSemestres() : int {
        return $this->nbSemestres;
    }
        
    /**
     * Convertit le diplôme en chaîne de caractères.
     * @return une chaîne de caractères
     */
    public function __toString() : string {
        return $this->intitule;
    }
    
} // class Diplome