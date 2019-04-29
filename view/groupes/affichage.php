<?php
// @need groupes la liste des groupes
// @need diplome le diplôme sélectionné (ou -1 si aucun)
// @need tuteurs la liste des tuteurs

if(count($data['etudiants']) == 0) {
    if($data['diplome'] == -1)
        $msg = "Sélectionnez un diplôme";
    elseif($data['semestre'] == -1)
        $msg = "Sélectionnez un semestre";
    else
        $msg = "Il n'y a aucun étudiant inscrit dans ce diplôme/semestre.";
    
    echo <<<HTML
<div class="media alert-danger text-center" id="message">
  <div class="media-body">
    <p class="lead mb-0" id="contenuMessage">{$msg}</p>
  </div>
</div>
HTML;
}
else {
    echo <<<HTML
<table class="table table-striped" id="tableEtudiants">
  <thead>
    <tr>
      <th scope="col">Nom - Prénom</th>
      <th scope="col">Tuteur</th>
      <th scope="col">CM</th>
      <th scope="col">TD</th>
      <th scope="col">TP</th>
    </tr>
  </thead>
HTML;
    $i = 0;
    for($i = 0; $i < count($data['etudiants']); $i++) {
        $etudiant = $data['etudiants'][$i];
        echo <<<HTML
        <tr>
          <td scope='row'>{$etudiant['nom']} {$etudiant['prenom']}</td>
HTML;
        echo "<td>";
        if($etudiant['tuteur'] == null)
            echo "Aucun";
        else
            echo $etudiant['tuteur'];
        echo "</td>";
        echo "<td>";
        if($etudiant['groupeCM'] == null)
            echo "Aucun";
        else
            echo $data['groupesCM'][$etudiant['groupeCM']];
        echo "</td>";
        echo "<td>";
        if($etudiant['groupeTD'] == null)
            echo "Aucun";
        else
            echo $data['groupesTD'][$etudiant['groupeTD']];
        echo "</td>";
        echo "<td>";
        if($etudiant['groupeTP'] == null)
            echo "Aucun";
        else
            echo $data['groupesTP'][$etudiant['groupeTP']];
        echo "</td>";
        echo <<<HTML
        </tr>
HTML;
    }
    echo "</table>";
}