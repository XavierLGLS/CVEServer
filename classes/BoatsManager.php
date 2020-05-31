<?php

require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'DBManager.php';
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'AppLogsManager.php';
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'AccountsManager.php';
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'Boat.php';

require_once dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'config_v2.php';

class BoatsManager extends DBManager
{
    private $apppLogsManager;

    function __construct()
    {
        parent::__construct();
        $this->appLogsManager = new AppLogsManager();
    }

    /**
     * Returns all cve boats
     * @return array: of boats
     */
    public function getAll(): array
    {
        $output = [];
        $req = "SELECT
                    cve_boats.id AS id,
                    cve_boats.type AS type_id,
                    cve_boats.cve_user_id AS user_id,
                    cve_boats.sw_boat_id AS sw_boat_id,
                    cve_boats.mission_step AS mission_step,
                    cve_boats.dry_dock AS dry_dock,
                    cve_boats.color AS color,
                    sailaway_boats.name AS name,
                    sailaway_boat_characteristics.name AS type_name,
                    sailaway_boat_characteristics.food_capacity AS food_capacity,
                    sailaway_boat_characteristics.water_capacity AS water_capacity,
                    sailaway_boat_characteristics.spare_parts_capacity AS spare_parts_capacity,
                    sailaway_boat_characteristics.additional_passengers_capacity AS passengers_capacity,
                    cve_boats.heading AS heading,
                    cve_boats.weather_coeff AS weather_coeff,
                    cve_boats.stock_food AS food,
                    cve_boats.stock_water AS water,
                    cve_boats.stock_spare_parts AS spare_parts,
                    cve_boats.stock_passengers AS passengers,
                    cve_boats.status_hull AS hull,
                    cve_boats.status_sails AS sails,
                    cve_boats.status_crew_health AS crew_health,
                    cve_boats.status_last_update_date AS last_status_update
                FROM cve_boats
                    INNER JOIN sailaway_boats ON cve_boats.sw_boat_id=sailaway_boats.id
                    INNER JOIN sailaway_boat_characteristics ON cve_boats.type=sailaway_boat_characteristics.type_id";
        $boats = $this->db->query($req)->fetchAll();
        foreach ($boats as $item) {
            if ($item->heading === NULL) {
                $heading = 0;
            } else {
                $heading = $item->heading;
            }
            if ($item->last_status_update === NULL) {
                $lastStatusUpdate = 0;
            } else {
                $lastStatusUpdate = $item->last_status_update;
            }
            if ($item->mission_step === NULL) {
                $missionStep = -1;
            } else {
                $missionStep = $item->mission_step;
            }
            if ($item->dry_dock === NULL) {
                $dryDock = -1;
            } else {
                $dryDock = $item->dry_dock;
            }
            $output[] = new Boat($item->id, $item->type_id, $item->user_id, $item->sw_boat_id, $missionStep, $dryDock, $item->name, $item->type_name, $item->weather_coeff, $item->color, $heading, $item->food, $item->food_capacity, $item->water, $item->water_capacity, $item->spare_parts, $item->spare_parts_capacity, $item->passengers, $item->passengers_capacity, $item->hull, $item->sails, $item->crew_health, $lastStatusUpdate);
        }
        return $output;
    }

    public function getCount(): int
    {
        return count($this->db->query("SELECT id FROM cve_boats")->fetchAll());
    }

