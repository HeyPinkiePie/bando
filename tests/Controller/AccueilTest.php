<?php

namespace App\Tests\Controller;

use App\Entity\Stagiaire;
use App\Repository\StagiaireRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AccueilTest extends WebTestCase
{
    public function testAccueilRedirectToLogin(): void
    {
        $client = static::createClient();
        $client->request('GET', '/');
        $this->assertResponseRedirects('/login');
    }

    public function testAccueilWhenLogin(): void
    {
        $client = static::createClient();
        $stagRepo = static::getContainer()->get(StagiaireRepository::class);
        $utilisateur = $stagRepo->findOneBy(["id" => 1]);
        $client->loginUser($utilisateur);
        $client->request('GET', '/');
        $this->assertResponseRedirects('/sorties/liste');
    }
}
