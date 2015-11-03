<?php
// Service web du projet Réservations M2L
// Ecrit le 24/10/2015 par Roger Alban

// Ce service web permet à un administrateur de supprimer un utilisateur

// Le service web doit recevoir 3 paramètres : nomAdmin, mdpAdmin, nomUser
// Les paramètres peuvent être passés par la méthode GET (pratique pour les tests, mais à éviter en exploitation) :
//     http://<hébergeur>/SupprimerUtilisateur.php?nomAdmin=zenelsy&mdpAdmin=passe&nomUser=
// Les paramètres peuvent être passés par la méthode POST (à privilégier en exploitation pour la confidentialité des données) :
//     http://<hébergeur>/SupprimerUtilisateur.php

// déclaration des variables globales pour pouvoir les utiliser aussi dans les fonctions

// déclaration des variables globales pour pouvoir les utiliser aussi dans les fonctions
global $doc;		// le document XML à générer
global $nomAdmin, $mdpAdmin, $nomUser;

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
$elt_commentaire = $doc->createComment('Service web SupprimerUtilisateur - BTS SIO - Lycée De La Salle - Rennes');
// place ce commentaire à la racine du document XML
$doc->appendChild($elt_commentaire);

// Récupération des données transmises
// la fonction $_GET récupère une donnée passée en paramètre dans l'URL par la méthode GET
if ( empty ($_GET ["nomAdmin"]) == true)  $nomAdmin = "";  else   $nomAdmin = $_GET ["nomAdmin"];
if ( empty ($_GET ["mdpAdmin"]) == true)  $mdpAdmin = "";  else   $mdpAdmin = $_GET ["mdpAdmin"];
if ( empty ($_GET ["nomUser"]) == true)  $nomUser = "";  else   $nomUser = $_GET ["nomUser"];
// si l'URL ne contient pas les données, on regarde si elles ont été envoyées par la méthode POST
// la fonction $_POST récupère une donnée envoyées par la méthode POST
if ( $nomAdmin == "" && $mdpAdmin == "" && $nomUser == "" )
{
    if ( empty ($_POST ["nomAdmin"]) == true)  $nomAdmin = "";  else   $nomAdmin = $_POST ["nomAdmin"];
    if ( empty ($_POST ["mdpAdmin"]) == true)  $mdpAdmin = "";  else   $mdpAdmin = $_POST ["mdpAdmin"];
    if ( empty ($_POST ["nomUser"]) == true)  $nomUser = "";  else   $nomUser = $_POST ["nomUser"];
}

// Contrôle de la présence des paramètres
if ($nomAdmin == "" || $mdpAdmin == "" || $nomUser == "")
{
    TraitementAnormal ("Erreur : données incomplètes.");
}
else
{	// connexion du serveur web à la base MySQL ("include_once" peut être remplacé par "require_once")
    include_once ('../modele/DAO.class.php');
    $dao = new DAO();

    if($dao->getNiveauUtilisateur($nomAdmin, $mdpAdmin) != "administrateur")
        TraitementAnormal ("Erreur : authentification incorrecte.");
    else{
        if($dao->getUtilisateur($nomUser) == null)
            TraitementAnormal ("Erreur : nom d'utilisateur inexistant.");
        else{
            if($dao->aPasseDesReservations($nomUser))
                TraitementAnormal ("Erreur : cet utilisateur a passé des réservations à venir.");
            else{
                TraitementNormal();
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

// fonction de traitement des cas normaux
function TraitementNormal($nomUser)
{	// redéclaration des données globales utilisées dans la fonction
    global $doc;
    global $dao;
    global $ADR_MAIL_EMETTEUR;

    $email = $dao->getUtilisateur($nomUser)->getEmail();
    // envoie un mail de confirmation de l'enregistrement
    $sujet = "Suppression de votre compte M2L";
    $message = "Votre compte de la M2L a été supprimé.";

    $ok = Outils::envoyerMail ($email, $sujet, $message, $ADR_MAIL_EMETTEUR);
    if ( $ok )
        $msg = "Enregistrement effectué, vous allez recevoir un mail de confirmation.";
    else
        $msg = "Enregistrement effectué ; l'envoi du mail à l'utilisateur a rencontré un problème.";

    $dao->supprimerUtilisateur($nomUser);

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