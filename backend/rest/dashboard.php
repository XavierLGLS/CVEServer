<?php
header('Content-Type: application/json');

require_once dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . 'config_v2.php';

require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'tools.php';
require_once dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'MissionsManager.php';
require_once dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'PoisManager.php';
require_once dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'BoatsManager.php';
require_once dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'AccountsManager.php';
$poisManager = new PoisManager();
$boatsManager = new BoatsManager();
$accountsManager = new AccountsManager();
$missionsManager = new MissionsManager();

$return;
$return["success"] = false;

if (!isRequestAuthenticated()) {
    $return["message"] = "You are not authorized to use this service. Please use a right key.";
} else {
    switch ($_POST["action"]) {
        case 'get-player-status':
            handleGetPlayerStatus();
            break;
        case 'get-other-players':
            handleGetOtherPlayers();
            break;
        case 'repair-hull':
            handleRepairHull();
            break;
        case 'repair-sails':
            handleRepairSails();
            break;
        case 'buy-items':
            handleBuyItems();
            break;
        case 'start-mission':
            handleStartMission();
            break;
        case 'cancel-mission':
            handleCancelMission();
            break;
        case 'validate-step':
            handleValidateStep();
            break;
        case 'use-dry-dock':
            handleUseDryDock();
            break;
        case 'leave-dry-dock':
            handleLeaveDryDock();
            break;
        default:
            $return["message"] = "The action is not correct";
            $return["success"] = false;
            break;
    }
}

echo json_encode($return);

