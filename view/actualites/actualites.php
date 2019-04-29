<?php
WebPage::addOnlineScript("var WEB_PATH = '".WEB_PATH."';");
WebPage::addJSScript("public/js/actualites.js");
?>
<section class="mytitle text-center bg-light">
  <div class="container">
    <h2 class="mb-5">Actualités</h2>
    <p class="lead mb-0">Retrouvez ici les dernières actualités concernant la Licence Informatique.</p>
  </div>
</section>

<form id="supprimerForm" action="<?php echo WEB_PATH; ?>actualites/supprimer.php" method="post">
  <input type="hidden" id="idSupp" name="idSupp" value="-1">
</form>

<form id="modifierForm" action="<?php echo WEB_PATH; ?>actualites/modifier.php" method="post">
  <input type="hidden" id="idModi" name="idModi" value="-1">
</form>

<div class="container">
  <div class="btn-group btn-group-toggle" style="padding-top: 10px; padding-bottom: 10px" data-toggle="buttons">
    <label class="btn btn-secondary active">
      <input type="radio" name="liste" id="all" autocomplete="off" checked onchange="javascript:changeMode(1, -1);"> Dernières
    </label>
    <label class="btn btn-secondary">
      <input type="radio" name="liste" id="L1" autocomplete="off" onchange="javascript:changeMode(0, -1);"> Toutes
    </label>
  </div>

  <div class="btn-group btn-group-toggle" style="padding-top: 10px; padding-bottom: 10px" data-toggle="buttons">
    <label class="btn btn-secondary active">
      <input type="radio" name="annee" id="all" autocomplete="off" checked onchange="javascript:changeMode(-1, 0);"> Toutes
    </label>
    <label class="btn btn-secondary">
      <input type="radio" name="annee" id="L1" autocomplete="off" onchange="javascript:changeMode(-1, 1);"> L1
    </label>
    <label class="btn btn-secondary">
      <input type="radio" name="annee" id="L2" autocomplete="off" onchange="javascript:changeMode(-1, 2);"> L2
    </label>
    <label class="btn btn-secondary">
      <input type="radio" name="annee" id="L3" autocomplete="off" onchange="javascript:changeMode(-1, 3);"> L3
    </label>
    <label class="btn btn-secondary">
      <input type="radio" name="annee" id="L3" autocomplete="off" onchange="javascript:changeMode(-1, 4);"> L3P
    </label>
  </div>
</div>

<div class="container" id="cartes">
</div>

<?php WebPage::addOnReady("changeMode(-1, -1);"); ?>