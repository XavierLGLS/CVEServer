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
        $req = $this->db->prepare("SELECT * FROM harbour_pickers WHERE name=? AND password=?");
        $req->execute(array($username, $password));
        while($user = $req->fetch()){
            return true;
            break;
        }
        return false; 
    }

    public function insertANewHarbour($name, $lat, $lng){
        //TODO
    }

    public function removeAnHarbour($id){
        //TODO
    }
}