<?php
// @need justificatifs la liste des justificatifs

if(count($data['justificatifs']) == 0) {
    $msg = "Il n'y a pas de justificatif actuellement pour ce diplôme.";
    
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
  <thead>
    <tr>
      <th scope="col">Numéro</th>
      <th scope="col">Nom - Prénom</th>
      <th scope="col">Adresse email</th>
      <th scope="col">Motif</th>
      <th scope="col">Date</th>
      <th scope="col" class="text-right">Actions</th>
    </tr>
  </thead>
HTML;
    foreach($data['justificatifs'] as $justificatif) {
        $heure = (date('G', $justificatif['debut']) != 0);
        
        // Pour les vérifications dans la base, il faut faire en sorte que la date de fin soit +1 jour si justif sur une journée
        if(!$heure)
            $justificatif['fin'] = strtotime('-1 day', $justificatif['fin']);
        
        if($justificatif['debut'] != $justificatif['fin'])
            $date = "Du ".DateTools::timestamp2Date($justificatif['debut'], $heure)." au ".DateTools::timestamp2Date($justificatif['fin'], $heure);
        else
            $date = "Le ".DateTools::timestamp2Date($justificatif['debut'], $heure);
        
        echo <<<HTML
        <tr id='ligne{$justificatif['id']}'>
          <td scope='row'>{$justificatif['numero']}</td>
          <td><a href="javascript:setEtudiant({$justificatif['idEtu']}, 'etudiants/ip.php', 'presentiel/justificatifs.php')">{$justificatif['nom']} {$justificatif['prenom']}</a></td>
          <td data-email='email'>{$justificatif['email']}</td>
          <td>{$justificatif['motif']}</td>
          <td>{$date}</td>
          <td class="text-right">
            <a class='btn btn-sm btn-outline-primary mr-1' href='javascript:infos({$justificatif['id']})' data-toggle="tooltip" data-placement="top" title="Plus d'informations">
              <i class="icon-info"></i>
            </a>
HTML;
        if(UserModel::estTuteurDiplome($data['diplome']) || UserModel::estRespDiplome($data['diplome'])) {
            echo <<<HTML
            <a class='btn btn-sm btn-outline-primary mr-1' href='javascript:redirect({$justificatif['id']}, "presentiel/modifier.php")' data-toggle="tooltip" data-placement="top" title="Éditer ce justificatif">
              <i class="icon-wrench"></i>
            </a>
            <a class='btn btn-sm btn-outline-danger' href='javascript:redirect({$justificatif['id']}, "presentiel/supprimer.php")' data-toggle="tooltip" data-placement="top" title="Supprimer ce justificatif">
              <i class="icon-trash"></i>
            </a>
HTML;
        }
        echo <<<HTML
          </td>
        </tr>
HTML;
    }
    echo "</table>";
}