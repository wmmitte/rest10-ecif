<?php

/** init the session * */
session_name('SessSngS');
session_start();

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept');

define("QLM", 20);
define("RECENT_ITEM_LENGTH", 100);

/** definition ds constantes */
if (isset($_SESSION['userMag']) && $_SESSION['userMag'] > 0) {

    define("CONCERNED_BTQ", $_SESSION['userMag']);
    define("BTQ_USERS", " in (SELECT id_user FROM t_user WHERE mag_user=" . CONCERNED_BTQ . ")");

    define("BTQ_VNT", " AND f.mag_fact=" . CONCERNED_BTQ . "");
    define("BTQ_DEP", " AND user_dep " . BTQ_USERS . "");
    define("BTQ_CRED", " AND fv.mag_fact=" . CONCERNED_BTQ . "");
    define("BTQ_DET", " AND ap.user_appro " . BTQ_USERS . "");
    define("BTQ_VERS", " AND vrs.caissier_vrsmnt " . BTQ_USERS . "");
    define("BTQ_REGC", " AND fv.caissier_fact " . BTQ_USERS . "");


    define("BTQ_CAIS_REM", " AND f.mag_fact=" . CONCERNED_BTQ . "");
    define("BTQ_CAIS_REGC", " AND cr.caissier_crce_clnt " . BTQ_USERS . "");
    define("BTQ_CAIS_PROV", " AND cais.user_cais " . BTQ_USERS . "");
    define("BTQ_CAIS_REGF", " AND df.caissier_dette_frns " . BTQ_USERS . "");
    define("BTQ_CAIS_VERS", " AND vrs.caissier_vrsmnt " . BTQ_USERS . "");
    define("BTQ_CAIS_DEP", " AND d.user_dep " . BTQ_USERS . "");
    define("BTQ_CAIS_CPT", " AND f.mag_fact=" . CONCERNED_BTQ . "");
} else {
    define("CONCERNED_BTQ", " ");
    define("BTQ_USERS", " ");

    define("BTQ_VNT", " ");
    define("BTQ_DEP", " ");
    define("BTQ_CRED", " ");
    define("BTQ_DET", " ");
    define("BTQ_VERS", " ");
    define("BTQ_REGC", " ");

    define("BTQ_CAIS_REM", " ");
    define("BTQ_CAIS_REGC", " ");
    define("BTQ_CAIS_PROV", " ");
    define("BTQ_CAIS_REGF", " ");
    define("BTQ_CAIS_VERS", " ");
    define("BTQ_CAIS_DEP", " ");
    define("BTQ_CAIS_CPT", " ");
}


/* inclusion de l'api slim et autres */
include 'includes/classes.php';
require 'api/Slim/Slim.php';
\Slim\Slim::registerAutoloader();

/* creation d'une nouvelel instance de la classe slim */
$app = new \Slim\Slim(array('debug' => true));

//activation du deboguage
$app->config('debug', true);


/* definitions des routes */
$app->post('/doLogin/', 'login');
$app->post('/caisse/', 'getCaisseJour');
$app->post('/caisseDate/', 'getCaisseDate');
$app->get('/doLogout', 'logout');

$app->get('/getaClients', 'getaClients');
$app->get('/getFournisseurs', 'getFournisseurs');
$app->get('/getUsers', 'getUsers');

$app->get('/getTypeDepenses', 'getTypeDepenses');

/*
 * ======= VENTES 
 */
$app->get('/ventesJour', 'getVentesJour');
$app->get('/ventesJourofs/:ofs', 'getVentesJourofs');


$app->post('/factures/', 'getFactures');
$app->post('/facture/undo/', 'undoFacture');
/*
 * ======= Bons 
 */
$app->post('/bons/', 'getBons');
$app->get('/bon/undo/:idfact', 'undoBon');


/*
 * ======= REGELEMENTS 
 */
$app->get('/getReglementsClientsRecents', 'getReglementsClientsRecents');


