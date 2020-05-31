<?php

require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'DBManager.php';
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'PoisManager.php';
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'AccountsManager.php';
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'MissionStep.php';
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'Mission.php';

require_once dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'config_v2.php';

class MissionsManager extends DBManager
{
    private $poisManager, $accountsManager;

    function __construct()
    {
        parent::__construct();
        $this->poisManager = new PoisManager();
        $this->accountsManager = new AccountsManager();
    }

    private function cancelAllPendingMissions(int $missionId)
    {
        $req = "SELECT cve_boats.id AS id
                FROM missions
                    INNER JOIN mission_steps ON mission_steps.mission=missions.id
                    INNER JOIN cve_boats ON cve_boats.mission_step=mission_steps.id
                WHERE missions.id=$missionId";
        $items = $this->db->query($req)->fetchAll();
        $partials = [];
        foreach ($items as $item) {
            $partials[] = $item->id;
        }
        if (count($partials) > 0) {
            $this->db->query("UPDATE cve_boats SET mission_step=NULL WHERE id IN (" . join(", ", $partials) . ")");
        }
    }

    private function getMissionReward(array $steps, array $places_of_availability, int $passengers): int
    {
        if (count($steps) > 0 && count($places_of_availability) > 0) {
            global $MISSION_REWARD_COEFF, $return;
            // extract lat and lng
            $stepsCoordinates = array_map(function ($step) {
                $output = [];
                if (isset($step["poi-id"])) {
                    $poi = $this->poisManager->getPoiById(intval($step["poi-id"]));
                    $output = $poi->getLocation();
                } else if (isset($step["lat"]) && isset($step["lng"])) {
                    $output["lat"] = floatval($step["lat"]);
                    $output["lng"] = floatval($step["lng"]);
                } else {
                    throw new Exception("unable to find step location");
                }
                return $output;
            }, $steps);
            $placesOfAvailabilityCoordinates = array_map(function ($id) {
                return $this->poisManager->getPoiById($id)->getLocation();
            }, $places_of_availability);
            // cumulated distance between steps
            $totalDistance = 0;
            for ($i = 0; $i < count($stepsCoordinates) - 1; $i++) {
                $totalDistance += $this->distance($stepsCoordinates[$i]["lat"], $stepsCoordinates[$i]["lng"], $stepsCoordinates[$i + 1]["lat"], $stepsCoordinates[$i + 1]["lng"], "N");
            }
            $nearestCoord = array_reduce($placesOfAvailabilityCoordinates, function ($a, $b) use ($stepsCoordinates) {
                if ($a === NULL) {
                    return $b;
                }
                $distA = $this->distance($a["lat"], $a["lng"], $stepsCoordinates[0]["lat"], $stepsCoordinates[0]["lng"], "N");
                $distB = $this->distance($b["lat"], $b["lng"], $stepsCoordinates[0]["lat"], $stepsCoordinates[0]["lng"], "N");
                if ($distA < $distB) {
                    return $a;
                } else {
                    return $b;
                }
            });
            // min distance between places of availability and first step
            $totalDistance += $this->distance($nearestCoord["lat"], $nearestCoord["lng"], $stepsCoordinates[0]["lat"], $stepsCoordinates[0]["lng"], "N");
            return $MISSION_REWARD_COEFF * $totalDistance * pow(1 + $passengers, 0.8);
        } else {
            return 0;
        }
    }

    public function updateMissionRewards(): void
    {
        $missions = $this->getAllMissions();
        foreach ($missions as $mission) {
            $steps = array_map(function ($item) {
                $output = [];
                $output["poi-id"] = $item->getPoiId();
                return $output;
            }, $mission->getSteps());
            $places = array_map(function ($item) {
                return $item->getId();
            }, $mission->getPlacesOfAvailability());
            $newReward = $this->getMissionReward($steps, $places, $mission->getPassengers());
            $missionId = $mission->getId();
            $this->db->query("UPDATE missions SET reward=$newReward WHERE id=$missionId");
        }
    }

