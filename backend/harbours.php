<?php
header('Content-Type: application/json');

require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'PoisManager.php';
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'tools.php';
$poisManager = new PoisManager();

function updateFile()
{
    global $harboursManager;
    $list = $harboursManager->getAll();
    $header = array('Latitude', 'Longitude', 'Name');
    if ($f = @fopen(dirname(__DIR__) . DIRECTORY_SEPARATOR . 'downloads' . DIRECTORY_SEPARATOR . 'harbours.csv', 'w')) {
        fputcsv($f, $header);
        foreach ($list as $row) {
            fputcsv($f, array($row->lat, $row->lng, $row->name));
        }
        fclose($f);
    }
}

$return;
$return["success"] = false;

if (!isRequestAuthenticated()) {
    $return["message"] = "You are not authorized to use this service. Please use a right key.";
} else {
    if (isset($_POST["request"])) {
        switch ($_POST["request"]) {
            case "add":
                if (isset($_POST["harbours"])) {
                    $harbours = json_decode($_POST["harbours"]);
                    $result = array(0);
                    foreach ($harbours as $harbour) {
                        $name = htmlspecialchars($harbour->name);
                        $id = $harboursManager->add($name, floatval($harbour->lat), floatval($harbour->lng));
                        array_push($result, $id);
                    }
                    updateFile();
                    $return["ids"] = $result;
                    $return["message"] = "Harbour(s) added";
                    $return["success"] = true;
                } else {
                    $return["message"] = "Wrong request structure";
                }
                break;
            case "get":
                // returns a json that contains the list of all registered harbours
                try {
                    $harbours = $harboursManager->getAll();
                    $return["harbours"] = $harbours;
                    $return["message"] = "Here are all harbours";
                    $return["success"] = true;
                } catch (Exception $e) {
                    $return["message"] = "Error while getting harbours in the database";
                }
                break;
            case "remove":
                $harboursManager->removeList(json_decode($_POST["id-list"], true));
                updateFile();
                $return["message"] = "Harbour(s) removed";
                $return["success"] = true;
                break;
            default:
                $return["message"] = "Wrong request structure";
        }
    } else {
        $return["message"] = "Nothing happens...";
    }
}

echo json_encode($return);