/*
 * ======= DEPENSSES 
 */

$app->get('/depensesJour', 'getDepensesJour');
$app->get('/depensesofs/:ofs', 'getDepensesofs');
$app->post('/saveDepense/', 'saveDepense');

/*
 * ======= VERSEMENTS 
 */
$app->get('/versementsofs/:ofs', 'getVersementsofs');


/*
 * ======= ETATS CREANCES 
 */
$app->get('/creances', 'getCreances');
$app->get('/creancesofs/:ofs', 'getCreancesofs');
$app->post('/searchcreances/', 'searchCreances');
$app->get('/getFactureDetails/:id', 'getFactureDetails');

/*
 * ======= ETATS DETTES  
 */

$app->get('/dettes', 'getDettes');
$app->post('/searchdettes/', 'searchDettes');
$app->get('/dettesofs/:ofs', 'getDettesofs');

/*
 * ======= MAGASINS 
 */

$app->get('/getMagasins', 'getMagasins');


/*
 * ======= ARTICLES 
 */

$app->get('/getArticles', 'getArticles');

/*
 * ======= CATEGORIES ARTICLES 
 */

$app->get('/getCategories', 'getCategories');

/*
 * ======= STOCK 
 */
$app->get('/etatstockofs/:ofs', 'getEtatstockofs');
$app->post('/searchetatstock/', 'searchEtatstock');
$app->post('/queryetatstock/', 'queryEtatstock');
$app->get('/approvisionnementArticles/:id', 'getApprovisionnementArticles');

// Sortie de stock
$app->post('/queryetatsortie/', 'queryEtatSortie');
$app->post('/querysorties/', 'querySorties');
$app->post('/queryarticlesorties/', 'queryArticleSorties');
$app->post('/insertsortie/', 'insertSortie');
$app->post('/insertstocksortie/', 'insertStockSortie');

// Articles
$app->post('/queryextcategoriesofmag/', 'getExtCategoriesOfMag');
$app->post('/queryextarticlesofcategorie/', 'getExtArticlesOfCategorie');

// Magasins
$app->post('/queryexceptedmagasins/', 'queryExceptedMagasins');

$app->get('/etatalerteofs/:ofs', 'getEtatAlerteofs');



/* execution de l'application */
$app->run();



/*
 * =============================================================================
 * =========== AUTHENTIFICATION ================================================
 * =============================================================================
 */

function login() {

    /* recuperation des variables */
    $request = \Slim\Slim::getInstance()->request();
    $update = json_decode($request->getBody(), true);
   
    /* class instance */
    $AuthM = new authentificationModel();

    /* begin */
    try {

        $result = $AuthM->login($update['login'], $update['password']);


        $response = array("datas" => $result);
        echo json_encode($response);
    } catch (Exception $e) {
        $response = array("status" => 0,
            "error" => $e->getMessage());
        echo json_encode($response);
    }
}

function logout() {

    /* class instance */
    $AuthM = new authentificationModel();

    /* begin */
    try {

        $result = $AuthM->logout();

        $response = array("datas" => $result);
        echo json_encode($response);
    } catch (Exception $e) {
        $response = array("status" => 0,
            "error" => $e->getMessage());
        echo json_encode($response);
    }
}

/*
 * =============================================================================
 * =========== STOCK ===========================================================
 * =============================================================================
 */

function getApprovisionnementArticles($id) {

    /* Variables */

    /* class instance */
    $StkM = new stockModel();

    /* begin */
    try {

        $result = $StkM->getApprovisionnementArticles($id);

        $response = array("status" => 0,
            "datas" => $result,
            "msg" => "");
        echo json_encode($response);
    } catch (Exception $e) {
        $response = array("status" => 0,
            "error" => $e->getMessage());
        echo json_encode($response);
    }
}

