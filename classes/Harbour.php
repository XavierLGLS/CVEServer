<?php

require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'Poi.php';

class Harbour extends Poi
{
    private $name;
    private $canProvideWater, $canSellFood, $canSellSpareParts, $hasADryDock;

    function __construct(int $id, float $lat, float $lng, string $creator, string $name, bool $canProvideWater, bool $canSellFood, bool $canSellSpareParts, bool $hasADryDock)
    {
        parent::__construct($id, $lat, $lng, $creator, 0);
        $this->name = $name;
        $this->canProvideWater = $canProvideWater;
        $this->canSellFood = $canSellFood;
        $this->canSellSpareParts = $canSellSpareParts;
        $this->hasADryDock = $hasADryDock;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function toJson(): array
    {
        $output = parent::toJson();
        $output["name"] = utf8_encode($this->name);
        $output["provides_water"] = $this->canProvideWater;
        $output["sells_food"] = $this->canSellFood;
        $output["sells_spare_parts"] = $this->canSellSpareParts;
        $output["dry_dock"] = $this->hasADryDock;
        return $output;
    }
}
