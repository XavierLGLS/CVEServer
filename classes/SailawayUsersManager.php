<?php

require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'DBManager.php';

//TODO: remove
class SailawayUsersManager extends DBManager
{
    /**
     * Checks the user is not already stored in the database, and add it
     * @param id: id of the sailaway user
     * @param name: name of the sailaway user
     */
    public function add(int $id, string $name): void
    {
        $req = $this->db->query("SELECT * FROM sailaway_users WHERE id=$id");
        if ($req->fetchColumn() > 0) {
            // This sailaway-user is already stored in the CVE database
            $this->db->query('UPDATE sailaway_users SET name=' . $this->db->quote($name) . " WHERE id=$id");
            return;
        } else {
            $this->db->query("INSERT INTO sailaway_users (id, name) VALUES ($id, " . $this->db->quote($name) . ')');
        }
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
     * Returns the sailaway username
     * @param user_id: id of the sailaway account
     * @return string: username
     */
    function getUserName(int $user_id): string
    {
        if (!isset($user_id)) {
            return "no sailaway username";
        }
        $req = $this->db->query("SELECT * FROM sailaway_users WHERE id=$user_id");
        if ($req->fetchColumn() > 0) {
            $req = $this->db->query("SELECT * FROM sailaway_users WHERE id=$user_id");
            $user = $req->fetch();
            return $user->name;
        } else {
            return '- undefined username -';
        }
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
}
