<?php
session_start();
require_once("../fonctions.php");

$mysqli = db_connexion();

include ('../nb_online.php');

// recuperation config jeu
$sql = "SELECT disponible FROM config_jeu";
$res = $mysqli->query($sql);
$t_dispo = $res->fetch_assoc();

$dispo = $t_dispo["disponible"];

if($dispo){

	if (@$_SESSION["id_perso"]) {
		//recuperation des variables de sessions
		$id = $_SESSION["id_perso"];
		$sql = "SELECT pv_perso, a_gele, est_gele, nom_perso FROM perso WHERE id_perso='$id'";
		$res = $mysqli->query($sql);
		$tpv = $res->fetch_assoc();
		
		$testpv = $tpv['pv_perso'];
		$a_g = $tpv['a_gele'];
		$e_g = $tpv['est_gele'];
		$pseudo_p = $tpv['nom_perso'];
		
		$mess = "";
		
		if($e_g){
			// redirection
			header("location:../tour.php");
		}
		else {
			if(isset($_GET["gele"]) && $_GET["gele"] == "ok"){
				if($a_g){
					echo "<font color=red>Vous avez déjà demandé de geler votre perso, le gel sera effectif à minuit</font><br />";
				}
				else {
					$date_gele = time();
					
					// maj du perso => statut en gele
					$sql = "UPDATE perso SET a_gele='1', date_gele=FROM_UNIXTIME($date_gele) WHERE id_perso='$id'";
					$mysqli->query($sql);
					
					// redirection vers la page d'accueil
					header("location:../logout.php");
				}
			}
			
			if ($testpv <= 0) {
				echo "<font color=red>Vous êtes mort...</font>";
			}
			else {
				$sql= "SELECT idJoueur_perso FROM perso WHERE id_perso ='".$id."'";
				$res = $mysqli->query($sql);
				$t = $res->fetch_assoc();
				
				$id_joueur = $t["idJoueur_perso"];
				
				$sql = "SELECT mdp_joueur FROM joueur WHERE id_joueur ='".$id_joueur."'";
				$res = $mysqli->query($sql);
				$tabAttr = $res->fetch_assoc();
				
				$mdp_joueur = $tabAttr["mdp_joueur"];
	
				if (isSet($_POST['eval_compte']) == "Enregistrer")
				{	
					if (isset($_POST['verif_mdp']) && $mdp_joueur == md5($_POST['verif_mdp']))
					{
						if ($_POST['email_change'] != "" )
						{	
							if (!filtremail($_POST['email_change'])){
								$mess = "<div class=\"erreur\">email incorrect</div>";
							}
							else {
								$email = $_POST['email_change'];
								$sql = "UPDATE joueur SET email_joueur='$email' WHERE id_joueur ='".$id_joueur."'";
								$mysqli->query($sql);
							}
						}
						if (isset($_POST['age_change']) && $_POST['age_change'] != "")
						{
							$age = $_POST['age_change'];
							$sql = "UPDATE joueur SET age_joueur='$age' WHERE id_joueur ='".$id_joueur."'";
							$mysqli->query($sql);
						}
						if (isset($_POST['pays_change']) && $_POST['pays_change'] != "")
						{
							$pays = $_POST['pays_change'];
							$sql = "UPDATE joueur SET pays_joueur='$pays' WHERE id_joueur ='".$id_joueur."'";
							$mysqli->query($sql);
						}
						if (isset($_POST['region_change']) && $_POST['region_change'] != "")
						{
							$region = $_POST['region_change'];
							$sql = "UPDATE joueur SET region_joueur='$region' WHERE id_joueur ='".$id_joueur."'";
							$mysqli->query($sql);
						}
						if (isset($_POST['mdp_change']) && $_POST['mdp_change'] != "" )
						{
							$mdp = md5($_POST['mdp_change']);
							$sql = "UPDATE joueur SET mdp_joueur='$mdp' WHERE id_joueur ='".$id_joueur."'";
							$mysqli->query($sql);
							
							$sql = "UPDATE pun_users SET password='$mdp' WHERE username ='".$pseudo_p."'";
							$mysqli->query($sql);
							
							$mess =  "<div class=\"info\">Mot de passe chang&eacute;</div>";
						}
						if (isset($_POST['mail_info'])){
							$statut = $_POST['mail_info'];
							if($statut == 'on'){
								$sql = "UPDATE joueur SET mail_info='1' WHERE id_joueur ='".$id_joueur."'";
								$mysqli->query($sql);
							}
						}
						else {
							$sql = "UPDATE joueur SET mail_info='0' WHERE id_joueur ='".$id_joueur."'";
							$mysqli->query($sql);
						}
					}
					else {
						$mess = "<div class=\"erreur\">mot de passe incorrect : Veuillez entrer votre mot de passe</div>";
					}
				}
				
				//recup infos			
				$sql = "SELECT * FROM joueur WHERE id_joueur ='".$id_joueur."'";
				$res = $mysqli->query($sql);
				$tabAttr = $res->fetch_assoc();
				
				$nom_joueur = $tabAttr["nom_joueur"];
				$email_joueur = $tabAttr["email_joueur"];
				$mdp_joueur = $tabAttr["mdp_joueur"];
				$age_joueur = $tabAttr["age_joueur"];
				$pays_joueur = $tabAttr["pays_joueur"];
				$region_joueur = $tabAttr["region_joueur"];
				$description_joueur = $tabAttr["description_joueur"];
				$mail_info_joueur = $tabAttr["mail_info"];
	?>
	<html>
	<head>
	  <title>Nord VS Sud</title>
	  <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
	   <meta http-equiv="Content-Language" content="fr" />
	  <link rel="stylesheet" type="text/css" media="screen" href="onglet.css" title="Version 1" />
	
	</head>
	<body>
	<div id="header">
	  <ul>
		<li><a href="profil.php">Profil</a></li>
		<li><a href="ameliorer.php">Améliorer son perso</a></li>
		<li><a href="equipement.php">Equiper son perso</a></li>
		<li id="current"><a href="#">Gérer son Compte</a></li>
	  </ul>
	</div>
	<br /><br /><br /><br />
	<div align=center><input type="button" value="Fermer cette fenêtre" onclick="window.close()"></div>
	<br />
	<center><td align="center"><a href="compte.php?gele=ok" OnClick="return(confirm('êtes vous sûr de vouloir geler votre perso ?'))">Geler son compte</a></center>
	<center><b><font color='red'>Veuillez taper votre mot de passe pour changer vos informations</font></b></center>
	<table class="tab_erreur"><tr><td><?php echo $mess; ?></td></tr></table>
	<form method="post" action="compte.php">
	<table border="1" width='100%'>
	<tr>
		<td>Mot de passe : </td><td><label>Tapez votre mot de passe : <input type="text" name="verif_mdp" value="" size="20" maxlength="20" ></label></td>
		<td><label>Nouveau mot de passe : <input type="text" name="mdp_change" value="" size="20" maxlength="20" ></label></td>
	</tr>
	</table>
	<br>
	<table border="1" width='100%'>
	  <tr>
		<td>Votre email : </td>
		<td><input type="text" name="email" value="<?php echo $email_joueur; ?>" size="40" maxlength="40" disabled></td>
		<td><label>Changer votre email &nbsp;&nbsp;: <input type="text" name="email_change" value="" size="40" maxlength="40" ></label></td>
	  </tr>
	  <tr>
		<td>Votre âge : </td>
		<td><input type="text" name="age" value="<?php if($age_joueur != NULL) echo $age_joueur; else echo ""?>" size="3" maxlength="3" disabled></td>
		<td><label>Changer votre âge &nbsp;&nbsp;&nbsp;&nbsp;: <input type="text" name="age_change" value="" size="3" maxlength="3" ></label></td>
	  </tr>
	  <tr>
		<td>Votre pays : </td>
		<td><input type="text" name="pays" value="<?php if($pays_joueur != NULL) echo $pays_joueur; else echo "";?>" size="40" maxlength="40" disabled></td>
		<td><label>Changer votre pays &nbsp;&nbsp;: <input type="text" name="pays_change" value="" size="40" maxlength="40" ></label></td>
	  </tr>
	  <tr>
		<td>Votre région : </td>
		<td><input type="text" name="region" value="<?php if($region_joueur != NULL){ echo "$region_joueur";} else{ echo "";}?>" size="40" maxlength="40" disabled></td>
		<td><label>Changer votre région : <input type="text" name="region_change" value="" size="40" maxlength="40" ></label></td>
	  </tr>
	</table>
	<table>
		<tr>
			<td><input type='checkbox' name='mail_info' <?php if($mail_info_joueur) echo 'checked';?> /> Recevoir un mail si on m'attaque</td>
		</tr>
	</table>
	<input type="submit" name="eval_compte" value="Enregistrer">
	</form>
	<?php		
			}
		}
	}
	else{
		echo "<font color=red>Vous ne pouvez pas accéder à cette page, veuillez vous loguer.</font>";
	}
	?>
	</body>
	</html>
<?php
}
else {
	// logout
	$_SESSION = array(); // On ecrase le tableau de session
	session_destroy(); // On detruit la session
	
	header("Location: index2.php");
}
?>