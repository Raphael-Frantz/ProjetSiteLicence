<?php
// @need groupes la liste des groupes
// @need diplome le diplôme sélectionné (ou -1 si aucun)

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
      <th scope="col">Numéro</th>
      <th scope="col">Nom - Prénom</th>
      <th scope="col">CM</th>
      <th scope="col">TD</th>
      <th scope="col">TP</th>
    </tr>
  </thead>
HTML;
    $lienModif = WEB_PATH."groupes/modifier.php";
    $lienSupp = WEB_PATH."groupes/supprimer.php";
    foreach($data['etudiants'] as $etudiant) {
        echo <<<HTML
        <tr>
          <th scope='row'>{$etudiant['numero']}</th>
HTML;
        if(UserModel::estTuteurDiplome($data['diplome']) || UserModel::estRespDiplome($data['diplome'])) {
            echo <<<HTML
          <td><a href="javascript:setEtudiant({$etudiant['id']}, 'etudiants/ip.php', 'groupes/attribution.php')">{$etudiant['nom']} {$etudiant['prenom']}</a></td>
HTML;
        }
        else {
            echo <<<HTML
          <td>{$etudiant['nom']} {$etudiant['prenom']}</td>
HTML;
        }
        echo <<<HTML
HTML;
        if(count($data['groupesCM']) == 0) {
            echo "<td>Pas de groupe</td>";
        }
        else {
            echo "<td>";
            echo "<select id='CM_{$etudiant['id']}' onchange='inscription({$etudiant['id']}, 1)'>";
            if($etudiant['groupeCM'] == null)
                echo "<option value='-1' selected>Aucun</option>";
            else
                echo "<option value='-1'>Aucun</option>";
            foreach($data['groupesCM'] as $groupeCM) {
                if($etudiant['groupeCM'] == $groupeCM['id'])
                    echo "<option value='{$groupeCM['id']}' selected>{$groupeCM['intitule']}</option>";
                else
                    echo "<option value='{$groupeCM['id']}'>{$groupeCM['intitule']}</option>";
            }
            echo "</select>";
            echo "</td>";
        }
        if(count($data['groupesTD']) == 0) {
            echo "<td>Pas de groupe</td>";
        }
        else {
            echo "<td>";
            echo "<select id='TD_{$etudiant['id']}' onchange='inscription({$etudiant['id']}, 2)'>";
            if($etudiant['groupeTD'] == null)
                echo "<option value='-1' selected>Aucun</option>";
            else
                echo "<option value='-1'>Aucun</option>";
            foreach($data['groupesTD'] as $groupeTD) {
                if($etudiant['groupeTD'] == $groupeTD['id'])
                    echo "<option value='{$groupeTD['id']}' selected>{$groupeTD['intitule']}</option>";
                else
                    echo "<option value='{$groupeTD['id']}'>{$groupeTD['intitule']}</option>";
            }
            echo "</select>";
            echo "</td>";
        }
        if(count($data['groupesTP']) == 0) {
            echo "<td>Pas de groupe</td>";
        }
        else {
            echo "<td>";
            echo "<select id='TP_{$etudiant['id']}' onchange='inscription({$etudiant['id']}, 3)'>";
            if($etudiant['groupeTP'] == null)
                echo "<option value='-1' selected>Aucun</option>";
            else
                echo "<option value='-1'>Aucun</option>";
            foreach($data['groupesTP'] as $groupeTP) {
                if($etudiant['groupeTP'] == $groupeTP['id'])
                    echo "<option value='{$groupeTP['id']}' selected>{$groupeTP['intitule']}</option>";
                else
                    echo "<option value='{$groupeTP['id']}'>{$groupeTP['intitule']}</option>";
            }
            echo "</select>";
            echo "</td>";
        }
        echo <<<HTML
        </tr>
HTML;
    }
    echo "</table>";
}