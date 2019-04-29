<?php
// *****************************************************************************************************
// * Classe pour représenter un groupe d'un EC
// *****************************************************************************************************
class GroupeEC extends Groupe {

    private $EC;                                 // EC
    
    /**
     * Crée un nouveau groupe d'EC.
     * @param id l'identifiant
     * @param intitule l'intitulé
     * @param type le type du groupe (constantes GRP_*)
     * @param EC l'EC
     */
	function __construct(int $id = -1, string $intitule = "", int $type = GRP_UNDEF, int $EC = -1) {
        parent::__construct($id, $intitule, $type, -1, -1);
        $this->EC = $EC;
	}
    
    /**
     * Retourne l'EC.
     * @return l'EC
     */
    public function getEC() : string {
        return $this->EC;
    }
    
} // class GroupeEC