function queryEtatstock() {
    $request = \Slim\Slim::getInstance()->request();
    $_PObj = json_decode($request->getBody(), true);

    /* Variables */

    /* class instance */
    $StkM = new stockModel();

    /* begin */
    try {

        $result = $StkM->queryEtatStock($_PObj);

        $response = array("status" => 0,
            "datas" => $result,
            "msg" => "");
        echo json_encode($response);
    } catch (Exception $e) {
        $response = array("status" => 0,
            "error" => $e->getMessage());
        echo json_encode($response);
    }
}

function querySorties() {
    $request = \Slim\Slim::getInstance()->request();
    $_PObj = json_decode($request->getBody(), true);

    /* Variables */

    /* class instance */
    $StkM = new sortieModel();

    /* begin */
    try {

        $result = $StkM->getSorties($_PObj);

        $response = array("status" => 0,
            "datas" => $result,
            "msg" => "");
        echo json_encode($response);
    } catch (Exception $e) {
        $response = array("status" => 0,
            "error" => $e->getMessage());
        echo json_encode($response);
    }
}

function queryArticleSorties() {
    $request = \Slim\Slim::getInstance()->request();
    $_PObj = json_decode($request->getBody(), true);

    /* Variables */

    /* class instance */
    $StkM = new sortieModel();

    /* begin */
    try {

        $result = $StkM->getArticleSorties($_PObj);

        $response = array("status" => 0,
            "datas" => $result,
            "msg" => "");
        echo json_encode($response);
    } catch (Exception $e) {
        $response = array("status" => 0,
            "error" => $e->getMessage());
        echo json_encode($response);
    }
}

function queryEtatSortie() {
    $request = \Slim\Slim::getInstance()->request();
    $_PObj = json_decode($request->getBody(), true);

    /* Variables */

    /* class instance */
    $StkM = new sortieModel();

    /* begin */
    try {

        $result = $StkM->getaSorties($_PObj);

        $response = array("status" => 0,
            "datas" => $result,
            "msg" => "");
        echo json_encode($response);
    } catch (Exception $e) {
        $response = array("status" => 0,
            "error" => $e->getMessage());
        echo json_encode($response);
    }
}

function insertSortie() {
    $request = \Slim\Slim::getInstance()->request();
    $_PObj = json_decode($request->getBody(), true);

    /* Variables */

    /* class instance */
    $StkM = new sortieModel();

    /* begin */
    try {

        $result = $StkM->insertSortie($_PObj);

        $response = array("status" => 0,
            "datas" => $result,
            "msg" => "");
        echo json_encode($response);
    } catch (Exception $e) {
        $response = array("status" => 1,
            "error" => $e->getMessage());
        echo json_encode($response);
    }
}


function insertStockSortie() {
    $request = \Slim\Slim::getInstance()->request();
    $_PObj = json_decode($request->getBody(), true);

    /* Variables */

    /* class instance */
    $StkM = new sortieModel();

    /* begin */
    try {

        $result = $StkM->insertStockSort($_PObj);

        $response = array("status" => 0,
            "datas" => $result,
            "msg" => "");
        echo json_encode($response);
    } catch (Exception $e) {
        $response = array("status" => 1,
            "error" => $e->getMessage());
        echo json_encode($response);
    }
}

function getExtCategoriesOfMag() {
    $request = \Slim\Slim::getInstance()->request();
    $_PObj = json_decode($request->getBody(), true);

    /* Variables */

    /* class instance */
    $StkM = new articleModel();

    /* begin */
    try {

        $result = $StkM->getExtCategoriesOfMag($_PObj);

        $response = array("status" => 0,
            "datas" => $result,
            "msg" => "");
        echo json_encode($response);
    } catch (Exception $e) {
        $response = array("status" => 1,
            "error" => $e->getMessage());
        echo json_encode($response);
    }
}

