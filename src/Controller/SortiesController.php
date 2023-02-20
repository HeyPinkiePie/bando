<?php

namespace App\Controller;
use App\Entity\EtatSorties;
use App\Entity\Sortie;
use App\Form\SortieAnnulationFormType;
use App\Form\SortieFormType;
use App\Form\SortieSearchFormType;
use App\Repository\CampusRepository;
use App\Repository\LieuRepository;
use App\Repository\SortieRepository;
use App\Repository\StagiaireRepository;
use App\Repository\VilleRepository;
use App\Services\InscriptionsService;
use App\Services\SortiesService;
use DateInterval;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * @method AccessDeniedException(string $string)
 */
 #[Route('/sorties', name: 'sorties')]
class SortiesController extends AbstractController
{

    #[isGranted("ROLE_USER")]
    #[Route('/liste', name: '_liste')]
    public function sorties(
        SortieRepository     $sortieRepository,
        Request              $request,
        FormFactoryInterface $formFactory
    ): Response
    {
        // Création du formulaire de recherche de sorties
        $form = $formFactory->create(SortieSearchFormType::class);

        // Gestion de la soumission du formulaire
        $form->handleRequest($request);

        // Si le formulaire a été soumis et est valide
        if ($form->isSubmitted() && $form->isValid()) {
            // Récupération des données du formulaire
            $data = [
                'nom' => $form->get('nom')->getData(),
                'debutSortie' => $form->get('debutSortie')->getData(),
                'finSortie' => $form->get('finSortie')->getData(),
                'campus' => $form->get('campus')->getData(),
                'organisateur' => $form->get('organisateur')->getData(),
                'inscrit' => $form->get('inscrit')->getData(),
                'non_inscrit' => $form->get('non_inscrit')->getData(),
                'sorties_passees' => $form->get('sorties_passees')->getData()
            ];

            // Si la case "Sorties passées" est cochée, on ignore la date de début de la sortie
            if ($data['sorties_passees']) {
                $data['debutSortie'] = null;
            }

            // Recherche des sorties en fonction des données renseignées par l'utilisateur
            $sorties = $sortieRepository->findSorties(
                $data['nom'],
                $data['debutSortie'],
                $data['finSortie'],
                $data['campus'],
                $data['organisateur'],
                $this->getUser(),
                $data['inscrit'],
                $data['non_inscrit'],
                $data['sorties_passees']
            );
        } else {
            // Si le formulaire n'a pas été soumis ou n'est pas valide, récupération de toutes les sorties
            $sorties = $sortieRepository->findSorties();
        }

        // Rendu de la vue et envoi des données
        return $this->render('sorties/sorties.html.twig', [
            'sorties' => $sorties,
            'form' => $form->createView(),
        ]);
    }

    #[isGranted("ROLE_USER")]
    #[Route('/sortie/{id}', name: '_detail')]
    public function detail(
        int              $id,
        SortieRepository $sortieRepository
    ): Response
    {
        $sortie = $sortieRepository->findOneBy(["id" => $id]);
        return $this->render('sorties/sortie-detail.html.twig',
            compact('sortie')
        );
    }

