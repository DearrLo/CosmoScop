<?php

namespace App\Tests\Entity;

use App\Entity\Planet;
use PHPUnit\Framework\TestCase;

class PlanetTest extends TestCase
{
    public function testGettersAndSetters(): void
    {
        $planet = new Planet();

        $name = 'Neptune';
        $description = 'A mysterious and deep-blue planet.';
        $image = 'neptune.webp';
        $distance = 4495.1;

        $planet->setName($name);
        $planet->setDescription($description);
        $planet->setImage($image);
        $planet->setDistanceFromSun($distance);

        $this->assertSame($name, $planet->getName());
        $this->assertSame($description, $planet->getDescription());
        $this->assertSame($image, $planet->getImage());
        $this->assertSame($distance, $planet->getDistanceFromSun());
    }
}
