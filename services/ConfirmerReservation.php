<?php
// Service web du projet Réservations M2L
// Ecrit le 22/09/2015 par Roger Alban

// Ce service web permet à un utilisateur autorisé de confirmer une réservation provisoire
// et fournit un flux XML contenant un compte-rendu d'exécution

// Le service web doit recevoir 3 paramètres : nom, mdp, numReservation
// Les paramètres peuvent être passés par la méthode GET (pratique pour les tests, mais à éviter en exploitation) :
//     http://<hébergeur>/ConsulterReservations.php?nom=zenelsy&mdp=passe
// Les paramètres peuvent être passés par la méthode POST (à privilégier en exploitation pour la confidentialité des données) :
//     http://<hébergeur>/ConsulterReservations.php

// déclaration des variables globales pour pouvoir les utiliser aussi dans les fonctions

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
if ( empty ($_GET ["mdp"]) == true)  $mdp = "";  else   $mdp = $_GET ["mdp"];
if ( empty ($_GET ["res"]) == true)  $res = "";  else   $res = $_GET ["res"];
// si l'URL ne contient pas les données, on regarde si elles ont été envoyées par la méthode POST
// la fonction $_POST récupère une donnée envoyées par la méthode POST
if ( $nom == "" && $mdp == "" && $res == "" )
{	
	if ( empty ($_POST ["nom"]) == true)  $nom = "";  else   $nom = $_POST ["nom"];
	if ( empty ($_POST ["mdp"]) == true)  $mdp = "";  else   $mdp = $_POST ["mdp"];
	if ( empty ($_POST ["res"]) == true)  $res = "";  else   $res = $_POST ["res"];
}

// Contrôle de la présence des paramètres
if ( $nom == "" || $mdp == "" || $res == "" )
{	
	TraitementAnormal ("Erreur : données incomplètes ou incorrectes.");
}
else
{	// connexion du serveur web à la base MySQL ("include_once" peut être remplacé par "require_once")
	include_once ('../modele/DAO.class.php');
	$dao = new DAO();
	// Controle de la présence de l'utilisateur
	if ($dao->getNiveauUtilisateur($nom, $mdp) == "inconnu")
		TraitementAnormal("Erreur : authentification incorrecte.");
	else
	{	
		if ($dao->existeReservation($res) == false)
			TraitementAnormal("Erreur : numéro de réservation inexistant.");
		else{
			if ($dao->estLeCreateur($nom, $res) == false)
				TraitementAnormal("Erreur : vous n'êtes pas l'auteur de cette réservation.");
			else{
				$laReservation = $dao->getReservation($res);
				$statut = $laReservation->getStatus();
				if($statut == 0)
					TraitementAnormal("Erreur : cette réservation est déjà confirmée");
				else{
					if(gmdate("Y-m-d\TH:i:s\Z", $laReservation->getEnd_time())<now())
						TraitementAnormal("Erreur : cette réservation est déjà passée");
					else
						TraitementNormal();
				}
			}
		}
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


?>