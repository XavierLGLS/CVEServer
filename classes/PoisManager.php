<?php

require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'DBManager.php';
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'Harbour.php';
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'Anchorage.php';
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'Oddity.php';
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'Waypoint.php';

class PoisManager extends DBManager
{
    public function getPoiById(int $id): Poi
    {
        $req = "SELECT
                pois.id AS id,
                pois.type AS type,
                pois.lat AS lat,
                pois.lng AS lng,
                pois.name AS name,
                sailaway_users.name AS creator,
                pois.provide_water AS water,
                pois.sell_food AS food,
                pois.sell_spare_parts AS spare_parts,
                pois.dry_dock AS dry_dock,
                pois.caption AS caption
            FROM pois
                LEFT OUTER JOIN cve_users ON pois.creator=cve_users.id
                LEFT OUTER JOIN sailaway_users ON cve_users.sw_user_id=sailaway_users.id
            WHERE pois.id=$id";
        $item = $this->db->query($req)->fetch();
        if ($item) {
            $creator = $item->creator;
            if ($creator === NULL) {
                $creator = "";
            }
            switch ($item->type) {
                case 0: // harbour
                    return new Harbour($item->id, $item->lat, $item->lng, $creator, $item->name, ($item->water ? true : false), ($item->food ? true : false), ($item->spare_parts ? true : false), ($item->dry_dock ? true : false));
                    break;
                case 1: // anchorage
                    return new Anchorage($id, $item->lat, $item->lng, $creator, $item->name);
                    break;
                case 2: // oddity
                    return new Oddity($item->id, $item->lat, $item->lng, $creator, $item->name, $item->caption);
                    break;
                case 3: // waypoint
                    return new Waypoint($item->id, $item->lat, $item->lng, $creator);
                    break;
                default:
                    throw new Exception("Unrecognized poi type in PoisManager::getPoiById()", 1);
                    break;
            }
        } else {
            throw new Exception("No Poi found with id $id", 1);
        }
    }

    /**
     * Returns all harbours stored in the database
     * @return array of harbours
     */
    public function getAllPois(): array
    {
        $output = [];
        $items = $this->db->query("SELECT id FROM pois")->fetchAll();
        foreach ($items as $item) {
            $output[] = $this->getPoiById($item->id);
        }
        return $output;
    }

    /**
     * Add an harbour in the database
     * @param name: name of the harbour
     * @param lat: latitude coordinate (between -180 and 180) of the harbour
     * @param lng: longitude coordinate (between -180 and 180) of the harbour
     * @param creatorId: id of the last cve_user that has edited the harbour
     * @param water: true if the harbour can provide water
     * @param food: true if the harbour can sell food
     * @param spareParts: true if the harbour can sell spare parts
     * @param dryDock: true if the harbour has a dry dock
     * @return Harbour: the harbour added
     */
    public function addHarbour(string $name, float $lat, float $lng, int $creatorId, bool $water, bool $food, bool $spareParts, bool $dryDock): Harbour
    {
        $_water = $water ? 1 : 0;
        $_food = $food ? 1 : 0;
        $_spareParts = $spareParts ? 1 : 0;
        $_dryDock = $dryDock ? 1 : 0;
        $this->db->query("INSERT INTO pois (name, lat, lng, type, creator, sell_food, sell_spare_parts, provide_water, dry_dock) VALUES (" . $this->db->quote($name) . ", $lat, $lng, 0, $creatorId, $_food, $_spareParts, $_water, $_dryDock)");
        $id = $this->db->lastInsertId();
        return $this->getPoiById($id);
    }

    public function addAnchorage(string $name, float $lat, float $lng, int $creatorId): Anchorage
    {
        $this->db->query("INSERT INTO pois (name, lat, lng, type, creator) VALUES (" . $this->db->quote($name) . ", $lat, $lng, 1, $creatorId)");
        $id = $this->db->lastInsertId();
        return $this->getPoiById($id);
    }

    public function addOddity(string $name, float $lat, float $lng, int $creatorId, string $caption): Oddity
    {
        $this->db->query("INSERT INTO pois (name, lat, lng, type, creator, caption) VALUES (" . $this->db->quote($name) . ", $lat, $lng, 2, $creatorId, " . $this->db->quote($caption) . ")");
        $id = $this->db->lastInsertId();
        return $this->getPoiById($id);
    }

    public function addWaypoint(float $lat, float $lng, int $creatorId): Waypoint
    {
        $this->db->query("INSERT INTO pois (name, lat, lng, type, creator, caption) VALUES (" . $this->db->quote(("")) . ", $lat, $lng, 3, $creatorId, " . $this->db->quote(("")) . ")");
        $id = $this->db->lastInsertId();
        return $this->getPoiById($id);
    }

    public function updateHarbourContent(int $id, int $creator, string $name, bool $water, bool $food, bool $spareParts, bool $dryDock): Harbour
    {
        $_water = $water ? 1 : 0;
        $_food = $food ? 1 : 0;
        $_spareParts = $spareParts ? 1 : 0;
        $_dryDock = $dryDock ? 1 : 0;
        $this->db->query("UPDATE pois SET creator=$creator, name=" . $this->db->quote($name) . ", sell_food=$_food, provide_water=$_water, sell_spare_parts=$_spareParts, dry_dock=$_dryDock WHERE id=$id");
        return $this->getPoiById($id);
    }

