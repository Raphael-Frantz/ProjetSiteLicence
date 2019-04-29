<?php
WebPage::addOnlineScript("var WEB_PATH = '".WEB_PATH."';");
WebPage::addJSScript("public/js/active-tooltips.js");
WebPage::addJSScript("public/js/tools.js");
?>
<section class="mytitle text-center bg-light">
  <div class="container">  
    <h2 class="mb-5">Groupes</h2>
    <p class="lead mb-0">Retrouvez ici des groupes dans le diplôme et dans chaque EC de l'étudiant(e) <span class='badge badge-primary'><?php echo $data['etudiant']; ?></span>.</p>
  </div>
</section>

<div class="modal fade" id="groupeModal" tabindex="-1" role="dialog" aria-labelledby="groupeModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="groupeModalLabel"></h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body" id="groupeModalBody">
        <div id='inputGroupeDiv' class="form-group row">
          <label for="inputGroupe" class="col-sm-4 col-form-label">Groupe</label>
          <div class="col-sm-8">
            <select class="form-control" id="inputGroupe" name="inputGroupe">
            <?php /* Liste des groupes du diplôme/semestre */ ?>
            </select>
          </div>
        </div>
        <div id='nogroupe' class="alert alert-danger lead text-center mt-2" role="alert">
        </div>
      </div>
      <div class="modal-footer">
        <button id='btn_modifier' type="button" class="btn btn-success" onclick="confModifier()" data-dismiss="modal">Modifier</button>
        <button type="button" class="btn btn-primary" data-dismiss="modal">Annuler</button>
      </div>
    </div>
  </div>
</div>

<script>
var request = null;
var diplome = -1;
var semestre = -1;
var type = -1;
var etudiant = -1;
var currentEC = -1;
function confModifier() {
    if(request == null) {
        var URL = '';
        if(semestre != -1) {
            URL = WEB_PATH + 'groupes/ws.php';
            data = { 'mode' : 3, 
                     'diplome' : diplome,
                     'semestre' : semestre,
                     'groupe' : $('#inputGroupe').val(),
                     'type' : type,
                     'etudiant' : etudiant };
        }
        else {
            URL = WEB_PATH + 'groupesec/ws.php'
            data = { 'mode' : 7, 
                     'EC' : currentEC,
                     'groupeEC' : $('#inputGroupe').val(),
                     'type' : type,
                     'etudiant' : etudiant };
        }
        request = $.ajax({
                type: 'POST',
                url: URL,
                data: data,
                dataType: 'json'
            });
        request.done(function (response, textStatus, jqXHR) {
                if(response['code'] == 1) {
                    if(semestre != -1)
                        $('#' + diplome + "_" + semestre + "_" + type).empty().append(response['typeStr'] + " : " + $('#inputGroupe option:selected').text());
                    else
                        $('#' + diplome + "_" + currentEC + "_" + type).empty().append(response['typeStr'] + " : " + $('#inputGroupe option:selected').text());
                }
                else
                    displayErrorModal(response['erreur']);
                request = null;
            });
        request.fail(function (jqXHR, textStatus, errorThrown){
                console.log(jqXHR);
                request = null;
            });
    }
}
function modifierGrp(etu, dip, sem, intType, typeN) {
    if(request == null) {
        etudiant = etu;
        diplome = dip;
        semestre = sem;
        type = typeN;

        request = $.ajax({
                type: 'POST',
                url: WEB_PATH + 'groupes/ws.php',
                data: { 'mode' : 1, 'json' : true, 'diplome' : dip, 'semestre' : sem, 'type' : typeN },
                dataType: 'json'
            });
        request.done(function (response, textStatus, jqXHR) {
                if(response['code'] == 1) {
                    $('#inputGroupe').empty();
                    if(response['groupes'].length == 0) {
                        $('#inputGroupeDiv').hide();
                        $('#nogroupe').empty().append("Il n'y a aucun groupe de " + intType + " dans le diplôme.");
                        $('#nogroupe').show();
                        $('#btn_modifier').hide();
                    }
                    else {
                        $('#btn_modifier').show();
                        $('#inputGroupeDiv').show();
                        $('#nogroupe').hide();
                        $('#inputGroupe').append("<option value='-1' checked>Aucun</option>");
                        $.each(response['groupes'], function(ind, val) {
                            $('#inputGroupe').append("<option value='" + ind + "'>" + val + "</option>"); 
                        });
                    }
                    
                    $('#groupeModalLabel').empty().append("Changer de groupe de " + intType);
                    $('#groupeModal').modal("show");
                }
                else
                    displayErrorModal(response['erreur']);
                request = null;
            });
        request.fail(function (jqXHR, textStatus, errorThrown){
                console.log(jqXHR);
                request = null;
            });
    }
}
function modifierGrpEC(etu, dip, ec, intType, typeN) {
    if(request == null) {
        etudiant = etu;
        diplome = dip;
        semestre = -1;
        currentEC = ec;
        type = typeN;
        
        request = $.ajax({
                type: 'POST',
                url: WEB_PATH + 'groupesec/ws.php',
                data: { 'mode' : 1, 'json' : true, 'EC' : ec },
                dataType: 'json'
            });
        request.done(function (response, textStatus, jqXHR) {
                if(response['code'] == 1) {
                    $('#inputGroupe').empty();
                    if(response['groupes'].length == 0) {
                        $('#inputGroupeDiv').hide();
                        $('#nogroupe').empty().append("Il n'y a aucun groupe de " + intType + " dans l'EC.");
                        $('#nogroupe').show();
                        $('#btn_modifier').hide();
                    }
                    else {
                        $('#inputGroupeDiv').show();
                        $('#nogroupe').hide();
                        $('#btn_modifier').show();
                        $('#inputGroupe').append("<option value='-1' checked>Aucun</option>");
                        $.each(response['groupes'], function(ind, val) {
                            if(val['type'] == type)
                                $('#inputGroupe').append("<option value='" + val['id'] + "'>" + val['intitule'] + "</option>"); 
                        });
                    }
                    $('#groupeModalLabel').empty().append("Changer de groupe de " + intType);
                    $('#groupeModal').modal("show");
                }
                else
                    displayErrorModal(response['erreur']);
                request = null;
            });
        request.fail(function (jqXHR, textStatus, errorThrown){
                console.log(jqXHR);
                request = null;
            });
    }
}
</script>

