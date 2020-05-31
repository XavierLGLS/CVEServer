<?php
header('Content-Type: application/json');

require_once dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . 'config_v2.php';

require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'tools.php';
require_once dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'BoatsManager.php';
$boatsManager = new BoatsManager();

$return;
$return["success"] = false;

if (!isRequestAuthenticated()) {
    $return["message"] = "You are not authorized to use this service. Please use a right key.";
} else {
    switch ($_POST["action"]) {
        case 'update-color':
            updateColor();
            break;
        default:
            $return["message"] = "The action is not correct";
            $return["success"] = false;
            break;
    }
}

echo json_encode($return);

function updateColor()
{
    global $return, $boatsManager;

    if (isset($_POST['color']) && isset($_POST['boat-id'])) {
        try {
            $color = intval($_POST['color']);
            $boat = $boatsManager->getBoatFromId(intval($_POST['boat-id']));
            $boatsManager->setBoatColor($boat, $color);
            $return["success"] = true;
        } catch (Exception $e) {
            $return["success"] = false;
            $return["message"] = $e->getMessage();
        }
    } else {
        $return["success"] = false;
        $return["message"] = "Not enough information in the request";
    }
}
