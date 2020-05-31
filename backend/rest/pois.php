<?php
header('Content-Type: application/json');

require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'tools.php';
require_once dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'PoisManager.php';
require_once dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'MissionsManager.php';
$poisManager = new PoisManager();
$missionsManager = new MissionsManager();

$return;
$return["success"] = false;

if (!isRequestAuthenticated()) {
    $return["message"] = "You are not authorized to use this service. Please use a right key.";
} else {
    switch ($_POST["action"]) {
        case 'get-pois':
            handleGetPois();
            break;
        case 'add-poi':
            handleAddPoi();
            break;
        case 'delete-poi':
            handleDeletePoi();
            break;
        case 'edit-poi-content':
            handleEditPoiContent();
            break;
        case 'edit-poi-location':
            handleEditPoiLocation();
            break;
        default:
            $return["message"] = "The action is not correct";
            $return["success"] = false;
            break;
    }
}

echo json_encode($return);

function handleGetPois()
{
    global $return, $poisManager, $missionsManager;

    try {
        $return["pois"] = [];
        $pois = $poisManager->getAllPois();
        foreach ($pois as $poi) {
            if ($poi->getType() != 3) { // if the poi is not a waypoint
                $json = $poi->toJson();
                $missions = $missionsManager->getAllMissionsInPoi($poi->getId());
                $json["missions"] = [];
                foreach ($missions as $mission) {
                    $json["missions"][] = $mission->toJsonOverview();
                }
                $return["pois"][] = $json;
            }
        }
        $return["success"] = true;
    } catch (Exception $e) {
        $return["success"] = false;
        $return["message"] = $e->getMessage();
    }
}

function handleAddPoi()
{
    global $poisManager;
    global $return;

    if (isset($_POST['type'])) {
        switch (intval($_POST['type'])) {
            case 0: // harbour
                if (isset($_POST['lat']) && isset($_POST['lng']) && isset($_POST['name']) && isset($_POST['creator-id'])  && isset($_POST['water']) && isset($_POST['food']) && isset($_POST['spare-parts']) && isset($_POST['dry-dock'])) {
                    try {
                        $lat = floatval($_POST['lat']);
                        $lng = floatval($_POST['lng']);
                        $name = htmlspecialchars($_POST['name']);
                        $creatorId = intval($_POST['creator-id']);
                        $water = boolval($_POST['water']);
                        $food = boolval($_POST['food']);
                        $spareParts = boolval($_POST['spare-parts']);
                        $dryDock = boolval($_POST['dry-dock']);
                        $harbour = $poisManager->addHarbour($name, $lat, $lng, $creatorId, $water, $food, $spareParts, $dryDock);
                        $return["poi"] = $harbour->toJson();
                        $return["success"] = true;
                    } catch (Exception $e) {
                        $return["success"] = false;
                        $return["message"] = $e->getMessage();
                    }
                } else {
                    $return["success"] = false;
                    $return["message"] = "Not enough information in the request";
                }
                break;
            case 1: // anchorage
                if (isset($_POST['lat']) && isset($_POST['lng']) && isset($_POST['name']) && isset($_POST['creator-id'])) {
                    try {
                        $lat = floatval($_POST['lat']);
                        $lng = floatval($_POST['lng']);
                        $name = htmlspecialchars($_POST['name']);
                        $creatorId = intval($_POST['creator-id']);
                        $anchorage = $poisManager->addAnchorage($name, $lat, $lng, $creatorId);
                        $return["poi"] = $anchorage->toJson();
                        $return["success"] = true;
                    } catch (Exception $e) {
                        $return["success"] = false;
                        $return["message"] = $e->getMessage();
                    }
                } else {
                    $return["success"] = false;
                    $return["message"] = "Not enough information in the request";
                }
                break;
            case 2: // oddity
                if (isset($_POST['lat']) && isset($_POST['lng']) && isset($_POST['name']) && isset($_POST['creator-id']) && isset($_POST['caption'])) {
                    try {
                        $lat = floatval($_POST['lat']);
                        $lng = floatval($_POST['lng']);
                        $name = htmlspecialchars($_POST['name']);
                        $caption = htmlspecialchars($_POST['caption']);
                        $creatorId = intval($_POST['creator-id']);
                        $oddity = $poisManager->addOddity($name, $lat, $lng, $creatorId, $caption);
                        $return["poi"] = $oddity->toJson();
                        $return["success"] = true;
                    } catch (Exception $e) {
                        $return["success"] = false;
                        $return["message"] = $e->getMessage();
                    }
                } else {
                    $return["success"] = false;
                    $return["message"] = "Not enough information in the request";
                }
                break;
            default:
                $return["message"] = "The type is not recognized";
                $return["success"] = false;
        }
    } else {
        $return["success"] = false;
        $return["message"] = "Not enough information in the request";
    }
}

