<?php
require("../config.php");
require("../db.php");
$db = new DB();
if (isset($_POST["request"])) {
    switch ($_POST["request"]) {
        case "add":
            if (isset($_POST["data"])) {
                $data = json_decode($_POST["data"]);
                $user_id = $_POST["data"]["user_id"];
                $list = $_POST["data"]["list"];
                $result = array();
                foreach ($list as $row) {
                    $name = htmlspecialchars($row["name"]);
                    $name = str_replace("'", "\'", $name);
                    $name = str_replace('"', '\"', $name);
                    $id = $db->insertANewHarbour($name, floatval($row["lat"]), floatval($row["lng"]), intval($user_id));
                    array_push($result, $id);
                }
                echo json_encode($result);
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
