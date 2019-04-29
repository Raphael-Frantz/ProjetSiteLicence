<?php
// *****************************************************************************************************
// * Classe pour représenter une EC
// *****************************************************************************************************
class EC {
    
    private $id;                                 // Identifiant
    private $code;                               // Code
    private $intitule;                           // Intitule

    /**
     * Crée une nouvelle EC.
     * @param id l'identifiant
     * @param code le code
     * @param intitule l'intitulé
     */
	function __construct(int $id = -1, string $code = "", string $intitule = "") {
		$this->id = $id;
        $this->code = $code;
        $this->intitule = $intitule;
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
     * Retourne le code.
     * @return le code
     */
    public function getCode() : string {
        return $this->code;
    }

    /**
     * Retourne l'intitulé.
     * @return l'intitulé
     */
    public function getIntitule() : string {
        return $this->intitule;
    }

    /**
     * Convertit l'EC en chaîne de caractères.
     * @return une chaîne de caractères
     */
    public function __toString() : string {
        return $this->code." - ".$this->intitule;
    }    
    
} // class EC