<?php

declare(strict_types=1);


namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JsonSerializable;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity
 * @ORM\Table(name="parameters")
 **/
class Parameter implements JsonSerializable
{
    /**
     * @ORM\Id
     * @ORM\Column(type="uuid", unique=true)
     * @Groups({"sensor_details", "parameter_details", "parameter_data"})
     */
    protected UuidInterface $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Sensor", inversedBy="parameters", cascade={"persist"})
     * @ORM\JoinColumn(name="sensor_id", referencedColumnName="id")
     */
    protected Sensor $sensor;

    /**
     * @var string $type
     * @ORM\Column(name="type", type="string")
     * @Groups({"sensor_list", "sensor_details", "parameter_details", "parameter_data"})
     */
    private string $type;

    /**
     * @ORM\Column(name="name", type="string")
     * @Groups({"sensor_details", "parameter_details", "parameter_data"})
     */
    private string $name;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\DataSet", mappedBy="parameter", cascade={"remove", "persist"}, orphanRemoval=true)
     * @ORM\OrderBy({"lastDateTime" = "ASC"})
     * @Groups({"parameter_details"})
     */
    private Collection $dataSets;

    public static function fromTypeAndName(string $type, string $name): self
    {
        return new self($type, $name);
    }

    private function __construct(string $type, string $name = '')
    {
        $this->id = Uuid::uuid4();
        $this->type = $type;
        $this->name = $name;
        $this->dataSets = new ArrayCollection();
    }

    public function id(): UuidInterface
    {
        return $this->id;
    }

    /**
     * @return UuidInterface
     * @Groups({"parameter_details", "parameter_data"})
     */
    public function getSensorId(): UuidInterface
    {
        return $this->sensor()->id();
    }

    public function type(): string
    {
        return $this->type;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function dataSets(): array
    {
        return $this->dataSets->toArray();
    }

    public function countDatasets(): int
    {
        return $this->dataSets->count();
    }

    public function latestDataset(): ?DataSet
    {
        if ($this->dataSets->isEmpty()) {
            return null;
        }

        return $this->dataSets->last();
    }

    public function addDataSet(DataSet $ds): self
    {
        $ds->setParameter($this);
        $this->dataSets->add($ds);
        return $this;
    }

    /**
     * @return array
     * @Groups({"parameter_details"})
     */
    public function getDataSets(): array
    {
        $data = [];
        /** @var DataSet $dataSet */
        foreach ($this->dataSets() as $dataSet) {
            $data[] = [
                'id' => $dataSet->id(),
                'first' => $dataSet->firstDateTime()->format(DATE_ATOM),
                'last' => $dataSet->lastDateTime()->format(DATE_ATOM),
                'numberOfValues' => $dataSet->numberOfValues(),
                'dataSource' => $dataSet->dataSource()->toInt(),
                'filename' => $dataSet->filename()
            ];
        }

        return $data;
    }

    /**
     * @return array
     * @Groups({"parameter_data"})
     */
    public function getData(): array
    {
        $timestamps = [];
        $values = [];
        /** @var DataSet $dataSet */
        foreach ($this->dataSets as $dataSet) {
            /** @var DateTimeValue $dateTimeValue */
            foreach ($dataSet->data() as $dateTimeValue) {
                $timestamps[] = $dateTimeValue->timestamp();
                $values[] = $dateTimeValue->value();
            }
        }

        array_multisort($timestamps, $values);

        $data = [];
        foreach ($timestamps as $key => $timestamp) {
            $data[] = [$timestamps[$key], $values[$key]];
        }
        return $data;
    }

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id(),
            'type' => $this->type(),
            'name' => $this->name(),
            'dataSets' => $this->dataSets()
        ];
    }

    /**
     * @param Sensor $sensor
     * @return Parameter
     */
    public function setSensor(Sensor $sensor): Parameter
    {
        $this->sensor = $sensor;
        return $this;
    }

    public function sensor(): Sensor
    {
        return $this->sensor;
    }
}
