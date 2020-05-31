
<?php

require_once dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'config_v2.php';

require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'DBManager.php';

class AppLogsManager extends DBManager
{
    private $startLocationId = 0;
    private $endLocationId = 1;
    private $startWeatherId = 2;
    private $endWeatherId = 3;
    private $startDamageId = 4;
    private $endDamageId = 5;
    private $startFoodId = 6;
    private $endFoodId = 7;
    private $startSwDataId = 8;
    private $endSwDataId = 9;
    private $startTrajectoriesId = 10;
    private $endTrajectoriesId = 11;
    private $startLogsId = 12;
    private $endLogsId = 13;

    private function timestampFloat()
    {
        list($usec, $sec) = explode(" ", microtime());
        return ((float) $usec + (float) $sec);
    }

    /**
     * Adds a log
     * @param text: log content
     */
    public function addStringLog(string $text): void
    {
        $this->db->query('INSERT INTO logs (text) VALUES (' . $this->db->quote($text) . ')');
    }

    /**
     * Returns all strings logged by the app
     */
    public function getStringLogs(): array
    {
        return $this->db->query('SELECT * FROM logs ORDER BY date DESC LIMIT 100')->fetchAll();
    }

    public function storeEvent(string $name): void
    {
        try {
            switch ($name) {
                case 'start-location':
                    $type = $this->startLocationId;
                    break;
                case 'end-location':
                    $type = $this->endLocationId;
                    break;
                case 'start-weather':
                    $type = $this->startWeatherId;
                    break;
                case 'end-weather':
                    $type = $this->endWeatherId;
                    break;
                case 'start-damage':
                    $type = $this->startDamageId;
                    break;
                case 'end-damage':
                    $type = $this->endDamageId;
                    break;
                case 'start-food':
                    $type = $this->startFoodId;
                    break;
                case 'end-food':
                    $type = $this->endFoodId;
                    break;
                case 'start-sw-data':
                    $type = $this->startDataId;
                    break;
                case 'end-sw-data':
                    $type = $this->endDataId;
                    break;
                case 'start-trajectories':
                    $type = $this->startTrajectoriesId;
                    break;
                case 'end-trajectories':
                    $type = $this->endTrajectoriesId;
                    break;
                case 'start-logs':
                    $type = $this->startLogsId;
                    break;
                case 'end-logs':
                    $type = $this->endLogsId;
                    break;
                default:
                    throw new Exception("undefined event with name $name");
            }
            $now = $this->timestampFloat();
            $this->db->query("INSERT INTO cve_activities (date, type) VALUES ($now, $type)");
        } catch (Exception $e) {
            $this->addStringLog($e->getMessage());
        }
    }

    public function removeOldActivityLogs(): void
    {
        global $APP_LOGS_DURATION;
        $activities = $this->db->query("SELECT id, date FROM cve_activities");
        $partials = [];
        foreach ($activities as $activity) {
            $date = strtotime($activity->date);
            $elapsedSec = intval(time() - $date);
            if ($elapsedSec > ($APP_LOGS_DURATION * 3600)) {
                $partials[] = $activity->id;
            }
        }
        if (count($partials) > 0) {
            $this->db->query("DELETE FROM cve_boat_positions WHERE id IN (" . join(", ", $partials) . ")");
        }
    }

    public function removeOldStringLogs(): void
    {
        global $CVE_LOGS_DURATION;
        $logs = $this->db->query("SELECT id, date FROM logs");
        $partials = [];
        foreach ($logs as $log) {
            $date = strtotime($log->date);
            $elapsedSec = intval(time() - $date);
            if ($elapsedSec > ($CVE_LOGS_DURATION * 3600 * 24)) {
                $partials[] = $log->id;
            }
        }
        if (count($partials) > 0) {
            $this->db->query("DELETE FROM logs WHERE id IN (" . join(", ", $partials) . ")");
        }
    }