function getExtArticlesOfCategorie() {
    $request = \Slim\Slim::getInstance()->request();
    $_PObj = json_decode($request->getBody(), true);

    /* Variables */

    /* class instance */
    $StkM = new articleModel();

    /* begin */
    try {

        $result = $StkM->getExtArticlesOfCategorie($_PObj);

        $response = array("status" => 0,
            "datas" => $result,
            "msg" => "");
        echo json_encode($response);
    } catch (Exception $e) {
        $response = array("status" => 1,
            "error" => $e->getMessage());
        echo json_encode($response);
    }
}

function queryExceptedMagasins() {
    $request = \Slim\Slim::getInstance()->request();
    $_PObj = json_decode($request->getBody(), true);

    /* Variables */

    /* class instance */
    $StkM = new magasinModel();

    /* begin */
    try {

        $result = $StkM->getaSorties($_PObj);

        $response = array("status" => 0,
            "datas" => $result,
            "msg" => "");
        echo json_encode($response);
    } catch (Exception $e) {
        $response = array("status" => 0,
            "error" => $e->getMessage());
        echo json_encode($response);
    }
}

function getEtatstockofs($offset) {

    /* Variables */

    /* class instance */
    $StkM = new stockModel();

    /* begin */
    try {

        $result = $StkM->etatStocklm($offset);

        $response = array("status" => 0,
            "datas" => $result,
            "msg" => "");
        echo json_encode($response);
    } catch (Exception $e) {
        $response = array("status" => 0,
            "error" => $e->getMessage());
        echo json_encode($response);
    }
}

function searchEtatstock() {

    $request = \Slim\Slim::getInstance()->request();
    $search = json_decode($request->getBody(), true);

    /* class instance */
    $StkM = new stockModel();

    /* begin */
    try {

        $result = $StkM->etatStock($search);

        $response = array("status" => 0,
            "datas" => $result,
            "msg" => "");
        echo json_encode($response);
    } catch (Exception $e) {
        $response = array("status" => 0,
            "error" => $e->getMessage());
        echo json_encode($response);
    }
}

function getEtatAlerteofs($offset) {

    /* Variables */

    /* class instance */
    $StkM = new stockModel();

    /* begin */
    try {

        $result = $StkM->etatAlertelm($offset);

        $response = array("status" => 0,
            "datas" => $result,
            "msg" => "");
        echo json_encode($response);
    } catch (Exception $e) {
        $response = array("status" => 0,
            "error" => $e->getMessage());
        echo json_encode($response);
    }
}

/*
 * =============================================================================
 * =========== VENTES ==========================================================
 * =============================================================================
 */

function getVentesJour() {

    /* Variables */

    /* class instance */
    $VntM = new venteModel();

    /* begin */
    try {

        $result = $VntM->getVentesJour();

        $response = array("status" => 0,
            "datas" => $result,
            "msg" => "");
        echo json_encode($response);
    } catch (Exception $e) {
        $response = array(
            "status" => 0,
            "session" => $_SESSION,
            "error" => $e->getMessage());
        echo json_encode($response);
    }
}

function getVentesJourofs($ofs) {

    /* Variables */

    /* class instance */
    $VntM = new venteModel();

    /* begin */
    try {

        $result = $VntM->getVentesJourofs($ofs);

        $response = array("status" => 0,
            "datas" => $result,
            "msg" => "");
        echo json_encode($response);
    } catch (Exception $e) {
        $response = array("status" => 0,
            "error" => $e->getMessage());
        echo json_encode($response);
    }
}

function getFactures() {
    $request = \Slim\Slim::getInstance()->request();
    $_PObj = json_decode($request->getBody(), true);

   // $search = $_PObj['search'];


    /* Variables */

    /* class instance */
    $Model = new factureModel();

    /* begin */
    try {

        $result = $Model->getFacturesOf($_PObj);

        $response = array("status" => 0,
            "datas" => $result,
            "msg" => "");
        echo json_encode($response);
    } catch (Exception $e) {
        $response = array("status" => 0,
            "error" => $e->getMessage());
        echo json_encode($response);
    }
}

