<?php
// Service web du projet Réservations M2L
// Ecrit le 13/10/2015 par Roger Alban

// Ce service web permet à un utilisateur autorisé de demander son mot de passe
// et fournit un flux XML contenant un compte-rendu d'exécution

// Le service web doit recevoir 1 paramètre : nom
// Les paramètres peuvent être passés par la méthode GET (pratique pour les tests, mais à éviter en exploitation) :
//     http://<hébergeur>/ConsulterReservations.php?nom=zenelsy&mdp=passe
// Les paramètres peuvent être passés par la méthode POST (à privilégier en exploitation pour la confidentialité des données) :
//     http://<hébergeur>/ConsulterReservations.php

// déclaration des variables globales pour pouvoir les utiliser aussi dans les fonctions
global $doc;		// le document XML à générer
global $nom, $lesReservations, $nbReponses;

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
$elt_commentaire = $doc->createComment('Service web ConfirmerReservation - BTS SIO - Lycée De La Salle - Rennes');
// place ce commentaire à la racine du document XML
$doc->appendChild($elt_commentaire);

// Récupération des données transmises
// la fonction $_GET récupère une donnée passée en paramètre dans l'URL par la méthode GET
if ( empty ($_GET ["nom"]) == true)  $nom = "";  else   $nom = $_GET ["nom"];

// si l'URL ne contient pas les données, on regarde si elles ont été envoyées par la méthode POST
// la fonction $_POST récupère une donnée envoyées par la méthode POST
if ( $nom == "")
{	
	if ( empty ($_POST ["nom"]) == true)  $nom = "";  else   $nom = $_POST ["nom"];
}

// Contrôle de la présence des paramètres
if ( $nom == "")
{	
	TraitementAnormal ("Erreur : données incomplètes.");
}
else
{	// connexion du serveur web à la base MySQL ("include_once" peut être remplacé par "require_once")
	include_once ('../modele/DAO.class.php');
	$dao = new DAO();
	// Controle de la présence de l'utilisateur
	if ($dao->getUtilisateur($nom) == null){
		TraitementAnormal ("Erreur : nom d'utilisateur inexistant.");
	}
	else{
		TraitementNormal($nom);
	}
	// ferme la connexion à MySQL
	unset($dao);
}

// Mise en forme finale
$doc->formatOutput = true;
// renvoie le contenu XML
echo $doc->saveXML();
// fin du programme
exit;
	
// fonction de traitement des cas anormaux
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

// fonction de traitement des cas normaux
function TraitementNormal($user)
{	// redéclaration des données globales utilisées dans la fonction
	global $doc;
	global $dao;
	global $ADR_MAIL_EMETTEUR;
	
	$email = $dao->getUtilisateur($user)->getEmail();
	// envoie un mail de confirmation de l'enregistrement
	$sujet = "Mot de passe M2L";
	$message = "Votre mot de passe est : " .$dao->getUtilisateur($user)->getPassword();

	$ok = Outils::envoyerMail ($email, $sujet, $message, $ADR_MAIL_EMETTEUR);
	if ( $ok )
		$msg = "Vous allez recevoir un mail avec votre nouveau mot de passe.";
	else
		$msg = "L'envoi de mail a rencontré un problème.";
	
	// crée l'élément 'data' à la racine du document XML
	$elt_data = $doc->createElement('data');
	$doc->appendChild($elt_data);
	// place l'élément 'reponse' juste après l'élément 'data'
	$elt_reponse = $doc->createElement('reponse', $msg);
	$elt_data->appendChild($elt_reponse);
	//echo $msg;
	return;
}
?>