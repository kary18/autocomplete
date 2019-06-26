<!DOCTYPE html>
<html lang="fr" dir="ltr">
    <head>
        <meta charset="utf-8">
        <title>Recherche commune</title>
        <script src="autocomplete.js"></script>
    </head>
    <body>
        <h1>Chercher une commune</h1>
        <form action="/index.php" method="post" name="form-loc" id="form-loc">
            <label for="localisation">Localisation</label>
            <input type="text" id="loc-input" name="loc-input" list="loc-datalist" placeholder="Ville ou Code postal...">
            <datalist id="loc-datalist">
            </datalist>
            <input type="submit" value="OK"  id="bouton-submit">
        </form>

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

$sSearch = $_POST['loc-input'] ?? "";
if (!empty($sSearch)) {
    $sSearch = htmlentities($sSearch);

    $nPos1 = strpos($sSearch, '(');
    $nPos2 = strpos($sSearch, ')');

    $sCommune = substr($sSearch, 0, $nPos1);
    $sCp = substr($sSearch, $nPos1+1, $nPos2-$nPos1-1);

    $aResult = $commune->read($sCp, $sCommune);

    echo sprintf("Commune: %s<br>", $aResult['commune']);
    echo sprintf("Code postal: %s<br>", $aResult['cp']);
    echo sprintf("Departement: %s %s<br>", $aResult['dep_code'], $aResult['dep']);
    echo sprintf("Latitude: %s<br>", $aResult['latitude']);
    echo sprintf("Longitude: %s<br>", $aResult['longitude']);

//    print_r($aResult);
}

    echo '</body>

</html>';
