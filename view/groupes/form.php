  <div class="form-group row">
    <label for="inputNom" class="col-sm-2 col-form-label">Intitulé</label>
    <div class="col-sm-10">
      <input type="text" class="form-control" id="inputIntitule" name="inputIntitule" placeholder="Nom" <?php 
if(isset($data['groupe']) && ($data['groupe']->getIntitule() != "")) echo " value='".$data['groupe']->getIntitule()."'"; ?>>
    </div>
  </div>
  <div class="form-group row">
    <label for="inputType" class="col-sm-2 col-form-label">Type du groupe</label>
    <div class="col-sm-10">
      <select class="form-control" id="inputType" name="inputType" <?php if(isset($data['groupe']) && ($data['groupe']->getId() != -1)) echo " disabled"; ?>>
        <option value="<?php echo Groupe::GRP_CM; ?>" <?php if(isset($data['groupe']) && ($data['groupe']->getType() == Groupe::GRP_CM)) echo "selected=\"selected\""; ?>>CM</option>
        <option value="<?php echo Groupe::GRP_TD; ?>" <?php if(isset($data['groupe']) && ($data['groupe']->getType() == Groupe::GRP_TD)) echo "selected=\"selected\""; ?>>TD</option>
        <option value="<?php echo Groupe::GRP_TP; ?>" <?php if(isset($data['groupe']) && ($data['groupe']->getType() == Groupe::GRP_TP)) echo "selected=\"selected\""; ?>>TP</option>
      </select>
    </div>    
  </div>
