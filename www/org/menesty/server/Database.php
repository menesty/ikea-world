<?php

/**
 * User: Menesty
 * Date: 12/30/13
 * Time: 5:54 PM
 */
class Database
{

    private $connection;

    private static $instance;

    private function __construct()
    {
        $connectionUrl = Configuration::get()->getDbDriver() . ":host=" . Configuration::get()->getDbHost()
            . ";dbname=" . Configuration::get()->getDbName().";charset=UTF8";
        try {
            $this->connection = new PDO($connectionUrl, Configuration::get()->getDbUser(), Configuration::get()->getDbPassword());
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, Configuration::get()->isDevMode() ? PDO::ERRMODE_EXCEPTION : PDO::ERRMODE_SILENT);
        } catch (Exception $e) {
            error_log("Error db connection :" . $e->getMessage() . "\n", 3, "errors.log");
        }

    }

    public static function get()
    {
        if (!self::$instance)
            self::$instance = new Database();

        return self::$instance;
    }

    public function getConnection()
    {
        return $this->connection;
    }

}