    public function getBoatFromId(int $id): Boat
    {
        $req = "SELECT
                    cve_boats.id AS id,
                    cve_boats.type AS type_id,
                    cve_boats.cve_user_id AS user_id,
                    cve_boats.sw_boat_id AS sw_boat_id,
                    cve_boats.mission_step AS mission_step,
                    cve_boats.dry_dock AS dry_dock,
                    cve_boats.color AS color,
                    sailaway_boats.name AS name,
                    sailaway_boat_characteristics.name AS type_name,
                    sailaway_boat_characteristics.food_capacity AS food_capacity,
                    sailaway_boat_characteristics.water_capacity AS water_capacity,
                    sailaway_boat_characteristics.spare_parts_capacity AS spare_parts_capacity,
                    sailaway_boat_characteristics.additional_passengers_capacity AS passengers_capacity,
                    cve_boats.heading AS heading,
                    cve_boats.weather_coeff AS weather_coeff,
                    cve_boats.stock_food AS food,
                    cve_boats.stock_water AS water,
                    cve_boats.stock_spare_parts AS spare_parts,
                    cve_boats.stock_passengers AS passengers,
                    cve_boats.status_hull AS hull,
                    cve_boats.status_sails AS sails,
                    cve_boats.status_crew_health AS crew_health,
                    cve_boats.status_last_update_date AS last_status_update
                FROM cve_boats
                    INNER JOIN sailaway_boats ON cve_boats.sw_boat_id=sailaway_boats.id
                    INNER JOIN sailaway_boat_characteristics ON cve_boats.type=sailaway_boat_characteristics.type_id
                WHERE cve_boats.id=$id";
        $item = $this->db->query($req)->fetch();
        if ($item) {
            if ($item->heading === NULL) {
                $heading = 0;
            } else {
                $heading = $item->heading;
            }
            if ($item->last_status_update === NULL) {
                $lastStatusUpdate = 0;
            } else {
                $lastStatusUpdate = $item->last_status_update;
            }
            if ($item->mission_step === NULL) {
                $missionStep = -1;
            } else {
                $missionStep = $item->mission_step;
            }
            if ($item->dry_dock === NULL) {
                $dryDock = -1;
            } else {
                $dryDock = $item->dry_dock;
            }
            return new Boat($item->id, $item->type_id, $item->user_id, $item->sw_boat_id, $missionStep, $dryDock, $item->name, $item->type_name, $item->weather_coeff, $item->color, $heading, $item->food, $item->food_capacity, $item->water, $item->water_capacity, $item->spare_parts, $item->spare_parts_capacity, $item->passengers, $item->passengers_capacity, $item->hull, $item->sails, $item->crew_health, $lastStatusUpdate);
        } else {
            throw new Exception("No boat found with the id $id", 1);
        }
    }

    public function getTrajectoryFromBoatId(int $id): array
    {
        $items = $this->db->query("SELECT lat, lng, teleportation FROM cve_boat_positions WHERE cve_boat_id=$id ORDER by date DESC")->fetchAll();
        $output = [];
        for ($i = 0; $i < count($items); $i++) {
            $item = $items[$i];
            $pos = [
                "lat" => $item->lat,
                "lng" => $item->lng
            ];
            array_push($output, $pos);
            if ($item->teleportation == 1) {
                break;
            }
        }
        return $output;
    }

    /**
     * Adds a new cve boat
     * @param cve_user_id: id of the cve user account linked to this boat
     * @param sw_boat_id: id of the boat tracked in sailaway
     * @param boat_type: id of the type of the boat
     */
    public function addBoat(int $cve_user_id, int $sw_boat_id, int $boat_type): void
    {
        global $INITIAL_STOCK_FOOD, $INITIAL_STOCK_WATER, $DEFAULT_COLORS;
        $color = hexdec($DEFAULT_COLORS[array_rand($DEFAULT_COLORS, 1)]);
        $this->db->query("INSERT INTO cve_boats (cve_user_id, sw_boat_id, type, stock_water, stock_food, color) VALUES ($cve_user_id, $sw_boat_id, $boat_type, $INITIAL_STOCK_WATER, $INITIAL_STOCK_FOOD, $color)");
    }