    public function getMissionById(int $id): Mission
    {
        $missionReq = " SELECT
                            missions.title AS title,
                            missions.caption AS caption,
                            missions.reward AS reward,
                            missions.type AS type,
                            missions.passengers AS passengers,
                            cve_users.id AS creator
                        FROM missions
                            INNER JOIN cve_users ON cve_users.id=missions.creator
                        WHERE missions.id=$id";
        $missionItem = $this->db->query($missionReq)->fetch();
        if (!$missionItem) {
            throw new Exception("No mission found for the id $id");
        }
        // steps
        $stepReq = "SELECT
                        mission_steps.id AS id,
                        mission_steps.poi AS poi_id,
                        mission_steps.caption AS caption,
                        mission_steps.rank AS rank
                    FROM mission_steps
                    WHERE mission_steps.mission=$id";
        $stepItems = $this->db->query($stepReq)->fetchAll();
        $steps = [];
        foreach ($stepItems as $item) {
            $steps[] = new MissionStep($item->id, $this->poisManager->getPoiById($item->poi_id), $item->caption, $item->rank);
        }
        // places
        $placesReq = "  SELECT
                            poi
                        FROM assoc_pois_missions
                        WHERE mission=$id";
        $placeItems = $this->db->query($placesReq)->fetchAll();
        $places = [];
        foreach ($placeItems as $item) {
            $places[] = $this->poisManager->getPoiById($item->poi);
        }
        return new Mission($id, $missionItem->title, $missionItem->type, $missionItem->reward, $missionItem->passengers, $this->accountsManager->getUserFromId($missionItem->creator), $missionItem->caption, $steps, $places);
    }

    public function getMissionByStepId(int $id): Mission
    {
        $missionId = $this->db->query("SELECT mission FROM mission_steps WHERE id=$id")->fetch()->mission;
        return $this->getMissionById($missionId);
    }

    /**
     * @param pois_of_availability: array of int
     * @param steps: array of [("poi-id" => int or ("lat" => float, "lng" => float)), "caption" => string ]
     */
    public function addMission(string $title, string $caption, int $passengers, array $steps, array $pois_of_availability, int $creator): Mission
    {
        $reward = $this->getMissionReward($steps, $pois_of_availability, $passengers);
        // missions table
        $missionsReq = "INSERT INTO missions (title, reward, passengers, caption, creator) VALUES (" . $this->db->quote($title) . ", $reward, $passengers, " . $this->db->quote($caption) . ", $creator)";
        $this->db->query($missionsReq); //TODO: Get id from PDOStatement instead of lastInsertId ?
        $missionId = $this->db->lastInsertId();

        // assoc_pois_missions table
        $assocPoisMissionsReq = "INSERT INTO assoc_pois_missions (mission, poi) VALUES ";
        foreach ($pois_of_availability as $poi_id) {
            $assocPoisMissionsReq .= (substr($assocPoisMissionsReq, -1) == " " ? "" : ", ") . "($missionId, $poi_id)";
        }
        $this->db->query($assocPoisMissionsReq);

        $rank = 0;
        $missionStepsReq = "INSERT INTO mission_steps (mission, poi, rank, caption) VALUES ";
        // mission_steps table
        foreach ($steps as $step) {
            $poiId = 0;
            if (isset($step["poi-id"])) {
                $poiId = $step["poi-id"];
            } else {
                $poiId = $this->poisManager->addWaypoint($step["lat"], $step["lng"], $creator)->getId();
            }
            $missionStepsReq .= (substr($missionStepsReq, -1) == " " ? "" : ", ") . "($missionId, $poiId, $rank, " . $this->db->quote($step["caption"]) . ")";
            $rank++;
        }
        $this->db->query($missionStepsReq);

        return $this->getMissionById($missionId);
    }

