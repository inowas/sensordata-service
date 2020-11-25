<?php

declare(strict_types=1);


namespace App\Entity;

use App\Model\DataSource;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JsonSerializable;

/**
 * @ORM\Entity
 * @ORM\Table(name="datasets")
 **/
class DataSet implements JsonSerializable
{
    /**
     * @ORM\Id
     * @ORM\Column(name="id", type="integer", unique=true)
     * @ORM\GeneratedValue()
     */
    protected int $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Parameter", inversedBy="dataSets", cascade={"remove", "persist"})
     * @ORM\JoinColumn(name="parameter_id", referencedColumnName="id")
     */
    protected Parameter $parameter;

    /**
     * @ORM\Column(name="data_source", type="integer")
     */
    protected int $dataSource;

    /**
     * @ORM\Column(name="filename", type="string")
     */
    protected string $filename;

    /**
     * @ORM\Column(name="first_date_time", type="datetime")
     */
    protected DateTime $firstDateTime;

    /**
     * @ORM\Column(name="lastDateTime", type="datetime")
     */
    protected DateTime $lastDateTime;

    /**
     * @ORM\Column(name="number_of_values", type="integer")
     */
    protected int $numberOfValues;

    /**
     * @ORM\Column(name="created_at", type="datetime")
     */
    protected DateTime $createdAt;

    /**
     * @ORM\OneToMany(targetEntity="DateTimeValue", mappedBy="dataSet", cascade={"remove", "persist"}, orphanRemoval=true)
     */
    private Collection $data;

    public static function fromDatasourceWithData(DataSource $ds, array $dateTimeValues, ?string $filename = ''): DataSet
    {
        return new self($ds, $dateTimeValues, $filename);
    }

    /**
     * @param DataSource $ds
     * @param array[Data] $dateTimeValues
     * @param string|null $filename
     */
    private function __construct(DataSource $ds, array $dateTimeValues, ?string $filename = '')
    {
        $this->createdAt = new DateTime();
        $this->dataSource = $ds->toInt();
        $this->data = new ArrayCollection();

        $minTimestamp = null;
        $maxTimestamp = null;

        foreach ($dateTimeValues as $dateTimeValue) {
            if ($dateTimeValue instanceof DateTimeValue) {

                $currentTimestamp = $dateTimeValue->timestamp();
                if (null === $minTimestamp || null == $maxTimestamp) {
                    $minTimestamp = $currentTimestamp;
                    $maxTimestamp = $currentTimestamp;
                }

                if ($currentTimestamp > $maxTimestamp) {
                    $maxTimestamp = $currentTimestamp;
                }

                if ($currentTimestamp < $minTimestamp) {
                    $maxTimestamp = $currentTimestamp;
                }

                $dateTimeValue->setDataSet($this);
                $this->data->add($dateTimeValue);
            }
        }

        $this->firstDateTime = new DateTime(sprintf("@%s", $minTimestamp));
        $this->lastDateTime = new DateTime(sprintf("@%s", $maxTimestamp));
        $this->numberOfValues = count($dateTimeValues);
        $this->filename = $filename ?? '';
    }

    public function id(): int
    {
        return $this->id;
    }

    public function parameter(): Parameter
    {
        return $this->parameter;
    }

    public function dataSource(): DataSource
    {
        return DataSource::fromInt($this->dataSource);
    }

    public function createdAt(): DateTime
    {
        return $this->createdAt;
    }

    public function data(): array
    {
        return $this->data->toArray();
    }

    public function addDateTimeValue(DateTimeValue $dtValue): void
    {
        /** @var DateTimeValue $value */
        foreach ($this->data() as $value) {
            if ($value->dateTime()->getTimestamp() === $dtValue->timestamp()) {
                return;
            }
        }

        $this->data[] = $dtValue;
        $dtValue->setDataSet($this);
        $this->numberOfValues = count($this->data());
        if ($dtValue->dateTime()->getTimestamp() < $this->firstDateTime->getTimestamp()) {
            $this->firstDateTime = new DateTime(sprintf("@%s", $dtValue->dateTime()->getTimestamp()));
        }

        if ($dtValue->dateTime()->getTimestamp() > $this->lastDateTime->getTimestamp()) {
            $this->lastDateTime = new DateTime(sprintf("@%s", $dtValue->dateTime()->getTimestamp()));
        }
    }

    public function firstDateTime(): DateTime
    {
        return $this->firstDateTime;
    }

    public function lastDateTime(): DateTime
    {
        return $this->lastDateTime;
    }

    public function numberOfValues(): int
    {
        return $this->numberOfValues;
    }

    public function jsonSerialize()
    {
        return [
            'id' => $this->id(),
            'dataSource' => $this->dataSource()->toInt(),
            'createdAt' => $this->createdAt()->getTimestamp(),
            'data' => $this->data()
        ];
    }

    public function setParameter(Parameter $parameter): DataSet
    {
        $this->parameter = $parameter;
        return $this;
    }
}
