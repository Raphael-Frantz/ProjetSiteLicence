<?php 
if(isset($data['actualite']) && ($data['actualite']->getId() != -1)) 
    echo "<input type='hidden' name='inputId' value='".$data['actualite']->getId()."'/>"; 
?>

  <div class="form-group row">
    <label for="inputDate" class="col-sm-2 col-form-label">Date</label>
    <div class="col-sm-10">
      <div class='input-group date' id='divDate' data-target-input='nearest'>
        <input type='text' name='inputDate' id='inputDate' class='form-control datetimepicker-input' data-target='#divDate'
<?php if(isset($data['actualite']) && ($data['actualite']->getDate() > 0)) echo " value='".DateTools::timestamp2Date($data['actualite']->getDate())."'"; ?>
/>
        <div class="input-group-append" data-target="#divDate" data-toggle="datetimepicker">
            <div class="input-group-text"><span class='input-group-addon'><img src='<?php echo WEB_PATH; ?>public/pictures/calendar.png'/></span></div>
        </div>
      </div>
    </div>
  </div>  
  
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/tempusdominus-bootstrap-4/5.0.0-alpha14/css/tempusdominus-bootstrap-4.min.css" />
<?php
WebPage::addJSScript("https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.22.2/moment-with-locales.min.js");
WebPage::addJSScript("https://cdnjs.cloudflare.com/ajax/libs/moment-timezone/0.5.21/moment-timezone-with-data-2012-2022.min.js");
WebPage::addJSScript("https://cdnjs.cloudflare.com/ajax/libs/tempusdominus-bootstrap-4/5.0.0-alpha14/js/tempusdominus-bootstrap-4.min.js");

WebPage::addOnlineScript("$('#divDate').datetimepicker( { locale: 'fr', format: 'DD/MM/YYYY' } );");
?>
  
  <div class="form-group row">
    <label for="inputTitre" class="col-sm-2 col-form-label">Titre</label>
    <div class="col-sm-10">
      <input type="text" class="form-control" name="inputTitre" id="inputTitre" placeholder="Saisir le titre de l'actualité"
<?php if(isset($data['actualite'])) echo " value=\"".htmlspecialchars($data['actualite']->getTitre())."\""; ?>
>
    </div>
  </div>
  <div class="form-group row">
    <label for="inputContenu" class="col-sm-2 col-form-label">Contenu</label>
    <div class="col-sm-10">
      <textarea class="form-control" id="inputContenu" name="inputContenu" rows="3" placeholder="Saisir le contenu de l'actualité"><?php
if(isset($data['actualite'])) echo $data['actualite']->getContenu(); ?></textarea>
    </div>
  </div>
  <div class="form-group row">
    <label for="inputTitreLien" class="col-sm-2 col-form-label">Texte du lien</label>
    <div class="col-sm-10">
      <input type="text" class="form-control" name="inputTitreLien" id="inputTitreLien" placeholder="Saisir le texte affiché sur le bouton du lien"
<?php if(isset($data['actualite'])) echo " value=\"".htmlspecialchars($data['actualite']->getTitreLien())."\""; ?>
>
    </div>
  </div>
  <div class="form-group row">
    <label for="inputLien" class="col-sm-2 col-form-label">Lien</label>
    <div class="col-sm-10">
      <input type="text" class="form-control" name="inputLien" id="inputLien" placeholder="Saisir le lien"
<?php if(isset($data['actualite'])) echo " value=\"".htmlspecialchars($data['actualite']->getLien())."\""; ?>
>
    </div>
  </div>
  
  <div class="form-group row">
    <label for="inputPrioritaire" class="col-sm-2 col-form-label">Prioritaire</label>
    <div class="col-sm-10">
      <div class="form-check form-check-inline">
        <input class="form-check-input" type="radio" name="inputPrioritaire" id="prioritaireOui" value="1"
<?php if(!isset($data['actualite']) || (isset($data['actualite']) && $data['actualite']->estPrioritaire())) echo "checked";?>>
        <label class="form-check-label" for="prioritaireOui">Oui</label>
      </div>
      <div class="form-check form-check-inline">
        <input class="form-check-input" type="radio" name="inputPrioritaire" id="prioritaireNon" value="0" 
<?php if(isset($data['actualite']) && !$data['actualite']->estPrioritaire()) echo "checked";?>>
        <label class="form-check-label" for="prioritaireNon">Non</label>
      </div>
    </div>
  </div>

  <div class="form-group row">
    <label for="inputActive" class="col-sm-2 col-form-label">Active</label>
    <div class="col-sm-10">
      <div class="form-check form-check-inline">
        <input class="form-check-input" type="radio" name="inputActive" id="activeOui" value="1"
<?php if(!isset($data['actualite']) || (isset($data['actualite']) && $data['actualite']->estActive())) echo "checked";?>>
        <label class="form-check-label" for="activeOui">Oui</label>
      </div>
      <div class="form-check form-check-inline">
        <input class="form-check-input" type="radio" name="inputActive" id="activeNon" value="0" 
<?php if(isset($data['actualite']) && !$data['actualite']->estActive()) echo "checked";?>>
        <label class="form-check-label" for="activeNon">Non</label>
      </div>
    </div>
  </div>
<?php
$annees = array();
if(isset($data['actualite']) && ($data['actualite']->getAnnees() != ""))
    $annees = preg_split("/;/", $data['actualite']->getAnnees());
?>
  <div class="form-group row">  
    <label class="col-sm-2 col-form-label">Concerne</label>
    <div class="col-sm-10">
        <div class="form-check form-check-inline">
            <input class="form-check-input" name="inputAnnees[]" type="checkbox" id="inputL1" value="L1"<?php
if((count($annees) == 0) || (in_array("L1", $annees))) echo "checked"; ?>>
            <label class="form-check-label" for="inputL1">L1</label>
        </div>
        <div class="form-check form-check-inline">
            <input class="form-check-input" name="inputAnnees[]" type="checkbox" id="inputL2" value="L2"<?php
if((count($annees) == 0) || (in_array("L2", $annees))) echo "checked"; ?>>
            <label class="form-check-label" for="inputL2">L2</label>
        </div>
        <div class="form-check form-check-inline">
            <input class="form-check-input" name="inputAnnees[]" type="checkbox" id="inputL3" value="L3"<?php
if((count($annees) == 0) || (in_array("L3", $annees))) echo "checked"; ?>>
            <label class="form-check-label" for="inputL3">L3</label>
        </div>
        <div class="form-check form-check-inline">
            <input class="form-check-input" name="inputAnnees[]" type="checkbox" id="inputL3P" value="L3P"<?php
if((count($annees) == 0) || (in_array("L3P", $annees))) echo "checked"; ?>>
            <label class="form-check-label" for="inputL3P">L3P</label>
        </div>
    </div>
  </div>