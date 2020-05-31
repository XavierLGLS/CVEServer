<?php

require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'DBManager.php';
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'BoatsManager.php';
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'CVEUser.php';
require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'backend' . DIRECTORY_SEPARATOR . 'tools.php';

class AccountsManager extends DBManager
{
    private $boatsManager;

    function __construct()
    {
        parent::__construct();
        $this->boatsManager = new BoatsManager();
    }

    public function getUserFromId(int $id): CVEUser
    {
        $req =
            "SELECT
                cve_users.sw_user_id AS sw_user_id,
                cve_users.email AS email,
                sailaway_users.name AS username,
                cve_users.money AS money,
                cve_users.admin_permission AS admin_perm,
                cve_users.poi_editor_permission AS poi_perm,
                cve_users.mission_editor_permission AS mission_perm,
                cve_users.last_connection_date AS last_connection_date,
                cve_users.confirmed_at AS registration_date,
                cve_users.password AS password_hash,
                cve_users.confirmation_token AS register_token,
                cve_users.password_reset_token AS password_token
            FROM cve_users
                INNER JOIN sailaway_users ON cve_users.sw_user_id=sailaway_users.id
            WHERE cve_users.id=$id";
        $req = $this->db->query($req);
        $answer = $req->fetch();
        if ($answer) {
            return new CVEUser($id, $answer->sw_user_id, $answer->email, $answer->username, $answer->money, $answer->admin_perm, $answer->poi_perm, $answer->mission_perm, $answer->password_hash, $answer->last_connection_date, $answer->registration_date, $answer->register_token, $answer->password_token);
        } else {
            throw new Exception("Cannot find a CVEUser with id=$id");
        }
    }

    public function getUserFromMail(string $email): CVEUser
    {
        $req =
            "SELECT
                cve_users.id AS id,
                cve_users.sw_user_id AS sw_user_id,
                sailaway_users.name AS username,
                cve_users.money AS money,
                cve_users.admin_permission AS admin_perm,
                cve_users.poi_editor_permission AS poi_perm,
                cve_users.mission_editor_permission AS mission_perm,
                cve_users.last_connection_date AS last_connection_date,
                cve_users.confirmed_at AS registration_date,
                cve_users.password AS password_hash,
                cve_users.confirmation_token AS register_token,
                cve_users.password_reset_token AS password_token
            FROM cve_users
                INNER JOIN sailaway_users ON cve_users.sw_user_id=sailaway_users.id
            WHERE cve_users.email=" . $this->db->quote($email);
        $req = $this->db->query($req);
        $answer = $req->fetch();
        if ($answer) {
            return new CVEUser($answer->id, $answer->sw_user_id, $email, $answer->username, $answer->money, $answer->admin_perm, $answer->poi_perm, $answer->mission_perm, $answer->password_hash, $answer->last_connection_date, $answer->registration_date, $answer->register_token, $answer->password_token);
        } else {
            throw new Exception("Cannot find a CVEUser with email $email");
        }
    }

    public function isMailAlreadyUsed(string $email): bool
    {
        return ($this->db->query("SELECT count(*) FROM cve_users WHERE email=" . $this->db->quote($email))->fetchColumn() > 0);
    }

    public function getAllUsers(): array
    {
        $req =
            "SELECT
                cve_users.id AS id,
                cve_users.sw_user_id AS sw_user_id,
                cve_users.email AS email,
                sailaway_users.name AS username,
                cve_users.money AS money,
                cve_users.admin_permission AS admin_perm,
                cve_users.poi_editor_permission AS poi_perm,
                cve_users.mission_editor_permission AS mission_perm,
                cve_users.last_connection_date AS last_connection_date,
                cve_users.confirmed_at AS registration_date,
                cve_users.password AS password_hash,
                cve_users.confirmation_token AS register_token,
                cve_users.password_reset_token AS password_token
            FROM cve_users
                INNER JOIN sailaway_users ON cve_users.sw_user_id=sailaway_users.id
            ORDER BY cve_users.last_connection_date DESC";
        $req = $this->db->query($req);
        $answer = $req->fetchAll();
        $output = [];
        foreach ($answer as $item) {
            $output[] = new CVEUser($item->id, $item->sw_user_id, $item->email, $item->username, $item->money, $item->admin_perm, $item->poi_perm, $item->mission_perm, $item->password_hash, $item->last_connection_date, $item->registration_date, $item->register_token, $item->password_token);
        }
        return $output;
    }

    public function addUser(string $email, string $passwordHashed, int $sw_user_id, string $confirmationToken): CVEUser
    {
        global $INITIAL_MONEY;
        $this->db->query('INSERT INTO cve_users (sw_user_id, money, password, email, confirmation_token) VALUES (' . $sw_user_id . ', ' . $INITIAL_MONEY . ', ' . $this->db->quote($passwordHashed) . ', ' . $this->db->quote($email) . ', ' . $this->db->quote($confirmationToken) . ')');
        $id = $this->db->lastInsertId();
        return $this->getUserFromId($id);
    }

