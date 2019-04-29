<?php
// @need diplome le diplôme à supprimer
?>
<section class="mytitle text-center bg-light">
  <div class="container">
    <h2 class="mb-5">Suppression d'un justificatif</h2>
    <p class="lead mb-0">
      Vous êtes sur le point de supprimer le justificatif de l'étudiant 
      <span class="badge lead"><?php echo $data['etudiant']; ?></span> 
      correspondant à la période 
<?php
$heure = (date('G', $data['justificatif']->getDateDebut()) != 0);
if($data['justificatif']->getDateDebut() != $data['justificatif']->getDateFin()) {
    echo "du ".DateTools::timestamp2Date($data['justificatif']->getDateDebut(), $heure).
         " au ".DateTools::timestamp2Date($data['justificatif']->getDateFin(), $heure);
}
else
    echo "du ".DateTools::timestamp2Date($data['justificatif']->getDateDebut(), $heure);
?>. Le motif était : <?php echo $data['justificatif']->getMotif(); ?>.
    </p>
  </div>
</section>

<section class="text-center bg-light">
  <div class="container">
    <form action="<?php echo WEB_PATH; ?>presentiel/supprimer.php" method="post">
      <button type="submit" name="btnSupprimer" class="btn btn-danger">Supprimer</button>
      <button type="submit" name="btnAnnuler" class="btn btn-primary">Annuler</button>
    </form>
  </div>
</section>