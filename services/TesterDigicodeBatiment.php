<?php
// Service web du projet R�servations M2L
// Ecrit le 22/10/2015 par Alban

// ce service web est appel� par le lecteur de digicode situ� � l'entr�e du batiment,
// afin de tester la validit� du digicode saisi par l'utilisateur

// Le service web doit recevoir 1 param�tre : digicode
// Les param�tres peuvent �tre pass�s par la m�thode GET (pratique pour les tests, mais � �viter en exploitation) :
//     http://<h�bergeur>/TesterDigicodeSalle.php?numSalle=10&digicode=123456
// Les param�tres peuvent �tre pass�s par la m�thode POST (� privil�gier en exploitation pour la confidentialit� des donn�es) :
//     http://<h�bergeur>/TesterDigicodeSalle.php

// d�claration des variables globales pour pouvoir les utiliser aussi dans les fonctions
global $doc;		// le document XML � g�n�rer
global $dao, $digicode;

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
$elt_commentaire = $doc->createComment('Service web TesterDigicodeBatiment - BTS SIO - Lycée De La Salle - Rennes');
// place ce commentaire � la racine du document XML
$doc->appendChild($elt_commentaire);

// R�cup�ration des donn�es transmises
// la fonction $_GET r�cup�re une donn�e pass�e en param�tre dans l'URL par la m�thode GET
if ( empty ($_GET ["digicode"]) == true)  $digicode = "";  else   $digicode = $_GET ["digicode"];
// si l'URL ne contient pas les donn�es, on regarde si elles ont �t� envoy�es par la m�thode POST
// la fonction $_POST r�cup�re une donn�e envoy�es par la m�thode POST
if ($digicode == "")
{
    if ( empty ($_POST ["digicode"]) == true)  $digicode = "";  else   $digicode = $_POST ["digicode"];
}

// Contr�le de la pr�sence des param�tres
if ($digicode == "")
{
    TraitementAnormal ();	// Erreur : donn�es incompl�tes
}
else
{	// connexion du serveur web � la base MySQL ("include_once" peut �tre remplac� par "require_once")
    include_once ('../modele/DAO.class.php');
    $dao = new DAO();

    TraitementNormal();

    // ferme la connexion � MySQL :
    unset($dao);
}
// Mise en forme finale
$doc->formatOutput = true;
// renvoie le contenu XML
echo $doc->saveXML();
// fin du programme
exit;


// fonction de traitement des cas anormaux
function TraitementAnormal()
{	// red�claration des donn�es globales utilis�es dans la fonction
    global $doc;
    // cr�e l'�l�ment 'data' � la racine du document XML
    $elt_data = $doc->createElement('data');
    $doc->appendChild($elt_data);
    // place l'élément 'reponse' juste apr�s l'�l�ment 'data'
    $elt_reponse = $doc->createElement('reponse', "0");		// on n'ouvre pas la porte
    $elt_data->appendChild($elt_reponse);
    return;
}


// fonction de traitement des cas normaux
function TraitementNormal()
{	// red�claration des donn�es globales utilis�es dans la fonction
    global $doc, $dao, $digicode;
    // cr�e l'�l�ment 'data' � la racine du document XML
    $elt_data = $doc->createElement('data');
    $doc->appendChild($elt_data);
    // place l'�l�ment 'reponse' juste apr�s l'�l�ment 'data'
    $reponse = $dao->testerDigicodeBatiment($digicode);		// la fonction testerDigicodeSalle fournit "0" ou "1"
    $elt_reponse = $doc->createElement('reponse', $reponse);
    $elt_data->appendChild($elt_reponse);
    return;
}
?>
