<div class="container-fluid">

    <!-- Liste des groupes -->

    <div class="row p-4 m-4 text-center border">

        <!-- Nom du groupe -->

        <div class="col text-left">
            <div>Nom du groupe</div>
        </div>

        <!-- Jour de l'examen -->

        <div class="col">
            <div>Jour de l'examen</div>
        </div>

        <!-- Heure de début de l'examen -->

        <div class="col">
            <div>Heure de début</div>
        </div>

        <!-- Heure de fin de l'examen -->

        <div class="col">
            <div>Heure de fin</div>
        </div>

    </div>

    <?php foreach($data['groupes'] as $groupe) { ?>

    <div>

        <!-- Entrée pour 1 groupe -->

        <div class="row mb-2">

            <!-- Identifiant du groupe -->

            <input type="hidden" name="groupe[]" value="<?php echo $groupe['id']; ?>"
                   class="form-control mb-2 mr-sm-2">

            <!-- Nom du groupe -->

            <div class="col form-group">
                <div class=""><?php echo $groupe['intitule']; ?></div>
            </div>

            <!-- Jour de l'examen -->

            <div class="col form-group">
                <input type="date"  placeholder="Jour de l'examen" name="jour[]" value="<?php echo $groupe['jour']; ?>"
                       class="form-control mb-2 mr-sm-2">
            </div>

            <!-- Heure de début de l'examen -->

            <div class="col form-group">
                <input type="time" placeholder="Heure de début" name="debut[]" value="<?php echo $groupe['debut']; ?>"
                       class="form-control mb-2 mr-sm-2">
            </div>

            <!-- Heure de fin de l'examen -->

            <div class="col form-group">
                <input type="time" placeholder="Heure de fin" name="fin[]" value="<?php echo $groupe['fin']; ?>"
                       class="form-control mb-2 mr-sm-2">
            </div>

        </div>

    </div>

    <?php } ?>

</div>