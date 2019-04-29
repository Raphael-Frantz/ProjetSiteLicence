<?php
// *****************************************************************************************************
// * Classe utilisée pour représenter une épreuve
// *****************************************************************************************************
class Epreuve {
    
    // Constantes pour les types d'épreuve
    const TYPE_UNDEF = -1;                       // Type indéfini
    const TYPE_ITP = 1;                          // Interrogation de TP
    const TYPE_EET = 2;                          // Examen écrit terminal (période d'examen)
    const TYPE_CR = 3;                           // Compte-rendu (rapport stage/projet)
    const TYPE_ORAL = 4;                         // Oral (dont soutenances)
    const TYPE_DS = 5;                           // Devoir surveillé (regroupe tous les inscrits)
    const TYPE_DST = 6;                          // Devoir surveillé terminal (idem, organisé à la fin de l'enseignement)
    const TYPE_CRTP = 7;                         // CRTP (note de compte-rendu de TP, moyenne d'au moins 2)
    const TYPE_EOT = 8;                          // Examen oral terminal (période d'examens
    const TYPE_IE = 9;                           // Interrogation écrite (par groupe de TD/TP)
    const TYPE_OTP = 10;                         // Note d'oral de TP
    const TYPE_PROJET = 11;                      // Note de projet
    const TYPE_STAGE = 12;                       // Note de stage
    const TYPE_ASSIDUITE = 13;                   // Présence obligatoire (pas d'évaluation)
    
    const TYPE_DESCRIPTION = [
      self::TYPE_UNDEF => "non défini",
      self::TYPE_ITP => "ITP",
      self::TYPE_EET => "EET",
      self::TYPE_CR => "CR",
      self::TYPE_ORAL => "Oral",
      self::TYPE_DS => "DS",
      self::TYPE_DST => "DST",
      self::TYPE_CRTP => "CRTP",
      self::TYPE_EOT => "EOT",
      self::TYPE_IE => "IE",
      self::TYPE_OTP => "OTP",
      self::TYPE_PROJET => "Projet",
      self::TYPE_STAGE => "Stage",
      self::TYPE_ASSIDUITE => "Assiduité"
    ];
    
    private $id;                                 // Identifiant
    private $intitule;                           // Intitulé
    private $type;                               // Type (constantes TYPE_*)
    private $idEC;                               // Identifiant de l'EC
    private $max;                                // Note maximale
    private $bloquee;                            // Bloquée ou non (pour les resp/intervenants)
    private $active;                             // Active ou non (pour la saisie)
    private $visible;                            // Visible ou non (pour les étudiants)
    private $session1;                           // Répartition session 1
    private $session2;                           // Répartition session 2
    private $session1Disp;                       // Répartition session 1 dispense
    private $session2Disp;                       // Répartition session 2 dispense
    
    /**
     * Crée une nouvelle épreuve
     * @param id l'identifiant
     * @param intitule l'intitulé
     * @param type le type
     * @param idEC l'identifiant de l'EC associée
     * @param max la note maximale
     * @param bloquee épreuve bloquée ou non (pour les resp/intervenants)
     * @param active épreuve active ou non (pour la saisie)
     * @param visible épreuve visible ou non (pour les étudiants)
     * @param session1 la répartition pour la session 1
     * @param session2 la répartition pour la session 2
     * @param session1Disp la répartition pour la session 1 pour les dispensés
     * @param session2Disp la répartition pour la session 2 pour les dispensés
     */
    function __construct(int $id = -1, string $intitule = "", int $type = self::TYPE_UNDEF, int $idEC = -1,
                         float $max = 20, bool $bloquee = false, bool $active = false, bool $visible = false, 
                         int $session1 = 0, int $session2 = 0, int $session1Disp = 0, int $session2Disp = 0) {
        $this->id = $id;
        $this->intitule = $intitule;
        $this->type = $type;
        $this->idEC = $idEC;
        $this->max = $max;
        $this->bloquee = $bloquee;
        $this->active = $active;
        $this->visible = $visible;
        $this->session1 = $session1;
        $this->session2 = $session2;
        $this->session1Disp = $session1Disp;
        $this->session2Disp = $session2Disp;
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
    public function setId($id) : void {
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
     * Retourne l'identifiant de l'EC.
     * @return l'identifiant de l'EC
     */
    public function getIdEC() : int {
        return $this->idEC;
    }

    /**
     * Retourne la note maximale.
     * @return la note maximale
     */
    public function getMax() : float {
        return $this->max;
    }

    /**
     * Indique si l'épreuve est bloquée.
     * @return 'true' si l'épreuve est bloquée ou 'false' sinon
     */
    public function isBloquee() : bool {
        return $this->bloquee;
    }
    
    /**
     * Indique si l'épreuve est active.
     * @return 'true' si l'épreuve est active ou 'false' sinon
     */
    public function isActive() : bool {
        return $this->active;
    }

    /**
     * Indique si l'épreuve est visible.
     * @return 'true' si l'épreuve est visible ou 'false' sinon
     */
    public function isVisible() : bool {
        return $this->visible;
    }  

    /**
     * Retourne la répartition en session 1
     * @return la répartition en session 1
     */
    public function getSession1() : int {
        return $this->session1;
    }  

    /**
     * Retourne la répartition en session 2
     * @return la répartition en session 2
     */
    public function getSession2() : int {
        return $this->session2;
    }  
    
    /**
     * Retourne la répartition en session 1 pour les dispenses
     * @return la répartition en session 1 pour les dispenses
     */
    public function getSession1Disp() : int {
        return $this->session1Disp;
    }

    /**
     * Retourne la répartition en session 2 pour les dispenses
     * @return la répartition en session 2 pour les dispenses
     */
    public function getSession2Disp() : int {
        return $this->session2Disp;
    }
    
    /**
     * Convertit l'épreuve en chaîne de caractères.
     * @return une chaîne de caractères
     */
    public function __toString() : string {
        return $this->intitule;
    }
    
    /**
     * Indique si le type correspond à une note de CC.
     * @param type le type de la note
     * @return 'true' si c'est du CC, 'false' sinon
     */
    public static function estCC(int $type) : bool {
        return ($type != self::TYPE_EET);
    }
    
    /**
     * Indique si le type correspond à une note de TP.
     * @param type le type de la note
     * @return 'true' si c'est du TP, 'false' sinon
     */
    public static function estTP(int $type) : bool {
        return ($type == self::TYPE_ITP) ||
               ($type == self::TYPE_CRTP) ||
               ($type == self::TYPE_OTP) ||
               ($type == self::TYPE_PROJET);
    }
    
} // class Epreuve