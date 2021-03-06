<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Exception;
use JsonSerializable;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\SerializedName;

/**
 * @ORM\Entity
 * @ORM\Table(name="sensors")
 **/
class Sensor implements JsonSerializable
{
    /**
     * @ORM\Id()
     * @ORM\Column(type="uuid", unique=true)
     * @Groups({"sensor_list", "sensor_details"})
     */
    protected UuidInterface $id;

    /**
     * @ORM\Column(name="project", type="string")
     * @Groups({"sensor_list", "sensor_details"})
     */
    private string $project;

    /**
     * @ORM\Column(name="name", type="string")
     * @Groups({"sensor_list", "sensor_details"})
     */
    private string $name;

    /**
     * @var string
     * @ORM\Column(name="location", type="string")
     * @Groups({"sensor_list", "sensor_details"})
     */
    private string $location;

    /**
     * @var ArrayCollection $parameters
     * @ORM\OneToMany(targetEntity="App\Entity\Parameter", mappedBy="sensor", cascade={"remove", "persist"}, orphanRemoval=true)
     * @Groups({"sensor_details"})
     */
    private Collection $parameters;

    public static function fromProjectNameAndLocation(string $project, string $name, string $location = ''): self
    {
        return new self($project, $name, $location);
    }

    /**
     * Sensor constructor.
     * @param string $project
     * @param string $name
     * @param string $location
     * @throws Exception
     */
    private function __construct(string $project, string $name, string $location = '')
    {
        $this->id = Uuid::uuid4();
        $this->project = $project;
        $this->name = $name;
        $this->location = $location;
        $this->parameters = new ArrayCollection();
    }

    public function id(): UuidInterface
    {
        return $this->id;
    }

    public function project(): string
    {
        return $this->project;
    }

    public function name(): string
    {
        return $this->name;

    }

    public function location(): string
    {
        return $this->location;
    }

    public function addParameter(Parameter $parameter): self
    {
        $parameter->setSensor($this);
        $this->parameters->add($parameter);
        return $this;
    }

    public function hasParameterWithType(string $type): bool
    {
        return $this->parameters()->exists(function ($key, $parameter) use ($type) {
            return $type === $parameter->name();
        });
    }

    public function hasParameterWithName(string $name): bool
    {
        /** @var Parameter $parameter */
        return $this->parameters()->exists(function ($key, $parameter) use ($name) {
            return $name === $parameter->name();
        });
    }

    public function getParameterWithName(string $name): ?Parameter
    {
        $filteredParameter = $this->parameters()->filter(function ($parameter) use ($name) {
            return $name === $parameter->name();
        });

        if (count($filteredParameter) > 0) {
            return $filteredParameter->first();
        }

        return null;
    }

    public function parameters(): Collection
    {
        return $this->parameters;
    }

    /**
     * @Groups({"sensor_list"})
     * @SerializedName("customer_name")
     */
    public function getParameterList(): array
    {
        $list = [];
        /** @var Parameter $parameter */
        foreach ($this->parameters() as $parameter) {
            $list[] = $parameter->type();
        }

        return $list;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id->toString(),
            'name' => $this->name,
            'project' => $this->project,
            'location' => $this->location,
            'parameters' => $this->parameters->toArray()
        ];
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
