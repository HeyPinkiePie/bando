<?php

namespace App\Tests\Repository;

use App\Entity\Campus;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class CampusRepositoryTest extends KernelTestCase
{

    private EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();
        $this->entityManager = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();
    }

    public function testSave(): void
    {
        $nombreDenregistrements = $this->entityManager->getRepository(Campus::class)->findAll();
        $faraday = (new Campus())
            ->setNom("Faraday");
        $this->entityManager->getRepository(Campus::class)->save($faraday, true);
        $this->assertCount(count($nombreDenregistrements) + 1, $this->entityManager->getRepository(Campus::class)->findAll());
        $this->assertCount(count($nombreDenregistrements) + 1, $this->entityManager->getRepository(Campus::class)->findAll());
        $this->entityManager->getRepository(Campus::class)->remove($faraday, true);
        $this->assertCount(count($nombreDenregistrements), $this->entityManager->getRepository(Campus::class)->findAll());
    }
}
