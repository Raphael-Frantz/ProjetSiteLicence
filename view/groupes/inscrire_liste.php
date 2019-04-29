<?php
// @need 

if(count($data['etudiants']) == 0) {
    if($data['diplome'] == -1)
        $msg = "Il n'y a aucun étudiant sur le site.";
    else {
        if($data['semestre'] == -1)
            $msg = "Il n'y a aucun étudiant inscrit dans ce diplôme.";
        else
            $msg = "Il n'y a aucun étudiant inscrit dans ce semestre.";
    }
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
      <th scope="col">Nom</th>
      <th scope="col">Prénom</th>
      <th class="text-right" scope="col">Actions</th>
    </tr>
  </thead>
HTML;
    $lienModif = WEB_PATH."etudiants/modifier.php";
    $lienSupp = WEB_PATH."etudiants/supprimer.php";
    foreach($data['etudiants'] as $etudiant) {
        echo <<<HTML
        <tr id='ligne{$etudiant['id']}'>
          <th scope='row'>{$etudiant['numero']}</th>
          <td>{$etudiant['nom']}</td>
          <td>{$etudiant['prenom']}</td>
          <td class="text-right">
HTML;
        echo <<<HTML
            <a class='btn btn-sm btn-outline-warning mr-2' href='javascript:inscription({$etudiant['id']})' data-toggle="tooltip" data-placement="top" title="Inscrire l'étudiant">
              <i class="icon-plus"></i>
            </a>
          </td>
        </tr>
HTML;
    }
    echo "</table>";
}