    public function updateAnchorageContent(int $id, int $creator, string $name): Anchorage
    {
        $this->db->query("UPDATE pois SET creator=$creator, name=" . $this->db->quote($name) . " WHERE id=$id");
        return $this->getPoiById($id);
    }

    public function updateOddityContent(int $id, int $creator, string $name, string $caption): Oddity
    {
        $this->db->query("UPDATE pois SET creator=$creator, name=" . $this->db->quote($name) . ", caption=" . $this->db->quote($caption) . " WHERE id=$id");
        return $this->getPoiById($id);
    }

    public function updatePoiLocation(int $id, int $creator, float $lat, float $lng): Poi
    {
        $this->db->query("UPDATE pois SET creator=$creator, lat=$lat, lng=$lng WHERE id=$id");
        return $this->getPoiById($id);
    }

    /**
     * Remove the defined poi from the database
     * @param id: id of the poi
     */
    public function remove(int $id): void
    {
        $this->db->query("DELETE FROM pois WHERE id=" . $id);
        // $this->db->query("DELETE FROM assoc_pois_missions WHERE poi=$id");
    }

    public function isPoiUsed(int $id): bool
    {
        if (count($this->db->query("SELECT id FROM assoc_pois_missions WHERE poi=" . $id)->fetchAll()) > 0) {
            return true;
        } else if (count($this->db->query("SELECT id FROM mission_steps WHERE poi=" . $id)->fetchAll()) > 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Remove a set of harbours defined by their ids
     * @param list: list of ids (int)
     */
    public function removeList(array $list): void
    {
        foreach ($list as $id) {
            $this->remove($id);
        }
    }

    /**
     * @return int: number of harbours stored in the database
     */
    public function countHarbours(): int
    {
        return $this->db->query("SELECT count(*) FROM pois WHERE type=0")->fetchColumn();
    }

    /**
     * @return int: number of anchorages stored in the database
     */
    public function countAnchorages(): int
    {
        return $this->db->query("SELECT count(*) FROM pois WHERE type=1")->fetchColumn();
    }

    /**
     * @return int: number of pois stored in the database
     */
    public function countAll(): int
    {
        return $this->db->query("SELECT count(*) FROM pois")->fetchColumn();
    }

    /**
     * Returns all harbours in the range of the considered location
     * @param lat: latitude coordinate (between -180 and 180) of the considered location
     * @param lng: longitude coordinate (between -180 and 180) of the considered location
     * @param range: range (in nm)
     * @param N: number max of pois
     * @return array: of ["dist" => distance to the considered location, "harbour" => harbour]
     */
    public function getNNearestPoisInRange(float $lat, float $lng, float $range, int $N): array
    {
        //step 1: get all pois in the defined range
        $items = $this->db->query("SELECT id, lat, lng FROM pois WHERE type!=3")->fetchAll();
        $pois = [];
        for ($i = 0; $i < count($items); $i++) {
            $item = $items[$i];
            $distance = $this->distance($lat, $lng, $item->lat, $item->lng, "N");
            if ($distance <= $range) {
                //get missions
                $req = "SELECT
                            missions.id,
                            missions.caption,
                            missions.title,
                            missions.type,
                            missions.reward,
                            sailaway_users.name AS creator
                        FROM missions
                            INNER JOIN assoc_pois_missions ON missions.id=assoc_pois_missions.mission
                            INNER JOIN cve_users ON missions.creator=cve_users.id
                            INNER JOIN sailaway_users ON cve_users.sw_user_id=sailaway_users.id
                        WHERE assoc_pois_missions.poi=$item->id";
                $missionItems = $this->db->query($req)->fetchAll();
                $missions = [];
                foreach ($missionItems as $missionItem) {
                    $missions[] = [
                        "id" => $missionItem->id,
                        "title" => utf8_encode($missionItem->title),
                        "caption" => utf8_encode($missionItem->caption),
                        "reward" => $missionItem->reward,
                        "type" => $missionItem->type,
                        "creator" => $missionItem->creator
                    ];
                }
                $poi = ($this->getPoiById($item->id))->toJson();
                $poi["missions"] = $missions;
                $poi["distance"] = $distance;
                $pois[] = $poi;
            }
        }
        // step 2: select N nearest pois in the list
        $distComp = function ($poi1, $poi2) use ($lat, $lng) {
            $dist1 = $this->distance($lat, $lng, $poi1['lat'], $poi1['lng'], "N");
            $dist2 = $this->distance($lat, $lng, $poi2['lat'], $poi2['lng'], "N");
            if ($dist1 == $dist2) {
                return 0;
            } else if ($dist1 < $dist2) {
                return -1;
            } else {
                return 1;
            }
        };
        usort($pois, $distComp);
        $output = [];
        if ($N > sizeof($pois)) {
            $N = sizeof($pois);
        }
        for ($i = 0; $i < $N; $i++) {
            $output[] = $pois[$i];
        }
        return $output;
    }
}