    //TODO: remove (when multiple boats)
    /**
     * @param cve_user_id: id of the cve user account
     * @return object: the unique cve boat linked to this user
     */
    public function getBoatFromCVEUser(CVEUser $user): Boat
    {
        $req = "SELECT
                    cve_boats.id AS id,
                    cve_boats.type AS type_id,
                    cve_boats.cve_user_id AS user_id,
                    cve_boats.sw_boat_id AS sw_boat_id,
                    cve_boats.mission_step AS mission_step,
                    cve_boats.dry_dock AS dry_dock,
                    cve_boats.color AS color,
                    sailaway_boats.name AS name,
                    sailaway_boat_characteristics.name AS type_name,
                    sailaway_boat_characteristics.food_capacity AS food_capacity,
                    sailaway_boat_characteristics.water_capacity AS water_capacity,
                    sailaway_boat_characteristics.spare_parts_capacity AS spare_parts_capacity,
                    sailaway_boat_characteristics.additional_passengers_capacity AS passengers_capacity,
                    cve_boats.heading AS heading,
                    cve_boats.weather_coeff AS weather_coeff,
                    cve_boats.stock_food AS food,
                    cve_boats.stock_water AS water,
                    cve_boats.stock_spare_parts AS spare_parts,
                    cve_boats.stock_passengers AS passengers,
                    cve_boats.status_hull AS hull,
                    cve_boats.status_sails AS sails,
                    cve_boats.status_crew_health AS crew_health,
                    cve_boats.status_last_update_date AS last_status_update
                FROM cve_boats
                    INNER JOIN sailaway_boats ON cve_boats.sw_boat_id=sailaway_boats.id
                    INNER JOIN sailaway_boat_characteristics ON cve_boats.type=sailaway_boat_characteristics.type_id
                WHERE cve_boats.cve_user_id=" . $user->getId();
        $item = $this->db->query($req)->fetch();
        if ($item) {
            if ($item->heading === NULL) {
                $heading = 0;
            } else {
                $heading = $item->heading;
            }
            if ($item->last_status_update === NULL) {
                $lastStatusUpdate = 0;
            } else {
                $lastStatusUpdate = $item->last_status_update;
            }
            if ($item->mission_step === NULL) {
                $missionStep = -1;
            } else {
                $missionStep = $item->mission_step;
            }
            if ($item->dry_dock === NULL) {
                $dryDock = -1;
            } else {
                $dryDock = $item->dry_dock;
            }
            return new Boat($item->id, $item->type_id, $item->user_id, $item->sw_boat_id, $missionStep, $dryDock, $item->name, $item->type_name, $item->weather_coeff, $item->color, $heading, $item->food, $item->food_capacity, $item->water, $item->water_capacity, $item->spare_parts, $item->spare_parts_capacity, $item->passengers, $item->passengers_capacity, $item->hull, $item->sails, $item->crew_health, $lastStatusUpdate);
        } else {
            throw new Exception("No boat found with the cve_user_id " . $user->getId(), 1);
        }
    }

    /**
     * Returns all boats linked to a cve user account
     * @param cve_user_id: id of the cve user account
     * @return array: of boats
     */
    public function getAllFromCVEUser(CVEUser $user): array
    {
        $req = $this->db->query("SELECT * FROM cve_boats WHERE cve_user_id=" . $user->getId());
        return $req->fetchAll();
    }

    /**
     * Returns all cve boats tracked by the defined sailaway boat
     * @param sw_boat_id: id of the sailaway boat
     * @return array: of boats
     */
    public function getAllFromSWBoat(int $sw_boat_id): array
    {
        $items = $this->db->query("SELECT id FROM cve_boats WHERE sw_boat_id=$sw_boat_id")->fetchAll();
        $output = [];
        foreach ($items as $item) {
            $output[] = $this->getBoatFromId($item->id);
        }
        return $output;
    }

