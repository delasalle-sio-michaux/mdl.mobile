<?php
// Service web du projet R�servations M2L
// Ecrit le 24/10/2015 par Roger Alban

// Ce service web permet � un administrateur de supprimer un utilisateur

// Le service web doit recevoir 3 param�tres : nomAdmin, mdpAdmin, nomUser
// Les param�tres peuvent �tre pass�s par la m�thode GET (pratique pour les tests, mais � �viter en exploitation) :
//     http://<h�bergeur>/SupprimerUtilisateur.php?nomAdmin=zenelsy&mdpAdmin=passe&nomUser=
// Les param�tres peuvent �tre pass�s par la m�thode POST (� privil�gier en exploitation pour la confidentialit� des donn�es) :
//     http://<h�bergeur>/SupprimerUtilisateur.php

// d�claration des variables globales pour pouvoir les utiliser aussi dans les fonctions

// d�claration des variables globales pour pouvoir les utiliser aussi dans les fonctions
global $doc;		// le document XML � g�n�rer
global $nomAdmin, $mdpAdmin, $nomUser;

// inclusion de la classe Outils
include_once ('../modele/Outils.class.php');
// inclusion des param�tres de l'application
include_once ('../modele/include.parametres.php');

// cr�e une instance de DOMdocument
$doc = new DOMDocument();

// specifie la version et le type d'encodage
$doc->version = '1.0';
$doc->encoding = 'ISO-8859-1';

// cr�e un commentaire et l'encode en ISO
$elt_commentaire = $doc->createComment('Service web SupprimerUtilisateur - BTS SIO - Lycée De La Salle - Rennes');
// place ce commentaire � la racine du document XML
$doc->appendChild($elt_commentaire);

// R�cup�ration des donn�es transmises
// la fonction $_GET r�cup�re une donn�e pass�e en param�tre dans l'URL par la m�thode GET
if ( empty ($_GET ["nomAdmin"]) == true)  $nomAdmin = "";  else   $nomAdmin = $_GET ["nomAdmin"];
if ( empty ($_GET ["mdpAdmin"]) == true)  $mdpAdmin = "";  else   $mdpAdmin = $_GET ["mdpAdmin"];
if ( empty ($_GET ["nomUser"]) == true)  $nomUser = "";  else   $nomUser = $_GET ["nomUser"];
// si l'URL ne contient pas les donn�es, on regarde si elles ont �t� envoy�es par la m�thode POST
// la fonction $_POST r�cup�re une donn�e envoy�es par la m�thode POST
if ( $nomAdmin == "" && $mdpAdmin == "" && $nomUser == "" )
{
    if ( empty ($_POST ["nomAdmin"]) == true)  $nomAdmin = "";  else   $nomAdmin = $_POST ["nomAdmin"];
    if ( empty ($_POST ["mdpAdmin"]) == true)  $mdpAdmin = "";  else   $mdpAdmin = $_POST ["mdpAdmin"];
    if ( empty ($_POST ["nomUser"]) == true)  $nomUser = "";  else   $nomUser = $_POST ["nomUser"];
}

// Contr�le de la pr�sence des param�tres
if ($nomAdmin == "" || $mdpAdmin == "" || $nomUser == "")
{
    TraitementAnormal ("Erreur : données incomplètes.");
}
else
{	// connexion du serveur web � la base MySQL ("include_once" peut �tre remplac� par "require_once")
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
    // ferme la connexion � MySQL
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
{	// red�claration des donn�es globales utilis�es dans la fonction
    global $doc;
    // cr�e l'�l�ment 'data' � la racine du document XML
    $elt_data = $doc->createElement('data');
    $doc->appendChild($elt_data);
    // place l'�l�ment 'reponse' juste apr�s l'�l�ment 'data'
    $elt_reponse = $doc->createElement('reponse', $msg);
    $elt_data->appendChild($elt_reponse);
    return;
}

// fonction de traitement des cas normaux
function TraitementNormal($nomUser)
{	// red�claration des donn�es globales utilis�es dans la fonction
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

    // cr�e l'�l�ment 'data' � la racine du document XML
    $elt_data = $doc->createElement('data');
    $doc->appendChild($elt_data);
    // place l'�l�ment 'reponse' juste apr�s l'�l�ment 'data'
    $elt_reponse = $doc->createElement('reponse', $msg);
    $elt_data->appendChild($elt_reponse);
    //echo $msg;
    return;
}
?>