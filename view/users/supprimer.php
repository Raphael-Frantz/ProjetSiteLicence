<?php
// @need diplome le diplôme à supprimer
?>
<section class="mytitle text-center bg-light">
  <div class="container">
    <h2 class="mb-5">Suppression d'un enseignant</h2>
    <p class="lead mb-0">Vous êtes sur le point de supprimer l'enseignant <span class="badge lead"><?php echo $data['user']; ?></span>.</p>
  </div>
</section>

<section class="text-center bg-light">
  <div class="container">
    <form action="<?php echo WEB_PATH; ?>users/supprimer.php" method="post">
      <button type="submit" name="btnSupprimer" value="<?php echo $data['user']->getId(); ?>" class="btn btn-danger">Supprimer</button>
      <button type="submit" name="btnAnnuler" class="btn btn-primary">Annuler</button>
    </form>
  </div>
</section>