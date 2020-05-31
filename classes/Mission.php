<?php

class Mission
{
    private $id, $reward, $passengers, $type;
    private $creator, $title, $caption;
    private $steps;
    private $placesOfAvailability;

    function __construct(int $id, string $title, int $type, int $reward, int $passengers, CVEUser $creator, string $caption, array $steps, array $placesOfAvailability)
    {
        $this->id = $id;
        $this->type = $type;
        $this->reward = $reward;
        $this->passengers = $passengers;
        $this->steps = $steps;
        $this->title = $title;
        $this->creator = $creator;
        $this->caption = $caption;
        $this->placesOfAvailability = $placesOfAvailability;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getStepCount(): int
    {
        return count($this->steps);
    }

    public function getCreatorId(): int
    {
        return $this->creator->getId();
    }

    public function getCreatorName(): string
    {
        return $this->creator->getUsername();
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getCaption(): string
    {
        return $this->caption;
    }

    public function getPassengers(): int
    {
        return $this->passengers;
    }

    public function getSteps(): array
    {
        return $this->steps;
    }

    public function getPlacesOfAvailability(): array
    {
        return $this->placesOfAvailability;
    }

    public function getReward(): int
    {
        return $this->reward;
    }

    public function isManual(): bool
    {
        return $this->type == 1;
    }

    public function toJson(): array
    {
        $steps = [];
        foreach ($this->steps as $step) {
            $steps[] = $step->toJson();
        }
        $places = [];
        foreach ($this->placesOfAvailability as $place) {
            $places[] = $place->toJson();
        }
        return [
            "title" => utf8_encode($this->title),
            "creator" => utf8_encode($this->creator->getUsername()),
            "caption" => utf8_encode($this->caption),
            "steps" => $steps,
            "id" => $this->id,
            "reward" => $this->reward,
            "type" => $this->type,
            "passengers" => $this->passengers,
            "places" => $places
        ];
    }

    public function toJsonOverview(): array
    {
        return [
            "title" => utf8_encode($this->title),
            "id" => $this->id,
            "reward" => $this->reward,
            "creator" => utf8_encode($this->creator->getUsername()),
            "type" => $this->type
        ];
    }
}
