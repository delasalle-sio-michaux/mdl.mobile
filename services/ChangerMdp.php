<?php
global $doc;		// le document XML à générer
global $name, $password;

// inclusion de la classe Outils
include_once ('../modele/Outils.class.php');
// inclusion des paramètres de l'application
include_once ('../modele/include.parametres.php');

// crée une instance de DOMdocument
$doc = new DOMDocument();

// specifie la version et le type d'encodage
$doc->version = '1.0';
$doc->encoding = 'ISO-8859-1';

// crée un commentaire et l'encode en ISO
$elt_commentaire = $doc->createComment('Service web AnnulerReservation - BTS SIO - Lycée De La Salle - Rennes');
// place ce commentaire à la racine du document XML
$doc->appendChild($elt_commentaire);

if ( empty ($_GET ["nom"]) == true)  $nom = "";  else   $nom = $_GET ["nom"];
if ( empty ($_GET ["ancienMdp"]) == true)  $mdp = "";  else   $mdp = $_GET ["ancienMdp"];
if ( empty ($_GET ["nouveauMdp"]) == true)  $nouveauMdp = "";  else   $nouveauMdp = $_GET ["nouveauMdp"];
if ( empty ($_GET ["confirmationMdp"]) == true)  $confirmationMdp = "";  else   $confirmationMdp = $_GET ["confirmationMdp"];

if ( $nom == "" && $mdp == "")
{
	if ( empty ($_POST ["nom"]) == true)  $nom = "";  else   $nom = $_POST ["nom"];
	if ( empty ($_POST ["ancienMdp"]) == true)  $mdp = "";  else   $mdp = $_POST ["ancienMdp"];
	if ( empty ($_GET ["nouveauMdp"]) == true)  $nouveauMdp = "";  else   $nouveauMdp = $_GET ["nouveauMdp"];
	if ( empty ($_GET ["confirmationMdp"]) == true)  $confirmationMdp = "";  else   $confirmationMdp = $_GET ["confirmationMdp"];
}

if ( $nom == "" || $mdp == "")
{
	TraitementAnormal ("Erreur : données incomplètes.");
}
else
{
	include_once ('../modele/DAO.class.php');
	$dao = new DAO();
	
	if ( $dao->getNiveauUtilisateur($nom, $mdp) == "inconnu" )
		TraitementAnormal("Erreur : authentification incorrecte.");
	else {
		if ($nouveauMdp != $confirmationMdp) {
			TraitementAnormal("Erreur : le nouveau mot de passe et sa confirmation sont différents.");
		}
		else {
			
		}
	}
}



// Mise en forme finale
$doc->formatOutput = true;
// renvoie le contenu XML
echo $doc->saveXML();
// fin du programme
exit;


function TraitementAnormal($msg)
{	// redéclaration des données globales utilisées dans la fonction
global $doc;
// crée l'élément 'data' à la racine du document XML
$elt_data = $doc->createElement('data');
$doc->appendChild($elt_data);
// place l'élément 'reponse' juste après l'élément 'data'
$elt_reponse = $doc->createElement('reponse', $msg);
$elt_data->appendChild($elt_reponse);
return;
}

function TraitementNormal()
{
	// redéclaration des données globales utilisées dans la fonction
	/*global $doc;
	global $name, $numRes, $password ;
	global $ADR_MAIL_EMETTEUR;

	// envoie un mail de confirmation de l'enregistrement
	$sujet = "Annulation de la réservation M2L";
	$message = "Vous venez d'annuler la réservation suivante :\n\n";
	$message .= "Votre numero de réservation : " . $numRes . "\n";

	$ok = Outils::envoyerMail ($email, $sujet, $message, $ADR_MAIL_EMETTEUR);
	if ( $ok )
		$msg = "Suppression effectuée.";
	else
		$msg = "Suppression effectuée ; l'envoi du mail à l'utilisateur a rencontré un problème.";

	// crée l'élément 'data' à la racine du document XML
	$elt_data = $doc->createElement('data');
	$doc->appendChild($elt_data);
	// place l'élément 'reponse' juste après l'élément 'data'
	$elt_reponse = $doc->createElement('reponse', $msg);
	$elt_data->appendChild($elt_reponse);
	return;*/
}

?>