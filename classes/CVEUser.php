<?php

require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'AccountsManager.php';

class CVEUser
{
    private $id, $sw_user_id;
    private $email, $username, $passwordHash;
    private $registrationToken, $passwordToken;
    private $registrationDate, $lastConnectionDate;
    private $admin_perm, $poi_perm, $mission_perm;

    function __construct(int $id, int $sw_user_id, string $email, string $username, int $money, bool $admin_perm, bool $poi_perm, bool $mission_perm, string $passwordHash, $lastConnectionDate, $registrationDate, $registrationToken, $passwordToken)
    {
        $this->id = $id;
        $this->sw_user_id = $sw_user_id;
        $this->admin_perm = $admin_perm;
        $this->poi_perm = $poi_perm;
        $this->mission_perm = $mission_perm;
        $this->email = $email;
        $this->username = $username;
        $this->passwordHash = $passwordHash;
        $this->registrationToken = $registrationToken;
        $this->passwordToken = $passwordToken;
        $this->money = $money;
        if ($lastConnectionDate !== NULL) {
            $this->lastConnectionDate = new DateTime($lastConnectionDate);
        }
        if ($registrationDate !== NULL) {
            $this->registrationDate = new DateTime($registrationDate);
        }
    }

    /**
     * GETTERS
     */

    public function isRegistered(): bool
    {
        return $this->registrationDate != NULL;
    }

    public function isAdmin(): bool
    {
        return $this->admin_perm;
    }

    public function isContributor(): bool
    {
        return ($this->poi_perm || $this->mission_perm);
    }

    public function hasPOIEditionPerm(): bool
    {
        return $this->poi_perm;
    }

    public function hasMissionEditionPerm(): bool
    {
        return $this->mission_perm;
    }

    public function hasConnected(): bool
    {
        return $this->lastConnectionDate !== NULL;
    }

    public function getTimeSinceLastConnection()
    {
        $now = new DateTime();
        if ($this->lastConnectionDate === NULL) {
            return NULL;
        }
        return date_diff($now, $this->lastConnectionDate);
    }

    public function getRegistrationDate(): DateTime
    {
        return $this->registrationDate;
    }

    public function getRegistrationToken(): string
    {
        if ($this->registrationToken === NULL) {
            return "";
        } else {
            return $this->registrationToken;
        }
    }

    public function getPasswordToken(): string
    {
        if ($this->passwordToken === NULL) {
            return "";
        } else {
            return $this->passwordToken;
        }
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getSailawayId(): int
    {
        return $this->sw_user_id;
    }

    public function getMoney(): int
    {
        return $this->money;
    }

    /**
     * SETTERS
     */

    public function setPasswordToken(string $val): void
    {
        $this->passwordToken = $val;
    }


    /**
     * METHODS
     */

    /**
     * @return bool: true if the token is the right one
     */
    public function confirmRegistration(string $token): bool
    {
        return $this->registrationToken === $token;
    }

    /**
     * @return bool: true if the token is the right one
     */
    public function changePassword(string $newPassword, string $token): bool
    {
        //TODO
        return true;
    }

    /**
     * @return bool: true if the password is the right one
     */
    public function checkPassword($attempt): bool
    {
        return password_verify($attempt, $this->passwordHash);
    }
}
