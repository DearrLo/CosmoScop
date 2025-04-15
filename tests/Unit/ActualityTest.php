<?php

namespace App\Tests\Entity;

use App\Entity\Actuality;
use PHPUnit\Framework\TestCase;

class ActualityTest extends TestCase
{
    public function testGettersAndSetters(): void
    {
        $actuality = new Actuality();

        $title = 'New Discovery on Mars';
        $content = 'Scientists found traces of water...';
        $date = new \DateTime('2025-04-15');
        $image = 'mars.jpg';
        $description = 'A short description of the discovery.';

        $actuality->setTitle($title);
        $actuality->setContent($content);
        $actuality->setPublicationDate($date);
        $actuality->setImageFilename($image);
        $actuality->setDescription($description);

        $this->assertSame($title, $actuality->getTitle());
        $this->assertSame($content, $actuality->getContent());
        $this->assertSame($date, $actuality->getPublicationDate());
        $this->assertSame($image, $actuality->getImageFilename());
        $this->assertSame($description, $actuality->getDescription());
    }

    public function testCommentsCollectionStartsEmpty(): void
    {
        $actuality = new Actuality();
        $this->assertCount(0, $actuality->getComments());
    }
}
