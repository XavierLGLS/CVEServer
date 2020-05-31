<?php

class Boat
{
    private $id, $swBoatId, $stepId, $dryDockId, $color;
    private $heading, $food, $water, $spareParts, $passengers, $hull, $sails, $crewHealth, $weatherCoeff;
    private $foodCapacity, $waterCapacity, $sparePartsCapacity, $passengersCapacity, $lastStateUpdateDate;
    private $name, $typeName;

    function __construct(int $id, int $typeId, int $cveUserId, int $swBoatId, int $stepId, int $dryDockId, string $name, string $typeName, float $weatherCoeff, int $color, int $heading, int $food, int $foodCapacity, int $water, int $waterCapacity, int $spareParts, int $sparePartsCapacity, int $passengers, int $passengersCapacity, float $hull, float $sails, float $crewHealth, int $lastStateUpdateDate)
    {
        $this->id = $id;
        $this->typeId = $typeId;
        $this->color = $color;
        $this->cveUserId = $cveUserId;
        $this->swBoatId = $swBoatId;
        $this->stepId = $stepId;
        $this->dryDockId = $dryDockId;
        $this->name = $name;
        $this->typeName = $typeName;
        $this->heading = $heading;
        $this->weatherCoeff = $weatherCoeff;
        $this->food = $food;
        $this->foodCapacity = $foodCapacity;
        $this->water = $water;
        $this->waterCapacity = $waterCapacity;
        $this->spareParts = $spareParts;
        $this->sparePartsCapacity = $sparePartsCapacity;
        $this->passengers = $passengers;
        $this->passengersCapacity = $passengersCapacity;
        $this->hull = $hull;
        $this->sails = $sails;
        $this->crewHealth = $crewHealth;
        $this->lastStateUpdateDate = $lastStateUpdateDate;
    }

    public function getLabel(): string
    {
        return $this->name . ' (' . $this->typeName . ')';
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getSwId(): int
    {
        return $this->swBoatId;
    }

    public function getCveUserId(): int
    {
        return $this->cveUserId;
    }

    public function getSparePartsStock(): int
    {
        return $this->spareParts;
    }

    public function getHullState(): float
    {
        return $this->hull;
    }

    public function getSailsState(): float
    {
        return $this->sails;
    }

    public function getCrewHealth(): float
    {
        return $this->crewHealth;
    }

    public function getWeatherCoeff(): float
    {
        return $this->weatherCoeff;
    }

    public function getPassengers(): int
    {
        return $this->passengers;
    }

    public function getPassengerCapacity(): int
    {
        return $this->passengersCapacity;
    }

    public function getFoodStock(): int
    {
        return $this->food;
    }

    public function getWaterStock(): int
    {
        return $this->water;
    }

    public function getLastStateUpdateDate(): string
    {
        return $this->lastStateUpdateDate;
    }

    public function getColor(): int
    {
        return $this->color;
    }

    public function isInMission(): bool
    {
        return $this->stepId >= 0;
    }

    public function isInDryDock(): bool
    {
        return $this->dryDockId >= 0;
    }

    public function getStepId(): int
    {
        if ($this->isInMission()) {
            return $this->stepId;
        } else {
            throw new Exception("This boat is not doing any mission");
        }
    }

    public function toJson(): array
    {
        return [
            "id" => $this->id,
            "name" => $this->name,
            "type_name" => $this->typeName,
            "food" => $this->food,
            "food_capacity" => $this->foodCapacity,
            "water" => $this->water,
            "water_capacity" => $this->waterCapacity,
            "spare_parts" => $this->spareParts,
            "spare_parts_capacity" => $this->sparePartsCapacity,
            "passengers" => $this->passengers,
            "passengers_capacity" => $this->passengersCapacity,
            "hull" => $this->hull,
            "sails" => $this->sails,
            "crew_health" => $this->crewHealth,
            "heading" => $this->heading,
            "in_dry_dock" => $this->isInDryDock(),
            "color" => $this->color
        ];
    }

    public function toOverviewJson(): array
    {
        return [
            "id" => $this->id,
            "name" => $this->name,
            "type_name" => $this->typeName,
            "heading" => $this->heading,
            "color" => $this->color
        ];
    }
}
