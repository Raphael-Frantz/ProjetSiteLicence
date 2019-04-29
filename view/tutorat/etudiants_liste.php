<?php
// @need groupes la liste des groupes
// @need diplome le diplôme sélectionné (ou -1 si aucun)

if((count($data['etudiants']) == 0) || (count($data['EC']) == 0)) {
    $msg = "";
    
    if(count($data['etudiants']) == 0)
        $msg .= "Il n'y a pas d'étudiant dans ce diplome/semestre. ";
    
    if(count($data['EC']) == 0)
        $msg .= "Il n'y a pas d'EC dans ce diplome/semestre. ";
    
    echo <<<HTML
<div class="media alert-danger text-center" id="message">
  <div class="media-body">
    <p class="lead mb-0" id="contenuMessage">{$msg}</p>
  </div>
</div>
HTML;
}
else {
    $i = 0;
    echo <<<HTML
<table class="table table-striped table-bordered" id="tableEtudiants">
HTML;
    foreach($data['etudiants'] as $idEtu => $etudiant) {
        if($i % 10 == 0) {
            echo <<<HTML
  <thead>
    <tr>
      <th scope="col">Numéro</th>
      <th scope="col">Nom - Prénom</th>
HTML;
    foreach($data['EC'] as $EC) {
        echo "<th scope='col'>{$EC['code']}</th>";
    }
    echo <<<HTML
    </tr>
  </thead>
HTML;
        }
        echo <<<HTML
        <tr>
          <td scope='row'>{$etudiant['numero']}</td>
HTML;
        if(UserModel::estTuteurDiplome($data['diplome']) || UserModel::estRespDiplome($data['diplome']))
            echo <<<HTML
          <td><a href="javascript:setEtudiant({$idEtu}, 'etudiants/ip.php', 'tutorat/etudiants.php')">{$etudiant['nom']} {$etudiant['prenom']}</a></td>
HTML;
        else
            echo <<<HTML
          <td>{$etudiant['nom']} {$etudiant['prenom']}</td>
HTML;
        $j = 0;
        foreach($etudiant['EC'] as $ins) {
            if(isset($data['diplome']) && UserModel::estRespDiplome($data['diplome']))
                echo "<td id='".$idEtu."_".$data['EC'][$j]['id']."' onclick='javascript:modifierIP($idEtu, \"{$etudiant['nom']} {$etudiant['prenom']}\", ".
                     $data['EC'][$j]['id'].", \"".$data['EC'][$j]['code']."\")'>";
            else
                echo "<td>";
            if($ins['type'] == InscriptionECModel::TYPE_INSCRIT)
                echo "X</td>";
            elseif($ins['type'] == null)
                echo "</td>";
            else
                echo "{$ins['note']}/{$ins['bareme']}</td>";
            $j++;
        }
        echo <<<HTML
        </tr>
HTML;
        $i++;
    }
    echo "</table>";
}