    /**
     * @param EntityManagerInterface $entityManager
     * @param StagiaireRepository $stagRepo
     * @param VilleRepository $villesRepo
     * @param LieuRepository $LieuxRepo
     * @param Request $request
     * @param SortiesService $serviceSorties
     * @return Response
     */
    #[isGranted("ROLE_USER")]
    #[Route('/creer', name: '_creer')]
    public function creer(
        EntityManagerInterface $entityManager,
        StagiaireRepository $stagRepo,
        VilleRepository $villesRepo,
        LieuRepository $LieuxRepo,
        Request $request,
        SortiesService $serviceSorties
    ): Response {
        try {
            //récupération du stagiaire connecté
            $user=$stagRepo->findOneAvecCampus($this->getUser()->getUserIdentifier());

            //initialisation de la sortie
            $sortie = new Sortie();
            $sortie->setOrganisateur($user);
            //mettre le campus de l'organisateur par défaut au début
            if (!$sortie->getCampus()) $sortie->setCampus($user->getCampus());

            //création du formulaire
            $form = $this->createForm(SortieFormType::class, $sortie);
            $form->handleRequest($request);

            //traiter l'envoi du formulaire
            if ($form->isSubmitted()) {

                // renseigner le lieu
                $idLieu = $request->request->get("choixLieux");
                if ($idLieu)  $sortie->setLieu( $LieuxRepo->findOneBy(["id" => $idLieu]));

                 //  trouver la date de fin en fonction de la durée et de la date de début
                $duree =(int) $request->request->get("duree");
                $serviceSorties->ajouterDureeAdateFin($sortie,$duree);

                //l'état dépend du bouton sur lequel on a cliqué
                $sortie->setEtat(($request->request->get( 'Publier' ) ?
                                        EtatSorties::Publiee->value: EtatSorties::Creee->value));

                //vérification des contraintes métier
                $metier=  $serviceSorties->verifSortieValide($sortie,$duree);

                //si OK on enregistre
                if ($form->isValid() && $metier["ok"]) {
                    $entityManager->persist($sortie);
                    $entityManager->flush();
                    return $this->redirectToRoute('sorties_liste');
                }
                else //sinon on reste sur la page mais on affiche les erreurs
                {
                    $this->addFlash("error","merci de vérifier, il y a des erreurs.".$metier["message"]);
                }
            }

            //passer la liste des villes au formulaire
            $villes = $villesRepo->findAll();
            return $this->render('sorties/creer.html.twig', [
                'form' => $form->createView(),
                "villes" => $villes
            ]);
        } catch (Exception $ex){
            return $this->render('pageErreur.html.twig', ["message"=>$ex->getMessage()]);
        }
    }

    /**
     * Modifie une sortie existante dans la base de données.
     *
     * @param int                    $id               L'identifiant de la sortie à modifier.
     * @param EntityManagerInterface $entityManager    L'entité manager pour accéder à la base de données.
     * @param VilleRepository        $villesRepo       Le repository pour accéder aux villes de la base de données.
     * @param LieuRepository         $LieuxRepo        Le repository pour accéder aux lieux de la base de données.
     * @param Request                $request
     * @param FormFactoryInterface   $formFactory      Le factory pour créer des formulaires.
     *
     * @throws Exception si la sortie n'existe pas ou si l'utilisateur cherchant à modifier la sortie n'en est pas l'organisateur.
     *
     * @return Response
     */
     #[isGranted("ROLE_USER")]
    #[Route('/modifier/{id}', name: '_modifier-sortie')]
    public function modifier(
        int                    $id,
        EntityManagerInterface $entityManager,
        VilleRepository        $villesRepo,
        LieuRepository         $LieuxRepo,
        Request                $request,
        FormFactoryInterface   $formFactory
    ): Response
    {

        // Récupère l'entité Sortie correspondant à l'ID passé en paramètre de la requête
        $sortie = $entityManager->getRepository(Sortie::class)->find($id);

        // Vérifie que l'utilisateur authentifié est l'organisateur de la sortie à modifier
        if($this->getUser() !== $sortie->getOrganisateur()) {
            throw $this->createAccessDeniedException('Vous ne pouvez pas modifier une sortie dont vous n\'êtes pas l\'organisateur.');
        }

        // Vérifie que la sortie existe
        if (!$sortie) {
            throw $this->createNotFoundException('La sortie n\'existe pas.');
        }

        // Calcule la durée de la sortie en minutes
        $duree = $sortie->getDebutSortie()->diff($sortie->getFinSortie())->i;


        // Crée le formulaire et pré-remplit les champs avec les valeurs actuelles de la sortie
        $form = $formFactory->createBuilder(SortieFormType::class, $sortie)->getForm();
        $form->handleRequest($request);

        // Traite l'envoi du formulaire
        if ($form->isSubmitted() && $form->isValid()) {
            // Met à jour la date de fin en fonction de la durée et de la date de début
            $duree = $request->request->get('duree');
            if ($duree) {
                $dateFin = clone $sortie->getDebutSortie();
                $dateFin->add(new DateInterval('PT' . $duree . 'M'));
                $sortie->setFinSortie($dateFin);
            }

            // Renseigne le lieu
            $idLieu = $request->request->get("choixLieux");
            $lieu = $LieuxRepo->findOneBy(["id" => $idLieu]);
            $sortie->setLieu($lieu);


            // Met à jour l'état de la sortie en fonction du bouton cliqué
            $sortie->setEtat($request->request->get('Publier') ? EtatSorties::Publiee->value : EtatSorties::Creee->value);


            // Enregistre les modifications dans la base de données
            $entityManager->flush();

            // Redirige vers la liste des sorties
            return $this->redirectToRoute('sorties_liste');
        }

        // Récupère la liste des villes pour le formulaire
        $villes = $villesRepo->findAll();

        // Affiche le formulaire de modification de la sortie
        return $this->render('sorties/modifier.html.twig', [
            'form' => $form->createView(),
            'villes' => $villes,
            'duree' => $duree
        ]);
    }



