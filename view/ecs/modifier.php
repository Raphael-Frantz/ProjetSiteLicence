<?php
// @need EC l'EC en cours
// @need resp la liste des responsables de l'EC
?>
<section class="mytitle text-center bg-light">
  <div class="container">
    <h2 class="mb-5">Modifier un EC</h2>
    <p class="lead mb-0">Modifier les données d'un EC.</p>
  </div>
</section>

<section class="mysection mb-2">
  <div class="container">
    <h2 class="mb-4">Informations générales</h2>
    <form action="<?php echo WEB_PATH; ?>ecs/modifier.php" method="post">
      <?php require("form.php"); ?>
      <button type="submit" name="btnModifier" class="btn btn-primary">Modifier</button>
      <button type="submit" name="btnAnnuler" class="btn btn-danger">Annuler</button>
    </form>
  </div>
</section>