<?php
// @need tuteurs la liste des tuteurs
// @need diplome le diplôme sélectionné (ou -1 si aucun)

if(count($data['tuteurs']) == 0) {
    if($data['mode'] == 1)
        $msg = "Il n'y a pas de tuteur pour ce diplôme.";
    elseif($data['mode'] == 2)
        $msg = "Il n'y a pas d'enseignant à ajouter comme tuteur pour ce diplôme.";
    
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
<table class="table table-striped table-bordered" id="tableTuteurs">
  <thead>
    <tr>
      <th scope="col">Nom - Prénom</th>
      <th scope="col">Adresse email</th>
      <th scope="col" class="text-right">Actions</th>
    </tr>
  </thead>
HTML;
    foreach($data['tuteurs'] as $idTut => $tuteur) {
        echo <<<HTML
        <tr id='ligne{$tuteur['id']}'>
          <td>{$tuteur['nom']}</td>
          <td data-email='email'>{$tuteur['email']}</td>
          <td class="text-right">
HTML;
        if($data['mode'] == 2)
            echo <<<HTML
            <a class='btn btn-sm btn-outline-warning mr-2' href='javascript:inscription({$tuteur['id']})' data-toggle="tooltip" data-placement="top" title="Ajouter cet enseignant">
              <i class="icon-plus"></i>
            </a>
HTML;
        if($data['mode'] == 1)
            echo <<<HTML
            <a class='btn btn-sm btn-outline-danger mr-2' href='javascript:desinscription({$tuteur['id']})' data-toggle="tooltip" data-placement="top" title="Retirer ce tuteur">
              <i class="icon-minus"></i>
            </a>
HTML;
        echo <<<HTML
            <a class='btn btn-sm btn-outline-primary mr-2' href='mailto:{$tuteur['email']}' data-toggle="tooltip" data-placement="top" title="Envoyer un mail">
              <i class="icon-envelope"></i>
            </a>
          </td>
        </tr>
HTML;
        echo <<<HTML
          </td>
        </tr>
HTML;
    }
    echo "</table>";
}