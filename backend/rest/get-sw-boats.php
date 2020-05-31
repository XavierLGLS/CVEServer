<?php
header('Content-Type: application/json');

require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'tools.php';
require_once dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'SailawayDataManager.php';

$return;
$return["success"] = false;

if (!isRequestAuthenticated()) {
    $return["message"] = "You are not authorized to use this service. Please use a right key.";
} else {
    if (isset($_POST["username"])) {
        $username = htmlspecialchars($_POST["username"]);
        $swDataManager = new SailawayDataManager();
        if ($swDataManager->userExists($username)) {
            $sw_id = $swDataManager->getUserId($username);
            $boats = $swDataManager->getBoatsFromSwUserId($sw_id);
            if (count($boats) > 0) {
                $return["success"] = true;
                $return["boats"] = $boats;
                $return["sw-user-id"] = $sw_id;
            } else {
                $return["success"] = false;
                $return["message"] = "No boat found for this user. Please make sure you have sailed a boat within last 6 months";
            }
        } else {
            $return["success"] = false;
            $return["message"] = "No user found in sailaway, please make sure you have been connected to Sailaway within last 6 months";
        }
    } else {
        $return["message"] = "You must define a username !";
    }
}

echo json_encode($return);