    /**
     * Updates the cve boat location
     * @param cve_boat_id: id of the cve boat
     * @param newLat: new latitude coordinate (between -180 and 180) of the cve boat
     * @param newLng: new longitude coordinate (between -180 and 180) of the cve boat
     * @param newHeading: new heading of the cve boat
     */
    public function updateLocation(Boat $cveBoat, float $newLat, float $newLng, float $newHeading): void
    {
        $now = (new DateTime())->getTimestamp();
        $cve_boat_id = $cveBoat->getId();
        //teleportation check
        $previousPos = $this->db->query("SELECT * FROM cve_boat_positions WHERE cve_boat_id=$cve_boat_id ORDER BY DATE DESC")->fetch();
        $teleportation = 0;
        if ($previousPos) {
            $distanceNm = $this->distance($previousPos->lat, $previousPos->lng, $newLat, $newLng, "N");
            $req = "
            SELECT
                cve_boats.id AS id,
                sailaway_boats.name AS boat_name,
                sailaway_users.name AS username,
                sailaway_boat_characteristics.max_speed AS max_speed,
                sailaway_boat_characteristics.name AS boat_type
            FROM cve_boats
                INNER JOIN sailaway_boats ON sailaway_boats.id=cve_boats.sw_boat_id
                INNER JOIN cve_users ON cve_users.id=cve_boats.cve_user_id
                INNER JOIN sailaway_users ON cve_users.sw_user_id=sailaway_users.id
                INNER JOIN sailaway_boat_characteristics ON cve_boats.type=sailaway_boat_characteristics.type_id
            WHERE
                cve_boats.id=$cve_boat_id
            ";
            $boat  = $this->db->query($req)->fetch();
            if ($boat) {
                $elapsedHours = ($now - $previousPos->date) / 3600;
                $detectedSpeed = $distanceNm / $elapsedHours;
                if ($detectedSpeed > $boat->max_speed) {
                    // teleportation !
                    $teleportation = 1;
                    $username = rawurldecode($boat->username);
                    $boatName = rawurldecode($boat->boat_name);
                    $boatType = rawurldecode($boat->boat_type);
                    $this->cancelMission($this->getBoatFromId($boat->id));
                    $this->appLogsManager->addStringLog(rawurlencode("Teleportation detected for $username using \"$boatName\" ($boatType), detected speed: " .  round($detectedSpeed, 2) . " knt"));
                }
            }
        }
        //update the trajectory        
        $this->db->query("INSERT INTO cve_boat_positions (cve_boat_id, lat, lng, date, teleportation) VALUES ($cve_boat_id, $newLat, $newLng, $now, $teleportation)");
        //update the heading
        $this->db->query("UPDATE cve_boats SET heading=$newHeading WHERE id=$cve_boat_id");
    }

    public function consumeFood(Boat $boat)
    {
        $newStock = $boat->getFoodStock() - (1 + $boat->getPassengers());
        $id = $boat->getId();
        if ($newStock > 0) {
            $this->db->query("UPDATE cve_boats SET stock_food=$newStock WHERE id=$id");
        } else {
            $this->db->query("UPDATE cve_boats SET stock_food=0 WHERE id=$id");
        }
    }

    public function consumeWater(Boat $boat)
    {
        $newStock = $boat->getWaterStock() - (1 + $boat->getPassengers());
        $id = $boat->getId();
        if ($newStock > 0) {
            $this->db->query("UPDATE cve_boats SET stock_water=$newStock WHERE id=$id");
        } else {
            $this->db->query("UPDATE cve_boats SET stock_water=0 WHERE id=$id");
        }
    }

    public function updateBoatState(Boat $boat)
    {
        $weatherCoeff = $boat->getWeatherCoeff();
        $lastStatusUpdate = $boat->getLastStateUpdateDate();
        $now = (new DateTime())->getTimestamp();
        if ($lastStatusUpdate != 0) {
            $elapsedMin = ($now - $lastStatusUpdate) / 60;
            $id = $boat->getId();

            // hull
            $hullState = $boat->getHullState();
            if ($hullState > 20) {
                $damage = $weatherCoeff * $elapsedMin * 20 / 172800; // lose of 20% for 4 months
            } else {
                $damage = $weatherCoeff * $elapsedMin * 80 / 43200; // lose of 80% for 1 month
            }
            if ($hullState - $damage < 0) {
                $this->db->query("UPDATE cve_boats SET status_hull = 0 WHERE id=$id");
            } else {
                $this->db->query("UPDATE cve_boats SET status_hull = status_hull - $damage WHERE id=$id");
            }

            // sails
            $sailsState = $boat->getSailsState();
            if ($sailsState > 20) {
                $damage = 2 * $weatherCoeff * $elapsedMin * 20 / 172800; // lose of 20% for 4 months
            } else {
                $damage = 2 * $weatherCoeff * $elapsedMin * 80 / 43200; // lose of 80% for 1 month
            }
            if ($sailsState - $damage < 0) {
                $this->db->query("UPDATE cve_boats SET status_sails = 0 WHERE id=$id");
            } else {
                $this->db->query("UPDATE cve_boats SET status_sails = status_hull - $damage WHERE id=$id");
            }

            // crew health
            $crewHealth = $boat->getCrewHealth();
            if ($boat->getFoodStock() == 0 || $boat->getWaterStock() == 0) {
                $newCrewHealth = $crewHealth - 100 * $weatherCoeff * $elapsedMin / (3 * 24 * 60); // lose of 100% in 3 days
                if ($newCrewHealth < 0) {
                    $newCrewHealth = 0;
                }
                $this->db->query("UPDATE cve_boats SET status_crew_health=$newCrewHealth WHERE id=$id");
            } else if ($crewHealth < 100 && $weatherCoeff < 2) {
                $newCrewHealth = $crewHealth + 100 * $elapsedMin / (3 * 24 * 60); // complete regeneration in 3 days
                if ($newCrewHealth > 100) {
                    $newCrewHealth = 100;
                }
            }
        }
        $this->db->query("UPDATE cve_boats SET status_last_update_date=$now WHERE id=" . $boat->getId());
    }

