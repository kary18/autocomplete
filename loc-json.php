<?php
require("autoload.php");

date_default_timezone_set('Europe/Paris');

// Configuration de la base de données
$dbu = new DBUtil('dbconfig.json');

try {
    $dbh = new PDO($dbu->getDBDSN(), $dbu->getDBUser(), $dbu->getDBPassword(), $dbu->getDBOptions() );
} catch (PDOException $e) {
    echo "Impossible de se connecter à la base de données: " . $e->getMessage() . "\n";
    die();
}

$commune = new CommuneModel($dbh);

$aResult = array();

$sSearch = $_GET['search'] ?? "";
if (!empty($sSearch)) {
    $sSearch = htmlentities($sSearch);

    if (intval($sSearch)>0) {
        $aResult = $commune->cp_index($sSearch);
    }
    else {
        $aResult = $commune->commune_index($sSearch);
    }
}

$aJson = array();

foreach ($aResult as $aCommune) {
    $aJson[] = sprintf("%s (%s)", $aCommune['commune'], $aCommune['cp']);
}

echo json_encode($aJson);

//Close database
$dbh = null;
