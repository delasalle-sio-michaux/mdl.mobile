<?php
// Service web du projet R�servations M2L
// Ecrit le 24/10/2015 par Roger Alban

// Ce service web permet � un administrateur de consulter les salles disponibles � la r�servation
// Le service web doit recevoir 2 param�tres : nom, mdp
// Les param�tres peuvent �tre pass�s par la m�thode GET (pratique pour les tests, mais � �viter en exploitation) :
//     http://<h�bergeur>/SupprimerUtilisateur.php?nomAdmin=zenelsy&mdpAdmin=passe&nomUser=
// Les param�tres peuvent �tre pass�s par la m�thode POST (� privil�gier en exploitation pour la confidentialit� des donn�es) :
//     http://<h�bergeur>/SupprimerUtilisateur.php

// d�claration des variables globales pour pouvoir les utiliser aussi dans les fonctions

// d�claration des variables globales pour pouvoir les utiliser aussi dans les fonctions
global $doc;		// le document XML � g�n�rer
global $nom, $mdp;

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
$elt_commentaire = $doc->createComment('Service web ConsulterSalles - BTS SIO - Lycée De La Salle - Rennes');
// place ce commentaire � la racine du document XML
$doc->appendChild($elt_commentaire);

// R�cup�ration des donn�es transmises
// la fonction $_GET r�cup�re une donn�e pass�e en param�tre dans l'URL par la m�thode GET
if ( empty ($_GET ["nom"]) == true)  $nom = "";  else   $nom = $_GET ["nom"];
if ( empty ($_GET ["mdp"]) == true)  $mdp = "";  else   $mdp = $_GET ["mdp"];
// si l'URL ne contient pas les donn�es, on regarde si elles ont �t� envoy�es par la m�thode POST
// la fonction $_POST r�cup�re une donn�e envoy�es par la m�thode POST
if ( $nom == "" && $mdp == "" )
{
    if ( empty ($_POST ["nom"]) == true)  $nom = "";  else   $nom = $_POST ["nom"];
    if ( empty ($_POST ["mdp"]) == true)  $mdp = "";  else   $mdp = $_POST ["mdp"];
}

// Contr�le de la pr�sence des param�tres
if ($nom == "" || $mdp == "")
{
    TraitementAnormal ("Erreur : données incomplètes.");
}
else
{	// connexion du serveur web � la base MySQL ("include_once" peut �tre remplac� par "require_once")
    include_once ('../modele/DAO.class.php');
    $dao = new DAO();

    if($dao->getNiveauUtilisateur($nom, $mdp) == "inconnu")
        TraitementAnormal ("Erreur : authentification incorrecte.");
    else{
        TraitementNormal ();
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
function TraitementNormal()
{	// red�claration des donn�es globales utilis�es dans la fonction
    global $doc;
    global $dao;
    global $nom;

    $lesSalles = $dao->listeSalles();
    $nbSalles = count($lesSalles);

    // cr�e l'�l�ment 'data' � la racine du document XML
    $elt_data = $doc->createElement('data');
    $doc->appendChild($elt_data);
    // place l'�l�ment 'reponse' juste apr�s l'�l�ment 'data'
    $elt_reponse = $doc->createElement('reponse', "Il y a " . $nbSalles . " disponible(s) à la résevation.");
    $elt_data->appendChild($elt_reponse);

    $elt_donnees = $doc->createElement('donnees');
    $elt_data->appendChild($elt_donnees);

    foreach ($lesSalles as $uneSalle)
    {
        // cr�e un �l�ment vide 'reservation'
        $elt_salle = $doc->createElement('salle');
        // place l'�l�ment 'reservation' dans l'�l�ment 'donnees'
        $elt_donnees->appendChild($elt_salle);

        // cr�e les �l�ments enfants de l'�l�ment 'reservation'
        $elt_id         = $doc->createElement('id', utf8_encode($uneSalle->getId()));
        $elt_salle->appendChild($elt_id);
        $elt_room  = $doc->createElement('room', utf8_encode($uneSalle->getRoom_name()));
        $elt_salle->appendChild($elt_room);
        $elt_capacity = $doc->createElement('capacity', utf8_encode($uneSalle->getCapacity()));
        $elt_salle->appendChild($elt_capacity);
        $elt_area_name   = $doc->createElement('area_name', utf8_encode($uneSalle->getAeraName()));
        $elt_salle->appendChild($elt_area_name);
        $elt_area_admin_email  = $doc->createElement('area_admin_email', utf8_encode($uneSalle->getAeraAdminEmail()));
        $elt_salle->appendChild($elt_area_admin_email);
    }
    return;
}
?>