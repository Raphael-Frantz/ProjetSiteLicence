<?php
// @need epreuve l'épreuve à modifier
?>

<?php WebPage::addOnlineScript(<<< JS

function toggleSection() {

    $('#form-general').toggle();
    $('#form-examens').toggle();

    $('#toggleButtonGoIn').toggle();
    $('#toggleButtonGoBack').toggle();
}

$('#toggleButtonGoBack').hide();
$('#form-examens').hide();

JS
); ?>

<section class="mytitle text-center bg-light">
  <div class="container">
    <h2 class="mb-5">Modifier une épreuve</h2>
    <p class="lead mb-0">Modifier les données d'une épreuve de l'EC <span class="badge badge-primary lead"><?php echo $data['EC']; ?></span>.</p>
  </div>
</section>
<div class="container">

<section class="mysection mb-2">
  <div class="container">
    <h2 class="mb-4">Informations générales</h2>
    <form action="<?php echo WEB_PATH; ?>epreuves/modifier.php" method="post">

      <!-- Dans un <div> pour le cacher si on accède à la planification des examens -->

      <div id="form-general">
        <?php require("form.php"); ?>
      </div>

      <div id="form-examens">
        <?php require("form-examens.php"); ?>
      </div>

      <button type="submit" name="btnModifier" class="btn btn-primary">Modifier</button>
      <button type="submit" name="btnAnnuler" class="btn btn-danger">Annuler</button>

      <!-- Accéder à la planification des examens -->

      <a id="toggleButton" href="javascript:toggleSection()" class="btn btn-primary float-right">
        <span id="toggleButtonGoIn">
            <span class="d-none d-sm-block">Planifier les examens</span>
            <span class="d-sm-none">Planification</span>
        </span>
        <span id="toggleButtonGoBack" >Retour</span>
      </a>

    </form>
  </div>
</section>