    /**
     * Méthode permettant à un utilisateur authentifié de s'inscrire à une sortie
     * @param int $idSortie L'identifiant de la sortie
     * @param SortieRepository $sortieRepo
     * @param EntityManagerInterface $entityManager
     * @param InscriptionsService $serv
     * @return Response
     */

    #[isGranted("ROLE_USER")]
    #[Route('/sinscrire/sortie/{id}', name: '_sinscrire')]
    public function Sinscrire(  int $id,

                              SortieRepository       $sortieRepo,
                              EntityManagerInterface $entityManager,
                              InscriptionsService    $serv): Response
    {

        try {


            // récupérer le stagiaire
            // $stag = $stagRepo->findOneBy(["id"=>$idStagiaire]);
            $stag = $this->getUser();
            // récupérer la sortie
            $sortie = $sortieRepo->findOneBy(["id" => $id]);
            // inscrire et confirmer ou infirmer l'inscription

            $tab = $serv->inscrire($stag, $sortie, $entityManager);
            if ($tab[0])
                $this->addFlash('success', 'vous avez été inscrit à la sortie');
            else  $this->addFlash('error', 'inscription impossible : ' . $tab[1]);

            //rediriger
            return $this->redirectToRoute('sorties_liste');

        } catch (Exception $ex){
            return $this->render('pageErreur.html.twig', ["message"=>$ex->getMessage()]);
        }

    }


    /**
     * Méthode permettant à un utilisateur authentifié de se désister d'une sortie à laquelle il est inscrit.
     *
     * @param int $id L'identifiant de la sortie.
     * @param SortieRepository $sortieRepository Le repository des sorties.
     * @param EntityManagerInterface $entityManager L'EntityManager pour gérer les entités Sortie.
     * @return Response
     */
    #[isGranted("ROLE_USER")]
    #[Route('/desistement/sortie/{id}', name: '_desistement')]
    public function desistement(int $id,
                                SortieRepository $sortieRepository,
                                EntityManagerInterface $entityManager,
                                InscriptionsService $inscriptionsService): Response
    {
        // Récupère la sortie correspondant à l'ID spécifié.
        $sortie = $sortieRepository->findOneBy(["id" => $id]);

        //récupère le participant
        $user = $this->getUser();

//        $participants = $sortie->getParticipants();
//        foreach ($participants as $participant) {
//            if ($participant === $user) {
//                // Si l'utilisateur participe bien à la sortie, le supprime de la liste des participants.
//                $sortie->removeParticipant($participant);
//                $entityManager->flush();
//                // Ajoute un message flash pour indiquer que le désistement a été enregistré.
//                $this->addFlash('success', 'Votre désistement a bien été pris en compte.');
//            }
//    }
           if ($inscriptionsService->SeDesinscrire($user,$sortie,$entityManager))
            $this->addFlash('success', 'Votre désistement a bien été pris en compte.');
           else
               $this->addFlash('error', 'Problème lors de votre désistement, veuilez contacter un administrateur.');


        // Redirige l'utilisateur vers la liste des sorties.
        return $this->redirectToRoute('sorties_liste');
    }

