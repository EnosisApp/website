<!DOCTYPE html>
<html>
	<head>
		<title>EnosisApp Batery reporter</title>
		<meta charset="utf-8" />
		<!-- Compiled and minified CSS -->
		<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/materialize/0.98.0/css/materialize.min.css">
		<link rel="stylesheet" href="css/main.css">
	</head>

	<body>
		<div class="container">
			<div class="row center-align">
				<img src="images/logo.jpg" alt="Logo mobile EnosisApp" style="margin-top: 24px;max-height: 128px; border-radius: 10px;" />
			</div>

			<div class="row">
				<div class="col s12">
					<p>Bonjour, <?=$_SESSION['user']?>.</p>
					<p>Le but de ce test est de mesurer l'impact de l'application EnosisApp sur la batterie de nos utilisateurs. Pour ce faire nous avons besoin de vous !</p>
					<p>Le test se décompose en deux étapes, une par jour:</p>
					<ul>
						<li>
							<b>Jour 1:</b> téléchargez l'application depuis <a href="https://enosisapp.fr" target="_blank">https://enosisapp.fr</a>. Allumez votre bluetooth, lancez l'application, fermez-la (en la laissant tourner en arrière-plan, sans la <i>kill</i>) et laissez-la tourner toute la journée. Lorsque votre téléphone arrive à 10% de batterie, notez le temps que votre batterie a tenu dans le champs correspondant au Jour 1.
						</li>
						<li>
							<b>Jour 2:</b> refaite exactement la même opération qu'au Jour 1, mais en ayant désinstallé l'application. Remplissez ensuite le reste du formulaire.
						</li>
					</ul>
					<p>
						<b>Notes importantes:</b> afin que le test soit concluant, veuillez débrancher votre téléphone aproximativement à la même heure durant les deux jours de test.
					</p>
				</div>
			</div>

			<div class="row">
				<div class="col s12 m6">
					<h4>Jour 1</h4>
					<form method="POST">
						<label for="day1">Entrez l'heure à laquelle votre batterie arrive à 10% </label>
						<div class="form-type">
							<input type="time" id="day1" name="day1"<?php if(isset($entries) && isset($entries['day1'])) echo ' value="'.$entries['day1'].'"' ?> />
						</div>
						<input type="submit" value="Valider" class="btn" />
					</form>
				</div>
				
				<?php if($entries) { ?>
				<div class="col s12 m6">
					<h4>Jour 2</h4>
					<form method="POST">
						<label for="day2">Entrez l'heure à laquelle votre batterie arrive à 10% </label>
						<div class="form-type">
							<input type="time" id="day2" name="day2"<?php if(isset($entries) && isset($entries['day2'])) echo ' value="'.$entries['day2'].'"' ?> />
						</div>
						<input type="submit" value="Valider" class="btn right" />
					</form>
				</div>

				<?php if($entries && $entries['day2'] != '00:00:00') { ?>
				<div class="col s12 m6 offset-m3">
					<form method="POST">
						<p>Avez-vous le ressenti une grosse différence dans l'utilisation de votre batterie ?</p>
						<p><input type="radio" id="Y" name="feeling" value="Y"<?php if(isset($entries) && isset($entries['feeling']) && $entries['feeling'] == 'Y') echo ' checked="checked"' ?> /><label for="Y"> Oui</label></p>
						<p><input type="radio" id="N" name="feeling" value="N"<?php if(isset($entries) && isset($entries['feeling']) && $entries['feeling'] == 'N') echo ' checked="checked"' ?> /><label for="N"> Non</label></p>
						<input type="submit" value="Valider" class="btn" />
					</form>
				</div>
				<?php } ?>
				<?php } ?>
			</div>
		</div>

		<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>
		<!-- Compiled and minified JavaScript -->
		<script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/0.98.0/js/materialize.min.js"></script>
		<script src="js/jskit.js"></script>
	</body>
</html>
