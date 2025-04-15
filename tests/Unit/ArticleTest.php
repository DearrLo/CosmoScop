<?php

namespace App\Tests\Entity;

use App\Entity\Article;
use PHPUnit\Framework\TestCase;

class ArticleTest extends TestCase
{
    public function testGettersAndSetters(): void
    {
        $article = new Article();

        $title = "CosmoScop Launch";
        $content = "The universe is vast and beautiful...";
        $publicationDate = new \DateTime('2025-04-15');
        $imageFilename = "cosmoscop.jpg";

        $article->setTitle($title);
        $article->setContent($content);
        $article->setPublicationDate($publicationDate);
        $article->setImageFilename($imageFilename);

        $this->assertSame($title, $article->getTitle());
        $this->assertSame($content, $article->getContent());
        $this->assertSame($publicationDate, $article->getPublicationDate());
        $this->assertSame($imageFilename, $article->getImageFilename());
    }
}
