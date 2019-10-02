<?php

class DB
{
    function __construct()
    {
        global $DB_NAME;
        global $DB_USERNAME;
        global $DB_HOST;
        global $DB_PASSWORD;
        $this->db = new PDO('mysql:dbname=' . $DB_NAME . ';host=' . $DB_HOST, $DB_USERNAME, $DB_PASSWORD);
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);
    }

    public function isHarbourPickerRegistered($username, $password){
        //Returns true id the user is registered
        // TODO
        return true; 
    }

    public function insertANewHarbour($name, $lat, $lng){
        //TODO
    }

    public function removeAnHarbour($id){
        //TODO
    }
}