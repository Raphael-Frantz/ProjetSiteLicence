<?php
// *****************************************************************************************************
// * Classe pour représenter un groupe.
// *****************************************************************************************************
class Groupe {
    
    // Constantes pour les types de groupe
    const GRP_UNDEF = -1;
    const GRP_CM = 1;
    const GRP_TD = 2;
    const GRP_TP = 3;
    
    private $id;                                 // Identifiant
    private $intitule;                           // Intitulé
    private $type;                               // Type (constantes GRP_*)
    private $diplome;                            // Diplôme
    private $semestre;                           // Semestre

    /**
     * Crée un nouveau groupe.
     * @param id l'identifiant
     * @param intitule l'intitulé
     * @param type le type du groupe (constantes GRP_*)
     * @param diplome l'identifiant du diplome
     * @param semestre le numéro de semestre
     */
	function __construct(int $id = -1, string $intitule = "", int $type = GRP_UNDEF, int $diplome = -1, int $semestre = 1) {
		$this->id = $id;
        $this->intitule = $intitule;
        $this->type = $type;
        $this->diplome = $diplome;
        $this->semestre = $semestre;
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
    public function setId(int $id) : void {
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
     * Retourne le type.
     * @return le type
     */
    public function getType() : int {
        return $this->type;
    }
    
    /**
     * Retourne la chaîne de caractères correspondant au type.
     * @param type le type
     * @return la chaîne de caractères
     */
    public static function type2String(int $type) : string {
        $resultat = "";
        switch($type) {
            case self::GRP_CM:
                $resultat = "CM";
                break;
            case self::GRP_TD:
                $resultat = "TD";
                break;
            case self::GRP_TP:
                $resultat = "TP";
                break;
            default:
                $resultat = "Aucun";
        }
        return $resultat;
    }     

    /**
     * Retourne le diplôme.
     * @return le diplôme
     */
    public function getDiplome() : string {
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
     * Convertit le groupe en chaîne de caractères.
     * @return une chaîne de caractères
     */
    public function __toString() : string {
        return $this->intitule;
    }
    
} // class Groupe