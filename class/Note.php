<?php
// *****************************************************************************************************
// * Classe utilisée pour représenter une note
// *****************************************************************************************************
class Note {
    
    // Constantes pour les notes
    const TYPE_AUCUNE = -1;                      // Aucune note
    const TYPE_ABI = -2;                         // Absence injustifiée
    const TYPE_ABJ = -3;                         // Absence justifiée
    
    private $idEpreuve;                          // Identifiant de l'épreuve
    private $idUtilisateur;                      // Identifiant de l'utilisateur
    private $note;                               // Note
    
    /**
     * Crée une nouvelle note.
     * @param idEpreuve l'identifiant de l'épreuve
     * @param idUtilisateur l'identifiant de l'utilisateur
     * @param note la note
     */
    function __construct(int $idEpreuve = -1, int $idUtilisateur = -1, float $note = 0) {
        $this->idEpreuve = $idEpreuve;
        $this->idUtilisateur = $idUtilisateur;
        $this->note = $note;
    }
    
    /**
     * Retourne l'identifiant de l'épreuve.
     * @return l'identifiant de l'épreuve
     */
    public function getIdEpreuve() : int {
        return $this->idEpreuve;
    }

    /**
     * Retourne l'identifiant de l'utilisateur.
     * @return l'identifiant de l'utilisateur
     */
    public function getIdUtilisateur() : int {
        return $this->idUtilisateur;
    }
    
    /**
     * Retourne la note.
     * @return la note
     */
    public function getNote() : float {
        return $this->note;
    }
    
    /**
     * Convertit une note pour affichage.
     * @param note la note à afficher
     * @param max le max
     * @return la note affichée
     */
    public static function convertirStr(float $note, float $max) : string {
        $result = '';
        if($note == Note::TYPE_ABI)
            $result = "<span class='badge badge-danger'>ABI</span>";
        elseif($note == Note::TYPE_ABJ)
            $result = "<span class='badge badge-info'>ABJ</span>";
        elseif($note == Note::TYPE_AUCUNE)
            $result = "<span class='badge badge-secondary'>NS</span>";
        else {
            if($note < $max / 2)
                $result = "<span class='badge badge-warning'>{$note} / {$max}</span>";
            else
                $result = "<span class='badge badge-success'>{$note} / {$max}</span>";
        }
        return $result;
    }
    
} // class Note