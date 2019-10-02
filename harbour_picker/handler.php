<?php
require("../config.php");
require("../db.php");
$db = new DB();
if (isset($_POST["request"])) {
    switch ($_POST["request"]) {
        case "add":
            if (isset($_POST["name"]) and isset($_POST["lat"]) and isset($_POST["lng"])) {
                $db->insertANewHarbour($_POST["name"], $_POST["lat"], $_POST["lng"]);
            }
            break;
    }
}
