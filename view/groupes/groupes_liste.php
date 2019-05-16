<?php
// @need groupes la liste des groupes
// @need diplome le diplôme sélectionné (ou -1 si aucun)

if(count($data['groupes']) == 0) {
    if($data['diplome'] == -1)
        $msg = "Sélectionnez un diplôme";
    else
        $msg = "Il n'y a aucun groupe";
    
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
    
<div class="alert alert-warning alert-dismissible fade show text-center" role="alert" id="edtNotAvailable" style="display: none;">
  Il n'existe pas d'emploi du temps pour ce groupe.
  <button type="button" class="close" aria-hidden="true" aria-label="Close" onclick="javascript:$('#edtNotAvailable').hide()">
    <span>&times;</span>
  </button>
</div>

<table class="table table-striped">
  <thead>
    <tr>
      <th scope="col">Intitulé</th>
      <th scope="col">Type</th>
      <th class="text-right" scope="col">Actions</th>
    </tr>
  </thead>
HTML;
    $lienModif = WEB_PATH."groupes/modifier.php";
    $lienSupp = WEB_PATH."groupes/supprimer.php";
    $lienGrp = WEB_PATH."groupes/etudiants.php";
    foreach($data['groupes'] as $groupe) {
        $edtAction = $groupe['planning'];
        if(empty($edtAction)) {
            $edtAction = "javascript:$('#edtNotAvailable').show()";
        }
        $type = Groupe::type2String($groupe['type']);
        echo <<<HTML
        <tr id='ligne{$groupe['id']}'>
          <th scope='row'>{$groupe['intitule']}</th>
          <td>{$type}</td>
          <td class="text-right">
            <a class="btn btn-sm btn-outline-primary mr-2" data-toggle='tooltip' 
               data-placement='top' title="Emploi du temps" href="{$edtAction}">
                <i class="icon-calendar"></i>
            </a>
            <button name='idModi' type='submit' class='btn btn-sm btn-outline-warning mr-2' data-toggle='tooltip' 
                    data-placement='top' title="Modifier le groupe" form='controlForm' formaction='$lienModif' 
                    value='{$groupe['id']}'>
              <i class='icon-wrench'></i>
            </button>
            <button name='groupe' type='submit' class='btn btn-sm btn-outline-primary mr-2' data-toggle='tooltip' 
                    data-placement='top' title="Liste des étudiants" form='controlForm' formaction='$lienGrp' 
                    value='{$groupe['id']}'>
              <i class='icon-list'></i>
            </button>
            <button name='idSupp' type='submit' class='btn btn-sm btn-outline-danger mr-2' data-toggle='tooltip' 
                    data-placement='top' title="Supprimer le groupe" form='controlForm' formaction='$lienSupp' 
                    value='{$groupe['id']}'>
              <i class='icon-trash'></i>
            </button>            
          </td>
        </tr>
HTML;
    }
    echo "</table>";
}