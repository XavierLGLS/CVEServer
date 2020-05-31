<?php

require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'Poi.php';

class Waypoint extends Poi
{

    function __construct(int $id, float $lat, float $lng, string $creator)
    {
        parent::__construct($id, $lat, $lng, $creator, 3);
    }

    public function toJson(): array
    {
        $output = parent::toJson();
        $output["name"] = "waypoint";
        return $output;
    }
}
