<?php 
WebPage::addOnlineScript("var WEB_PATH = '".WEB_PATH."';");

/* Pour la recherche */
WebPage::addJSScript("public/js/typeahead.min.js"); 
WebPage::addOnlineScript("$('input[name=rechercher]').typeahead({ minLength: 3, source: rechercher, updater: selectEtudiant });");

/* Pour les dates */
WebPage::addCSSScript("public/css/tempusdominus-bootstrap-4.css");
WebPage::addCSSScript("vendor/fontawesome/css/all.min.css");
WebPage::addJSScript("public/js/moment.js");
WebPage::addJSScript("public/js/tempusdominus-bootstrap-4.min.js");
?>    

<script>
function selectType() {
    if($('#inputType').val() == 2) {
        $('#inputDateDebut_div').hide();
        $('#inputDateFin_div').hide();
        $('#inputDateDebutPlage_div').show();
        $('#inputDateFinPlage_div').show();
    }
    else {
        $('#inputDateDebut_div').show();
        $('#inputDateFin_div').show();
        $('#inputDateDebutPlage_div').hide();
        $('#inputDateFinPlage_div').hide();        
    }
}
function selectEtudiant(item) {
    $("#inputId").val(item.id);
    $("#inputName").val(item);
    return item.name;
}
function rechercher(entry, process) {
    request = $.ajax({
        type: 'POST',
        url: WEB_PATH + 'etudiants/ws.php',
        data: { "mode" : 5, "nom" : encodeURIComponent(entry) },
        dataType: 'json'
    });
    request.done(function (response, textStatus, jqXHR) {
        if(response['code'] == 1) {
            data = new Array();
            for(i = 0; i < response['etudiants'].length; i++) {
                var group = {
                    id: response['etudiants'][i]['id'],
                    name: response['etudiants'][i]['nom'] + " " + response['etudiants'][i]['prenom'],
                    toString: function () {
                        return this.name;
                    }
                };
                data.push(group);
            }
            return process(data);
        }
        else
            console.log(response);
    });
    request.fail(function (jqXHR, textStatus, errorThrown){
        console.log(jqXHR);
    });    
    return request;
}
</script>

     <div class="form-group row">
        <label for='recherche' class="col-sm-2 col-form-label">Rechercher</label>
        <div class="col-sm-10">
          <div class="input-group">
            <div class="input-group-prepend">
              <span class="input-group-text"><i class="icon-magnifier"></i></span>
            </div>
            <input id="rechercher" autocomplete="off" name="rechercher" class='typeahead form-control' type="text" data-provide="typeahead" placeholder="Saisir l'étudiant à rechercher" value="" />
          </div>
          <small id="inputDateDebutHelp" class="form-text text-muted">Commencez par saisir le nom ou prénom, puis sélectionnez l'étudiant(e) dans la liste</small>
        </div>
      </div>
      <div class="form-group row">
        <label for="inputName" class="col-sm-2 col-form-label">Etudiant</label>
        <div class="col-sm-10">
          <input type="hidden" name="inputId" id="inputId" value="<?php if(isset($data['etudiant'])) echo $data['etudiant']->getIdUtilisateur(); ?>">
          <input type="text" readonly class="form-control" name="inputName" id="inputName" placeholder="L'étudiant sélectionné apparaîtra ici" value="<?php if(isset($data['etudiant'])) echo $data['etudiant']->__toString(); ?>">
          <small id="inputDateDebutHelp" class="form-text text-muted">Faites une recherche pour sélectionner un étudiant</small>
        </div>
      </div>
      <div class="form-group row">
        <label for="inputType" class="col-sm-2 col-form-label">Type d'absence</label>
        <div class="col-sm-10">
          <div class='input-group'>
            <select class='form-control' name='inputType' id='inputType' value='' onchange='selectType()'/>
              <option value='1'>Journée(s) entière(s)</option>
              <option value='2'
<?php
if(isset($data['justificatif']) && ($data['justificatif']->getDateDebut() != 0)) {
    if(date('G', $data['justificatif']->getDateDebut()) != 0)
        echo "selected";
}
?>
>Dates avec heures</option>
            </select>
          </div>
          <small id="inputDateDebutHelp" class="form-text text-muted">L'absence est soit sur une/des journée(s) entière(s), soit sur une plage horaire précise</small>
        </div>
      </div>
      <div class="form-group row" id="inputDateDebut_div">
        <label for="inputDateDebut" class="col-sm-2 col-form-label">Jour de début</label>
        <div class="col-sm-10">
          <div class="input-group date" id="inputDate" data-target-input="nearest">
            <input type="text" name="inputDateDebut" class="form-control datetimepicker-input" data-target="#inputDate" value='<?php 
