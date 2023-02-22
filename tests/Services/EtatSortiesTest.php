<?php

namespace App\Tests\Services;

use App\Entity\EtatSortiesEnum;
use App\Entity\Sortie;
use App\Repository\SortieRepository;
use App\Services\EtatSorties;
use Doctrine\Persistence\ObjectManager;
use Exception;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class EtatSortiesTest extends KernelTestCase
{
    /**
     * @throws Exception
     */
    public function testSomething(): void
    {
        $kernel = self::bootKernel();
        $container = static::getContainer();

        $sortieCree = (new Sortie())
            ->setEtat(EtatSortiesEnum::Creee->value);
        $sortiePubliee = (new Sortie())
            ->setEtat(EtatSortiesEnum::Publiee->value)
            ->setDateLimiteInscription(new \DateTime());
        $sortieCloturee = (new Sortie())
            ->setEtat(EtatSortiesEnum::Cloturee->value);
        $sortieEnCours = (new Sortie())
            ->setEtat(EtatSortiesEnum::EbCours->value);
        $sortiePassee = (new Sortie())
            ->setEtat(EtatSortiesEnum::Passee->value);
        $sortieAnnullee = (new Sortie())
            ->setEtat(EtatSortiesEnum::Annulee->value);
        $sortieArchivee = (new Sortie())
            ->setEtat(EtatSortiesEnum::Archivee->value);
        $sortieComplete = (new Sortie())
            ->setEtat(EtatSortiesEnum::Complet->value);

        $sortiesRepository = $this->createMock(SortieRepository::class);
        $sortiesRepository
            ->expects(self::never())
            ->method('findByEtat')
            ->willReturn([
                $sortieCree, $sortiePubliee, $sortieCloturee, $sortieEnCours, $sortiePassee, $sortieAnnullee, $sortieArchivee, $sortieComplete
            ]);

        $objectManager = $this->createMock(ObjectManager::class);
        $objectManager
            ->expects($this->any())
            ->method('getRepository')
            ->willReturn($sortiesRepository);

        $svc = $container->get(EtatSorties::class);
        $svc->updateEtatSorties();
    }
}
