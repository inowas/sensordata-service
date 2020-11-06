<?php

declare(strict_types=1);


namespace App\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use JsonSerializable;

/**
 * @ORM\Entity
 * @ORM\Table(name="data")
 **/
class Data implements JsonSerializable
{
    /**
     * @ORM\Id
     * @ORM\Column(name="id", type="integer")
     * @ORM\GeneratedValue()
     */
    protected int $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\DataSet", inversedBy="data")
     * @ORM\JoinColumn(name="data_id", referencedColumnName="id")
     */
    private DataSet $dataSet;

    /**
     * @var DateTime $dateTime
     * @ORM\Column(type="datetime")
     */
    protected DateTime $dateTime;

    /**
     * @var float $value
     * @ORM\Column(name="value", type="float")
     */
    private $value;

    /**
     * SensorValue constructor.
     * @param DateTime $dateTime
     * @param array $data
     */
    public function __construct(DateTime $dateTime, float $value)
    {
        $this->dateTime = $dateTime;
        $this->value = $value;
    }

    public function dateTime(): DateTime
    {
        return $this->dateTime;
    }

    /**
     * Specify data which should be serialized to JSON
     * @link https://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4.0
     */
    public function jsonSerialize(): array
    {
        return [
            'date_time' => $this->dateTime->format(DATE_ATOM),
            'value' => $this->value
        ];
    }

    public function dataSet(): DataSet
    {
        return $this->dataSet;
    }
}
