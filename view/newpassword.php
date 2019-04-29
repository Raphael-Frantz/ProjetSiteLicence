<section class="mytitle text-center bg-light">
  <div class="container">
    <h2 class="mb-5">Changement de mot de passe</h2>
    <p class="lead mb-0">
       Si vous avez fait la procédure de changement de mot de passe, 
       saisissez votre adresse email, la clé de vérification puis saisissez votre nouveau mot de passe.
    </p>
  </div>
</section>

<section class="mysection mb-2">
  <div class="container">
    <form id="controlForm" action="" method="post" class="mt-2">
      <div class="form-group row">
        <label for="inputEmail" class="col-sm-2 col-form-label">Adresse email</label>
        <div class="col-sm-10">
          <input type="email" class="form-control" name="inputEmail" id="inputEmail" placeholder="Saisissez votre adresse email de l'université"<?php
if(isset($_POST['inputEmail'])) 
    echo " value='".$_POST['inputEmail']."'";
elseif(isset($_GET['email'])) echo " value='".$_GET['email']."'";
?>>
          <small class="form-text text-muted">Saisissez votre adresse email de l'URCA</small>
        </div>
      </div>
      <div class="form-group row">
        <label for="inputKey" class="col-sm-2 col-form-label">Clé de vérification</label>
        <div class="col-sm-10">
          <input type="text" class="form-control" name="inputKey" id="inputKey" placeholder="Saisissez la clé reçue par email"<?php
if(isset($_POST['inputKey'])) 
    echo " value='".$_POST['inputKey']."'";
elseif(isset($_GET['key'])) echo " value='".$_GET['key']."'";
?>>
          <small class="form-text text-muted">Ce champ est rempli automatiquement quand vous cliquez sur le lien reçu par email</small>
        </div>
      </div>
      <div class="form-group row">
        <label for="inputPassword" class="col-sm-2 col-form-label">Mot de passe</label>
        <div class="col-sm-10">
          <input type="password" class="form-control" name="inputPassword" id="inputPassword" placeholder="Saisissez le nouveau mot de passe">
          <small class="form-text text-muted">Le mot de passe doit contenir entre 6 et 40 caractères avec des lettres, des chiffres et au moins un caractère spécial</small>
        </div>
      </div>
      <div class="form-group row">
        <label for="inputConfPassword" class="col-sm-2 col-form-label">Confirmation</label>
        <div class="col-sm-10">
          <input type="password" class="form-control" name="inputConfPassword" id="inputConfPassword" placeholder="Saisissez la confirmation du mot de passe">
          <small class="form-text text-muted">Saisissez à nouveau votre nouveau de passe</small>
        </div>
      </div>
      <button type="submit" name="btnValider" class="btn btn-primary">Valider</button>
    </form>
  </div>
</section>