function handleDeletePoi()
{
    global $poisManager;
    global $return;

    if (isset($_POST['poi-id'])) {
        try {
            $id = intval($_POST['poi-id']);
            if(!$poisManager->isPoiUsed($id)){
                $poisManager->remove($id);
                $return["success"] = true;
            }else{
                $return["success"] = false;
                $return["message"] = "This POI is used by a mission. You cannot delete it. Please contact an admin";    
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

function handleEditPoiContent()
{
    global $poisManager;
    global $return;

    if (isset($_POST['poi-id']) && isset($_POST['type']) && isset($_POST['editor-id']) && isset($_POST['name'])) {
        $id = intval($_POST['poi-id']);
        $type = intval($_POST['type']);
        $editor = intval($_POST['editor-id']);
        $name = htmlspecialchars($_POST['name']);
        switch ($type) {
            case 0: // harbour
                if (isset($_POST['water']) && isset($_POST['food']) && isset($_POST['spare-parts']) && isset($_POST['dry-dock'])) {
                    try {
                        $water = boolval($_POST['water']);
                        $food = boolval($_POST['food']);
                        $spareParts = boolval($_POST['spare-parts']);
                        $dryDock = boolval($_POST['dry-dock']);
                        //temp
                        $return["water"] = $water;
                        $return["food"] = $food;
                        //endtemp
                        $return["poi"] = $poisManager->updateHarbourContent($id, $editor, $name, $water, $food, $spareParts, $dryDock)->toJson();
                        $return["success"] = true;
                    } catch (Exception $e) {
                        $return["message"] = $e->getMessage();
                        $return["success"] = false;
                    }
                } else {
                    $return["success"] = false;
                    $return["message"] = "Not enough information in the request";
                }
                break;
            case 1: // anchorage
                try {
                    $return["poi"] = $poisManager->updateAnchorageContent($id, $editor, $name)->toJson();
                    $return["success"] = true;
                } catch (Exception $e) {
                    $return["message"] = $e->getMessage();
                    $return["success"] = false;
                }
                break;
            case 2: // oddity
                if (isset($_POST['caption'])) {
                    try {
                        $caption = htmlspecialchars($_POST['caption']);
                        $return["poi"] = $poisManager->updateOddityContent($id, $editor, $name, $caption)->toJson();
                        $return["success"] = true;
                    } catch (Exception $e) {
                        $return["message"] = $e->getMessage();
                        $return["success"] = false;
                    }
                } else {
                    $return["success"] = false;
                    $return["message"] = "Not enough information in the request";
                }
                break;
            default:
                $return["message"] = "The type is not recognized";
                $return["success"] = false;
        }
    } else {
        $return["success"] = false;
        $return["message"] = "Not enough information in the request";
    }
}

function handleEditPoiLocation()
{
    global $poisManager;
    global $return;

    if (isset($_POST['lat']) && isset($_POST['lng']) && isset($_POST['poi-id']) && isset($_POST['creator-id'])) {
        try {
            $lat = floatval($_POST['lat']);
            $lng = floatval($_POST['lng']);
            $id = intval($_POST['poi-id']);
            $creator = intval($_POST['creator-id']);
            $return["poi"] = $poisManager->updatePoiLocation($id, $creator, $lat, $lng)->toJson();
            $return["success"] = true;
        } catch (Exception $e) {
            $return["message"] = $e->getMessage();
            $return["success"] = false;
        }
    } else {
        $return["success"] = false;
        $return["message"] = "Not enough information in the request";
    }
}
