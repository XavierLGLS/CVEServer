<?php

require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'Poi.php';

class Oddity extends Poi
{
    private $name, $caption;

    function __construct(int $id, float $lat, float $lng, string $creator, string $name, string $caption)
    {
        parent::__construct($id, $lat, $lng, $creator, 2);
        $this->name = $name;
        $this->caption = $caption;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function toJson(): array
    {
        $output = parent::toJson();
        $output["name"] = utf8_encode($this->name);
        $output["caption"] = utf8_encode($this->caption);
        return $output;
    }
}
