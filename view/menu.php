<?php
// @need nothing
?>
<nav class="navbar navbar-expand-lg navbar-light bg-light">
  <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
    <span class="navbar-toggler-icon"></span>
  </button>
  <div class="collapse navbar-collapse justify-content-md-center" id="navbarNav">
    <ul class="navbar-nav">
      <li class="nav-item <?php if(WebPage::getTitle() == "Accueil") echo "active"; ?>">
        <a class="nav-link" href="<?php echo WEB_PATH; ?>accueil/index.php">Accueil</a>
      </li>          
<?php
if(UserModel::isConnected()) {
    if(UserModel::estEnseignant()) {
        if(UserModel::estRespDiplome()) {
?>
      <li class="nav-item dropdown">
        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownMenuLinkGestion" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
          Gestion
        </a>
        <div class="dropdown-menu" aria-labelledby="navbarDropdownMenuLinkGestion">
<?php
            if(UserModel::estAdmin()) {
?>
          <a class="dropdown-item <?php if(WebPage::getTitle() == "Gestion des enseignants") echo "active"; ?>" href="<?php echo WEB_PATH; ?>users/index.php">Gestion des enseignants</a>
<?php
            }
?>
          <a class="dropdown-item <?php if(WebPage::getTitle() == "Gestion des diplômes") echo "active"; ?>" href="<?php echo WEB_PATH; ?>diplomes/index.php">Gestion des diplômes</a>
          <a class="dropdown-item <?php if(WebPage::getTitle() == "Gestion des ECs") echo "active"; ?>" href="<?php echo WEB_PATH; ?>ecs/index.php">Gestion des ECs</a>
          <a class="dropdown-item <?php if(WebPage::getTitle() == "Gestion des étudiants") echo "active"; ?>" href="<?php echo WEB_PATH; ?>etudiants/index.php">Gestion des étudiants</a>
          <a class="dropdown-item <?php if(WebPage::getTitle() == "Gestion des groupes") echo "active"; ?>" href="<?php echo WEB_PATH; ?>groupes/index.php">Gestion des groupes</a>
          <a class="dropdown-item <?php if(WebPage::getTitle() == "Attribution des groupes") echo "active"; ?>" href="<?php echo WEB_PATH; ?>groupes/attribution.php">Attribution des groupes</a>
          <a class="dropdown-item <?php if(WebPage::getTitle() == "Gestion des tuteurs") echo "active"; ?>" href="<?php echo WEB_PATH; ?>tutorat/tuteurs.php">Gestion des tuteurs</a>
          <a class="dropdown-item <?php if(WebPage::getTitle() == "Attribution des tuteurs") echo "active"; ?>" href="<?php echo WEB_PATH; ?>tutorat/attribution.php">Attribution des tuteurs</a>
        </div>
      </li>
<?php /*
      <li class="nav-item dropdown">
        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownMenuLinkDiplome" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
          Mes diplômes
        </a>
        <div class="dropdown-menu" aria-labelledby="navbarDropdownMenuLinkDiplome">
          <a class="dropdown-item <?php if(WebPage::getTitle() == "Gestion des épreuves") echo "active"; ?>" href="<?php echo WEB_PATH; ?>diplomes/epreuves.php">Gestion des épreuves</a>
        </div>
      </li>
      */
?>
<?php
        }
?>
      <li class="nav-item dropdown">
        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownMenuLinkEspace" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
          Espace personnel
        </a>
        <div class="dropdown-menu" aria-labelledby="navbarDropdownMenuLinkEspace">
<?php
        if(UserModel::estEnseignant()) {
?>
          <a class="dropdown-item <?php if(WebPage::getTitle() == "Mes infos") echo "active"; ?>" href="<?php echo WEB_PATH; ?>mesinfos/general.php">Mes ECs</a>
<?php
        }
        if(UserModel::estTuteur()) {
?>
          <a class="dropdown-item <?php if(WebPage::getTitle() == "Tutorat") echo "active"; ?>" href="<?php echo WEB_PATH; ?>mesinfos/tutorat.php">Tutorat</a>
<?php
        }
?>
<?php
        if(UserModel::estRespDiplome() || UserModel::estTuteur()) {
?>
          <a class="dropdown-item <?php if(WebPage::getTitle() == "IP des étudiants") echo "active"; ?>" href="<?php echo WEB_PATH; ?>tutorat/etudiants.php">IP des étudiants</a>
<?php
        }
?>        
          <a class="dropdown-item <?php if(WebPage::getTitle() == "Liste des ECs") echo "active"; ?>" href="<?php echo WEB_PATH; ?>ecs/liste.php">Liste des ECs</a>
<?php
        if(UserModel::estEnseignant()) {
?>
          <a class="dropdown-item <?php if(WebPage::getTitle() == "Justificatifs d'absence") echo "active"; ?>" href="<?php echo WEB_PATH; ?>presentiel/justificatifs.php">Justificatifs d'absence</a>
          <a class="dropdown-item <?php if(WebPage::getTitle() == "Changer de mot de passe") echo "active"; ?>" href="<?php echo WEB_PATH; ?>mesinfos/motdepasse.php">Changer de mot de passe</a>
<?php
        }
?>          
        </div>
      </li>
<?php
    }
    if(UserModel::estEtudiant()) {
?>
      <li class="nav-item dropdown">
        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownMenuLinkEtu" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
          Espace personnel
        </a>
        <div class="dropdown-menu" aria-labelledby="navbarDropdownMenuLinkEtu">
          <a class="dropdown-item <?php if(WebPage::getTitle() == "Mes infos") echo "active"; ?>" href="<?php echo WEB_PATH; ?>mesinfos/general.php">Mes infos</a>
          <a class="dropdown-item <?php if(WebPage::getTitle() == "Mes notes") echo "active"; ?>" href="<?php echo WEB_PATH; ?>mesinfos/notes.php">Mes notes</a>
          <a class="dropdown-item <?php if(WebPage::getTitle() == "Mes groupes") echo "active"; ?>" href="<?php echo WEB_PATH; ?>mesinfos/groupes.php">Mes groupes</a>
          <a class="dropdown-item <?php if(WebPage::getTitle() == "Mes IP") echo "active"; ?>" href="<?php echo WEB_PATH; ?>mesinfos/ip.php">Mes IP</a>
          <a class="dropdown-item <?php if(WebPage::getTitle() == "Mon présentiel") echo "active"; ?>" href="<?php echo WEB_PATH; ?>mesinfos/presentiel.php">Mon présentiel</a>
          <a class="dropdown-item <?php if(WebPage::getTitle() == "Mon emploi du temps") echo "active"; ?>" href="<?php echo WEB_PATH; ?>mesinfos/emploidutemps.php">Mon emploi du temps</a>
          <a class="dropdown-item <?php if(WebPage::getTitle() == "Changer de mot de passe") echo "active"; ?>" href="<?php echo WEB_PATH; ?>mesinfos/motdepasse.php">Changer de mot de passe</a>
        </div>
      </li>
<?php
    }
    
    if(UserModel::isTemporary()) {
?>
      <li class="nav-item">
        <a class="btn btn-warning" href="<?php echo WEB_PATH; ?>users/role.php">Switch</a>
      </li>
<?php        
    }
    else {
?>
      <li class="nav-item">
        <a class="btn btn-danger" href="<?php echo WEB_PATH; ?>logout.php">Déconnexion</a>
      </li>
<?php
    }
}
else {
?>
      <li class="nav-item">
        <a class="btn btn-primary" href="<?php echo WEB_PATH; ?>login.php">Connexion</a>
      </li>
<?php
}
?>
    </ul>
  </div>
</nav>