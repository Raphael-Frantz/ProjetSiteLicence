<section class="mytitle text-center bg-light">
  <div class="container">
    <h2 class="mb-5">Changement de mot de passe</h2>
    <p class="lead mb-0">Vous pouvez modifier votre mot de passe depuis cette page.</p>
  </div>
</section>

<section>
  <div class="container">
    <form action="" method="post" class="mt-2">
      <div class="form-group row">
        <label for="inputOldPassword" class="col-sm-2 col-form-label">Mot de passe</label>
        <div class="col-sm-10">
          <input type="password" class="form-control" name="inputOldPassword" id="inputOldPassword" placeholder="Saisissez votre mot de passe actuel">
          <small class="form-text text-muted">Pour une question de sécurité, saisissez votre mot de passe actuel</small>
        </div>
      </div>
      <div class="form-group row">
        <label for="inputPassword" class="col-sm-2 col-form-label">Nouveau</label>
        <div class="col-sm-10">
          <input type="password" class="form-control" name="inputPassword" id="inputPassword" placeholder="Saisissez votre nouveau mot de passe">
          <small class="form-text text-muted">Le mot de passe doit contenir entre 6 et 40 caractères avec des lettres, des chiffres et au moins un caractère spécial</small>
        </div>
      </div>
      <div class="form-group row">
        <label for="inputConfPassword" class="col-sm-2 col-form-label">Confirmation</label>
        <div class="col-sm-10">
          <input type="password" class="form-control" name="inputConfPassword" id="inputConfPassword" placeholder="Saisissez la confirmation de votre nouveau mot de passe">
          <small class="form-text text-muted">Saisissez à nouveau votre nouveau de passe</small>
        </div>
      </div>
      <button type="submit" name="btnValider" class="btn btn-primary">Valider</button>
    </form>
  </div>
</section>