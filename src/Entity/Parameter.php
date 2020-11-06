<?php

declare(strict_types=1);


namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="sensor_parameters")
 **/
class Parameter
{
    /**
     * @ORM\Id
     * @ORM\Column(name="id", type="integer")
     * @ORM\GeneratedValue()
     */
    protected int $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Sensor", inversedBy="parameters", cascade={"persist"})
     * @ORM\JoinColumn(name="sensor_id", referencedColumnName="id")
     */
    protected Sensor $sensor;

    /**
     * @var string $type
     * @ORM\Column(name="type", type="string")
     */
    private string $type;

    /**
     * @ORM\Column(name="name", type="string")
     */
    private string $name;

    /**
     * @var ArrayCollection $dataSets
     * @ORM\OneToMany(targetEntity="App\Entity\DataSet", mappedBy="parameter", cascade={"persist"})
     */
    private ArrayCollection $dataSets;

    public function __construct(string $type, string $name = '')
    {
        $this->type = $type;
        $this->name = $name;
        $this->dataSets = new ArrayCollection();
    }

    public function id(): int
    {
        return $this->id;
    }

    public function type(): string
    {
        return $this->type;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function dataSets(): ArrayCollection
    {
        return $this->dataSets;
    }
}
