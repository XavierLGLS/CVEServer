<?php
require("../config.php");
require("../db.php");
$db = new DB();
if (isset($_POST["request"])) {
    switch ($_POST["request"]) {
        case "add":
            if (isset($_POST["name"]) and isset($_POST["lat"]) and isset($_POST["lng"]) and isset($_POST["user_id"])) {
                $db->insertANewHarbour($_POST["name"], floatval($_POST["lat"]), floatval($_POST["lng"]), intval($_POST["user_id"]));
            }
            break;
        case "get":
            // returns a json that contains the list of all registered harbours
            $harbours = $db->getAllHarbours();
            echo json_encode($harbours);
            break;
        case "remove":
            $db->removeHarbours(json_decode($_POST["list"]["list"], true));
            break;
    }
}
