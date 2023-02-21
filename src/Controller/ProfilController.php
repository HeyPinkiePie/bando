<?php

namespace App\Controller;

use App\Form\ProfilType;
use App\Repository\StagiaireRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class ProfilController extends AbstractController
{
    #[isGranted("ROLE_USER")]
    #[Route('/profil', name: 'profil_modif')]
    public function index(EntityManagerInterface $em, Request $request, StagiaireRepository $stagiaireRepository, UserPasswordHasherInterface $passwordHasher,SessionInterface $session): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED',message: 'Vous n\'êtes pas authentifié, merci de vous authentifier');
        $this->denyAccessUnlessGranted('ROLE_USER','ROLE_ADMIN',message: 'Vous n\'avez pas les droits d\'accès ou vous n\'êtes pas connécté');

        $stagiaire = $this->getUser();
        $profilForm = $this->createForm(ProfilType::class, $stagiaire);
        $profilForm->handleRequest($request);
        $stagiaireFind = $stagiaireRepository->findOneBy(['email'=>$stagiaire->getUserIdentifier()]);

        if ($profilForm->isSubmitted() && $profilForm->isValid()){
            if(!$passwordHasher->isPasswordValid($stagiaireFind,$profilForm['currentPassword']->getData())){
                $this->addFlash('erreur', 'Le mot de passe est erroné');
            }else if (
                empty($profilForm->get('nom')->getData()) ||
                strlen($profilForm->get('nom')->getData()) > 255
            ) {
                $this->addFlash('erreur', 'Mauvais format du nom');
            } else if (
                empty($profilForm->get('prenom')->getData()) ||
                strlen($profilForm->get('prenom')->getData()) > 255
            ) {
                $this->addFlash('erreur', 'Mauvais format du prenom');
            } else if (
                empty($profilForm->get('telephone')->getData()) ||
                strlen($profilForm->get('telephone')->getData()) != 10
            ) {
                $this->addFlash('erreur', 'Mauvais format du numéro de téléphone');
            } else {
                $stagiaire = $profilForm->getData();
                if(!is_null($profilForm['password']->getData())){
                    if(!preg_match('/^(?=.{8,}$)(?=.*?[a-z])(?=.*?[A-Z])(?=.*?[0-9])(?=.*?\W).*$/',$profilForm['password']->getData())){
                        $this->addFlash('erreur','Le mot de passe ne respect pas le format requis (Au minimum 8 caractères, une minuscule, une majuscule et 1 signe spécial');
                    } else{
                        $encoded_password = $passwordHasher->hashPassword($stagiaire, $profilForm['password']->getData());
                        $stagiaire->setPassword($encoded_password);
                        $em->persist($stagiaire);
                        $em->flush();
                        $this->addFlash('success', 'Votre profil a été mis à jour !');
                    }
                }else{
                    $em->persist($stagiaire);
                    $em->flush();
                    $this->addFlash('success', 'Votre profil a été mis à jour !');
                }
            }
        }
        return $this->render('profil/modif.html.twig', [
            'profilForm' => $profilForm,
            'stagiaire' => $stagiaire
        ]);
    }
    #[isGranted("ROLE_USER")]
    #[Route('/profilAffiche/{id}', name: 'profil_affich')]
    public function affiche(EntityManagerInterface $em, Request $request, StagiaireRepository $stagiaireRepository, int $id): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER','ROLE_ADMIN',message: 'Vous n\'avez pas les droits d\'accès ou vous n\'êtes pas connécté');
        //Récupération du stagiaire passé en paramètre de l'appel au contrôleur.
        $stagiaire = $stagiaireRepository->findOneBy(['id' => $id]);

        //Récupération du stagiaire connecté
        $stagiaireConnecteInterface = $this->getUser();
        $stagiaireConnecte = $stagiaireRepository->findOneBy(['email' => $stagiaireConnecteInterface->getUserIdentifier()]);

        //Si le stagiaire connecté est le stagiaire recherché alors il est renvoyé vers la page de modification du profil
        if ($stagiaire->getId() == $stagiaireConnecte->getId()) {
            return $this->redirectToRoute('profil_modif');
        }
        return $this->render('profil/affiche.html.twig', [
            'stagiaire' => $stagiaire,
        ]);
    }
}
