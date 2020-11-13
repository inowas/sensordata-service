<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\DataSet;
use App\Entity\DateTimeValue;
use App\Entity\Parameter;
use App\Entity\Sensor;
use App\Model\DataSource;
use DateTime;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class LoadSensors extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $sensor = Sensor::fromProjectNameAndLocation('DEU1', 'I-2', '')
            ->addParameter(Parameter::fromTypeAndName('ec', 'EC [mS/cm]'))
            ->addParameter(Parameter::fromTypeAndName('ec_25', 'EC25 [mS/cm]'))
            ->addParameter(Parameter::fromTypeAndName('h', 'h [mWs]'))
            ->addParameter(Parameter::fromTypeAndName('hlevel', 'hlevel [mNN]'))
            ->addParameter(Parameter::fromTypeAndName('ldo', 'LDO [mg/l]'))
            ->addParameter(Parameter::fromTypeAndName('ph', 'pH [-]'))
            ->addParameter(Parameter::fromTypeAndName('t', 'T [-]'))
            ->addParameter(Parameter::fromTypeAndName('t_intern', 'Tintern [-]'))
            ->addParameter(Parameter::fromTypeAndName('v_batt', 'Vbatt [V]'));

        $manager->persist($sensor);
        $manager->flush();
    }
}