function handleGetPlayerStatus(): void
{
    global $return, $poisManager, $boatsManager, $accountsManager, $missionsManager, $DASH_POIS_DETECTION_RANGE, $DASH_MAX_POIS_NUMBER;

    if (isset($_POST['boat-id'])) {
        try {
            $id = intval($_POST['boat-id']);
            $boat = $boatsManager->getBoatFromId($id);
            $boatJson = $boat->toJson();
            $boatJson["trajectory"] = $boatsManager->getTrajectoryFromBoatId($id);
            $return["boat"] = $boatJson;
            if ($boat->isInMission()) {
                $missionJson = $missionsManager->getMissionByStepId($boat->getStepId())->toJson();
                $missionJson["current_step"] = $boat->getStepId();
                $missionJson["dist"] = $missionsManager->getDistanceToNextStep($boat);
                $return["mission"] = $missionJson;
            }
            $return["pois"] = $poisManager->getNNearestPoisInRange($boatJson["trajectory"][0]["lat"], $boatJson["trajectory"][0]["lng"], $DASH_POIS_DETECTION_RANGE, $DASH_MAX_POIS_NUMBER);
            $return["money"] = $accountsManager->getUserFromId($boat->getCveUserId())->getMoney();
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

function handleGetOtherPlayers(): void
{
    global $return, $boatsManager, $accountsManager, $missionsManager;

    try {
        $boats = $boatsManager->getAll();
        $return["boats"] = [];
        foreach ($boats as $boat) {
            if (!$boat->isInDryDock()) {
                $boatJson = $boat->toOverviewJson();
                $boatJson["trajectory"] = $boatsManager->getTrajectoryFromBoatId($boat->getId());
                $boatJson["skipper"] = ($accountsManager->getUserFromId($boat->getCveUserId()))->getUsername();
                if ($boat->isInMission()) {
                    $boatJson["mission"] = ($missionsManager->getMissionByStepId($boat->getStepId()))->getTitle();
                }
                $return["boats"][] = $boatJson;
            }
        }
        $return["success"] = true;
    } catch (Exception $e) {
        $return["success"] = false;
        $return["message"] = $e->getMessage();
    }
}

function handleRepairHull(): void
{
    global $return, $boatsManager;

    if (isset($_POST['boat-id'])) {
        try {
            $id = intval($_POST['boat-id']);
            $return["boat"] = ($boatsManager->repairBoatHull($boatsManager->getBoatFromId($id)))->toJson();
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

function handleRepairSails(): void
{
    global $return, $boatsManager;

    if (isset($_POST['boat-id'])) {
        try {
            $id = intval($_POST['boat-id']);
            $return["boat"] = ($boatsManager->repairBoatSails($boatsManager->getBoatFromId($id)))->toJson();
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

function handleBuyItems(): void
{
    global $return, $boatsManager, $accountsManager;

    if (isset($_POST['boat-id']) && isset($_POST['items']) && isset($_POST['user-id'])) {
        try {
            $boat = $boatsManager->getBoatFromId(intval($_POST['boat-id']));
            $user = $accountsManager->getUserFromId(intval($_POST['user-id']));
            $items = $_POST['items'];
            $return["boat"] = ($boatsManager->buyBoatItems($boat, $user, $items))->toJson();
            $return["money"] = $accountsManager->getUserFromId($boat->getCveUserId())->getMoney();
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

function handleStartMission(): void
{
    global $return, $boatsManager, $missionsManager;

    if (isset($_POST['boat-id']) && isset($_POST['mission-id'])) {
        try {
            $boat = $boatsManager->getBoatFromId(intval($_POST['boat-id']));
            if ($boat->isInMission()) {
                throw new Exception("This boat is already doing a mission");
            }
            $mission = $missionsManager->getMissionById(intval($_POST['mission-id']));
            if ($mission->getPassengers() > $boat->getPassengerCapacity()) {
                $passengers = $mission->getPassengers();
                throw new Exception("This boat cannot embark $passengers passengers");
            }
            $boatsManager->setMission($boat, $mission);
            $missionJson = $mission->toJson();
            $boat = $boatsManager->getBoatFromId(intval($_POST['boat-id']));
            $return["boat"] = $boat->toJson();
            $missionJson["current_step"] = $boat->getStepId();
            $missionJson["dist"] = $missionsManager->getDistanceToNextStep($boat);
            $return["mission"] = $missionJson;
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

function handleCancelMission(): void
{
    global $return, $boatsManager;

    if (isset($_POST['boat-id'])) {
        try {
            $boat = $boatsManager->getBoatFromId(intval($_POST['boat-id']));
            $boatsManager->cancelMission($boat);
            $boat = $boatsManager->getBoatFromId(intval($_POST['boat-id']));
            $return["boat"] = $boat->toJson();
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

function handleValidateStep(): void
{
    global $return, $boatsManager, $missionsManager, $accountsManager;

    if (isset($_POST['boat-id'])) {
        try {
            $boat = $boatsManager->getBoatFromId(intval($_POST['boat-id']));
            $missionsManager->validateStep($boat);
            $boat = $boatsManager->getBoatFromId(intval($_POST['boat-id']));
            if ($boat->isInMission()) {
                $boat = $boatsManager->getBoatFromId(intval($_POST['boat-id']));
                $missionJson = ($missionsManager->getMissionByStepId($boat->getStepId()))->toJson();
                $missionJson["current_step"] = $boat->getStepId();
                $missionJson["dist"] = $missionsManager->getDistanceToNextStep($boat);
                $return["mission"] = $missionJson;
                $return["finished"] = false;
            } else {
                $return["finished"] = true;
                $return["money"] = $accountsManager->getUserFromId($boat->getCveUserId())->getMoney();
            }
            $return["boat"] = $boat->toJson();
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

function handleUseDryDock(): void
{
    global $return, $boatsManager, $poisManager;

    if (isset($_POST['boat-id']) && isset($_POST['poi-id'])) {
        try {
            $boat = $boatsManager->getBoatFromId(intval($_POST['boat-id']));
            $dryDock = $poisManager->getPoiById(intval($_POST['poi-id']));
            $boatsManager->putInDryDock($boat, $dryDock);
            // return
            $boat = $boatsManager->getBoatFromId($boat->getId());
            $boatJson = $boat->toJson();
            $boatJson["trajectory"] = $boatsManager->getTrajectoryFromBoatId($boat->getId());
            $return["boat"] = $boatJson;
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

function handleLeaveDryDock(): void
{
    global $return, $boatsManager;

    if (isset($_POST['boat-id'])) {
        try {
            $boat = $boatsManager->getBoatFromId(intval($_POST['boat-id']));
            $boatsManager->exitFromDryDock($boat);
            // return
            $boat = $boatsManager->getBoatFromId($boat->getId());
            $boatJson = $boat->toJson();
            $boatJson["trajectory"] = $boatsManager->getTrajectoryFromBoatId($boat->getId());
            $return["boat"] = $boatJson;
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
