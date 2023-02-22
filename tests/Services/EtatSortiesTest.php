<?php

namespace App\Tests\Services;

use App\Entity\EtatSortiesEnum;
use App\Entity\Sortie;
use App\Repository\SortieRepository;
use App\Services\EtatSorties;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectManager;
use Exception;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class EtatSortiesTest extends KernelTestCase
{
    /**
     * @throws Exception
     */
    public function testUpdateSorties(): void
    {
        $kernel = self::bootKernel();
        $container = static::getContainer();

        $sortieCree = (new Sortie())
            ->setEtat(EtatSortiesEnum::Creee->value)
            ->setFinSortie(new \DateTime());
        $sortiePubliee = (new Sortie())
            ->setEtat(EtatSortiesEnum::Publiee->value)
            ->setFinSortie(new \DateTime());
        $sortieCloturee = (new Sortie())
            ->setEtat(EtatSortiesEnum::Cloturee->value)
            ->setFinSortie(new \DateTime());
        $sortieEnCours = (new Sortie())
            ->setEtat(EtatSortiesEnum::EbCours->value)
            ->setFinSortie(new \DateTime());
        $sortiePassee = (new Sortie())
            ->setEtat(EtatSortiesEnum::Passee->value)
            ->setFinSortie(new \DateTime());
        $sortieAnnullee = (new Sortie())
            ->setEtat(EtatSortiesEnum::Annulee->value)
            ->setFinSortie(new \DateTime());
        $sortieArchivee = (new Sortie())
            ->setEtat(EtatSortiesEnum::Archivee->value)
            ->setFinSortie(new \DateTime());
        $sortieComplete = (new Sortie())
            ->setEtat(EtatSortiesEnum::Complet->value)
            ->setFinSortie(new \DateTime());

        $sortiesRepository = $this->createMock(SortieRepository::class);
        $sortiesRepository
            ->expects(self::once())
            ->method('findByEtat')
            ->willReturn([
                $sortieCree, $sortiePubliee, $sortieCloturee, $sortieEnCours, $sortiePassee, $sortieAnnullee, $sortieArchivee, $sortieComplete
            ]);

        $objectManager = $this->createMock(EntityManagerInterface::class);
        $objectManager
            ->expects(self::never())
            ->method('getRepository')
            ->willReturn($sortiesRepository);

        $svc = new EtatSorties($sortiesRepository, $objectManager);
        $svc->updateEtatSorties();
    }
}
