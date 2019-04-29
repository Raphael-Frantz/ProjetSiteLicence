<?php
// @need nothing
?>
<!doctype html>
<html lang="fr">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="icon" href="<?php echo WEB_PATH; ?>public/pictures/favicon.png">
    <title><?php echo WebPage::getTitle(); ?></title>
    <link rel="stylesheet" href="<?php echo WEB_PATH; ?>vendor/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?php echo WEB_PATH; ?>public/css/signin.css">
  </head>

  <body class="text-center">
    <form class="form-signin" action="<?php echo WEB_PATH; ?>login.php" method="post">
      <img class="mb-4" src="<?php echo WEB_PATH; ?>public/pictures/logo_URCA.png" alt="" width="200">
      <h1 class="h3 mb-3 font-weight-normal">Connexion</h1>
<?php
      if(($pageError = WebPage::getCurrentErrorMsg()) != "") echo "<label class=\"alert alert-danger\">$pageError</label>";
      if(($pageMessage = WebPage::getCurrentMsg()) != "") echo "<label class=\"alert alert-success\">$pageMessage</label>";
?>
      <label for="inputEmail" class="sr-only">Adresse email</label>
      <input type="email" id="inputEmail" name="email" class="form-control" placeholder="Adresse email" required autofocus>
      <label for="inputPassword" class="sr-only">Mot de passe</label>
      <input type="password" id="inputPassword" name="password" class="form-control" placeholder="Mot de passe" required>
      <button class="btn btn-lg btn-primary btn-block" type="submit"> Connexion </button>
      <p class="mt-5"><a href="<?php echo WEB_PATH; ?>">Retour à l'accueil</a></p>
      <p class="mb-3"><a href="<?php echo WEB_PATH; ?>recovery.php">Mot de passe oublié/inconnu</a></p>
      <p class="mt-5 mb-3 text-muted">&copy; 2018-2019</p>
    </form>
  </body>
</html>