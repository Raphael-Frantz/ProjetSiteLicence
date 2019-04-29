<?php
// @need 

WebPage::addOnlineScript("var WEB_PATH = '".WEB_PATH."';");
WebPage::addJSScript("public/js/tools.js");
WebPage::addJSScript("public/js/etudiants.js");
?>
<section class="mytitle text-center bg-light">
  <div class="container">
    <h2 class="mb-5">Gestion des étudiants</h2>
    <p class="lead mb-0">
      Ici, vous trouverez la liste de tous les étudiants créés sur le site.
      Vous pouvez en créer, les importer depuis un fichier CSV ou les modifier.
      Vous pouvez également les inscrire ou les desincrire des diplômes (par semestre).
    </p>
  </div>
</section>

<div class="modal fade" id="importation" tabindex="-1" role="dialog" aria-labelledby="importationModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="importationModalLabel">Importation</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <form id="importForm" method="post" action="<?php echo WEB_PATH; ?>etudiants/importer.php" enctype="multipart/form-data">
          <div class="form-group row">
            <label for="inputFichier" class="col-sm-2 col-form-label">Fichier</label>
            <div class="col-sm-10">
              <div class="form-group">
                <input type="file" class="form-control-file" name="inputFichier" id="inputFichier" lang="fr">
              </div>
              <small class="form-text text-muted">Sélectionnez un fichier CSV avec le bon format (celui fourni sur le bureau virtuel)</small>
            </div>
          </div>           
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-success" onclick="confImporter()" data-dismiss="modal">Importer</button>
        <button type="button" class="btn btn-primary" data-dismiss="modal">Annuler</button>
      </div>
    </div>
  </div>
</div>

<form id="controlForm" action="" method="post"></form>

<form class="form-inline justify-content-center mt-2">
  <div class="form-group mb-2">
    <select class="form-control mr-2" id="diplome" name="diplome" onchange="javascript:selectionDiplome(false)">
        <option value="-1">Tous les diplômes</option>
<?php
foreach($data['diplomes'] as $diplome) {
    echo "<option value=\"{$diplome['id']}\"";
    if(isset($data['diplome']) && ($data['diplome']->getId() == $diplome['id']))
        echo " selected=\"selected\"";
    echo ">{$diplome['intitule']}</option>";
}
?>
    </select>
    <select class="form-control mr-2" id="semestre" name="semestre" onchange="javascript:selectionSemestre()">
<?php
if(isset($data['nbSemestres'])) {
    echo "<option value='-1'>Tous les semestres</option>";
    for($i = 1; $i <= $data['nbSemestres']; $i++) {
        echo "<option value='$i'";
        if($data['semestre'] == $i)
            echo " selected=\"selected\"";
        echo ">Semestre ".($i + $data['minSemestre'] - 1)."</option>";
    }
}
?>
    </select>
     <a id="ajouterLink" class="btn btn-outline-primary mr-2" href="ajouter.php">Créer un étudiant</a>
     <a id="ajouterLink" class="btn btn-outline-primary mr-2" href="javascript:importer()">Importer</a>
     <a id="inscrireLink" class="btn btn-outline-primary mr-2" style="display: none;" href="<?php echo WEB_PATH; ?>etudiants/inscrire.php">Inscrire</a>
  </div>
</form>

<div id="contenu"></div>

<?php WebPage::addOnReady("selectionDiplome(true);"); ?>