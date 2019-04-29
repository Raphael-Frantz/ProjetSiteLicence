<?php
// *************************************************************************************************
// * Modèle pour les notes.
// *************************************************************************************************
class NoteModel {
    
    // Constantes pour la table des notes
    const DB = "inf_note";                       // Table des notes
    const DBF_USER = "not_user";                 // Identifiant utilisateur
    const DBF_EPREUVE = "not_epreuve";           // Identifiant épreuve
    const DBF_NOTE = "not_note";                 // Note
    
    /**
     * Retourne la liste des notes d'un étudiant.
     * @param id l'identifiant de l'étudiant
     * @return la liste des notes (ou null en cas d'erreur)
     */
    public static function getListEtudiant(int $id) : array {
        $DB = MyPDO::getInstance();
        
        /* Sélection de tous les EC auxquels est inscrit l'étudiant */
        $SQL_ins = "SELECT ".DiplomeModel::DBF_INTITULE." as diplome, ".
                         DiplomeModel::DBF_MINSEMESTRE." as minSemestre, ".
                         UEModel::DBF_SEMESTRE." as semestre, ".
                         ECModel::DBF_ID.", ".
                         ECModel::DBF_CODE." as code, ".
                         ECModel::DBF_INTITULE." as intitule, ".
                         UEModel::DBF_POSITION.", ".
                         UEECModel::DBF_POSITION.", ".
                         InscriptionECModel::DBF_TYPE." as type, ".
                         InscriptionECModel::DBF_NOTE." as noteip, ".
                         InscriptionECModel::DBF_BAREME." as bareme".
               " FROM ".DiplomeModel::DB.", ".UEModel::DB.", ".ECModel::DB.", ".UEECModel::DB.", ".InscriptionECModel::DB.", ".
                        InscriptionDiplomeModel::DB.
               " WHERE ".InscriptionDiplomeModel::DBF_ETUDIANT."=:etudiant AND ".
                         InscriptionDiplomeModel::DBF_DIPLOME."=".DiplomeModel::DBF_ID." AND ".
                         UEModel::DBF_DIPLOME."=".DiplomeModel::DBF_ID." AND ".
                         UEECModel::DBF_EC."=".ECModel::DBF_ID." AND ".
                         UEECModel::DBF_UE."=".UEModel::DBF_ID." AND ".
                         InscriptionECModel::DBF_EC."=".UEECModel::DBF_EC." AND ".
                         InscriptionECModel::DBF_ETUDIANT."=:etudiant".
               " GROUP BY diplome, semestre, code";
        
        /* Sélection de toutes les épreuves, de tous les EC auquels sont inscrit l'étudiant */
        $SQL_epr = "SELECT diplome, minSemestre, semestre, ".
                         UEModel::DBF_POSITION.", ".
                         UEECModel::DBF_POSITION.", ".
                         ECModel::DBF_ID." as idEC, ".
                         "code, ".
                         "intitule, ".
                         "type, ".
                         "noteip, ".
                         "bareme, ".
                         EpreuveModel::DBF_ID.", ".
                         EpreuveModel::DBF_INTITULE." as epreuve, ".
                         EpreuveModel::DBF_MAX." as max, ".
                         EpreuveModel::DBF_TYPE." as typeE, ".
                         EpreuveModel::DBF_SESSION1." as session1, ".
                         EpreuveModel::DBF_SESSION2." as session2, ".
                         EpreuveModel::DBF_VISIBLE." as visible".
               " FROM ($SQL_ins) AS I, ".EpreuveModel::DB.
               " WHERE ".EpreuveModel::DBF_EC."=".ECModel::DBF_ID;

        /* Sélection de toutes les notes de l'étudiant */
        $SQL_not = "SELECT ".self::DBF_EPREUVE.", ".self::DBF_NOTE." as note".
               " FROM ".self::DB.
               " WHERE ".self::DBF_USER."=:etudiant";
                            
        $SQL = "SELECT * FROM ($SQL_epr) AS A LEFT OUTER JOIN ($SQL_not) AS B".
                 " ON A.".EpreuveModel::DBF_ID."=B.".self::DBF_EPREUVE.
               " ORDER BY diplome ASC, ".
                         "semestre ASC, ".
                            UEModel::DBF_POSITION." ASC, ".
                            UEECModel::DBF_POSITION." ASC, ".
                            "epreuve ASC";               
               
        if(($request = $DB->prepare($SQL)) && $request->execute([ ":etudiant" => $id ])) {
            $result = array();

            while($row = $request->fetch())
                $result[] = $row;

            return $result;
        }
        else
            return null;
    }

