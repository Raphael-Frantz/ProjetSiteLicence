<?php
// @need IPs : la liste des IPs
// @need etudiant: l'objet étudiant

WebPage::addOnlineScript("var WEB_PATH = '".WEB_PATH."';");
WebPage::addJSScript("public/js/active-tooltips.js");
WebPage::addJSScript("public/js/tools.js");
WebPage::addJSScript("public/js/ip.js");
?>

<section class="mytitle text-center bg-light">
  <div class="container">
    <h2 class="mb-5">Inscription(s) pédagogique(s)</h2>
    <p class="lead mb-0">Retrouvez ici les IPs de l'étudiant(e) <span class='badge badge-primary'><?php echo $data['etudiant']; ?></span>.</p>
  </div>
</section>

<div class="modal fade" id="modification" tabindex="-1" role="dialog" aria-labelledby="modificationModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modificationModalLabel">Modification</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <input type="hidden" id="currentEC" name="currentEC" value=""/>
        <input type="hidden" id="currentEtu" name="currentEtu" value=""/>
        <div class="form-group row">
          <label for="inputNom" class="col-sm-2 col-form-label">Etudiant</label>
          <div class="col-sm-10">
            <input class="form-control" id='inputNom' type="text" placeholder="Nom de l'étudiant" readonly/>
          </div>
        </div>
        <div class="form-group row">
          <label for="inputEC" class="col-sm-2 col-form-label">EC</label>
          <div class="col-sm-10">
            <input class="form-control" id='inputEC' type="text" placeholder="Nom de l'EC" readonly/>
          </div>
        </div>
        <div class="form-group row">
          <label for="inputInscription" class="col-sm-2 col-form-label">Inscription</label>
          <div class="col-sm-10">
            <select class="form-control" name="choixInscription" id="inputInscription" onchange="javascript:selectionInscription()">
              <option value="<?php echo InscriptionECModel::TYPE_NONINSCRIT; ?>">Non inscrit</option>
              <option value="<?php echo InscriptionECModel::TYPE_INSCRIT; ?>">Inscrit</option>
              <option value="<?php echo InscriptionECModel::TYPE_VALIDE; ?>">Validé</option>
            </select>
          </div>
        </div>
        <div class="form-group row" id="noteGroup">
          <label for="inputNote" class="col-sm-2 col-form-label">Note</label>
          <div class="col-sm-10">
            <input class="form-control" name="note" id="inputNote" type="text"/>
          </div>
        </div>
        <div class="form-group row" id="baremeGroup">
          <label for="inputBareme" class="col-sm-2 col-form-label">Barème</label>
          <div class="col-sm-10">
            <input class="form-control" name="bareme" id="inputBareme" type="text" value="20"/>
          </div>
        </div>        
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-success" onclick="modifierIPConf()" data-dismiss="modal">Modifier</button>
        <button type="button" class="btn btn-primary" data-dismiss="modal">Annuler</button>
      </div>
    </div>
  </div>
</div>

<section>
  <div class="container">
  
  <form class="form-inline justify-content-between mt-2 mb-2">
    <div>
<?php
if(!isset($data['erreur'])) {
?>
      <a data-toggle='tooltip' data-placement='top' title="Notes de l'étudiant(e)s" class="btn btn-outline-primary mr-2" href="<?php echo WEB_PATH; ?>etudiants/notes.php">Notes</a>
      <a data-toggle='tooltip' data-placement='top' title="Présentiel de l'étudiant(e)s" class="btn btn-outline-primary mr-2" href="<?php echo WEB_PATH; ?>etudiants/presentiel.php">Présentiel</a>
      <a data-toggle='tooltip' data-placement='top' title="Groupes de l'étudiant(e)s" class="btn btn-outline-primary mr-2" href="<?php echo WEB_PATH; ?>etudiants/groupes.php">Groupes</a>
<?php
}
?>
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
  
<?php 
if(isset($data['erreur'])) {
    echo <<<HTML
<div class="media alert-danger text-center">
  <div class="media-body">
    <p class="lead mb-0">{$data['erreur']}</p>
  </div>
</div>
HTML;
}
else {
    $i = 0;
    while($i < count($data['IPs'])) {
        echo "<h3 class='mb-2'>{$data['IPs'][$i]['diplome']}</h3>";
        echo <<<HTML
<table class="table table-striped table-bordered">
  <thead>
    <tr>
      <th scope="col">Semestre</th>
      <th scope="col">EC</th>
      <th scope="col">IP</th>
    </tr>
  </thead>
  <tbody>
HTML;

        $j = $i;
        while(($j < count($data['IPs'])) && ($data['IPs'][$j]['diplome'] == $data['IPs'][$i]['diplome'])) {
            // Recherche du prochain indice pour changer de diplôme/semestre
            $k = $j;
            while(($k < count($data['IPs'])) && 
                  ($data['IPs'][$k]['diplome'] == $data['IPs'][$j]['diplome']) &&
                  ($data['IPs'][$k]['semestre'] == $data['IPs'][$j]['semestre']))
                $k++;
                  
            // Affichage de toutes les lignes du semestre
            $first = true;
            do {
                // Affichage du semestre et de l'EC
                if($first) {
                    $first = false;
                    $tmpSem = $data['IPs'][$j]['semestre'] + $data['IPs'][$j]['minSemestre'] - 1;
                    echo "<tr><td rowspan='".($k - $j)."'>Semestre {$tmpSem}</td>";
                    echo "<td>{$data['IPs'][$j]['code']} - {$data['IPs'][$j]['intitule']}</td>";
                }
                else
                    echo "<tr><td>{$data['IPs'][$j]['code']} - {$data['IPs'][$j]['intitule']}</td>";            
            
                // Si admin et/ou responsable diplôme : on peut cliquer sur la case
                if(UserModel::estAdmin() || UserModel::estRespDiplome($data['IPs'][$i]['idDiplome'])) {
                    echo <<<HTML
<td id='{$data['etudiant']->getIdUtilisateur()}_{$data['IPs'][$j]['idEC']}' 
    onclick='javascript:modifierIP({$data['etudiant']->getIdUtilisateur()}, "{$data['etudiant']->__toString()}", 
                                 {$data['IPs'][$j]['idEC']}, "{$data['IPs'][$j]['code']}")'>
HTML;
                }
                else
                    echo "<td>";
            
                // Affichage de l'inscription
                switch($data['IPs'][$j]['type']) {
                    case InscriptionECModel::TYPE_INSCRIT:
                        echo "X";
                        break;
                    case InscriptionECModel::TYPE_VALIDE:
                        echo $data['IPs'][$j]['note']."/".$data['IPs'][$j]['bareme'];
                        break;
                }
                echo "</td></tr>";
                $j++;
            } while($j < $k);
        }
        $i = $j;
        echo "</table>";
    }
}
?>
  </div>
</section>