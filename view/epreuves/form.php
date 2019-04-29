  <div class="form-group row">
    <label for="inputNom" class="col-sm-2 col-form-label">Intitulé</label>
    <div class="col-sm-10">
      <input autofocus type="text" class="form-control" id="inputIntitule" name="inputIntitule" placeholder="Saisir l'intitulé"
<?php if(isset($data['epreuve']) && ($data['epreuve']->getIntitule() != "")) echo " value='".$data['epreuve']->getIntitule()."'"; ?>>
      <small class="form-text text-muted">L'intitulé peut être par exemple DS, ou DS n°1, DS n°2, etc.</small>
    </div>
  </div>
  <div class="form-group row">
    <label for="inputPrenom" class="col-sm-2 col-form-label">Type</label>
    <div class="col-sm-10">
      <select class="form-control" id="inputType" name="inputType">
<?php
foreach(Epreuve::TYPE_DESCRIPTION as $cle => $valeur) {
    echo "<option value='$cle'";
    if(isset($data['epreuve']) && $data['epreuve']->getType() == $cle) echo "selected=\"selected\"";
    echo ">$valeur</option>";
}
?>
      </select>
      <small class="form-text text-muted">Le type correspond à DS, DST, Projet, etc. ; vous devez choisir un type</small>
    </div>
  </div>
  <div class="form-group row">
    <label for="inputMax" class="col-sm-2 col-form-label">Maximum</label>
    <div class="col-sm-10">
      <input type="text" class="form-control" id="inputMax" name="inputMax" placeholder="Saisir la valeur maximale"
<?php if(isset($data['epreuve']) && ($data['epreuve']->getMax() != 0)) echo " value='".$data['epreuve']->getMax()."'"; ?>>
      <small class="form-text text-muted">La note maximum pour la saisie (qui peut être différente de la répartition définie dans les MCC)</small>
    </div>
  </div>
  <div class="form-group row">
    <label for="inputSession1" class="col-sm-2 col-form-label">Session 1</label>
    <div class="col-sm-10">
      <input type="text" class="form-control" id="inputSession1" name="inputSession1" placeholder="Saisir la répartition"
<?php if(isset($data['epreuve']) && ($data['epreuve']->getSession1() != 0)) echo " value='".$data['epreuve']->getSession1()."'"; ?>>
      <small class="form-text text-muted">La répartition de la note en première session (un pourcentage)</small>
    </div>
  </div>
  <div class="form-group row">
    <label for="inputSession2" class="col-sm-2 col-form-label">Session 2</label>
    <div class="col-sm-10">
      <input type="text" class="form-control" id="inputSession2" name="inputSession2" placeholder="Saisir la répartition"
<?php if(isset($data['epreuve']) && ($data['epreuve']->getSession2() != 0)) echo " value='".$data['epreuve']->getSession2()."'"; ?>>
      <small class="form-text text-muted">La répartition de la note en deuxième session (un pourcentage)</small>
    </div>
  </div>
  <div class="form-group row">
    <label for="inputSession1Disp" class="col-sm-2 col-form-label">Session 1 disp.</label>
    <div class="col-sm-10">
      <input type="text" class="form-control" id="inputSession1Disp" name="inputSession1Disp" placeholder="Saisir la répartition"
<?php if(isset($data['epreuve']) && ($data['epreuve']->getSession1Disp() != 0)) echo " value='".$data['epreuve']->getSession1Disp()."'"; ?>>
      <small class="form-text text-muted">La répartition de la note en première session pour les dispensés (un pourcentage)</small>
    </div>
  </div>
  <div class="form-group row">
    <label for="inputSession2Disp" class="col-sm-2 col-form-label">Session 2 disp.</label>
    <div class="col-sm-10">
      <input type="text" class="form-control" id="inputSession2Disp" name="inputSession2Disp" placeholder="Saisir la répartition"
<?php if(isset($data['epreuve']) && ($data['epreuve']->getSession2Disp() != 0)) echo " value='".$data['epreuve']->getSession2Disp()."'"; ?>>
      <small class="form-text text-muted">La répartition de la note en deuxième session pour les dispensés (un pourcentage)</small>
    </div>
  </div>  
  <div class="form-group row">
    <label for="inputActive" class="col-sm-2 col-form-label">Active</label>
    <div class="col-sm-10">
      <div class="form-check form-check-inline">
        <input class="form-check-input" type="radio" name="inputActive" id="active" value="1"
<?php if(!isset($data['epreuve']) || $data['epreuve']->isActive()) echo " checked=\"checked\""; ?>>
        <label class="form-check-label" for="active">active</label>
      </div>
      <div class="form-check form-check-inline">
        <input class="form-check-input" type="radio" name="inputActive" id="inactive" value="0"
<?php if(isset($data['epreuve']) && !$data['epreuve']->isActive()) echo " checked=\"checked\""; ?>>
        <label class="form-check-label" for="inactive">inactive</label>
      </div>
      <small class="form-text text-muted">Les intervenants ne peuvent saisir une note que si l'épreuve est activée</small>
    </div>
  </div>
  <div class="form-group row">
    <label for="inputVisible" class="col-sm-2 col-form-label">Visible</label>
    <div class="col-sm-10">
      <div class="form-check form-check-inline">
        <input class="form-check-input" type="radio" name="inputVisible" id="visible" value="1"
<?php if(isset($data['epreuve']) && $data['epreuve']->isVisible()) echo " checked=\"checked\""; ?>>
        <label class="form-check-label" for="visible">visible</label>
      </div>
      <div class="form-check form-check-inline">
        <input class="form-check-input" type="radio" name="inputVisible" id="nonvisible" value="0"
<?php if(!isset($data['epreuve']) || !$data['epreuve']->isVisible()) echo " checked=\"checked\""; ?>>
        <label class="form-check-label" for="nonvisible">non visible</label>
      </div>
      <small class="form-text text-muted">Tant que l'épreuve n'est pas visible, les étudiants ne peuvent pas voir la note</small>
    </div>
  </div>  