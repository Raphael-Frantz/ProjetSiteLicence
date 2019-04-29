<?php
// *****************************************************************************************************
// * Classe pour représenter une UE
// *****************************************************************************************************
class UE {
    
    private $id;                                 // Identifiant
    private $diplome;                            // Diplôme (id)
    private $semestre;                           // Semestre
    private $position;                           // Position

    /**
     * Crée une nouvelle UE.
     * @param id l'identifiant
     * @param diplome le diplôme
     * @param semestre le semestre
     * @param position la position
     */
	function __construct(int $id = -1, int $diplome = -1, int $semestre = -1, int $position = -1) {
		$this->id = $id;
        $this->diplome = $diplome;
        $this->semestre = $semestre;
        $this->position = $position;
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
     * Retourne le diplôme.
     * @return le diplôme
     */
    public function getDiplome() : int {
        return $this->diplome;
    }

    /**
     * Retourne le semestre.
     * @return le semestre
     */
    public function getSemestre() : int {
        return $this->semestre;
    }
    
    /**
     * Retourne la position.
     * @return la position
     */
    public function getPosition() : int {
        return $this->position;
    }    
    
} // class UE