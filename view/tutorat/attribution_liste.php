<?php
// @need 

if(count($data['etudiants']) == 0) {
    if(isset($data['tuteur']))
        $msg = "Il n'y a pas d'étudiant pour ce tuteur. ";
    else
        $msg = "Il n'y a pas d'étudiant dans ce diplôme. ";
    
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
<table class="table table-striped" id="tableEtudiants">
  <thead>
    <tr>
      <th scope="col">Numéro</th>
      <th scope="col">Nom - Prénom</th>
      <th scope="col">Adresse email</th>
HTML;
    if(isset($data['tuteurs'])) {
        echo "<th scope='col'>Tuteur</th>";
    }
    else {
      echo "<th scope='col' class='align-right'>Actions</th>";
    }
    echo <<<HTML
    </tr>
  </thead>
HTML;
    foreach($data['etudiants'] as $idEtu => $etudiant) {
        echo <<<HTML
        <tr id='ligne{$etudiant['id']}'>
          <td scope='row'>{$etudiant['numero']}</td>
HTML;
        if(UserModel::estTuteurDiplome($data['diplome']) || UserModel::estRespDiplome($data['diplome'])) {
            echo <<<HTML
          <td><a href="javascript:setEtudiant({$etudiant['id']}, 'etudiants/ip.php', 'tutorat/attribution.php')">{$etudiant['nom']}</a></td>
HTML;
        }
        else {
            echo <<<HTML
          <td>{$etudiant['nom']}</td>
HTML;
        }
        echo <<<HTML
          <td data-email='email'>{$etudiant['email']}</td>
HTML;
        echo "<td>";
        if(isset($data['tuteurs'])) {
            echo "<select id='tut_{$etudiant['id']}' onchange='inscription({$etudiant['id']})'>";
            if($etudiant['tuteur'] == null)
                echo "<option value='-1' selected>Aucun</option>";
            else
                echo "<option value='-1'>Aucun</option>";
            foreach($data['tuteurs'] as $tuteur) {
                if($etudiant['tuteur'] == $tuteur['id'])
                    echo "<option value='{$tuteur['id']}' selected>{$tuteur['nom']}</option>";
                else
                    echo "<option value='{$tuteur['id']}'>{$tuteur['nom']}</option>";
            }
            echo "</select>";
        }
        else {
            echo <<<HTML
            <a class='btn btn-sm btn-outline-danger mr-2' href='javascript:desinscription({$etudiant['id']})' data-toggle="tooltip" data-placement="top" title="Retirer ce tuteur">
              <i class="icon-minus"></i>
            </a>
HTML;
        }
        echo "</td>";
        
        echo <<<HTML
        </tr>
HTML;
    }
    echo "</table>";
}