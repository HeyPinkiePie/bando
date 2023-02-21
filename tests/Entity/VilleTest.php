<?php

namespace App\Tests\Entity;

use App\Entity\Lieu;
use App\Entity\Ville;
use PHPUnit\Framework\TestCase;

class VilleTest extends TestCase
{
    public function testAccesseurs(): void
    {
        $nantes = (new Ville())
            ->setNom("Nantes")
            ->setCodePostal("44000");
        // Getters & Setters
        $this->assertEquals("Nantes", $nantes->getNom());
        $this->assertEquals("44000", $nantes->getCodePostal());
        $this->assertNull($nantes->getId());
        $this->assertEquals(0, $nantes->getLieux()->count());
        // Add & Remove
        $bowling = (new Lieu());
        $nantes->addLieux($bowling);
        $this->assertEquals(1, $nantes->getLieux()->count());
        $nantes->removeLieux($bowling);
        $this->assertEquals(0, $nantes->getLieux()->count());
    }
}