    /**
     * @param email: the user email
     * @param attemptPassword: the password entered by the user when it logs in
     * @return user: the user if registered, else return NULL
     */
    public function checkLogin(string $email,  string $attemptPassword): array
    {
        $user = NULL;
        try {
            $user = $this->getUserFromMail($email);
        } catch (Exception $e) {
            $output["status"] = "there is no account using $email in CVE";
            return $output;
        }
        if (!$user->isRegistered()) {
            $output["status"] = "account not yet registered, have a look to your mailbox (you may also check your trashbox)";
            return $output;
        }
        if ($user->checkPassword($attemptPassword)) {
            $output["status"] = "success";
            $output["user"] = $user;
        } else {
            $output["status"] = "wrong password";
        }
        return $output;
    }

    /**
     * Remove the user defined by the id
     * @param id: the user id
     */
    public function removeUser(int $id): void
    {
        // remove from cve_users
        $this->db->query("DELETE FROM cve_users WHERE id=$id");
        // remove all boats linked to this user
        $this->db->query("DELETE FROM cve_boats WHERE cve_user_id=$id");
    }

    /**
     * Set the remember token used by the cookie to automatically login after a brower closure
     * @param id: id of the user
     * @param token: remember token
     */
    public function setRememberToken(int $id, string $token): void
    {
        $this->db->query('UPDATE cve_users SET remember_token=' . $this->db->quote($token) . " WHERE id=$id");
    }

    /**
     * @param user: the user account
     * @param token: the registration confirmation token
     * @return int: 1 id the registration is successful, -1 if there is no token, 0 if the token does not correspond
     */
    public function checkRegistrationConfirmation(CVEUser $user, string $token): array
    {
        $output = [];
        if ($token === NULL || $token === "") {
            $output["status"] = "you need a token to confirm your registration, the link you use seems to be wrong";
        } else if ($user->isRegistered()) {
            $output["status"] = "account registration already confirmed, please login with your credentials";
        } else if ($user->confirmRegistration($token)) {
            $this->db->query("UPDATE cve_users SET confirmation_token=\"\", confirmed_at=NOW() WHERE id=" . $user->getId());
            $output["status"] = "success";
        } else {
            $output["status"] = "wrong token, the link you use seems to be wrong";
        }
        return $output;
    }

    /**
     * @param token: the password change token
     * @param id: the user id
     * @param newPassword: the new password set by the user
     */
    public function setNewPassword(CVEUser $user, string $newPassword): void
    {
        $hash = hashPassword($newPassword);
        $this->db->query('UPDATE cve_users SET password=' . $this->db->quote($hash) . " WHERE id=" . $user->getId());
    }

    public function checkPasswordToken(CVEUser &$user, string $token): string
    {
        if ($user->getPasswordToken() === "") {
            return "no password modification requested for this account";
        } else if ($token === $user->getPasswordToken()) {
            $this->db->query('UPDATE cve_users SET password_reset_token=NULL WHERE id=' . $user->getId());
            $user->setPasswordToken("");
            return "success";
        } else {
            return "the token you use doesnt match, it seems you use an invalid link";
        }
    }

    /**
     * Stores the token sent by mail to the user that wants to reset its password
     * @param id: the user id
     * @param token: randomly generated token that will authenticate the user
     */
    public function setPasswordReset(CVEUser &$user, string $token): void
    {
        $this->db->query('UPDATE cve_users SET password_reset_token=' . $this->db->quote($token) . " WHERE id=" . $user->getId());
        $user->setPasswordToken($token);
    }

    public function confirmRegistration(int $user_id): void
    {
        $now = new DateTime();
        $this->db->query('UPDATE cve_users SET confirmation_token="", confirmed_at=' . $this->db->quote($now->format("Y-m-d")) . ' WHERE id=' . $user_id);
    }

    //TODO: remove (when multiple boats)
    /**
     * @param cve_user_id: id of the cve user account
     * @param sw_boat_id: id of the new sailaway boat linked to the account
     */
    public function changeBoat(int $cve_user_id, int $sw_boat_id): void
    {
        // remove all boats linked to this user
        $this->db->query("DELETE FROM cve_boats WHERE cve_user_id=$cve_user_id");
        // add a new boat
        $boat_type = $this->db->query("SELECT * FROM sailaway_boats WHERE id=$sw_boat_id")->fetch()->type;
        $this->boatsManager->addBoat($cve_user_id, $sw_boat_id, $boat_type);
    }

    public function updateUserLastConnectionDate(CVEUser $user): void
    {
        $now = new DateTime();
        if (!$user->hasConnected()) {
            $this->db->query("UPDATE cve_users SET last_connection_date=" . $this->db->quote($now->format("Y-m-d")) . " WHERE id=" . $user->getId());
        } else if (intval($user->getTimeSinceLastConnection()->format("%a")) > 0) {
            $this->db->query("UPDATE cve_users SET last_connection_date=" . $this->db->quote($now->format("Y-m-d")) . " WHERE id=" . $user->getId());
        }
    }

    public function setUserPermissions(int $userId, bool $poiPerm, bool $missionPerm): void
    {
        $poi = $poiPerm ? 1 : 0;
        $mission = $missionPerm ? 1 : 0;
        $this->db->query("UPDATE cve_users SET poi_editor_permission=$poi, mission_editor_permission=$mission WHERE id=$userId");
    }

    public function connectUser(CVEUser $user): void
    {
        $this->updateUserLastConnectionDate($user);
        $_SESSION['auth'] = $user;
    }
}