    private function getEventsHistory(): array
    {
        $output = [];
        $items = $this->db->query("SELECT type, date FROM cve_activities ORDER BY date")->fetchAll();
        $startLocation = null;
        $startDamage = null;
        $startFood = null;
        $startWeather = null;
        $startSwData = null;
        $startTrajectories = null;
        $startLogs = null;
        foreach ($items as $item) {
            switch ($item->type) {
                    // location
                case $this->startLocationId:
                    if ($startLocation == null) {
                        $startLocation = $item;
                    }
                    break;
                case $this->endLocationId:
                    if ($startLocation != null) {
                        $outputItem = [];
                        $outputItem["type"] = "location";
                        $outputItem["start"] = $startLocation->date;
                        $outputItem["duration"] = $item->date - $startLocation->date;
                        $output[] = $outputItem;
                        $startLocation = null;
                    }
                    break;
                    // damage
                case $this->startDamageId:
                    if ($startDamage == null) {
                        $startDamage = $item;
                    }
                    break;
                case $this->endDamageId:
                    if ($startDamage != null) {
                        $outputItem = [];
                        $outputItem["type"] = "damage";
                        $outputItem["start"] = $startDamage->date;
                        $outputItem["duration"] = $item->date - $startDamage->date;
                        $output[] = $outputItem;
                        $startDamage = null;
                    }
                    break;
                    // food
                case $this->startFoodId:
                    if ($startFood == null) {
                        $startFood = $item;
                    }
                    break;
                case $this->endFoodId:
                    if ($startFood != null) {
                        $outputItem = [];
                        $outputItem["type"] = "food";
                        $outputItem["start"] = $startFood->date;
                        $outputItem["duration"] = $item->date - $startFood->date;
                        $output[] = $outputItem;
                        $startFood = null;
                    }
                    break;
                    // weather
                case $this->startWeatherId:
                    if ($startWeather == null) {
                        $startWeather = $item;
                    }
                    break;
                case $this->endWeatherId:
                    if ($startWeather != null) {
                        $outputItem = [];
                        $outputItem["type"] = "weather";
                        $outputItem["start"] = $startWeather->date;
                        $outputItem["duration"] = $item->date - $startWeather->date;
                        $output[] = $outputItem;
                        $startWeather = null;
                    }
                    break;
                    // sailaway users/boats
                case $this->startSwDataId:
                    if ($startSwData == null) {
                        $startSwData = $item;
                    }
                    break;
                case $this->endSwDataId:
                    if ($startSwData != null) {
                        $outputItem = [];
                        $outputItem["type"] = "sw-accounts";
                        $outputItem["start"] = $startSwData->date;
                        $outputItem["duration"] = $item->date - $startSwData->date;
                        $output[] = $outputItem;
                        $startSwData = null;
                    }
                    break;
                    // trajectories
                case $this->startTrajectoriesId:
                    if ($startTrajectories == null) {
                        $startTrajectories = $item;
                    }
                    break;
                case $this->endTrajectoriesId:
                    if ($startTrajectories != null) {
                        $outputItem = [];
                        $outputItem["type"] = "trajectories";
                        $outputItem["start"] = $startTrajectories->date;
                        $outputItem["duration"] = $item->date - $startTrajectories->date;
                        $output[] = $outputItem;
                        $startSwData = null;
                    }
                    break;
                    // logs
                case $this->startLogsId:
                    if ($startLogs == null) {
                        $startLogs = $item;
                    }
                    break;
                case $this->endLogsId:
                    if ($startLogs != null) {
                        $outputItem = [];
                        $outputItem["type"] = "logs";
                        $outputItem["start"] = $startLogs->date;
                        $outputItem["duration"] = $item->date - $startLogs->date;
                        $output[] = $outputItem;
                        $startSwData = null;
                    }
                    break;
            }
        }
        return $output;
    }

    public function getLastDates(): array
    {
        function getLastType($list, $type)
        {
            $items = array_filter($list, function ($event) use ($type) {
                return $event["type"] == $type;
            });
            if (count($items) > 0) {
                return array_reduce(
                    $items,
                    function ($carry, $item) {
                        if ($carry["start"] > $item["start"]) {
                            return $carry;
                        } else {
                            return $item;
                        }
                    }
                );
            } else {
                return null;
            }
        }

        function getElapsedMinutes($event)
        {
            if ($event === null) {
                return null;
            } else {
                return intval((time() - $event["start"]) / 60);
            }
        }

        $output = [];
        $events = $this->getEventsHistory();
        $output["location"] = getElapsedMinutes(getLastType($events, "location"));
        $output["damage"] = getElapsedMinutes(getLastType($events, "damage"));
        $output["weather"] = getElapsedMinutes(getLastType($events, "weather"));
        $output["food"] = getElapsedMinutes(getLastType($events, "food"));
        $output["sw_accounts"] = getElapsedMinutes(getLastType($events, "sw-accounts"));
        $output["trajectories"] = getElapsedMinutes(getLastType($events, "trajectories"));
        $output["logs"] = getElapsedMinutes(getLastType($events, "logs"));

        return $output;
    }

    public function getAverageDurations(): array
    {
        function getAverageDuration($list, $type)
        {
            $sorted = array_filter($list, function ($item) use ($type) {
                return $item["type"] == $type;
            });
            if (count($sorted) > 0) {
                $average = 0;
                foreach ($sorted as $item) {
                    $average += $item["duration"] / count($sorted);
                }
                return $average;
            } else {
                return null;
            }
        }

        $output = [];
        $events = $this->getEventsHistory();
        $output["location"] = getAverageDuration($events, "location");
        $output["damage"] = getAverageDuration($events, "damage");
        $output["weather"] = getAverageDuration($events, "weather");
        $output["food"] = getAverageDuration($events, "food");
        $output["sw_accounts"] = getAverageDuration($events, "sw-accounts");
        $output["trajectories"] = getAverageDuration($events, "trajectories");
        $output["logs"] = getAverageDuration($events, "logs");

        return $output;
    }
}
