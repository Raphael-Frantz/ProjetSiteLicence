<?php
// @need 
?>
<section class="mytitle text-center bg-light">
  <div class="container">
    <h2 class="mb-5">Ajouter un justificatif d'absence</h2>
    <p class="lead mb-0">Saisissez les informations concernant le justificatif d'absence.</p>
  </div>
</section>

<section class="mysection mb-2">
  <div class="container">
    <h2 class="mb-4">Informations générales</h2>
    <form action="<?php echo WEB_PATH; ?>presentiel/ajouter.php" method="post">
<?php require("form.php"); ?>
      <button type="submit" name="btnAjouter" class="btn btn-primary">Créer</button>
      <button type="submit" name="btnAnnuler" class="btn btn-danger">Annuler</button>
    </form>
  </div>
</section>

<?php WebPage::addOnReady("selectType();"); ?>