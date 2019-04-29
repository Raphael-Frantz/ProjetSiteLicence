<?php
// *****************************************************************************************************
// * Classe pour représenter un justificatif d'absence
// *****************************************************************************************************
class Justificatif {
    
    private $id;                                 // Identifiant
    private $etudiant;                           // Identifiant de l'étudiant
    private $dateDebut;                          // Date de début
    private $dateFin;                            // Date de fin
    private $motif;                              // Motif
    private $remarque;                           // Remarque
    private $editeur;                            // Editeur
    private $dateSaisie;                         // Date de saisie

    /**
     * Crée un nouveau diplôme.
     * @param id l'identifiant
     * @param intitule l'intitulé
     */
	function __construct(int $id = -1, int $etudiant = -1, int $dateDebut = 0, int $dateFin = 0, 
                         string $motif = "", string $remarque = "", int $editeur = -1, int $dateSaisie = 0) {
		$this->id = $id;
        $this->etudiant = $etudiant;
        $this->dateDebut = $dateDebut;
        $this->dateFin = $dateFin;
        $this->motif = $motif;
        $this->remarque = $remarque;
        $this->editeur = $editeur;
        $this->dateSaisie = $dateSaisie;
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
     * Retourne l'étudiant.
     * @return l'identifiant
     */
    public function getEtudiant() : int {
        return $this->etudiant;
    }
    
    /**
     * Retourne la date de début
     * @return la date de début
     */
    public function getDateDebut() : int {
        return $this->dateDebut;
    }
    
    /**
     * Retourne la date de fin
     * @return la date de fin
     */
    public function getDateFin() : int {
        return $this->dateFin;
    }    
    
    /**
     * Retourne le motif
     * @return le motif
     */
    public function getMotif() : string {
        return $this->motif;
    }   
    
    /**
     * Retourne les remarques
     * @return les remarques
     */
    public function getRemarque() : string {
        return $this->remarque;
    }   
    
    /**
     * Retourne l'éditeur
     * @return l'éditeur
     */
    public function getEditeur() : int {
        return $this->editeur;
    }    

    /**
     * Retourne la date de saisie
     * @return la date de saisie
     */
    public function getDateSaisie() : int {
        return $this->dateSaisie;
    }    
    
    
} // class Justificatif