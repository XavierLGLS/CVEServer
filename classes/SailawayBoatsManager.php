<?php

require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'DBManager.php';

//TODO: remove
class SailawayBoatsManager extends DBManager
{
    /**
     * Checks the boat is not already stored in the database, and adds it
     * @param id: sailaway boat id
     * @param type: type id of the boat
     * @param user_id: id of the sailaway user
     * @param name: name of the boat
     */
    public function addBoat(int $id, int $type, int $user_id, string $name): void
    {
        $req = $this->db->query("SELECT * FROM sailaway_boats WHERE id=$id");
        if ($req->fetchColumn() > 0) {
            // This sailaway-boat is already stored in the CVE database
            $this->db->query('UPDATE sailaway_boats SET name=' . $this->db->quote($name) . " WHERE id=$id");
        } else {
            $this->db->query("INSERT INTO sailaway_boats (id, user_id, type, name) VALUES ($id, $user_id, $type, " . $this->db->quote($name) . ')');
        }
    }

    /**
     * Returns the type of the boat defined by its sailaway id
     * @param sw_boat_id: id of the sailaway boat
     * @return int: id of the type
     */
    public function getType(int $sw_boat_id): int
    {
        $req = $this->db->query("SELECT * FROM sailaway_boats WHERE id=$sw_boat_id");
        $boat = $req->fetch();
        return $boat->type;
    }

    /**
     * Returns all boat owned by the sailaway user
     * @param sw_user_id: id of the sailaway user
     * @return array: of sailaway boats
     */
    public function getBoatsFromSwUser(CVEUser $user): array
    {
        $req = "
        SELECT
            sailaway_boats.id AS id,
            sailaway_boats.name AS name,
            sailaway_boat_characteristics.name AS type
        FROM `sailaway_boats`
            INNER JOIN `sailaway_boat_characteristics` ON sailaway_boats.type=sailaway_boat_characteristics.type_id
        WHERE sailaway_boats.user_id=" . $user->getSailawayId();
        return $this->db->query($req)->fetchAll();
    }

    /**
     * @param sw_boat_id: id of the sailaway boat
     * @return string: name of the boat
     */
    public function getSailawayBoatName(int $sw_boat_id): string
    {
        return $this->db->query('SELECT name FROM sailaway_boats WHERE id=' . $sw_boat_id)->fetch()->name;
    }

    /**
     * @param type_id: id of the boat type
     * @return string: name of the type
     */
    public function getBoatTypeName(int $type_id): string
    {
        return $this->db->query('SELECT name FROM sailaway_boat_characteristics WHERE type_id=' . $type_id)->fetch()->name;
    }

    /**
     * @param sw_boat_id: id of the sailaway boat
     * @return string: name of the owner in sailaway
     */
    public function getUsername(int $sw_boat_id): string
    {
        $req = "
        SELECT sailaway_users.name AS username
        FROM sailaway_boats
            INNER JOIN sailaway_users ON sailaway_users.id = sailaway_boats.user_id
        WHERE sailaway_boats.id = $sw_boat_id
        ";
        return $this->db->query($req)->fetch()->username;
    }

    /**
     * Returns all boats characteristics
     * @return array: of characteristics
     */
    public function getBoatsCharacteristics(): array
    {
        $req = $this->db->query('SELECT * FROM sailaway_boat_characteristics');
        return $req->fetchAll();
    }

    /**
     * Returns all characteristics of a type id
     * @param type: id of the type
     * @return object: characteristics of the boat
     */
    public function getBoatCharacteristics(int $type): object
    {
        $req = $this->db->query("SELECT * FROM sailaway_boat_characteristics WHERE type_id=$type");
        return $req->fetch();
    }

    /**
     * Sets a new food capacity for the defined type of boat
     * @param type_id: id of the sailaway boat type
     * @param newCapacity: new food capacity
     */
    public function setBoatFoodCapacity(int $type_id, int $newCapacity): void
    {
        $this->db->query("UPDATE sailaway_boat_characteristics SET food_capacity=$newCapacity WHERE type_id=$type_id");
    }

    /**
     * Sets a new food capacity for the defined type of boat
     * @param type_id: id of the sailaway boat type
     * @param newCapacity: new spare parts capacity
     */
    public function setBoatSparePartsCapacity(int $type_id, int $newCapacity): void
    {
        $this->db->query("UPDATE sailaway_boat_characteristics SET spare_parts_capacity=$newCapacity WHERE type_id=$type_id");
    }

    /**
     * Sets a new food capacity for the defined type of boat
     * @param type_id: id of the sailaway boat type
     * @param newCapacity: new passengers capacity
     */
    public function setBoatPassengersCapacity(int $type_id, int $newCapacity): void
    {
        $this->db->query("UPDATE sailaway_boat_characteristics SET additional_passengers_capacity=$newCapacity WHERE type_id=$type_id");
    }

    /**
     * Sets a new food capacity for the defined type of boat
     * @param type_id: id of the sailaway boat type
     * @param newCapacity: new water capacity
     */
    public function setBoatWaterCapacity(int $type_id, int $newCapacity): void
    {
        $this->db->query("UPDATE sailaway_boat_characteristics SET water_capacity=$newCapacity WHERE type_id=$type_id");
    }

    /**
     * Sets a new theorical maximum speed for the defined type of boat
     * @param type_id: id of the sailaway boat type
     * @param newSpeed: new maximum speed (knots)
     */
    public function setSailawayBoatMaxSpeed(int $type_id, int $newSpeed): void
    {
        $this->db->query("UPDATE sailaway_boat_characteristics SET max_speed=$newSpeed WHERE type_id=$type_id");
    }
}
