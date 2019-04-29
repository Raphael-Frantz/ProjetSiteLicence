<?php
// @need actualite l'actualité à supprimer
?>
<section class="mytitle text-center bg-light">
  <div class="container">
    <h2 class="mb-5">Suppression d'une actualité</h2>
    <p class="lead mb-0">Vous êtes sur le point de supprimer l'actualité <span class="badge lead"><?php echo $data['actualite']; ?></span>.</p>
  </div>
</section>

<section class="text-center bg-light">
  <div class="container">
    <form action="<?php echo WEB_PATH; ?>actualites/supprimer.php" method="post">
      <button type="submit" name="btnSupprimer" value="<?php echo $data['actualite']->getId(); ?>" class="btn btn-primary">Supprimer</button>
      <button type="submit" name="btnAnnuler" class="btn btn-danger">Annuler</button>
    </form>
  </div>
</section>