    /**
     * Retourne les notes des étudiants d'une épreuve.
     * @param EC l'identifiant de l'EC
     * @param epreuve l'identifiant de l'épreuve
     * @param groupe le groupe de l'EC (ou -1 pour tous)
     * @return la liste des notes (ou null en cas d'erreur)
     */
    public static function getListeNotesEpreuves(int $EC, int $epreuve, int $groupe) : array {
        $DB = MyPDO::getInstance();
        
        $SQL = "SELECT ".UserModel::DBF_ID." as id, ".
                         EtudiantModel::DBF_NUMERO." as numero, ".
                         UserModel::DBF_NAME." as nom, ".
                         UserModel::DBF_FIRSTNAME." as prenom, ".
                         UserModel::DBF_MAIL." as email, ".
                         self::DBF_NOTE." as note, ".
                         EpreuveModel::DBF_MAX." as max ".
               " FROM ";
        if($groupe == -1) {
            $SQL .=
                 "(SELECT * FROM ".InscriptionECModel::DB.", ".
                                   UserModel::DB.", ".
                                   EtudiantModel::DB.", ".
                                   EpreuveModel::DB.
                 " WHERE ".InscriptionECModel::DBF_EC."=:EC AND ".
                           InscriptionECModel::DBF_ETUDIANT."=".UserModel::DBF_ID." AND ".
                           EtudiantModel::DBF_USER."=".UserModel::DBF_ID." AND ".
                           EpreuveModel::DBF_ID."=:epreuve"." AND ".
                           InscriptionECModel::DBF_TYPE."=".InscriptionECModel::TYPE_INSCRIT.
                           ") AS A ".
               " LEFT OUTER JOIN ".self::DB.
               " ON ".self::DBF_USER."=".EtudiantModel::DBF_USER." AND ".self::DBF_EPREUVE."=:epreuve".
               " ORDER BY ".UserModel::DBF_NAME." ASC, ".UserModel::DBF_FIRSTNAME." ASC";
            $data = [ ":EC" => $EC, ":epreuve" => $epreuve ];
        }
        else {
            $SQL .=
                 "(SELECT * FROM ".InscriptionGroupeECModel::DB.", ".
                                   UserModel::DB.", ".
                                   EtudiantModel::DB.", ".
                                   EpreuveModel::DB.
                 " WHERE ".InscriptionGroupeECModel::DBF_GROUPE."=:groupe AND ".
                           InscriptionGroupeECModel::DBF_ETUDIANT."=".UserModel::DBF_ID." AND ".
                           EtudiantModel::DBF_USER."=".UserModel::DBF_ID." AND ".
                           EpreuveModel::DBF_ID."=:epreuve AND ".
                           EpreuveModel::DBF_EC."=:EC".
                           ") AS A ".
               " LEFT OUTER JOIN ".self::DB.
               " ON ".self::DBF_USER."=".EtudiantModel::DBF_USER." AND ".self::DBF_EPREUVE."=:epreuve".
               " ORDER BY ".UserModel::DBF_NAME." ASC, ".UserModel::DBF_FIRSTNAME." ASC";
            $data = [ ':EC' => $EC, ':epreuve' => $epreuve, ':groupe' => $groupe ];
        }
        
        if(($request = $DB->prepare($SQL)) && $request->execute($data)) {
            $result = array();

            while($row = $request->fetch())
                $result[] = $row;

            return $result;
        }
        else
            return null;
    }
    
    /**
     * Ajoute une note à un étudiant pour une épreuve.
     * @param etudiant l'identifiant de l'étudiant
     * @param epreuve l'identifiant de l'épreuve
     * @return 'true' en cas de réussite ou 'false' sinon
     */
    public static function setNote(int $etudiant, int $epreuve, float $note) : bool {
        $DB = MyPDO::getInstance();
        
        $SQL_supp = "DELETE FROM ".self::DB.
                    " WHERE ".self::DBF_USER."=:etudiant AND ".
                              self::DBF_EPREUVE."=:epreuve;";
        $SQL_add = "INSERT INTO ".self::DB.
                   " (".self::DBF_EPREUVE.", ".self::DBF_USER.", ".self::DBF_NOTE.
                   ") VALUES (:epreuve, :etudiant, :note);";
        if(($request_supp = $DB->prepare($SQL_supp)) && 
           $request_supp->execute([ ':etudiant' => $etudiant, ':epreuve' => $epreuve ])) {
            if($note != Note::TYPE_AUCUNE) {
                return (($request_add = $DB->prepare($SQL_add)) && 
                        $request_add->execute([ ':etudiant' => $etudiant, ':epreuve' => $epreuve, ':note' => $note ]));
            }
            else
                return true;
        }
        else
            return false;
    }

} // class NoteModel