    public function removeMission(int $id): void
    {
        // cancel all pending missions
        $this->cancelAllPendingMissions($id);

        // delete all waypoints linked to this mission
        $waypoints = $this->db->query("SELECT pois.id FROM pois INNER JOIN mission_steps ON mission_steps.poi=pois.id INNER JOIN missions ON mission_steps.mission=missions.id WHERE missions.id=$id AND pois.type=3")->fetchAll();
        if (count($waypoints) > 0) {
            $partials = [];
            foreach ($waypoints as $waypoint) {
                $partials[] = $waypoint->id;
            }
            $this->db->query("DELETE FROM pois WHERE id IN (" . join(", ", $partials) . ")");
        }

        $this->db->query("DELETE FROM missions WHERE id=$id");
        $this->db->query("DELETE FROM mission_steps WHERE mission=$id");
        $this->db->query("DELETE FROM assoc_pois_missions WHERE mission=$id");
    }

    public function isMissionUsed(int $id): bool
    {
        $stepIds = array_map(function ($item) {
            return $item->id;
        }, $this->db->query("SELECT id FROM mission_steps WHERE mission=$id")->fetchAll());

        if (count($this->db->query("SELECT id FROM cve_boats WHERE mission_step IN (" . join(", ", $stepIds) . ")")->fetchAll()) > 0) {
            return true;
        } else {
            return false;
        }
    }

    public function getAllMissions(): array
    {
        global $return;
        $missionsReq = "SELECT
                            missions.id AS id,
                            missions.title AS title,
                            missions.reward AS reward,
                            missions.type AS type,
                            missions.passengers AS passengers,
                            missions.caption AS caption,
                            cve_users.id AS creator
                        FROM missions
                            INNER JOIN cve_users ON cve_users.id=missions.creator";
        $missionItems = $this->db->query($missionsReq)->fetchAll();
        $output = [];
        foreach ($missionItems as $missionItem) {
            // steps
            $stepReq = "SELECT
                        mission_steps.id AS id,
                        mission_steps.poi AS poi_id,
                        mission_steps.caption AS caption,
                        mission_steps.rank AS rank
                    FROM mission_steps
                    WHERE mission_steps.mission=$missionItem->id";
            $stepItems = $this->db->query($stepReq)->fetchAll();
            $steps = [];
            foreach ($stepItems as $item) {
                $steps[] = new MissionStep($item->id, $this->poisManager->getPoiById($item->poi_id), $item->caption, $item->rank);
            }
            // places
            $placesReq = "  SELECT
                            poi
                        FROM assoc_pois_missions
                        WHERE mission=$missionItem->id";
            $placeItems = $this->db->query($placesReq)->fetchAll();
            $places = [];
            foreach ($placeItems as $item) {
                $places[] = $this->poisManager->getPoiById($item->poi);
            }
            $output[] = new Mission($missionItem->id, $missionItem->title, $missionItem->type, $missionItem->reward, $missionItem->passengers, $this->accountsManager->getUserFromId($missionItem->creator), $missionItem->caption, $steps, $places);
        }
        return $output;
    }

    public function getMissionsMatching(string $field): array
    {
        $missionsReq = "SELECT
                            missions.id AS id,
                            missions.title AS title,
                            missions.reward AS reward,
                            missions.type AS type,
                            missions.passengers AS passengers,
                            missions.caption AS caption,
                            cve_users.id AS creator
                        FROM missions
                            INNER JOIN cve_users ON cve_users.id=missions.creator
                        WHERE missions.title LIKE " . $this->db->quote("%" . $field . "%");
        $missionItems = $this->db->query($missionsReq)->fetchAll();
        $output = [];
        foreach ($missionItems as $missionItem) {
            // steps
            $stepReq = "SELECT
                        mission_steps.id AS id,
                        mission_steps.poi AS poi_id,
                        mission_steps.caption AS caption,
                        mission_steps.rank AS rank
                    FROM mission_steps
                    WHERE mission_steps.mission=$missionItem->id";
            $stepItems = $this->db->query($stepReq)->fetchAll();
            $steps = [];
            foreach ($stepItems as $item) {
                $steps[] = new MissionStep($item->id, $this->poisManager->getPoiById($item->poi_id), $item->caption, $item->rank);
            }
            // places
            $placesReq = "  SELECT
                            poi
                        FROM assoc_pois_missions
                        WHERE mission=$missionItem->id";
            $placeItems = $this->db->query($placesReq)->fetchAll();
            $places = [];
            foreach ($placeItems as $item) {
                $places[] = $this->poisManager->getPoiById($item->poi);
            }
            $output[] = new Mission($missionItem->id, $missionItem->title, $missionItem->type, $missionItem->reward, $missionItem->passengers, $this->accountsManager->getUserFromId($missionItem->creator), $missionItem->caption, $steps, $places);
        }
        return $output;
    }

