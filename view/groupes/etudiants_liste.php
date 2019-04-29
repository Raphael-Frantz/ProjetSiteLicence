<?php
// @need 

if(count($data['etudiants']) == 0) {
    if($data['diplome'] == -1)
        $msg = "Il n'y a aucun étudiant sur le site.";
    else {
        if(($data['mode'] == 1) ||($data['mode'] == 4))
            if($data['semestre'] == -1)
                $msg = "Il n'y a aucun étudiant inscrit dans ce diplôme.";
            else
                $msg = "Il n'y a aucun étudiant inscrit dans ce semestre.";
        else
            $msg = "Il n'y a aucun étudiant non inscrit dans ce diplôme et ce semestre.";
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
      <th scope="col">Nom - Prénom</th>
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
HTML;
        if(UserModel::estTuteurDiplome($data['diplome']) || UserModel::estRespDiplome($data['diplome'])) {
            echo <<<HTML
          <td><a href="javascript:setEtudiant({$etudiant['id']}, 'etudiants/ip.php', 'groupes/etudiants.php')">{$etudiant['nom']} {$etudiant['prenom']}</a></td>
HTML;
        }
        else {
            echo <<<HTML
          <td>{$etudiant['nom']} {$etudiant['prenom']}</td>
HTML;
        }
        echo <<<HTML
          <td class="text-right">
HTML;
        if($data['mode'] == 4)
            echo <<<HTML
            <a class='btn btn-sm btn-outline-danger mr-2' href='javascript:desinscription({$etudiant['id']})' data-toggle="tooltip" data-placement="top" title="Désinscrire l'étudiant">
              <i class="icon-minus"></i>
            </a>
HTML;
        echo <<<HTML
          </td>
        </tr>
HTML;
    }
    echo "</table>";
}