    /**
     * Remove all positions that are older than the TRAJECTORY_DURATION defined in the config file
     */
    public function removeOldPositions(): void
    {
        global $TRAJECTORY_DURATION;

        // detection of last positions
        $items = $this->db->query("SELECT MAX(id) AS id FROM cve_boat_positions GROUP BY cve_boat_id")->fetchAll();
        $exceptions = [];
        foreach ($items as $item) {
            $exceptions[] = $item->id;
        }

        $positions = $this->db->query("SELECT * FROM cve_boat_positions WHERE id NOT IN (" . join(", ", $exceptions) . ")");
        $partials = [];
        foreach ($positions as $position) {
            $elapsedSec = (new DateTime())->getTimestamp() - $position->date;
            if ($elapsedSec > ($TRAJECTORY_DURATION * 3600)) {
                $partials[] = $position->id;
            }
        }
        if (count($partials) > 0) {
            $this->db->query("DELETE FROM cve_boat_positions WHERE id IN (" . join(", ", $partials) . ")");
        }
    }

    /**
     * @return array: past trajectory of the cve boat
     */
    public function getTrajectory(int $cve_boat_id): array
    {
        $traj = $this->db->query("SELECT * FROM cve_boat_positions WHERE cve_boat_id=$cve_boat_id ORDER BY date")->fetchAll();
        $output = [];
        $index = count($traj) - 1;
        //only get the past trajectory since the last teleportation
        while ($index >= 0) {
            array_push($output, $traj[$index]);
            if ($traj[$index]->teleportation) {
                break;
            } else {
                $index--;
            }
        }
        return $output;
    }

    public function getLastPos(Boat $boat): array
    {
        $boatId = $boat->getId();
        $traj = $this->db->query("SELECT lat, lng FROM cve_boat_positions WHERE cve_boat_id=$boatId ORDER BY date DESC")->fetch();
        $output = [];
        if (count($traj) > 0) {
            $output["lat"] = $traj->lat;
            $output["lng"] = $traj->lng;
        } else {
            $output["lat"] = NULL;
            $output["lng"] = NULL;
        }
        return $output;
    }


    public function repairBoatHull(Boat $boat): Boat
    {
        $id = $boat->getId();
        $spareParts = $boat->getSparePartsStock();
        if ($spareParts > 0) {
            $currentLevel = $boat->getHullState();
            if ($currentLevel >= 90) {
                $newLevel = 100;
            } else {
                $newLevel = $currentLevel + 10;
            }
            $newSpareParts = $spareParts - 1;
            $this->db->query("UPDATE cve_boats SET stock_spare_parts=$newSpareParts, status_hull=$newLevel WHERE id=$id");
        }
        return $this->getBoatFromId($id);
    }