function undoFacture() {

    /* Variables */
$request = \Slim\Slim::getInstance()->request();
    $_PObj = json_decode($request->getBody(), true);

    /* class instance */
    $Model = new factureModel();

    /* begin */
    try {

        $result = $Model->undoFacture($_PObj);

        $response = array("status" => 0,
            "datas" => $result,
            "msg" => "");
        echo json_encode($response);
    } catch (Exception $e) {
        $response = array("status" => 0,
            "error" => $e->getMessage());
        echo json_encode($response);
    }
}

/*
 * =============================================================================
 * =========== BONS ======================================================
 * =============================================================================
 */
function getBons() {
    $request = \Slim\Slim::getInstance()->request();
    $_PObj = json_decode($request->getBody(), true);

    $search = $_PObj['search'];


    /* Variables */

    /* class instance */
    $Model = new bonModel();

    /* begin */
    try {

        $result = $Model->getBonsOf($search);

        $response = array("status" => 0,
            "datas" => $result,
            "msg" => "");
        echo json_encode($response);
    } catch (Exception $e) {
        $response = array("status" => 0,
            "error" => $e->getMessage());
        echo json_encode($response);
    }
}

function undoBon($idfact) {


    /* Variables */

    /* class instance */
    $Model = new bonModel();

    /* begin */
    try {

        $result = $Model->undoBon($idfact);

        $response = array("status" => 0,
            "datas" => $result,
            "msg" => "");
        echo json_encode($response);
    } catch (Exception $e) {
        $response = array("status" => 0,
            "error" => $e->getMessage());
        echo json_encode($response);
    }
}

/*
 * =============================================================================
 * =========== REGLEMENTS ======================================================
 * =============================================================================
 */

function getReglementsClientsRecents() {

    /* Variables */

    /* class instance */
    $RegM = new reglementModel();

    /* begin */
    try {

        $result = $RegM->getReglementsClientsRecents();

        $response = array("status" => 0,
            "datas" => $result,
            "msg" => "");
        echo json_encode($response);
    } catch (Exception $e) {
        $response = array("status" => 0,
            "error" => $e->getMessage());
        echo json_encode($response);
    }
}

/*
 * =============================================================================
 * =========== SORTIE CAISSE ===================================================
 * =============================================================================
 */

function getDepensesJour() {

    /* Variables */

    /* class instance */
    $DecM = new decaissementModel();

    /* begin */
    try {

        $result = $DecM->getDepensesJour();

        $response = array("status" => 0,
            "datas" => $result,
            "msg" => "");
        echo json_encode($response);
    } catch (Exception $e) {
        $response = array("status" => 0,
            "error" => $e->getMessage());
        echo json_encode($response);
    }
}

function getDepensesofs($ofs) {

    /* Variables */

    /* class instance */
    $DecM = new decaissementModel();

    /* begin */
    try {

        $result = $DecM->getDepensesofs($ofs);


        $response = array("status" => 0,
            "datas" => $result,
            "msg" => "");
        echo json_encode($response);
    } catch (Exception $e) {
        $response = array("status" => 0,
            "error" => $e->getMessage());
        echo json_encode($response);
    }
}

function saveDepense() {

    $request = \Slim\Slim::getInstance()->request();
    $objec = json_decode($request->getBody(), true);

    /* class instance */
    $DecM = new decaissementModel();

    /* begin */
    try {

        $result = $DecM->saveDepense($objec);

        $response = array("status" => 0,
            "datas" => $result,
            "msg" => "");
        echo json_encode($response);
    } catch (Exception $e) {
        $response = array("status" => 0,
            "error" => $e->getMessage());
        echo json_encode($response);
    }
}

function getVersementsofs($ofs) {

    /* Variables */

    /* class instance */
    $DecM = new decaissementModel();

    /* begin */
    try {

        $result = $DecM->getVersementsofs($ofs);

        $response = array("status" => 0,
            "datas" => $result,
            "msg" => "");
        echo json_encode($response);
    } catch (Exception $e) {
        $response = array("status" => 0,
            "error" => $e->getMessage());
        echo json_encode($response);
    }
}

