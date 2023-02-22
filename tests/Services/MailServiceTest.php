<?php

namespace App\Tests\Services;

use App\Entity\Sortie;
use App\Entity\Stagiaire;
use App\Services\MailService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;

class MailServiceTest extends KernelTestCase
{
    /**
     * @throws TransportExceptionInterface
     */
    public function testSomething(): void
    {
        $kernel = self::bootKernel();

        $mailer = $this->createMock(MailerInterface::class);
        $mailer->expects(self::once())
            ->method('send');

        $mailService = new MailService($mailer);

        $stagiaire = (new Stagiaire())
            ->setEmail('test@test.fr');

        $sortie = (new Sortie())
            ->setOrganisateur($stagiaire);

        $sortie->addParticipant($stagiaire);

        $mailService->sendMailParticipants($sortie, "Mail service");
    }
}
