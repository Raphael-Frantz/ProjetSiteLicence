<?php
// @need etudiants la liste des étudiants
// @need diplome le diplôme sélectionné (ou -1 si aucun)

if(count($data['etudiants']) == 0) {
    if($data['diplome'] == -1)
        $msg = "Il n'y a aucun étudiant sur le site.";
    else
        $msg = "Il n'y a aucun étudiant non inscrit dans ce diplôme et ce semestre.";

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
      <th scope="col">Email</th>
      <th class="text-right" scope="col">Actions</th>
    </tr>
  </thead>
HTML;
    foreach($data['etudiants'] as $etudiant) {
        echo <<<HTML
        <tr id='ligne{$etudiant['id']}'>
          <th scope='row'>{$etudiant['numero']}</th>
          <td>{$etudiant['nom']}</td>
          <td>{$etudiant['prenom']}</td>
          <td>{$etudiant['email']}</td>
          <td class="text-right">
            <a data-toggle='tooltip' data-placement='top' title="IPs de l'étudiant(e)" 
               href="javascript:setEtudiant({$etudiant['id']}, 'etudiants/ip.php')" class='btn btn-sm mr-1 btn-outline-primary'>
              <i class='icon-list'></i>
            </a>
            <a data-toggle='tooltip' data-placement='top' title="Notes de l'étudiant(e)" 
               href="javascript:setEtudiant({$etudiant['id']}, 'etudiants/notes.php')" class='btn btn-sm mr-1 btn-outline-primary'>
              <i class='icon-notebook'></i>
            </a>
            <a data-toggle='tooltip' data-placement='top' title="Présentiel de l'étudiant(e)" 
               href="javascript:setEtudiant({$etudiant['id']}, 'etudiants/presentiel.php')" class='btn btn-sm mr-1 btn-outline-primary'>
              <i class='icon-user-following'></i>
            </a>
            <a data-toggle='tooltip' data-placement='top' title="Groupes de l'étudiant(e)" 
               href="javascript:setEtudiant({$etudiant['id']}, 'etudiants/groupes.php')" class='btn btn-sm mr-1 btn-outline-primary'>
              <i class='icon-layers'></i>
            </a>              
          </td>
        </tr>
HTML;
    }
    echo "</table>";
}