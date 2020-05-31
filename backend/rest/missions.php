<?php
header('Content-Type: application/json');

require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'tools.php';
require_once dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'MissionsManager.php';
require_once dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'AccountsManager.php';
$missionsManager = new MissionsManager();
$accountsManager = new AccountsManager();

$return;
$return["success"] = false;

if (!isRequestAuthenticated()) {
    $return["message"] = "You are not authorized to use this service. Please use a right key.";
} else {
    switch ($_POST["action"]) {
        case 'get-all-missions-matching':
            handleGetAllMissionsMatching();
            break;
        case 'get-mission-by-id':
            handleGetMissionById();
            break;
        case 'get-all-missions-in-poi':
            handleGetAllMissionsInPoi();
            break;
        case 'add-mission':
            handleAddMission();
            break;
        case 'edit-mission':
            handleEditMission();
            break;
        case 'remove-mission':
            handleRemoveMission();
            break;
        default:
            $return["message"] = "The action is not correct";
            $return["success"] = false;
            break;
    }
}

echo json_encode($return);

function handleGetMissionById(): void
{
    global $return, $missionsManager;

    if (isset($_POST['mission-id'])) {
        try {
            $id = intval($_POST['mission-id']);
            $mission = $missionsManager->getMissionById($id);
            $return["mission"] = $mission->toJson();
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

/**
 * Returns all missions whose title contains the string
 */
function handleGetAllMissionsMatching(): void
{
    global $return, $missionsManager, $accountsManager;

    if (isset($_POST['field']) && isset($_POST['user-id'])) {
        try {
            $field = htmlspecialchars($_POST['field']);
            $missions = $missionsManager->getMissionsMatching($field);
            $user = $accountsManager->getUserFromId(intval($_POST['user-id']));
            $output = [];
            foreach ($missions as $mission) {
                if ($mission->isManual() && ($mission->getCreatorId() == $user->getId() || $user->isAdmin())) {
                    $output[] = [
                        "id" => $mission->getId(),
                        "title" => $mission->getTitle(),
                        "creator" => $mission->getCreatorName()
                    ];
                }
            }
            $return["missions"] = $output;
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

function handleGetAllMissionsInPoi(): void
{
    global $return, $missionsManager, $accountsManager;

    if (isset($_POST["poi-id"]) && isset($_POST["user-id"])) {
        try {
            $id = intval($_POST["poi-id"]);
            $missions = $missionsManager->getAllMissionsInPoi($id);
            $user = $accountsManager->getUserFromId(intval($_POST['user-id']));
            $output = [];
            foreach ($missions as $mission) {
                if ($mission->isManual() && ($mission->getCreatorId() == $user->getId() || $user->isAdmin())) {
                    $output[] = [
                        "id" => $mission->getId(),
                        "title" => $mission->getTitle(),
                        "creator" => $mission->getCreatorName()
                    ];
                }
            }
            $return["missions"] = $output;
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

function handleAddMission(): void
{
    global $return, $missionsManager;

    if (isset($_POST["mission"]) && isset($_POST["user-id"])) {
        try {
            $creator = intval($_POST["user-id"]);
            $missionJson = json_decode($_POST["mission"]);

            if (isset($missionJson->name)) {
                $title = htmlspecialchars($missionJson->name);
            } else {
                throw new Exception("no title defined");
            }

            $caption = "";
            if (isset($missionJson->caption)) {
                $caption = htmlspecialchars($missionJson->caption);
            }

            if (isset($missionJson->passengers)) {
                $passengers = intval($missionJson->passengers);
            } else {
                throw new Exception("no passengers defined");
            }

            $steps = [];
            if (sizeof($missionJson->steps) < 1) {
                throw new Exception("no step");
            }
            foreach ($missionJson->steps as $elt) {
                $step = [];
                if (isset($elt->poi_id)) {
                    $step["poi-id"] = intval($elt->poi_id);
                } else if (isset($elt->lat) && isset($elt->lng)) {
                    $step["lat"] = floatval($elt->lat);
                    $step["lng"] = floatval($elt->lng);
                } else {
                    throw new Exception("no step location");
                }
                if (isset($elt->caption)) {
                    $step["caption"] = htmlspecialchars($elt->caption);
                } else {
                    $step["caption"] = "";
                }
                $steps[] = $step;
            }

            $mission = $missionsManager->addMission($title, $caption, $passengers, $steps, $missionJson->places_of_availability, $creator);

            $return["mission"] = $mission->toJson();
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

function handleEditMission(): void
{
    global $return, $missionsManager;

    if (isset($_POST["mission"]) && isset($_POST["user-id"])) {
        try {
            $userId = intval($_POST["user-id"]);
            $missionJson = json_decode($_POST["mission"]);

            if (isset($missionJson->id)) {
                $missionId = intval($missionJson->id);
            } else {
                throw new Exception("no mission id defined");
            }

            if (isset($missionJson->name)) {
                $title = htmlspecialchars($missionJson->name);
            } else {
                throw new Exception("no title defined");
            }

            if (isset($missionJson->passengers)) {
                $passengers = intval($missionJson->passengers);
            } else {
                throw new Exception("no passengers defined");
            }

            $caption = "";
            if (isset($missionJson->caption)) {
                $caption = htmlspecialchars($missionJson->caption);
            }

            $steps = [];
            if (sizeof($missionJson->steps) < 1) {
                throw new Exception("no step");
            }
            foreach ($missionJson->steps as $elt) {
                $step = [];
                if (isset($elt->poi_id)) {
                    $step["poi-id"] = intval($elt->poi_id);
                } else if (isset($elt->lat) && isset($elt->lng)) {
                    $step["lat"] = floatval($elt->lat);
                    $step["lng"] = floatval($elt->lng);
                } else {
                    throw new Exception("no step location");
                }
                if (isset($elt->caption)) {
                    $step["caption"] = htmlspecialchars($elt->caption);
                } else {
                    $step["caption"] = "";
                }
                $steps[] = $step;
            }

            $missionsManager->updateMission($missionId, $title, $caption, $passengers, $steps, $missionJson->places_of_availability, $userId);

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

function handleRemoveMission(): void
{
    global $return, $missionsManager;

    if (isset($_POST["mission-id"])) {
        try {
            $id = intval($_POST["mission-id"]);
            if (!$missionsManager->isMissionUsed($id)) {
                $missionsManager->removeMission($id);
                $return["success"] = true;
            } else {
                $return["success"] = false;
                $return["message"] = "Because a player is doing this mission, you cannot delete it. Please contact an admin.";
            }
        } catch (Exception $e) {
            $return["success"] = false;
            $return["message"] = $e->getMessage();
        }
    } else {
        $return["success"] = false;
        $return["message"] = "Not enough information in the request";
    }
}
