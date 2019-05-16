<?php
// @need epreuves la liste des épreuves

WebPage::addJSScript("public/js/active-tooltips.js");
WebPage::addJSScript("public/js/tools.js");
WebPage::addOnlineScript("var WEB_PATH = '".WEB_PATH."';");

$respEC = UserModel::estRespEC($data['EC']->getId());
$respDip = UserModel::estAdmin() || RespDiplomeModel::estResponsableDiplomeEC(UserModel::getId(), $data['EC']->getId());
?>
<section class="mytitle text-center bg-light">
  <div class="container">
    <h2 class="mb-5">Liste des épreuves de <?php echo $data['EC']->getCode(); ?></h2>
    <p class="lead mb-0">
        Ici, vous trouverez la liste de toutes les épreuves de l'EC.
    </p>
  </div>
</section>

<form id="controlForm" action="" method="post"></form>

<script>
var request = null;
function setEpreuve(id, url) {
    if(request == null) {
        request = $.ajax({
                type: 'POST',
                url: WEB_PATH + 'epreuves/ws.php',
                data: { 'mode' : 4, 'epreuve' : id },
                dataType: 'json'
            });
        request.done(function (response, textStatus, jqXHR) {
                document.location.href = WEB_PATH + url;
                request = null;
            });
        request.fail(function (jqXHR, textStatus, errorThrown){
                console.log(jqXHR);
                request = null;
            });   
    }
}
function bloque(epreuve, etat) {
    if(request == null) {
        request = $.ajax({
                type: 'POST',
                url: WEB_PATH + 'epreuves/ws.php',
                data: { 'mode' : 5, 'epreuve' : epreuve, 'etat' : etat },
                dataType: 'json'
            });
        request.done(function (response, textStatus, jqXHR) {
                if(response['code'] == 1) {
                    if(response['etat'] == 1) {
                        $('#blo_' + response['epreuve']).hide();
                        $('#deb_' + response['epreuve']).show();
                    }
                    else {
                        $('#blo_' + response['epreuve']).show();
                        $('#deb_' + response['epreuve']).hide();                        
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
  <form class="form-inline justify-content-between mt-2">
    <div class="form-group mb-2">
<?php
if($respDip) {
?>
      <a data-toggle='tooltip' data-placement='top' title='Créer une nouvelle épreuve' id="ajouterLink" class="btn btn-outline-primary mr-2" href="<?php echo WEB_PATH; ?>epreuves/ajouter.php">Créer une épreuve</a>
<?php
}
?>
    </div>
    <div class="form-group mb-2">
      <a data-toggle='tooltip' data-placement='top' title='Retour à la liste des ECs' class="btn btn-outline-primary mr-2" href="<?php echo WEB_PATH; ?>ecs/liste.php">Retour</a>  
    </div>
  </form>
</div>

<?php
if(!isset($data['epreuves']) || ($data['epreuves'] === null) || (sizeof($data['epreuves']) == 0)) {
?>
<div class="media alert-danger text-center">
  <div class="media-body">
    <p class="lead mb-0">Il n'y a pas d'épreuve pour cet EC dans la base actuellement.</p>
  </div>
</div>
<?php
}
else {
?>
<table class="table table-striped">
  <thead>
    <tr>
      <th scope="col">Intitulé</th>
      <th scope="col">Type</th>
      <th scope="col">Max</th>
      <th scope="col">Session 1</th>
      <th scope="col">Session 2</th>
      <th scope="col">Session 1 (disp)</th>
      <th scope="col">Session 2 (disp)</th>
      <th class="text-right" scope="col">Actions</th>
    </tr>
  </thead>
  <tbody>
<?php
$total = [1 => 0, 2 => 0, 3 => 0, 4 => 0];
foreach($data['epreuves'] as $epreuve) {
    if(($epreuve['bloquee'] != 1) || ($respDip)) {
        echo "<tr><td>".$epreuve['intitule']."</td>";
        echo "<td>".Epreuve::TYPE_DESCRIPTION[$epreuve['type']]."</td>";
        echo "<td>".$epreuve['max']."</td>";
        echo "<td>".$epreuve['session1']."%</td>";
        echo "<td>".$epreuve['session2']."%</td>";
        echo "<td>".$epreuve['session1disp']."%</td>";
        echo "<td>".$epreuve['session2disp']."%</td>";
        echo "<td class='text-right'>";

        $total[1] += $epreuve['session1'];
        $total[2] += $epreuve['session2'];
        $total[3] += $epreuve['session1disp'];
        $total[4] += $epreuve['session2disp'];
        
        // Activation/désactivation de la saisie de l'épreuve
        if($respEC || $respDip) {
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
        }
        
        // Cache/rend visible l'épreuve
        if($respEC || $respDip) {
            if($epreuve['visible'] == 1) {
              $tmp1 = "style='display: none;'";
              $tmp2 = "";
            }
            else {
              $tmp1 = "";
              $tmp2 = "style='display: none;'";
            }
            echo "<a id='vis_{$epreuve['id']}' $tmp1 class='btn btn-sm btn-outline-success mr-1' data-toggle='tooltip' data-placement='top' ".
               "title=\"Rend visible l'épreuve\" href='javascript:visible({$epreuve['id']},1)'><i class='icon-eye'></i></a>";
            echo "<a id='cac_{$epreuve['id']}' $tmp2 class='btn btn-sm btn-outline-danger mr-1' data-toggle='tooltip' data-placement='top' ".
               "title=\"Cache l'épreuve\" href='javascript:visible({$epreuve['id']},0)'><i class='icon-eye'></i></a>";
        }
        
        // Blocage de l'épreuve
        /*if($respDip) {
            if($epreuve['bloquee'] == 1) {
              $tmp1 = "style='display: none;'";
              $tmp2 = "";
            }
            else {
              $tmp1 = "";
              $tmp2 = "style='display: none;'";
            }
            echo "<a id='blo_{$epreuve['id']}' $tmp1 class='btn btn-sm btn-outline-success mr-1' data-toggle='tooltip' data-placement='top' ".
               "title=\"Bloque l'épreuve\" href='javascript:bloque({$epreuve['id']},1)'><i class='icon-lock'></i></a>";
            echo "<a id='deb_{$epreuve['id']}' $tmp2 class='btn btn-sm btn-outline-danger mr-1' data-toggle='tooltip' data-placement='top' ".
               "title=\"Débloque l'épreuve\" href='javascript:bloque({$epreuve['id']},0)'><i class='icon-lock'></i></a>";
        }*/
        
        // Saisie des notes
        echo <<<HTML
<a data-toggle='tooltip' data-placement='top' title='Saisie des notes'
       href="javascript:setEpreuve({$epreuve['id']}, 'notes/saisie.php')" class='btn btn-sm btn-outline-primary mr-1'>
        <i class='icon-book-open'></i>
      </a>
HTML;
        
        // Suppression/modification de l'épreuve
        if($respDip) {
            $lienSupp = WEB_PATH."epreuves/supprimer.php";
            $lienModi = WEB_PATH."epreuves/modifier.php";
            echo "<button name='idModi' type='submit' class='btn btn-sm btn-outline-warning mr-1' data-toggle='tooltip' data-placement='top' ".
               "title=\"Modifier l'épreuve\" form='controlForm' formaction='$lienModi' value='{$epreuve['id']}'><i class='icon-wrench'></i></button>";
            echo "<button name='idSupp' type='submit' class='btn btn-sm btn-outline-danger mr-1' data-toggle='tooltip' data-placement='top' ".
               "title=\"Supprimer l'épreuve\" form='controlForm' formaction='$lienSupp' value='{$epreuve['id']}'><i class='icon-trash'></i></button>";
        }
        echo "</td>";
        echo "</tr>";
    }
}
?>
  </tbody>
  <tr>
    <td></td>
    <td></td>
    <td></td>
<?php
for($i = 1; $i <= 4; $i++) {
    echo "<td>";
    if($total[$i] == 100)
        echo "<span class='badge badge-success'>{$total[$i]}%</span>";
    else
        echo "<span class='badge badge-danger'>{$total[$i]}%</span>";
    echo "</td>";
}
?>
    <td>
<?php
if(UserModel::estAdmin() && 
   (($total[1] != 100) || ($total[2] != 100)))
    echo "<span class='badge badge-danger'>Des erreurs sont présentes sur les répartitions</span>";
?>
    </td>
  </tr>
</table>
<?php
}