    public function updateMission(int $missionId, string $title, string $caption, int $passengers, array $steps, array $places_of_availability, int $creator): void
    {
        // cancel all pending missions
        $this->cancelAllPendingMissions($missionId);

        $reward = $this->getMissionReward($steps, $places_of_availability, $passengers);

        $missionReq = "UPDATE missions SET
                            title=" . $this->db->quote($title) . ",
                            caption=" . $this->db->quote($caption) . ",
                            reward=$reward, 
                            passengers=$passengers
                        WHERE id=$missionId";
        $this->db->query($missionReq);

        // delete all waypoints linked to this mission
        $waypoints = $this->db->query("SELECT pois.id FROM pois INNER JOIN mission_steps ON mission_steps.poi=pois.id INNER JOIN missions ON mission_steps.mission=missions.id WHERE missions.id=$missionId AND pois.type=3")->fetchAll();
        if (count($waypoints) > 0) {
            $partials = [];
            foreach ($waypoints as $waypoint) {
                $partials[] = $waypoint->id;
            }
            $this->db->query("DELETE FROM pois WHERE id IN (" . join(", ", $partials) . ")");
        }

        // steps
        $this->db->query("DELETE FROM mission_steps WHERE mission=$missionId");
        $rank = 0;
        $missionStepsReq = "INSERT INTO mission_steps (mission, poi, rank, caption) VALUES ";
        // mission_steps table
        foreach ($steps as $step) {
            $poiId = 0;
            if (isset($step["poi-id"])) {
                $return["test"][] = "poi";
                $poiId = $step["poi-id"];
            } else {
                $return["test"][] = "waypoint";
                $poiId = ($this->poisManager->addWaypoint($step["lat"], $step["lng"], $creator))->getId();
            }
            $missionStepsReq .= (substr($missionStepsReq, -1) == " " ? "" : ", ") . "($missionId, $poiId, $rank, " . $this->db->quote($step["caption"]) . ")";
            $rank++;
        }
        $this->db->query($missionStepsReq);

        // places
        $this->db->query("DELETE FROM assoc_pois_missions WHERE mission=$missionId");
        $assocPoisMissionsReq = "INSERT INTO assoc_pois_missions (mission, poi) VALUES ";
        foreach ($places_of_availability as $poi_id) {
            $assocPoisMissionsReq .= (substr($assocPoisMissionsReq, -1) == " " ? "" : ", ") . "($missionId, $poi_id)";
        }
        $this->db->query($assocPoisMissionsReq);
    }

