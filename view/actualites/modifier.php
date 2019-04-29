<?php
// @need actualite l'actualité en cours
?>
<section class="mytitle text-center bg-light">
  <div class="container">
    <h2 class="mb-5">Modifier une actualité</h2>
    <p class="lead mb-0">Modifier les données d'une actualité.</p>
  </div>
</section>
<div class="container">

<form action="<?php echo WEB_PATH; ?>actualites/modifier.php" method="post">
  <?php require("form.php"); ?>
  <button type="submit" name="btnModifier" class="btn btn-primary">Modifier</button>
  <button type="submit" name="btnAnnuler" class="btn btn-danger">Annuler</button>
</form>
</div>