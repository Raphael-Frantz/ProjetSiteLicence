<?php
// @optional actualite l'actualité à ajouter
?>
<section class="mytitle text-center bg-light">
  <div class="container">
    <h2 class="mb-5">Ajouter une actualité</h2>
    <p class="lead mb-0">Lorsque vous ajoutez une nouvelle actualité, celle-ci est affichée au début.</p>
  </div>
</section>

<div class="container">
    <form action="<?php echo WEB_PATH; ?>actualites/ajouter.php" method="post">
      <?php require("form.php"); ?>
      <button type="submit" name="btnAjouter" class="btn btn-primary">Ajouter</button>
      <button type="submit" name="btnAnnuler" class="btn btn-danger">Annuler</button>
    </form>
</div>