if(isset($data['justificatif']) && ($data['justificatif']->getDateDebut() != 0)) echo DateTools::timestamp2Date($data['justificatif']->getDateDebut()); ?>'/>
            <div class="input-group-append" data-target="#inputDate" data-toggle="datetimepicker">
              <div class="input-group-text"><i class="icon-calendar"></i></div>
            </div>
          </div>
          <small id="inputDateDebutHelp" class="form-text text-muted">Le format de la date est JJ/MM/YYYY</small>
<?php WebPage::addOnlineScript("$('#inputDate').datetimepicker({locale: 'fr', format: 'DD/MM/YYYY'})"); ?>
        </div>
      </div>
      <div class="form-group row" id="inputDateFin_div">
        <label for="inputDateFin" class="col-sm-2 col-form-label">Jour de fin</label>
        <div class="col-sm-10">
          <div class="input-group date" id="inputDateFin" data-target-input="nearest">
            <input type="text" name="inputDateFin" class="form-control datetimepicker-input" data-target="#inputDateFin" value='<?php 
if(isset($data['justificatif']) && ($data['justificatif']->getDateFin() != 0)) echo DateTools::timestamp2Date($data['justificatif']->getDateFin()); ?>'/>
            <div class="input-group-append" data-target="#inputDateFin" data-toggle="datetimepicker">
              <div class="input-group-text"><i class="icon-calendar"></i></div>
            </div>
          </div>
          <small id="inputDateFinHelp" class="form-text text-muted">Le format de la date est JJ/MM/YYYY</small>
<?php WebPage::addOnlineScript("$('#inputDateFin').datetimepicker({locale: 'fr', format: 'DD/MM/YYYY'})"); ?>
        </div>
      </div>      
      <div class="form-group row" id="inputDateDebutPlage_div">
        <label for="inputDateDebutPlage" class="col-sm-2 col-form-label">Date de début</label>
        <div class="col-sm-10">
          <div class="input-group date" id="inputDateDebutPlage" data-target-input="nearest">
            <input type="text" name="inputDateDebutPlage" class="form-control datetimepicker-input" data-target="#inputDateDebutPlage" value='<?php 
if(isset($data['justificatif']) && ($data['justificatif']->getDateDebut() != 0)) echo DateTools::timestamp2Date($data['justificatif']->getDateDebut(), true); ?>'/>
            <div class="input-group-append" data-target="#inputDateDebutPlage" data-toggle="datetimepicker">
              <div class="input-group-text"><i class="icon-calendar"></i></div>
            </div>
          </div>
          <small id="inputDateDebutPlageHelp" class="form-text text-muted">Le format de la date est JJ/MM/YYYY HH:MM</small>
<?php WebPage::addOnlineScript("$('#inputDateDebutPlage').datetimepicker({locale: 'fr'})"); ?>
        </div>
      </div>
      <div class="form-group row" id="inputDateFinPlage_div">
        <label for="inputDateFinPlage" class="col-sm-2 col-form-label">Date de fin</label>
        <div class="col-sm-10">
          <div class="input-group date" id="inputDateFinPlage" data-target-input="nearest">
            <input type="text" name="inputDateFinPlage" class="form-control datetimepicker-input" data-target="#inputDateFinPlage" value='<?php 
if(isset($data['justificatif']) && ($data['justificatif']->getDateFin() != 0)) echo DateTools::timestamp2Date($data['justificatif']->getDateFin(), true); ?>'/>
            <div class="input-group-append" data-target="#inputDateFinPlage" data-toggle="datetimepicker">
              <div class="input-group-text"><i class="icon-calendar"></i></div>
            </div>
          </div>
          <small id="inputDateFinPlageHelp" class="form-text text-muted">Le format de la date est JJ/MM/YYYY HH:MM ; si la date est identique à celle de début, ne précisez rien</small>
<?php WebPage::addOnlineScript("$('#inputDateFinPlage').datetimepicker({locale: 'fr'})"); ?>
        </div>
      </div>
      <div class="form-group row">
        <label for="inputMotif" class="col-sm-2 col-form-label">Motif</label>
        <div class="col-sm-10">
          <input type="text" class="form-control" name="inputMotif" id="inputMotif" placeholder="Saisissez le motif de l'absence" value="<?php if(isset($data['justificatif'])) echo $data['justificatif']->getMotif(); ?>">
          <small id="inputDateDebutHelp" class="form-text text-muted">Pour protéger les données privées des étudiant(e)s, vous devez saisir un motif générique (comme "Maladie", "Rendez-vous médical", etc.)</small>
        </div>
      </div>
      <div class="form-group row">
        <label for="inputRemarque" class="col-sm-2 col-form-label">Remarque</label>
        <div class="col-sm-10">
          <textarea class="form-control" name="inputRemarque" id="inputRemarque" placeholder="Tapez ici vos remarques éventuelles (non affichées pour les étudiants)"><?php if(isset($data['justificatif'])) echo $data['justificatif']->getRemarque(); ?></textarea>
        </div>
      </div>