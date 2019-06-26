<?php

/* Classe DBUtil fonctions de gestion de la base de données
    Fonctionnalités:
        - configuration database avec un fichier JSON
        - vérifications basiques de sécurité

*/

class DBUtil
{
    // Constantes - valeurs par défaut
    private const DBCONFIG_FILE = 'dbconfig-dist.json';
    private const DBCONFIG_HOST = 'localhost';
    private const DBCONFIG_DBNAME = 'databasename';
    private const DBCONFIG_CHARSET = 'utf8';
    private const DBCONFIG_USER = 'user';
    private const DBCONFIG_PASSWORD = 'password';
    private const DBCONFIG_OPTIONS = array(PDO::ATTR_PERSISTENT => true);
    private const DBCONFIG_CREATEDB = 'createdb.sql';
    private const DBCONFIG_LOADDATA = 'loaddata.sql';
    private const DBCONFIG_BACKUP = 'backup.sql';
    private const DBCONFIG_DBAUSER = 'root';
    private const DBCONFIG_DBAPASSWORD = '';
    private const DBCONFIG_FIELDLIST = array('host', 'databasename', 'charset', 'user', 'password', 'options', 'createdb', 'loaddata', 'backup', 'backupstruct', 'dbauser', 'dbapassword');

//    private $mydb = null;
    private $dbConfigured = false;

    // Configuration de la base de données
    private $dbHost = self::DBCONFIG_HOST;
    private $dbName = self::DBCONFIG_DBNAME;
    private $dbCharset = self::DBCONFIG_CHARSET;
    private $dbUser = self::DBCONFIG_USER;
    private $dbPassword = self::DBCONFIG_PASSWORD;
    private $dbOptions = self::DBCONFIG_OPTIONS;
    private $dbCreateDB = self::DBCONFIG_CREATEDB;
    private $dbLoadData = self::DBCONFIG_LOADDATA;
    private $dbBackup = self::DBCONFIG_BACKUP;
    private $dbDBAUser = self::DBCONFIG_DBAUSER;
    private $dbDBAPassword = self::DBCONFIG_DBAPASSWORD;

    public function __construct(string $sDBConfigFile)
    {
        if (! self::configure($sDBConfigFile) ) {
            throw new \Exception("DBUtil: error configuring databse", 1);
        }
    }

    private function configure(string $sDBConfigFile):bool
    {
        $lReturn = false;

        if (file_exists($sDBConfigFile)) {
            // Le fichier de configuration existe
            $fp = fopen($sDBConfigFile, 'r');
            if ( $fp === false ) {
                echo "Erreur ouverture fichier $sDBConfigFile\n";
            } else {
                // Le fichier de config est ouvert
                $sConfig = fread($fp,4096);
                if ($sConfig===false) {
                    echo "Erreur de lecture du fichier $sDBConfigFile\n";
                } else {
                    // Decodage des données JSON
                    $aConfigDB = json_decode($sConfig, true);
                    fclose($fp);

                    if ( ! is_null($aConfigDB) && self::checkParameters($aConfigDB) ) {
                        // Configuration
                        $lReturn = true;
                        $this->dbConfigured = true;

                        $this->dbHost = $aConfigDB['host'];
                        $this->dbName = $aConfigDB['databasename'];
                        $this->dbCharset = $aConfigDB['charset'];
                        $this->dbUser = $aConfigDB['user'];
                        $this->dbPassword = $aConfigDB['password'];
                        $this->dbOptions = $aConfigDB['options'];
                        $this->dbCreateDB = $aConfigDB['createdb'];
                        $this->dbLoadData = $aConfigDB['loaddata'];
                        $this->dbBackup = $aConfigDB['backup'];
                        $this->dbDBAUser = $aConfigDB['dbauser'];
                        $this->dbDBAPassword = $aConfigDB['dbapassword'];
                    }
                }
            }

        } else {
            echo "**** Erreur fichier inexistant ".$sDBConfigFile.PHP_EOL.PHP_EOL;
            self::writeDBConfigDist($sDBConfigFile);
        }

        return($lReturn);
    }