/*
 * =============================================================================
 * =========== ETATS ===========================================================
 * =============================================================================
 */

function getCreances() {

    /* Variables */

    /* class instance */
    $EtaM = new etatModel();

    /* begin */
    try {

        $result = $EtaM->getEtatCreances();

        $response = array("status" => 0,
            "datas" => $result,
            "msg" => "");
        echo json_encode($response);
    } catch (Exception $e) {
        $response = array("status" => 0,
            "error" => $e->getMessage());
        echo json_encode($response);
    }
}

function getCreancesofs($ofs) {

    /* Variables */

    /* class instance */
    $EtaM = new etatModel();

    /* begin */
    try {

        $result = $EtaM->getEtatCreancesofs($ofs);

        $response = array("status" => 0,
            "datas" => $result,
            "msg" => "");
        echo json_encode($response);
    } catch (Exception $e) {
        $response = array("status" => 0,
            "error" => $e->getMessage());
        echo json_encode($response);
    }
}

function searchCreances() {
    $request = \Slim\Slim::getInstance()->request();
    $search = json_decode($request->getBody(), true);
    /* Variables */

    /* class instance */
    $EtaM = new etatModel();

    /* begin */
    try {

        $result = $EtaM->getSearchEtatCreances($search);

        $response = array("status" => 0,
            "datas" => $result,
            "msg" => "");
        echo json_encode($response);
    } catch (Exception $e) {
        $response = array("status" => 0,
            "error" => $e->getMessage());
        echo json_encode($response);
    }
}

function getFactureDetails($id) {

    /* Variables */

    /* class instance */
    $EtaM = new etatModel();

    /* begin */
    try {

        $result = $EtaM->getFactureDetails($id);

        $response = array("status" => 0,
            "datas" => $result,
            "msg" => "");
        echo json_encode($response);
    } catch (Exception $e) {
        $response = array("status" => 0,
            "error" => $e->getMessage());
        echo json_encode($response);
    }
}

function searchDettes() {
    $request = \Slim\Slim::getInstance()->request();
    $search = json_decode($request->getBody(), true);
    /* Variables */

    /* class instance */
    $EtaM = new etatModel();

    /* begin */
    try {

        $result = $EtaM->getSearchEtatDettes($search);

        $response = array("status" => 0,
            "datas" => $result,
            "msg" => "");
        echo json_encode($response);
    } catch (Exception $e) {
        $response = array("status" => 0,
            "error" => $e->getMessage());
        echo json_encode($response);
    }
}

function getDettes() {

    /* Variables */

    /* class instance */
    $EtaM = new etatModel();

    /* begin */
    try {

        $result = $EtaM->getEtatDettes();

        $response = array("status" => 0,
            "datas" => $result,
            "msg" => "");
        echo json_encode($response);
    } catch (Exception $e) {
        $response = array("status" => 0,
            "error" => $e->getMessage());
        echo json_encode($response);
    }
}

function getDettesofs($ofs) {

    /* Variables */

    /* class instance */
    $EtaM = new etatModel();

    /* begin */
    try {

        $result = $EtaM->getEtatDettesofs($ofs);

        $response = array("status" => 0,
            "datas" => $result,
            "msg" => "");
        echo json_encode($response);
    } catch (Exception $e) {
        $response = array("status" => 0,
            "error" => $e->getMessage());
        echo json_encode($response);
    }
}

function getCaisseJour() {

    /* recuperation des variables */
    $request = \Slim\Slim::getInstance()->request();
    $search = json_decode($request->getBody(), true);

    /* class instance */
    $EtaM = new etatModel();

    /* begin */
    try {

        $result = $EtaM->getEtatCaisseJour($search);

        $response = array("status" => 0,
            "datas" => $result,
            "msg" => "");
        echo json_encode($response);
    } catch (Exception $e) {
        $response = array("status" => 0,
            "error" => $e->getMessage());
        echo json_encode($response);
    }
}