<section>
  <div class="container">
  
  <form class="form-inline justify-content-between mt-2 mb-2">
    <div>
      <a data-toggle='tooltip' data-placement='top' title="IP de l'étudiant(e)s" class="btn btn-outline-primary mr-2" href="<?php echo WEB_PATH; ?>etudiants/ip.php">IP</a>
      <a data-toggle='tooltip' data-placement='top' title="Notes de l'étudiant(e)s" class="btn btn-outline-primary mr-2" href="<?php echo WEB_PATH; ?>etudiants/notes.php">Notes</a>
      <a data-toggle='tooltip' data-placement='top' title="Présentiel de l'étudiant(e)s" class="btn btn-outline-primary mr-2" href="<?php echo WEB_PATH; ?>etudiants/presentiel.php">Présentiel</a>
    </div>
<?php
if(isset($data['back']) && ($data['back'] != "")) {
    $path = WEB_PATH.$data['back'];
    echo <<<HTML
    <div>
      <a data-toggle='tooltip' data-placement='top' title="Retour" class="btn btn-outline-primary mr-2" href="$path">Retour</a>
    </div>
HTML;
}
?>    
  </form>  
  
    <h3 class="mb-4">Groupes dans les diplômes</h3>
    
<?php
if(count($data['groupes']) == 0) {
    echo <<<HTML
    <div class='card mb-4 border-primary box-shadow'>
      <div class='card-body'>
        <p class='lead mb-0'>L'étudiant(e) n'est inscrit dans aucun diplôme.</p>
      </div>
    </div>
HTML;
}
else {
   echo <<<HTML
    <p class="lead mb-2">
        Voici la liste des groupes dans lesquels l'étudiant(e) est inscrit(e) :
    </p>   
<table class="table table-striped table-bordered" id="tableGroupes">
HTML;
    $diplome = "";
    $semestre = "";
    $tmp = [];
    foreach($data['groupes'] as $groupe) {
        if($groupe['intituleCM'] === null) $groupe['intituleCM'] = "Aucun";
        if($groupe['intituleTD'] === null) $groupe['intituleTD'] = "Aucun";
        if($groupe['intituleTP'] === null) $groupe['intituleTP'] = "Aucun";
        
        $tmpSem = $groupe['semestre'] + $groupe['minSemestre'] - 1;
        echo <<<HTML
  <thead>
    <tr>
      <th scope="col" colspan="3" class="text-center table-primary">{$groupe['diplome']} - Semestre {$tmpSem}</th>
    </tr>
  </thead>
  <tr>
    <td>
      <div class="form-inline justify-content-between">
        <span id='{$groupe['idDiplome']}_{$groupe['semestre']}_1'>CM : {$groupe['intituleCM']}</span>
        <span>
HTML;
        if(UserModel::estAdmin() || UserModel::estRespDiplome($groupe['idDiplome']))
            echo <<<HTML
          <a class='btn btn-sm btn-outline-primary mr-2' data-toggle='tooltip' data-placement='top' title="Modifier le groupe"
             href='javascript:modifierGrp({$data['etudiant']->getIdUtilisateur()}, {$groupe['idDiplome']}, {$groupe['semestre']}, "CM", 1)'>
            <i class='icon-wrench'></i>
          </a>
HTML;
        echo <<<HTML
        </span>
      </div> 
    </td>
    <td>
      <div class="form-inline justify-content-between">
        <span id='{$groupe['idDiplome']}_{$groupe['semestre']}_2'>TD : {$groupe['intituleTD']}</span>
        <span>
HTML;
        if(UserModel::estAdmin() || UserModel::estRespDiplome($groupe['idDiplome']))
            echo <<<HTML
          <a class='btn btn-sm btn-outline-primary mr-2' data-toggle='tooltip' data-placement='top' title="Modifier le groupe"
             href='javascript:modifierGrp({$data['etudiant']->getIdUtilisateur()}, {$groupe['idDiplome']}, {$groupe['semestre']}, "TD", 2)'>
            <i class='icon-wrench'></i>
          </a>
HTML;
        echo <<<HTML
        </span>
      </div> 
    </td>
    <td>
      <div class="form-inline justify-content-between">
        <span id='{$groupe['idDiplome']}_{$groupe['semestre']}_3'>TP : {$groupe['intituleTP']}</span>
        <span>
HTML;
        if(UserModel::estAdmin() || UserModel::estRespDiplome($groupe['idDiplome']))
            echo <<<HTML
          <a class='btn btn-sm btn-outline-primary mr-2' data-toggle='tooltip' data-placement='top' title="Modifier le groupe"
             href='javascript:modifierGrp({$data['etudiant']->getIdUtilisateur()}, {$groupe['idDiplome']}, {$groupe['semestre']}, "TP", 3)'>
            <i class='icon-wrench'></i>
          </a>
HTML;
        echo <<<HTML
        </span>
      </div> 
    </td>
  </tr>  
HTML;
    }
    echo "</table>";
}
?>
  </div>
