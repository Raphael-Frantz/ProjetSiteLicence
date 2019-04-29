<?php
// @need ECS la liste des ECs

WebPage::addJSScript("public/js/active-tooltips.js");
WebPage::addJSScript("public/js/tools.js");
WebPage::addOnlineScript("var WEB_PATH = '".WEB_PATH."';");
?>
<section class="mytitle text-center bg-light">
  <div class="container">
    <h2 class="mb-5">Configuration de l'EC <?php echo $data['EC']->getCode(); ?></h2>
    <p class="lead mb-0">
        Depuis cette page, vous pouvez consulter la configuration de l'EC.
    </p>
  </div>
</section>

<form id="controlForm" action="" method="post"></form>

<script>
var request = null;
function change(user, epreuve) {
    if(request == null) {
        request = $.ajax({
                type: 'POST',
                url: WEB_PATH + 'epreuves/ws.php',
                data: { 'mode' : 3, 'user' : user, 'epreuve' : epreuve, 'type' : $('#d_' + user + "_" + epreuve).attr("value") },
                dataType: 'json'
            });
        request.done(function (response, textStatus, jqXHR) {
                if(response['code'] == 1) {
                    selector = $('#d_' + response['user'] + "_" + response['epreuve']);
                    
                    switch(response['type']) {
                        case 0:
                            selector.removeClass().addClass("btn btn-danger btn-sm");
                            selector.text('NON');
                            selector.attr("value", 1);
                            break;
                        case 1:
                            selector.removeClass().addClass("btn btn-success btn-sm");
                            selector.text('OUI');
                            selector.attr("value", 0);
                            break;
                    }
                }
                else {
                    displayErrorModal("Une erreur technique est survenue. " + response['erreur']);
                }
                request = null;
            });
        request.fail(function (jqXHR, textStatus, errorThrown){
                console.log(jqXHR);
                displayErrorModal("Une erreur technique est survenue. Veuillez prévenir l'administrateur.");
                request = null;
            });
    }
}
function active(epreuve, etat) {
    if(request == null) {
        request = $.ajax({
                type: 'POST',
                url: WEB_PATH + 'epreuves/ws.php',
                data: { 'mode' : 1, 'epreuve' : epreuve, 'etat' : etat },
                dataType: 'json'
            });
        request.done(function (response, textStatus, jqXHR) {
                if(response['code'] == 1) {
                    if(response['etat'] == 1) {
                        $('#act_' + response['epreuve']).hide();
                        $('#des_' + response['epreuve']).show();
                    }
                    else {
                        $('#act_' + response['epreuve']).show();
                        $('#des_' + response['epreuve']).hide();                        
                    }
                }
                else
                    displayErrorModal(response['erreur']);
                request = null;
            });
        request.fail(function (jqXHR, textStatus, errorThrown){
                console.log(jqXHR);
                displayErrorModal("Une erreur technique est survenue. Veuillez prévenir l'administrateur.");
                request = null;
            });
    }
}
function visible(epreuve, etat) {
    if(request == null) {
        request = $.ajax({
                type: 'POST',
                url: WEB_PATH + 'epreuves/ws.php',
                data: { 'mode' : 2, 'epreuve' : epreuve, 'etat' : etat },
                dataType: 'json'
            });
        request.done(function (response, textStatus, jqXHR) {
                if(response['code'] == 1) {
                    if(response['etat'] == 1) {
                        $('#vis_' + response['epreuve']).hide();
                        $('#cac_' + response['epreuve']).show();
                    }
                    else {
                        $('#vis_' + response['epreuve']).show();
                        $('#cac_' + response['epreuve']).hide();                        
                    }
                }
                else
                    displayErrorModal(response['erreur']);
                request = null;
            });
        request.fail(function (jqXHR, textStatus, errorThrown){
                console.log(jqXHR);
                displayErrorModal("Une erreur technique est survenue. Veuillez prévenir l'administrateur.");
                request = null;
            });
    }
}
</script>

