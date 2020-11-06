<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Exception;
use JsonSerializable;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

/**
 * @ORM\Entity
 * @ORM\Table(name="sensors")
 **/
class Sensor implements JsonSerializable
{
    /**
     * @ORM\Id()
     * @ORM\Column(type="uuid", unique=true)
     */
    protected UuidInterface $id;

    /**
     * @ORM\Column(name="project", type="string")
     */
    private string $project;

    /**
     * @ORM\Column(name="name", type="string")
     */
    private string $name;

    /**
     * @var string
     * @ORM\Column(name="location", type="string")
     */
    private string $location;

    /**
     * @var ArrayCollection $parameters
     * @ORM\OneToMany(targetEntity="App\Entity\Parameter", mappedBy="sensor", cascade={"persist"})
     */
    private ArrayCollection $parameters;

    /**
     * Sensor constructor.
     * @param string $project
     * @param string $name
     * @param string $location
     * @throws Exception
     */
    public function __construct(string $project, string $name, string $location = '')
    {
        $this->id = Uuid::uuid4();
        $this->project = $project;
        $this->name = $name;
        $this->location = $location;
        $this->parameters = new ArrayCollection();
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
        $this->parameters->add($parameter);
        return $this;
    }

    public function parameters(): ArrayCollection
    {
        return $this->parameters;
    }

    public function jsonSerialize()
    {
        return [
            'name' => $this->name,
            'project' => $this->project,
            'location' => $this->location,
            'parameters' => $this->parameters->toArray()
        ];
    }
}