    #[isGranted("ROLE_USER")]
    #[Route('/annulation/sortie/{id}', name: '_annulation')]
    public function annulation(int $id, SortieRepository $sortieRepository, EntityManagerInterface $entityManager, StagiaireRepository $stagiaireRepository, VilleRepository $villeRepository, LieuRepository $lieuRepository, CampusRepository $campusRepository, Request $request): Response
    {
        //Contrôle de l'id organisateur entrant
        if (!is_int($id)) {
            $this->addFlash('erreur', 'Erreur l\'utilisateur n\'est pas reconnu');
            return $this->redirectToRoute('_sorties');
        }

        // Récupère la sortie correspondant à l'ID spécifié.
        $sortie = $sortieRepository->findOneBy(['id' => $id]);

        //Récupérer le campus associé à la sortie
        $campus = $campusRepository->findOneBy(['id' => $sortie->getCampus()]);

        //Récupérer le lieu associé à la sortie
        $lieu = $lieuRepository->findOneBy(['id' => $sortie->getLieu()->getId()]);

        //Récupérer la ville associé à la sortie
        $ville = $villeRepository->findOneBy(['id' => $lieu->getVille()->getId()]);

        //Récupère l'id du stagiaire connecté
        $stagiaireConnecte = $this->getUser();
        $stagiaire = $stagiaireRepository->findOneBy(['email' => $stagiaireConnecte->getUserIdentifier()]);
        $sortieForm = $this->createForm(SortieAnnulationFormType::class, $sortie);
        $sortieForm->handleRequest($request);


        //Vérifie si l'id de l'organisateur correspond à l'id de l'utilisateur connecté, si la date de début sortie n'est pas dépassée , si se n'est le cas il est renvoyé vers la liste des sorties


        if($sortie->getOrganisateur()->getId() != $stagiaire->getId() || $sortie->getEtat() > 3){
            return $this->redirectToRoute('sorties_liste');

        }
        if ($sortieForm->isSubmitted() && $sortieForm->isValid()) {
            foreach ($sortie->getParticipants() as $value) {
                $sortie->removeParticipant($value);
            }
            $sortie->setEtat(6);
            $entityManager->persist($sortie);
            $entityManager->flush();
            return $this->redirectToRoute('sorties_liste');
        }

        return $this->render('sorties/annulation.html.twig', [
            'sortieForm' => $sortieForm,
            'sortie' => $sortie,
            'campus' => $campus,
            'lieu' => $lieu,
            'ville' => $ville
        ]);
    }

     #[isGranted("ROLE_USER")]
     #[Route('/retourLieux', name: '_retourLieu')]
     public function retourDesLieux(Request $request,
                                    SessionInterface $session,
                                    EntityManagerInterface $entityManager,
                                    StagiaireRepository $stagRepo,
                                    VilleRepository $villesRepo,
                                    LieuRepository $LieuxRepo,
                                    SortiesService $serviceSorties){
        try{
         // Récupérer les données de la session
         $sortie = $session->get('sortie');
         $form = $this->createForm(SortieFormType::class, $sortie);
         $form->handleRequest($request);

         //traiter l'envoi du formulaire
         if ($form->isSubmitted()) {

             // renseigner le lieu
             $idLieu = $request->request->get("choixLieux");
             if ($idLieu)  $sortie->setLieu( $LieuxRepo->findOneBy(["id" => $idLieu]));

             //  trouver la date de fin en fonction de la durée et de la date de début
             $duree =(int) $request->request->get("duree");
             $serviceSorties->ajouterDureeAdateFin($sortie,$duree);

             //l'état dépend du bouton sur lequel on a cliqué
             $sortie->setEtat(($request->request->get( 'Publier' ) ?
                 EtatSorties::Publiee->value: EtatSorties::Creee->value));

             //vérification des contraintes métier
             $metier=  $serviceSorties->verifSortieValide($sortie,$duree);

             //si OK on enregistre
             if ($form->isValid() && $metier["ok"]) {
                 $entityManager->persist($sortie);
                 $entityManager->flush();
                 return $this->redirectToRoute('sorties_liste');
             }
             else //sinon on reste sur la page mais on affiche les erreurs
             {
                 $this->addFlash("error","merci de vérifier, il y a des erreurs.".$metier["message"]);
             }
         }

         //passer la liste des villes au formulaire
         $villes = $villesRepo->findAll();
         return $this->render('sorties/creer.html.twig', [
             'form' => $form->createView(),
             'sortie'=>$sortie,
             "villes" => $villes
         ]);
     } catch (Exception $ex){
 return $this->render('pageErreur.html.twig', ["message"=>$ex->getMessage()]);
 }
     }

}