<div class="container">
  <div class="justify-content-end btn-toolbar mt-2 mr-2">
    <div class="input-group">
      <a data-toggle='tooltip' data-placement='top' title='Retourner à la liste des ECs' href='<?php echo WEB_PATH; ?>ecs/liste.php' class='btn btn-outline-primary'>Retour</a>
    </div>
  </div>  
</div>

<section>
  <div class="container">
    <h3>Liste des responsables</h3>
  
<?php
if(isset($data['responsables']) && (count($data['responsables']) > 0)) {
?>
    <table class="table table-striped">
      <thead>
        <tr>
          <th scope="col">Nom/prénom</th>
        </tr>
      </thead>
      <tbody>    
<?php
    foreach($data['responsables'] as $responsable) {
        echo "<tr>";
        echo "<td>".$responsable['nom']." ".$responsable['prenom']."</td>";
        echo "</tr>";
    }
?>
      </tbody>
    </table>
<?php
}
else {
    echo <<<HTML
<div class="media alert-danger text-center">
  <div class="media-body">
    <p class="lead mb-0">Il n'y a pas d'intervenant actuellement.</p>
  </div>
</div>    
HTML;
}
?> 
  
    <h3>Liste des intervenants</h3>
<?php
if(isset($data['intervenantsGrp']) && (count($data['intervenantsGrp']) > 0)) {
?>
    <table class="table table-striped">
      <thead>
        <tr>
          <th scope="col">Nom/prénom</th>
          <th scope="col">Groupe</th>
        </tr>
      </thead>
      <tbody>    
<?php
    foreach($data['intervenantsGrp'] as $intervenant) {
        echo "<tr>";
        echo "<td>".$intervenant['nom']." ".$intervenant['prenom']."</td>";
        echo "<td>{$intervenant['groupe']} (".Groupe::type2String($intervenant['type']).")</td>";
        echo "</tr>";
    }
?>
      </tbody>
    </table>
<?php
}
else {
    echo <<<HTML
<div class="media alert-danger text-center">
  <div class="media-body">
    <p class="lead mb-0">Il n'y a pas d'intervenant actuellement.</p>
  </div>
</div>    
HTML;
}
?>
  </div>
</section>

<?php
/*
<section>
  <div class="container">
    <h3>Saisie du présentiel</h3>

  </div>
</section>
*/
?>
    
<section>
  <div class="container">
    <h3>Saisie des notes</h3>

