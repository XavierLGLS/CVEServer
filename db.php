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

    public function isHarbourPickerRegistered($username, $password)
    {
        //Returns true id the user is registered
        $req = $this->db->prepare("SELECT count(*) FROM harbour_pickers WHERE name=? AND password=?");
        $req->execute(array($username, $password));
        if ($req->fetchColumn() > 0) {
            return true;
        } else {
            return false;
        }
    }

    public function insertANewHarbour($name, $lat, $lng)
    {
        $this->db->query("INSERT INTO harbours (name, lat, lng) VALUES ('$name', $lat, $lng)");
    }

    public function removeAnHarbour($id)
    {
        //TODO
    }

    public function getAllHarbours()
    {
        $output = array();
        foreach($this->db->query("SELECT * FROM harbours") as $row){
            array_push($output, $row);
        }
        return $output;
    }
}