</section>

<section class="bg-light">
  <div class="container">
    <h3 class="mb-4">Groupes dans les EC</h3>
    
<?php
if(count($data['groupesEC']) == 0) {
    echo <<<HTML
    <div class='card mb-4 border-primary box-shadow'>
      <div class='card-body'>
        <p class='lead mb-0'>L'étudiant(e) est inscrit(e) dans aucun EC.</p>
      </div>
    </div>
HTML;
}
else {    
    echo <<<HTML
    <p class="lead mb-2">
        Voici la liste des groupes d'EC dans lesquels l'étudiant(e) est inscrit(e)s :
    </p>   
<table class="table table-striped table-bordered mb-0" id="tableGroupes">
HTML;

    $i = 0;
    while($i < count($data['groupesEC'])) {
        echo <<<HTML
  <thead>
    <tr>
      <th scope="col" colspan="3" class="text-center table-primary">{$data['groupesEC'][$i]['diplome']}</th>
    </tr>
  </thead>
HTML;
        $s = $i;
        while(($s < count($data['groupesEC'])) &&
              ($data['groupesEC'][$s]['diplome'] == $data['groupesEC'][$i]['diplome'])) {
            $tmpSem = $data['groupesEC'][$s]['semestre'] + $data['groupesEC'][$s]['minSemestre'] - 1;
            echo <<<HTML
    <tr class="table-secondary text-center">
      <th scope="col" colspan="3">Semestre {$tmpSem}</th>
    </tr>
  </thead>
HTML;
            $j = $s;
            while(($j < count($data['groupesEC'])) && 
                  ($data['groupesEC'][$j]['semestre'] == $data['groupesEC'][$s]['semestre']) &&
                  ($data['groupesEC'][$j]['diplome'] == $data['groupesEC'][$s]['diplome'])) {
                if($data['groupesEC'][$j]['intituleCM'] === null) $data['groupesEC'][$j]['intituleCM'] = "Aucun";
                if($data['groupesEC'][$j]['intituleTD'] === null) $data['groupesEC'][$j]['intituleTD'] = "Aucun";
                if($data['groupesEC'][$j]['intituleTP'] === null) $data['groupesEC'][$j]['intituleTP'] = "Aucun";
                echo <<<HTML
<tr class="table-success text-center">
  <th scope='col' colspan='3'>
    ({$data['groupesEC'][$j]['code']}) {$data['groupesEC'][$j]['intitule']}
  </td>
</tr>
<tr>
  <td>
    <div class="form-inline justify-content-between">
      <span id='{$data['groupesEC'][$j]['idDiplome']}_{$data['groupesEC'][$j]['idEC']}_1'>CM : {$data['groupesEC'][$j]['intituleCM']}</span>
      <span>
HTML;
      if(UserModel::estAdmin() || UserModel::estRespDiplome($groupe['idDiplome']))
          echo <<<HTML
        <a class='btn btn-sm btn-outline-primary mr-2' data-toggle='tooltip' data-placement='top' title="Modifier le groupe"
           href='javascript:modifierGrpEC({$data['etudiant']->getIdUtilisateur()}, {$data['groupesEC'][$j]['idDiplome']}, {$data['groupesEC'][$j]['idEC']}, "CM", 1)'>
          <i class='icon-wrench'></i>
        </a>
HTML;
      echo <<<HTML
      </span>
    </div> 
  </td>
  <td>
    <div class="form-inline justify-content-between">
      <span id='{$data['groupesEC'][$j]['idDiplome']}_{$data['groupesEC'][$j]['idEC']}_2'>TD : {$data['groupesEC'][$j]['intituleTD']}</span>
      <span>
HTML;
      if(UserModel::estAdmin() || UserModel::estRespDiplome($groupe['idDiplome']))
          echo <<<HTML
        <a class='btn btn-sm btn-outline-primary mr-2' data-toggle='tooltip' data-placement='top' title="Modifier le groupe"
           href='javascript:modifierGrpEC({$data['etudiant']->getIdUtilisateur()}, {$data['groupesEC'][$j]['idDiplome']}, {$data['groupesEC'][$j]['idEC']}, "TD", 2)'>
          <i class='icon-wrench'></i>
        </a>
HTML;
      echo <<<HTML
      </span>
    </div> 
  </td>
  <td>
    <div class="form-inline justify-content-between">
      <span id='{$data['groupesEC'][$j]['idDiplome']}_{$data['groupesEC'][$j]['idEC']}_3'>TP : {$data['groupesEC'][$j]['intituleTP']}</span>
      <span>
HTML;
      if(UserModel::estAdmin() || UserModel::estRespDiplome($groupe['idDiplome']))
          echo <<<HTML
        <a class='btn btn-sm btn-outline-primary mr-2' data-toggle='tooltip' data-placement='top' title="Modifier le groupe"
           href='javascript:modifierGrpEC({$data['etudiant']->getIdUtilisateur()}, {$data['groupesEC'][$j]['idDiplome']}, {$data['groupesEC'][$j]['idEC']}, "TP", 3)'>
          <i class='icon-wrench'></i>
        </a>
HTML;
      echo <<<HTML
      </span>
    </div> 
  </td>
</tr>
HTML;
                $j++;
            }
            $s = $j;
        }
        $i = $s;
    }
    echo "</table>";
}
?>
  </div>
</section>