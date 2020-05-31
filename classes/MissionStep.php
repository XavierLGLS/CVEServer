<?php

class MissionStep
{
    private $id;
    private $description;
    private $poi;
    private $rank;

    function __construct(int $id, Poi $poi, string $description, int $rank)
    {
        $this->id = $id;
        $this->description = $description;
        $this->poi = $poi;
        $this->rank = $rank;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getLat(): float
    {
        return $this->poi->getLocation()["lat"];
    }

    public function getPoiId(): int
    {
        return $this->poi->getId();
    }

    public function getLng(): float
    {
        return $this->poi->getLocation()["lng"];
    }

    public function getRank(): int
    {
        return $this->rank;
    }

    public function getLocationName(): string
    {
        return $this->poi->getName();
    }

    public function toJson(): array
    {
        return [
            "id" => $this->id,
            "caption" => utf8_encode($this->description),
            "poi" => $this->poi->toJson(),
            "rank" => $this->rank
        ];
    }
}
