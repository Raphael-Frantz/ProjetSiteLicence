<?php
// *****************************************************************************************************
// * Classe utilisée pour représenter un étudiant
// *****************************************************************************************************
class Etudiant {
    
    private $idUtilisateur;                      // Identifiant utilisateur
	private $nom;                                // Nom
	private $prenom;                             // Prénom
    private $email;                              // Addresse email
    private $numero;                             // Numéro d'étudiant
    
    /**
     * Crée un nouvel étudiant
     * @param id l'identifiant utilisateur associé
     * @param nom le nom
     * @param prenom le prénom
     * @param numero le numéro
     * @param email l'adresse email
     */
    function __construct(int $id = -1, string $nom = "", string $prenom = "", int $numero = -1,
                         string $email = "") {
        $this->idUtilisateur = $id;
        $this->nom = $nom;
        $this->prenom = $prenom;
        $this->numero = $numero;
        $this->email = $email;
    }
    
    /**
     * Crée un utilisateur depuis l'étudiant.
     * @return un utilisateur
     */
    public function creerUtilisateur() : User {
        return new User($this->idUtilisateur, $this->nom, $this->prenom, $this->email, User::generatePassword(), User::TYPE_ETUDIANT);
    }
    
    /**
     * Retourne l'identifiant utilisateur.
     * @return l'identifiant utilisateur
     */
    public function getIdUtilisateur() : int {
        return $this->idUtilisateur;
    }
    
    /**
     * Modifie l'identifiant utilisateur.
     * @param idUtilisateur l'identifiant
     */
    public function setIdUtilisateur($idUtilisateur) : void {
        $this->idUtilisateur = $idUtilisateur;
    }
    
    /**
     * Retourne le nom.
     * @return le nom
     */
    public function getNom() : string {
        return $this->nom;
    }

    /**
     * Retourne le prénom.
     * @return le prénom
     */
    public function getPrenom() : string {
        return $this->prenom;
    }
    
    /**
     * Retourne le numéro d'étudiant.
     * @return le numéro
     */
    public function getNumero() : int {
        return $this->numero;
    }

    /**
     * Retourne l'adresse email.
     * @return l'adresse email
     */
    public function getEmail() : string {
        return $this->email;
    }
        
    /**
     * Convertit l'étudiant en chaîne de caractères.
     * @return une chaîne de caractères
     */
    public function __toString() : string {
        return $this->nom." ".$this->prenom;
    }
    
} // class Etudiant