    public function getAllMissionsInPoi(int $poi_id): array
    {
        $missionsReq = "SELECT
                            missions.id AS id,
                            missions.title AS title,
                            missions.reward AS reward,
                            missions.type AS type,
                            missions.passengers AS passengers,
                            missions.caption AS caption,
                            cve_users.id AS creator
                        FROM missions
                            INNER JOIN cve_users ON cve_users.id=missions.creator
                            INNER JOIN assoc_pois_missions ON assoc_pois_missions.mission=missions.id
                        WHERE assoc_pois_missions.poi=$poi_id";
        $missionItems = $this->db->query($missionsReq)->fetchAll();
        $output = [];
        foreach ($missionItems as $missionItem) {
            // steps
            $stepReq = "SELECT
                        mission_steps.id AS id,
                        mission_steps.poi AS poi_id,
                        mission_steps.caption AS caption,
                        mission_steps.rank AS rank
                    FROM mission_steps
                    WHERE mission_steps.mission=$missionItem->id";
            $stepItems = $this->db->query($stepReq)->fetchAll();
            $steps = [];
            foreach ($stepItems as $item) {
                $steps[] = new MissionStep($item->id, $this->poisManager->getPoiById($item->poi_id), $item->caption, $item->rank);
            }
            // places
            $placesReq = "  SELECT
                            poi
                        FROM assoc_pois_missions
                        WHERE mission=$missionItem->id";
            $placeItems = $this->db->query($placesReq)->fetchAll();
            $places = [];
            foreach ($placeItems as $item) {
                $places[] = $this->poisManager->getPoiById($item->poi);
            }
            $output[] = new Mission($missionItem->id, $missionItem->title, $missionItem->type, $missionItem->reward, $missionItem->passengers, $this->accountsManager->getUserFromId($missionItem->creator), $missionItem->caption, $steps, $places);
        }
        return $output;
    }

    public function validateStep(Boat $boat): void
    {
        $boatId = $boat->getId();
        $stepId = $boat->getStepId();
        $stepItem = $this->db->query("SELECT mission, rank FROM mission_steps WHERE id=$stepId")->fetch();
        $missionId = $stepItem->mission;
        $newRank = $stepItem->rank + 1;
        $mission = $this->getMissionById($missionId);
        $count = $mission->getStepCount();
        if ($newRank < $count) {
            // next step
            $newStepId = $this->db->query("SELECT id FROM mission_steps WHERE rank=$newRank AND mission=$missionId")->fetch()->id;
            $this->db->query("UPDATE cve_boats SET mission_step=$newStepId WHERE id=$boatId");
        } else {
            // finished
            $userId = $boat->getCveUserId();
            $reward = $mission->getReward();
            $this->db->query("UPDATE cve_boats SET mission_step=NULL WHERE id=$boatId");
            $this->db->query("UPDATE cve_boats SET stock_passengers=0 WHERE id=$boatId");
            $this->db->query("UPDATE cve_users SET money = money + $reward WHERE id=$userId");
        }
    }

    public function getDistanceToNextStep(Boat $boat): float
    {
        $boatId = $boat->getId();
        $stepId = $boat->getStepId();
        $poiId = $this->db->query("SELECT poi FROM mission_steps WHERE id=$stepId")->fetch()->poi;
        $lastPos = $this->db->query("SELECT lat, lng FROM cve_boat_positions WHERE cve_boat_id=$boatId ORDER BY date DESC")->fetch();
        $poi = $this->poisManager->getPoiById($poiId);
        return $this->distance($poi->getLocation()["lat"], $poi->getLocation()["lng"], $lastPos->lat, $lastPos->lng, "N");
    }

    public function addAutoGeneratedMission(int $start, int $end, int $passengers, int $reward, string $name): void
    {
        global $AUTO_GENERATED_MISSIONS_CREATOR;
        $this->db->query("INSERT INTO missions (type, reward, passengers, creator, title, caption) VALUES (0, $reward, $passengers, $AUTO_GENERATED_MISSIONS_CREATOR, " . $this->db->quote($name) . ", " . $this->db->quote("") . ")");
        $missionId = $this->db->lastInsertId();
        $this->db->query("INSERT INTO mission_steps (mission, poi, rank, caption) VALUES ($missionId, $end, 0, " . $this->db->quote("") . ")");
        $this->db->query("INSERT INTO assoc_pois_missions (mission, poi) VALUES ($missionId, $start)");
    }
}
