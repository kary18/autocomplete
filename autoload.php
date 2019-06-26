<?php
// autoloader des classes
// voir documentation https://www.php.net/manual/en/language.oop5.autoload.php

define('DIRECTORY_LIST', array('./', './lib/') );

spl_autoload_register(function ($sClassname) {
    $lLoaded = false;
    foreach (DIRECTORY_LIST as $sDir) {
        $sFile = $sDir.$sClassname.'.php';
        if (file_exists($sFile)) {
            // Class file found
//            echo "Loading:$sFile\n";
            $lLoaded = true;
            require_once($sFile);
        }
    }
    if (! $lLoaded) {
        throw new \Exception("Autoload: Unable to load class " . $sClassname, 1);
    }

});
