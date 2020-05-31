<?php

require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'Poi.php';

class Anchorage extends Poi
{
    private $name;

    function __construct(int $id, float $lat, float $lng, string $creator, string $name)
    {
        parent::__construct($id, $lat, $lng, $creator, 1);
        $this->name = $name;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function toJson(): array
    {
        $output = parent::toJson();
        $output["name"] = utf8_encode($this->name);
        return $output;
    }
}
