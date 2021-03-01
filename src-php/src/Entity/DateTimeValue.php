<?php

declare(strict_types=1);


namespace App\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Exception;
use JsonSerializable;

/**
 * @ORM\Entity
 * @ORM\Table(name="data", indexes={@ORM\Index(name="data_index", columns={"timestamp", "dataset_id"})})
 **/
class DateTimeValue implements JsonSerializable
{
    /**
     * @ORM\Id
     * @ORM\Column(name="id", type="integer")
     * @ORM\GeneratedValue()
     */
    private int $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\DataSet", inversedBy="data")
     * @ORM\JoinColumn(name="dataset_id", referencedColumnName="id")
     */
    private DataSet $dataSet;

    /**
     * @ORM\Column(name="timestamp", type="integer")
     */
    private int $timestamp;

    /**
     * @ORM\Column(name="value", type="float")
     */
    private float $value;

    public static function fromDateTimeValue(DateTime $dt, float $value): self
    {
        return new self($dt->getTimestamp(), $value);
    }

    public static function fromTimestampValue(int $timestamp, float $value): self
    {
        return new self($timestamp, $value);
    }

    /**
     * SensorValue constructor.
     * @param int $timestamp
     * @param float $value
     */
    private function __construct(int $timestamp, float $value)
    {
        $this->timestamp = $timestamp;
        $this->value = $value;
    }

    public function id(): int
    {
        return $this->id;
    }

    /**
     * @return DateTime
     * @throws Exception
     */
    public function dateTime(): DateTime
    {
        return new DateTime(sprintf("@%d", $this->timestamp));
    }

    public function timestamp(): int
    {
        return $this->timestamp;
    }

    public function value(): float
    {
        return $this->value;
    }

    public function dataSet(): DataSet
    {
        return $this->dataSet;
    }

    public function toArray(): array
    {
        return [
            'timestamp' => $this->timestamp,
            'value' => $this->value
        ];
    }

    public function jsonSerialize()
    {
        return $this->toArray();
    }

    public function setDataSet(DataSet $dataSet): DateTimeValue
    {
        $this->dataSet = $dataSet;
        return $this;
    }
}