<?php
    if(count($data['intervenants']) == 0) {
        echo <<<HTML
<div class="media alert-danger text-center">
  <div class="media-body">
    <p class="lead mb-0">Il n'y a pas d'intervenant actuellement.</p>
  </div>
</div>    
HTML;
    }
    else {
?>
    
    <p class="lead">
        Le tableau ci-dessous indique pour chaque épreuve, quels intervenants peuvent saisir les notes :
    </p>
    
    <table class="table table-striped">
      <thead>
        <tr>
          <th scope="col">Nom/prénom</th>
<?php
foreach($data['epreuves'] as $epreuve) {
    echo "<th scope='col'>{$epreuve['intitule']}</th>";
}
?>
        </tr>
        <tr>
          <th></th>
<?php
$estResp = (UserModel::estRespEC($data['EC']->getId()));

foreach($data['epreuves'] as $epreuve) {
    echo "<th scope='col'>";
    
    if($estResp) {
        if($epreuve['active'] == 1) {
          $tmp1 = "style='display: none;'";
          $tmp2 = "";
        }
        else {
          $tmp1 = "";
          $tmp2 = "style='display: none;'";
        }
        echo "<a id='act_{$epreuve['id']}' $tmp1 class='btn btn-sm btn-outline-success mr-1' data-toggle='tooltip' data-placement='top' ".
           "title=\"Active l'épreuve\" href='javascript:active({$epreuve['id']},1)'><i class='icon-check'></i></a>";
        echo "<a id='des_{$epreuve['id']}' $tmp2 class='btn btn-sm btn-outline-danger mr-1' data-toggle='tooltip' data-placement='top' ".
           "title=\"Désactive l'épreuve\" href='javascript:active({$epreuve['id']},0)'><i class='icon-close'></i></a>";
           
        if($epreuve['visible'] == 1) {
          $tmp1 = "style='display: none;'";
          $tmp2 = "";
        }
        else {
          $tmp1 = "";
          $tmp2 = "style='display: none;'";
        }
        echo "<a id='vis_{$epreuve['id']}' $tmp1 class='btn btn-sm btn-outline-success' data-toggle='tooltip' data-placement='top' ".
           "title=\"Rend visible l'épreuve\" href='javascript:visible({$epreuve['id']},1)'><i class='icon-eye'></i></a>";
        echo "<a id='cac_{$epreuve['id']}' $tmp2 class='btn btn-sm btn-outline-danger' data-toggle='tooltip' data-placement='top' ".
           "title=\"Cache l'épreuve\" href='javascript:visible({$epreuve['id']},0)'><i class='icon-eye'></i></a>";
    }
    else {
        if($epreuve['active'])
            echo "<span class='mr-1 badge badge-success'><i data-toggle='tooltip' data-placement='top' title=\"L'épreuve est active\" class='icon-check'></i></span>";
        else
            echo "<span class='mr-1 badge badge-danger'><i data-toggle='tooltip' data-placement='top' title=\"L'épreuve n'est pas active\" class='icon-close'></i></span>";
        if($epreuve['visible'])
            echo "<span class='badge badge-success'><i data-toggle='tooltip' data-placement='top' title=\"L'épreuve est visible par les étudiants\" class='icon-eye'></i></span>";
        else
            echo "<span class='badge badge-danger'><i data-toggle='tooltip' data-placement='top' title=\"L'épreuve n'est pas visible par les étudiants\" class='icon-eye'></i></span>";
    }
    echo "</th>";
}
?>
        </tr>
      </thead>
      <tbody>    
<?php
foreach($data['intervenants'] as $intervenant) {
    echo "<tr>";
    echo "<td>{$intervenant['nom']} {$intervenant['prenom']}</td>";
    
    // Vérification si l'intervenant est responsable de la matière
    $i = 0;
    while(($i < count($data['responsables'])) && ($data['responsables'][$i]['id'] != $intervenant['id']))
        $i++;
    $resp = $i < count($data['responsables']);    
    
    foreach($data['epreuves'] as $epreuve) {
        if(!$resp) {
            if(isset($data['droits'][$intervenant['id']])
               && in_array($epreuve['id'], $data['droits'][$intervenant['id']])) {
                if($estResp)
                    echo "<td><button id='d_{$intervenant['id']}_{$epreuve['id']}' class='btn btn-success btn-sm' value='0' onclick='javascript:change({$intervenant['id']}, {$epreuve['id']});'>OUI</button></td>";
                else
                    echo "<td><button class='btn btn-success btn-sm' disabled>OUI</button></td>";
            }
            else {
                if($estResp)
                    echo "<td><button id='d_{$intervenant['id']}_{$epreuve['id']}' class='btn btn-danger btn-sm' value='1' onclick='javascript:change({$intervenant['id']}, {$epreuve['id']});'>NON</button></td>";
                else
                    echo "<td><button class='btn btn-danger btn-sm' disabled>NON</button></td>";
            }
        }
        else {
            // Le responsable a le droit de saisir les notes sur toutes les épreuves
            echo "<td><button class='btn btn-success btn-sm' disabled>OUI</button></td>";
        }
    }
    echo "</tr>";
}
?>
      </tbody>
    </table>
<?php
    }
?>
  </div>
</section>