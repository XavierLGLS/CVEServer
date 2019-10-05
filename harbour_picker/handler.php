<?php
require("../config.php");
require("../db.php");
$db = new DB();
if (isset($_POST["request"])) {
    switch ($_POST["request"]) {
        case "add":
            if (isset($_POST["data"])) {
                $data = json_decode($_POST["data"]);
                $user_id = $data->user_id;
                $list = $data->list;
                foreach ($list as $row) {
                    $db->insertANewHarbour($row->name, floatval($row->lat), floatval($row->lng), intval($user_id));
                }
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