    // Verifie que les champs nécessaires sont configurés
    private function checkParameters($aConfig)
    {
        $lReturn = false;

        foreach(self::DBCONFIG_FIELDLIST as $sField) {
            if ( isset($aConfig[$sField]) ) {
                $lReturn = true;
            } else {
                echo "Erreur configuration: paramètre erronné $sField\n";
            }
        }

        return($lReturn);
    }

    // Ecris un fichier contenant une configuration type
    private function writeDBConfigDist(string $sDBConfigFile)
    {

        if (! file_exists(self::DBCONFIG_FILE)) {
            $aConfigDB['host'] = self::DBCONFIG_HOST;
            $aConfigDB['databasename'] = self::DBCONFIG_DBNAME;
            $aConfigDB['charset'] = self::DBCONFIG_CHARSET;
            $aConfigDB['user'] = self::DBCONFIG_USER;
            $aConfigDB['password'] = self::DBCONFIG_PASSWORD;
            $aConfigDB['options'] = self::DBCONFIG_OPTIONS;
            $aConfigDB['createdb'] = self::DBCONFIG_CREATEDB;
            $aConfigDB['loaddata'] = self::DBCONFIG_LOADDATA;
            $aConfigDB['backup'] = self::DBCONFIG_BACKUP;
            $aConfigDB['dbauser'] = self::DBCONFIG_DBAUSER;
            $aConfigDB['dbapassword'] = self::DBCONFIG_DBAPASSWORD;

            $fp = fopen(self::DBCONFIG_FILE, 'w');
            if ( $fp === false ) {
                $sMessage = "Erreur ouverture fichier " . self::DBCONFIG_FILE . " en écriture";
                echo $sMessage;
            } else {
                echo "Ecriture de la configuration par défaut " . self::DBCONFIG_FILE . PHP_EOL . PHP_EOL;
                echo "Vous pouvez copier le fichier ".self::DBCONFIG_FILE." sous le nom $sDBConfigFile et le modifier pour correspondre à votre configuration\n";
                if (! fwrite($fp, json_encode($aConfigDB,JSON_PRETTY_PRINT))) {
                    $sMessage = "Erreur écriture fichier " . self::DBCONFIG_FILE . PHP_EOL;
                    echo $sMessage;
                }
            }
            fclose($fp);
        }
    }

    // vérifications ultra-basiques de sécurité
    public function checkSecurity():bool
    {
        $aMessages = [];

        $lReturn = true;
        if ($this->dbUser==='root') {
            $aMessages[] = "Vous ne devriez pas vous connecter à votre base en tant que root\n";
            $lReturn = false;
        }
        if ($this->dbPassword==='') {
            $aMessages[] =  "Vous devriez avoir un mot de passe pour votre utilisateur de base de données\n";
            $lReturn = false;
        }
        if ($this->dbDBAPassword==='') {
            $aMessages[] =  "Vous devriez avoir un mot de passe pour votre utilisateur root de votre base de données\n";
            $lReturn = false;
        }

        if (!$lReturn) {
            echo "**** CheckSecurity\n";
            foreach ($aMessages as $sMessage) {
                echo $sMessage;
            }
            throw new \Exception("DBUtil: unsecure connection", 2);
        }

        return($lReturn);
    }

    public function getDBDSN()
    {
        $sDSNTemplate = 'mysql:host=%s;dbname=%s;charset=%s';
        return( sprintf($sDSNTemplate, $this->dbHost, $this->dbName, $this->dbCharset) );
    }

    public function getDBUser()
    {
        return( $this->dbUser );
    }

    public function getDBPassword()
    {
        return( $this->dbPassword );
    }

    public function getDBOptions()
    {
        return( $this->dbOptions );
    }

}