    public function repairBoatSails(Boat $boat): Boat
    {
        $id = $boat->getId();
        $spareParts = $boat->getSparePartsStock();
        if ($spareParts > 0) {
            $currentLevel = $boat->getSailsState();
            if ($currentLevel >= 90) {
                $newLevel = 100;
            } else {
                $newLevel = $currentLevel + 10;
            }
            $newSpareParts = $spareParts - 1;
            $this->db->query("UPDATE cve_boats SET stock_spare_parts=$newSpareParts, status_sails=$newLevel WHERE id=$id");
        }
        return $this->getBoatFromId($id);
    }

    public function buyBoatItems(Boat $boat, CVEUser $user, array $items): Boat
    {
        global $PRICE_FOOD, $PRICE_SPARE_PART, $PRICE_WATER;

        $totalCost = intval($items['food']) * $PRICE_FOOD + intval($items['spare_parts']) * $PRICE_SPARE_PART + intval($items['water']) * $PRICE_WATER;
        if ($totalCost > $user->getMoney()) {
            throw new Exception("You cannot afford this !");
        } else {
            $boatId = $boat->getId();
            $food = $items['food'];
            $water = $items['water'];
            $spareParts = $items['spare_parts'];
            $this->db->query("UPDATE cve_boats SET stock_food = stock_food + $food, stock_water = stock_water + $water, stock_spare_parts = stock_spare_parts + $spareParts WHERE id=$boatId");
            $userId = $user->getId();
            $this->db->query("UPDATE cve_users SET money = money - $totalCost WHERE id=$userId");
        }
        return $this->getBoatFromId($boat->getId());
    }

    public function setMission(Boat $boat, Mission $mission): void
    {
        $boatId = $boat->getId();
        $missionId = $mission->getId();
        $firstStepId = $this->db->query("SELECT id FROM mission_steps WHERE mission=$missionId AND rank=0")->fetch()->id;
        $passengers = $mission->getPassengers();
        $this->db->query("UPDATE cve_boats SET mission_step=$firstStepId, stock_passengers=$passengers WHERE id=$boatId");
    }

    public function setWeatherCoeff(Boat $boat, float $newCoeff): void
    {
        $boatId = $boat->getId();
        $this->db->query("UPDATE cve_boats SET weather_coeff=$newCoeff WHERE id=$boatId");
    }

    public function cancelMission(Boat $boat): void
    {
        $boatId = $boat->getId();
        $this->db->query("UPDATE cve_boats SET mission_step=NULL, stock_passengers=0 WHERE id=$boatId");
    }

    public function putInDryDock(Boat $boat, Harbour $harbour): void
    {
        $boatId = $boat->getId();
        $harbourId = $harbour->getId();
        $lat = $harbour->getLocation()['lat'];
        $lng = $harbour->getLocation()['lng'];
        $this->db->query("UPDATE cve_boats SET dry_dock=$harbourId WHERE id=$boatId");
        $now = (new DateTime())->getTimestamp();
        $this->db->query("INSERT INTO cve_boat_positions (cve_boat_id, lat, lng, date, teleportation) VALUES ($boatId, $lat, $lng, $now, 0)");
    }

    public function exitFromDryDock(Boat $boat): void
    {
        $boatId = $boat->getId();
        $this->db->query("UPDATE cve_boats SET dry_dock=NULL WHERE id=$boatId");
    }

    public function repairInDryDock(boat $boat): void
    {
        global $DRY_DOCK_REPAIR;

        $hull = $boat->getHullState();
        $sails = $boat->getSailsState();
        if ($hull < 100 || $sails < 100) {
            $boatId = $boat->getId();
            $newHull = $hull + $DRY_DOCK_REPAIR;
            if ($newHull > 100) {
                $newHull = 100;
            }
            $newSails = $sails + $DRY_DOCK_REPAIR;
            if ($newSails > 100) {
                $newSails = 100;
            }
            $this->db->query("UPDATE cve_boats SET status_hull=$newHull, status_sails=$newSails WHERE id=$boatId");
        }
    }

    public function setBoatColor(Boat $boat, int $color): void
    {
        $id = $boat->getId();
        $this->db->query("UPDATE cve_boats SET color=$color WHERE id=$id");
    }
}
