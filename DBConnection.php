<?php
class DBConnection {
    private static $_DBConnectionObject;
    private $_PDOInstance;
 
    private function __construct() {
        try {
            $this->_PDOInstance = new PDO('mysql:host=localhost;dbname=comanda;charset=utf8', 'root', '', array(PDO::ATTR_EMULATE_PREPARES => false,PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
 
            $this->_PDOInstance->exec("SET CHARACTER SET utf8");
 
        } catch (PDOException $e) {
 
            print "Error:<br/>" . $e->getMessage();
 
            die();
        }
    }
 
    public function SetQuery($sql) {
        return $this->_PDOInstance->prepare($sql);
    }
 
    public static function NewDBConnection() {
        if (!isset(self::$_DBConnectionObject)) {
            self::$_DBConnectionObject = new DBConnection();
        }
 
        return self::$_DBConnectionObject;        
    }
 
    public function __clone() {
        trigger_error('Clonating this object is not allowed.', E_USER_ERROR);
    }
}