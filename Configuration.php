<?php

/**
 * User: Menesty
 * Date: 12/27/13
 * Time: 11:57 PM
 */
class Configuration
{
    private static $instance;

    private $siteRoot;

    private $libPath;

    private $dataPath;

    private $languageMessagePath;

    private $dbHost = "localhost";

    private $dbDriver = "mysql";

    private $dbName = "u580543188_world";

    private $dbUser = "u580543188_world";

    private $dbPassword = "ikea-world";

    private $controllerPath;

    private $templatePath;

    private $authUser = "desktop";

    private $authPassword = "ikea-desktop";

    private $emailAccount = array("o.maks.78.len@gmail.com", "LenMaks78");

    const DEV_MODE = "dev";

    const PROD_MODE = "prod";

    private $mode = Configuration::DEV_MODE;

    private function __construct()
    {
        $this->siteRoot = $_SERVER["DOCUMENT_ROOT"];
        $this->classPath = $this->siteRoot . DIRECTORY_SEPARATOR . "org" . DIRECTORY_SEPARATOR . "menesty" . DIRECTORY_SEPARATOR . "server" . DIRECTORY_SEPARATOR;
        $this->controllerPath = $this->classPath . "controller";
        $this->templatePath = $this->classPath . "templates";
        $this->libPath = $this->siteRoot . DIRECTORY_SEPARATOR . "lib" . DIRECTORY_SEPARATOR;
        $this->dataPath = $this->classPath . "data" . DIRECTORY_SEPARATOR;
        $this->languageMessagePath = $this->dataPath . "languages" . DIRECTORY_SEPARATOR;

    }

    public function getLanguageMessagePath(){
        return $this->languageMessagePath;
    }

    public function getEmailAccount()
    {
        return $this->emailAccount;
    }

    public function getDataPath()
    {
        return $this->dataPath;
    }

    public function getTemplatePath()
    {
        return $this->templatePath;
    }

    public function getTemplatePaths($template)
    {
        $fileName = $this->templatePath . DIRECTORY_SEPARATOR . $template;

        if (!is_file($fileName))
            throw new Exception("Template not found by path :" . $fileName);

        if (!is_readable($fileName))
            throw new Exception("Template have not read permissions :" . $fileName);

        return $fileName;
    }

    public function getClassPath()
    {
        return $this->classPath;
    }

    public function getLibPath()
    {
        return $this->libPath;
    }

    public function isDevMode()
    {
        return $this->mode == Configuration::DEV_MODE;
    }

    public static function get()
    {
        if (!self::$instance)
            self::$instance = new Configuration();

        return self::$instance;
    }

    public function getAuthUser()
    {
        return $this->authUser;
    }

    public function getAuthPassword()
    {
        return $this->authPassword;
    }

    public function getSiteRoot()
    {
        return $this->siteRoot;
    }

    public function getDbHost()
    {
        return $this->dbHost;
    }

    public function getDbUser()
    {
        return $this->dbUser;
    }

    public function getDbName()
    {
        return $this->dbName;
    }

    public function getDbPassword()
    {
        return $this->dbPassword;
    }

    public function getControllerPath()
    {
        return $this->controllerPath;
    }

    public function getDbDriver()
    {
        return $this->dbDriver;
    }

}