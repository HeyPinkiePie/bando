<?php

namespace App\Tests\Services;

use App\Entity\EtatSortiesEnum;
use App\Entity\Sortie;
use App\Entity\Stagiaire;
use App\Services\InscriptionsService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class InscriptionsServiceTest extends KernelTestCase
{
    public function testInscriptionDesinscription(): void
    {
        self::bootKernel();
        $entitymanager = $this->createMock(EntityManagerInterface::class);
        $entitymanager->expects(self::never())
            ->method('getRepository')
            ->willReturn([]);
        $sortie = (new Sortie())
            ->setEtat(EtatSortiesEnum::Publiee->value)
            ->setNombreInscriptionsMax(10)
            ->setDateLimiteInscription((new \DateTime())->modify('+5 day'));
        $stagiaire = new Stagiaire();
        $svc = new InscriptionsService();
        $inscription = $svc->inscrire($stagiaire, $sortie, $entitymanager);
        $this->assertCount(1, $sortie->getParticipants());
        $this->assertTrue($inscription[0]);
        $this->assertEquals("", $inscription[1]);
        $desinscription = $svc->SeDesinscrire($stagiaire, $sortie, $entitymanager);
        $this->assertCount(0, $sortie->getParticipants());
        $this->assertTrue($desinscription);
    }
}