function getCaisseDate() {

    /* recuperation des variables */
    $request = \Slim\Slim::getInstance()->request();
    $search = json_decode($request->getBody(), true);

    /* class instance */
    $EtaM = new etatModel();

    /* begin */
    try {

        $result = $EtaM->getEtatCaisseDate($search);

        $response = array("status" => 0,
            "datas" => $result,
            "msg" => "");
        echo json_encode($response);
    } catch (Exception $e) {
        $response = array("status" => 0,
            "error" => $e->getMessage());
        echo json_encode($response);
    }
}

/*
 * =============================================================================
 * =========== RECHERCHES AVANCEES ===========================================================
 * =============================================================================
 */

function getaClients() {

    /* Variables */

    /* class instance */
    $RechDM = new rechercheDataModel();

    /* begin */
    try {

        $result = $RechDM->getaClients();

        $response = array("status" => 0,
            "datas" => $result,
            "msg" => "");
        echo json_encode($response);
    } catch (Exception $e) {
        $response = array("status" => 0,
            "error" => $e->getMessage());
        echo json_encode($response);
    }
}

function getFournisseurs() {

    /* Variables */

    /* class instance */
    $RechDM = new rechercheDataModel();

    /* begin */
    try {

        $result = $RechDM->getFournisseurs();

        $response = array("status" => 0,
            "datas" => $result,
            "msg" => "");
        echo json_encode($response);
    } catch (Exception $e) {
        $response = array("status" => 0,
            "error" => $e->getMessage());
        echo json_encode($response);
    }
}

function getUsers() {

    /* Variables */

    /* class instance */
    $RechDM = new rechercheDataModel();

    /* begin */
    try {

        $result = $RechDM->getUsers();

        $response = array("status" => 0,
            "datas" => $result,
            "msg" => "");
        echo json_encode($response);
    } catch (Exception $e) {
        $response = array("status" => 0,
            "error" => $e->getMessage());
        echo json_encode($response);
    }
}

function getMagasins() {

    /* Variables */

    /* class instance */
    $RechDM = new rechercheDataModel();

    /* begin */
    try {

        $result = $RechDM->getMagasins();

        $response = array("status" => 0,
            "datas" => $result,
            "msg" => "");
        echo json_encode($response);
    } catch (Exception $e) {
        $response = array("status" => 0,
            "error" => $e->getMessage());
        echo json_encode($response);
    }
}

function getArticles() {

    /* Variables */

    /* class instance */
    $RechDM = new rechercheDataModel();

    /* begin */
    try {

        $result = $RechDM->getArticles();

        $response = array("status" => 0,
            "datas" => $result,
            "msg" => "");
        echo json_encode($response);
    } catch (Exception $e) {
        $response = array("status" => 0,
            "error" => $e->getMessage());
        echo json_encode($response);
    }
}

function getCategories() {

    /* Variables */

    /* class instance */
    $RechDM = new rechercheDataModel();

    /* begin */
    try {

        $result = $RechDM->getCategories();

        $response = array("status" => 0,
            "datas" => $result,
            "msg" => "");
        echo json_encode($response);
    } catch (Exception $e) {
        $response = array("status" => 0,
            "error" => $e->getMessage());
        echo json_encode($response);
    }
}

function getTypeDepenses() {

    /* Variables */

    /* class instance */
    $RechDM = new rechercheDataModel();

    /* begin */
    try {

        $result = $RechDM->getTypeDepenses();

        $response = array("status" => 0,
            "datas" => $result,
            "msg" => "");
        echo json_encode($response);
    } catch (Exception $e) {
        $response = array("status" => 0,
            "error" => $e->getMessage());
        echo json_encode($response);
    }
}

?>