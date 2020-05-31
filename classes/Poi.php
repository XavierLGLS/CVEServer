<?php

class Poi
{
    protected $id;
    protected $lat, $lng;
    protected $creator;

    private $type;

    function __construct(int $id, float $lat, float $lng, string $creator, int $type)
    {
        $this->type = $type;
        $this->id = $id;
        $this->lat = $lat;
        $this->lng = $lng;
        $this->creator = $creator;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getLocation(): array
    {
        return [
            "lat" => $this->lat,
            "lng" => $this->lng
        ];
    }

    public function getName(): string
    {
        return 'unnamed';
    }

    public function getType(): int
    {
        return $this->type;
    }

    protected function toJson(): array
    {
        return [
            "id" => $this->id,
            "type" => $this->type,
            "lat" => $this->lat,
            "lng" => $this->lng,
            "creator" => utf8_encode($this->creator)
        ];
    }
}
