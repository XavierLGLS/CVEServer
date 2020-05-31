<?php

require_once dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'config_v2.php';

class DBManager
{
    protected $db;

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

    /**
     * Returns the distance between 2 locations
     * @param lat1: latitude coordinate (between -180 and 180) of the first location
     * @param lng1: longitude coordinate (between -180 and 180) of the first location
     * @param lat2: latitude coordinate (between -180 and 180) of the second location
     * @param lng2: longitude coordinate (between -180 and 180) of the second location
     * @param unit: if 'K' km, if 'N' nm, else 'miles'
     * @return float: distance with in defined unit
     */
    protected function distance(float $lat1, float $lng1, float $lat2, float $lng2,  string $unit): float
    {
        if (($lat1 == $lat2) && ($lng1 == $lng2)) {
            return 0;
        } else {
            $theta = $lng1 - $lng2;
            $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
            $dist = acos($dist);
            $dist = rad2deg($dist);
            $miles = $dist * 60 * 1.1515;
            $unit = strtoupper($unit);

            if ($unit == "K") {
                return ($miles * 1.609344);
            } else if ($unit == "N") {
                return ($miles * 0.8684);
            } else {
                return $miles;
            }
        }
    }
}
