<?php

namespace App\Services;

use App\Entity\Sortie;
use App\Entity\Stagiaire;
use Doctrine\ORM\EntityManagerInterface;

class InscriptionsService
{

    /**
     * Méthode qui inscrit un stagiaire à une sortie
     * @param Stagiaire $stag
     * @param Sortie $sortie
     * @param EntityManagerInterface $em
     * @return array Tableau qui contient : true ou false en fonction de l'inscription + le message explicatif
     */
    function inscrire(Stagiaire $stag,Sortie $sortie,EntityManagerInterface $em):array{
        $message="";
        // Vérifier qu'il est possible de s'inscrire à la sortie
        // la sortie doit être à l'état 2
        if (!$sortie->getEtat()==\App\Entity\EtatSortiesEnum::Publiee->value)
                $message="Sortie a l'état".$sortie->getEtat();
        // la date d'inscription n'est pas dépassée
        if ($sortie->getDateLimiteInscription() <=  new \DateTime('now'))
            $message="La date limite d'inscrition est dépassée.";

        // le nb d'inscrits ne dépasse pas le nb max d'inscription
        if( $sortie->getParticipants()->count() >= $sortie->getNombreInscriptionsMax())
                $message="nb de participants max atteint";

        // le stagiaire ne doit pas déjà être inscrit
        if ($sortie->getParticipants()->contains($stag))
                $message="Vous êtes déjà inscrit !";

        if ($message=="")
        {
            // inscrire
            $sortie->addParticipant($stag);
            // persister
             $em->persist($sortie);
             $em->flush();
        }
        else return [false,$message];
        return [true,$message];
    }

    function SeDesinscrire(Stagiaire $stag,Sortie $sortie,EntityManagerInterface $em):bool
    {
        $participants = $sortie->getParticipants();
        foreach ($participants as $participant) {
            // Vérifie que l'utilisateur actuel participe bien à la sortie.
            if ($participant === $stag) {
                // Si l'utilisateur participe bien à la sortie, le supprime de la liste des participants.
                $sortie->removeParticipant($participant);
                $em->flush();
                return true;
            }
        }
        return false;
    }

}