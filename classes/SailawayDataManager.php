<?php

require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'DBManager.php';

class SailawayDataManager extends DBManager
{
    private function boatUsedInCVE(int $boatId): bool
    {
        return $this->db->query("SELECT count(*) FROM cve_boats WHERE sw_boat_id=$boatId")->fetchColumn() > 0;
    }

    private function userUsedInCVE(int $userId): bool
    {
        return $this->db->query("SELECT count(*) FROM cve_users WHERE sw_user_id=$userId")->fetchColumn() > 0;
    }

    /**
     * Returns true if this uername is used in sailaway
     * @param username: checked username
     */
    function userExists(string $username): bool
    {
        $req = $this->db->query('SELECT * FROM sailaway_users WHERE name=' . $this->db->quote($username));
        return ($req->fetchColumn() > 0);
    }

    /**
     * Returns the sailaway id
     * @param username
     * @return int: id of the sailaway user
     */
    function getUserId(string $username): int
    {
        $req = $this->db->query('SELECT id FROM sailaway_users WHERE name=' . $this->db->quote($username));
        $user = $req->fetch();
        return $user->id;
    }

    function getUserBoats(CVEUser $user): array
    {
        $req = "
        SELECT
            sailaway_boats.id AS id,
            sailaway_boats.name AS name,
            sailaway_boat_characteristics.name AS type,
            sailaway_boats.type AS type_id
        FROM `sailaway_boats`
            INNER JOIN `sailaway_boat_characteristics` ON sailaway_boats.type=sailaway_boat_characteristics.type_id
        WHERE sailaway_boats.user_id=" . $user->getSailawayId();
        return $this->db->query($req)->fetchAll();
    }

    function getBoatsFromSwUserId(int $id): array
    {
        $req = "
        SELECT
            sailaway_boats.id AS id,
            sailaway_boats.name AS name,
            sailaway_boat_characteristics.name AS type,
            sailaway_boats.type AS type_id
        FROM `sailaway_boats`
            INNER JOIN `sailaway_boat_characteristics` ON sailaway_boats.type=sailaway_boat_characteristics.type_id
        WHERE sailaway_boats.user_id=$id";
        return $this->db->query($req)->fetchAll();
    }

    function addSailawayBoat(int $id, int $type, int $userId, string $boatName): void
    {
        $req = $this->db->query("SELECT * FROM sailaway_boats WHERE id=$id");
        $now = new DateTime();
        if ($req->fetchColumn() > 0) {
            // This sailaway-boat is already stored in the CVE database
            $this->db->query('UPDATE sailaway_boats SET name=' . $this->db->quote($boatName) . ', last_update_date=' . $this->db->quote($now->format("Y-m-d")) . ' WHERE id=' . $id);
            return;
        } else {
            $this->db->query("INSERT INTO sailaway_boats (id, user_id, type, name, last_update_date) VALUES ($id, $userId, $type, " . $this->db->quote($boatName) . ", " . $this->db->quote($now->format("Y-m-d")) . ")");
        }
    }

    function addSailawayUser(int $id, string $name): void
    {
        $req = $this->db->query("SELECT * FROM sailaway_users WHERE id=$id");
        $now = new DateTime();
        if ($req->fetchColumn() > 0) {
            // This sailaway-user is already stored in the CVE database
            $this->db->query('UPDATE sailaway_users SET name=' . $this->db->quote($name) . ", last_update_date=" . $this->db->quote($now->format("Y-m-d")) . " WHERE id=$id");
            return;
        } else {
            $this->db->query("INSERT INTO sailaway_users (id, name, last_update_date) VALUES ($id, " . $this->db->quote($name) . ", " . $this->db->quote($now->format("Y-m-d")) . ")");
        }
    }

    function removeOldSailwayData(): void
    {
        global $SW_DATA_DURATION;

        // boats
        $boats = $this->db->query("SELECT id, last_update_date FROM sailaway_boats");
        $partials = [];
        foreach ($boats as $boat) {
            $date = strtotime($boat->last_update_date);
            $elapsedSec = intval(time() - $date);
            if ($elapsedSec > ($SW_DATA_DURATION * 24 * 3600)) {
                if (!$this->boatUsedInCVE($boat->id)) {
                    $partials[] = $boat->id;
                }
            }
        }
        if (count($partials) > 0) {
            $this->db->query("DELETE FROM sailaway_boats WHERE id IN (" . join(", ", $partials) . ")");
        }

        // users
        $users = $this->db->query("SELECT id, last_update_date FROM sailaway_users");
        $partials = [];
        foreach ($users as $user) {
            $date = strtotime($user->last_update_date);
            $elapsedSec = intval(time() - $date);
            if ($elapsedSec > ($SW_DATA_DURATION * 24 * 3600)) {
                if (!$this->userUsedInCVE($user->id)) {
                    $partials[] = $user->id;
                }
            }
        }
        if (count($partials) > 0) {
            $this->db->query("DELETE FROM sailaway_users WHERE id IN (" . join(", ", $partials) . ")");
        }
    }
}
