<?php

namespace App\Tests\Repository;

use App\Entity\Campus;
use App\Entity\Lieu;
use App\Entity\Ville;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class LieuRepositoryTest extends KernelTestCase
{

    private EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();
        $this->entityManager = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();
    }

    public function testSomething(): void
    {
        $nombreDenregistrements = $this->entityManager->getRepository(Lieu::class)->findAll();

        $nantes = (new Ville())
            ->setNom("Nantes")
            ->setCodePostal("44000");


        $bowling = (new Lieu())
            ->setNom("Bowling")
            ->setVille($nantes)
            ->setRue("Rue de ma rue");

        $this->entityManager->getRepository(Ville::class)->save($nantes, true);
        $this->entityManager->getRepository(Lieu::class)->save($bowling, true);
        $this->assertCount(count($nombreDenregistrements) + 1, $this->entityManager->getRepository(Lieu::class)->findAll());
        $this->assertCount(count($nombreDenregistrements) + 1, $this->entityManager->getRepository(Lieu::class)->findAll());
        $this->entityManager->getRepository(Lieu::class)->remove($bowling, true);
        $this->assertCount(count($nombreDenregistrements), $this->entityManager->getRepository(Lieu::class)->findAll());
    }
}
