<?php

namespace App\Tests\Entity;

use App\Entity\Campus;
use App\Entity\Sortie;
use App\Entity\Stagiaire;
use PHPUnit\Framework\TestCase;

class CampusTest extends TestCase
{
    public function testAccesseurs(): void
    {
        $faraday = (new Campus())
            ->setNom("Faraday");
        // Getters & Setters
        $this->assertNull($faraday->getId());
        $this->assertEquals("Faraday", $faraday->getNom());
        $this->assertEquals(0, $faraday->getSorties()->count());
        $this->assertEquals(0, $faraday->getStagiaires()->count());
        // Add & Remove
        $sortie = new Sortie();
        $faraday->addSorty($sortie);
        $stagiaire = new Stagiaire();
        $faraday->addStagiaire($stagiaire);
        $this->assertEquals(1, $faraday->getSorties()->count());
        $this->assertEquals(1, $faraday->getStagiaires()->count());
        $faraday->removeSorty($sortie);
        $faraday->removeStagiaire($stagiaire);
        $this->assertEquals(0, $faraday->getSorties()->count());
        $this->assertEquals(0, $faraday->getStagiaires()->count());
    }
}
