<?php
// header('Content-Type: application/json');

require_once dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'config_v2.php';
require_once dirname(__FILE__) .  DIRECTORY_SEPARATOR . 'tools.php';
require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'SailawayBoatsManager.php';
$sailawayBoatsManager = new SailawayBoatsManager();

// $return;
// $return["success"] = false;

if (!isRequestAuthenticated()) {
    // $return["message"] = "You are not authorized to use this service. Please use a right key.";
    addFlashError('You are not authorized to use this service. Please use a right key');
} else {
    $boat_types = $sailawayBoatsManager->getBoatsCharacteristics();
    foreach ($boat_types as $boat_type) {
        //food capacity
        if (isset($_POST['food_capacity_' . $boat_type->type_id])) {
            $sailawayBoatsManager->setBoatFoodCapacity($boat_type->type_id, intval($_POST['food_capacity_' . $boat_type->type_id]));
        }
        if (isset($_POST['water_capacity_' . $boat_type->type_id])) {
            $sailawayBoatsManager->setBoatWaterCapacity($boat_type->type_id, intval($_POST['water_capacity_' . $boat_type->type_id]));
        }
        if (isset($_POST['max_speed_' . $boat_type->type_id])) {
            $sailawayBoatsManager->setSailawayBoatMaxSpeed($boat_type->type_id, intval($_POST['max_speed_' . $boat_type->type_id]));
        }
        if (isset($_POST['spare_parts_capacity_' . $boat_type->type_id])) {
            $sailawayBoatsManager->setBoatSparePartsCapacity($boat_type->type_id, intval($_POST['spare_parts_capacity_' . $boat_type->type_id]));
        }
        if (isset($_POST['passengers_capacity_' . $boat_type->type_id])) {
            $sailawayBoatsManager->setBoatPassengersCapacity($boat_type->type_id, intval($_POST['passengers_capacity_' . $boat_type->type_id]));
        }
        //other characteristics...
    }
    addFlashSuccess('Characteristics updated !');
    // $return["success"] = true;
    // $return["message"] = "capacities updated !";
}

// echo json_encode($return);
header('Location:../pages/boat_characteristics_management');
