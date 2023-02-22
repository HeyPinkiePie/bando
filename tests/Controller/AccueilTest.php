<?php

namespace App\Tests\Controller;

use App\Repository\StagiaireRepository;
use Exception;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AccueilTest extends WebTestCase
{
    /**
     * Vérifie si un utilisateur non connecté est redirigé vers la page
     * de login lorsqu'il accède a la page d'accueil du site web
     *
     * @return void
     */
    public function testAccueilRedirectToLogin(): void
    {
        $client = static::createClient();
        $client->request('GET', '/');
        $this->assertResponseRedirects('/login');
    }

    /**
     * Vérifie si un utilisateur connecté est redirigé vers la page
     * des sorties lorsqu'il accède a la page d'accueil du site web
     *
     * @return void
     * @throws Exception
     */
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
