<?php
// @need groupe le groupe à modifier
?>
<section class="mytitle text-center bg-light">
  <div class="container">
    <h2 class="mb-5">Modifier un groupe</h2>
    <p class="lead mb-0">Modifier les données d'un groupe.</p>
  </div>
</section>

<section class="mysection mb-2">
  <div class="container">
    <h2 class="mb-4">Informations générales</h2>
    <form action="<?php echo WEB_PATH; ?>groupes/modifier.php" method="post">
      <?php require("form.php"); ?>
      <button type="submit" name="btnModifier" class="btn btn-primary">Modifier</button>
      <button type="submit" name="btnAnnuler" class="btn btn-danger">Annuler</button>
    </form>
  </div>
</section>