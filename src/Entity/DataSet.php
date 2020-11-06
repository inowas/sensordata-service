<?php

declare(strict_types=1);


namespace App\Entity;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\UuidInterface;

/**
 * @ORM\Entity
 * @ORM\Table(name="datasets")
 **/
class DataSet
{
    public const SOURCE_UIT = 0;
    public const SOURCE_CSV = 1;
    public const SOURCE_OTHER = 2;

    /**
     * @ORM\Id
     * @ORM\Column(name="id", type="uuid", unique=true)
     */
    protected UuidInterface $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Parameter", inversedBy="dataSets", cascade={"persist"})
     * @ORM\JoinColumn(name="parameter_id", referencedColumnName="id")
     */
    protected $parameter;

    /**
     * @ORM\Column(name="source", type="smallint")
     */
    protected int $source;

    /**
     * @var ArrayCollection $data
     * @ORM\OneToMany(targetEntity="App\Entity\Data", mappedBy="dataSet", cascade={"persist"})
     */
    private $data;

    /**
     * SensorValue constructor.
     * @param DateTime $dateTime
     * @param array $data
     */
    public function __construct(DateTime $dateTime, array $data)
    {
        $this->dateTime = $dateTime;
        $this->data = $data;
    }
}
