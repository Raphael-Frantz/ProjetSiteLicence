<?php
// @need groupe le groupe à supprimer
?>
<section class="mytitle text-center bg-light">
  <div class="container">
    <h2 class="mb-5">Suppression d'un groupe de <?php echo $data['EC']; ?></h2>
    <p class="lead mb-0">Vous êtes sur le point de supprimer le groupe <span class="badge lead"><?php echo $data['groupe']; ?></span> de l'EC <span class="badge"><?php echo $data['EC']; ?></span>.</p>
  </div>
</section>

<section class="text-center bg-light">
  <div class="container">
    <form action="<?php echo WEB_PATH; ?>groupesec/supprimer.php" method="post">
      <button type="submit" name="btnSupprimer" class="btn btn-danger">Supprimer</button>
      <button type="submit" name="btnAnnuler" class="btn btn-primary">Annuler</button>
    </form>
  </div>
</section>