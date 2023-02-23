<?php

namespace App\Controller\Admin;

use App\Entity\Stagiaire;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class StagiaireCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Stagiaire::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            // TextField::new('password')->hideOnForm(),
            EmailField::new('email'),
            TextField::new('nom'),
            TextField::new('prenom'),
            NumberField::new('telephone'),
            ArrayField::new('roles')->hideOnForm(),
            BooleanField::new('administrateur')->renderAsSwitch(false)->setHelp("n'activer que si nécessaire"),
            BooleanField::new('actif')->renderAsSwitch(false)->hideOnForm(),
            BooleanField::new('premiereConnexion')->hideOnForm()->renderAsSwitch(false),
            AssociationField::new('campus'),
        ];
    }

    /**
     * Surcharge de la méthode de la classe AbstractCrudController appelée lors de la création d'une entité
     * @param EntityManagerInterface $em
     * @param $stag
     * @return void
     */
    public function persistEntity(EntityManagerInterface $em, $stag): void
    {
        if (!$stag instanceof Stagiaire) return;
        //chiffrer le mot de passe avant son envoi
        //Bonjour1234+
        $stag->setPassword("$2y$13\$RYJPMquswPTWjML84hGdcuV7.V8mT6R.m/95MARlOCaA6y0iUbDfi");
        // $stag->setPassword("$2y$13\$zwa64OHleb.MuWUuCBi2yeT6ZHM28wyiSNacOIKhflcdO3OyMYwui");//Passw0rd
        //par défaut le stagiaire est actif
        $stag->setPremiereConnexion(true);
        $stag->setActif(true);
        //gestion des rôles : USER par défaut, Admin en plus si la case est cochée
        $roles = ["ROLE_USER"];
        if ($stag->isAdministrateur()) $roles[] = "ROLE_ADMIN";
        $stag->setRoles($roles);
        parent::persistEntity($em, $stag);
    }

    /**
     * Surcharge de la méthode de la classe AbstractCrudController appelée lors de la modification d'une entité
     *
     * @param EntityManagerInterface $entityManager
     * @param $stag
     * @return void
     */
    public function updateEntity(EntityManagerInterface $entityManager, $stag): void
    {
        if (!$stag instanceof Stagiaire) return;
        //mise à jour des rôles lors de l'UPDATE
        $stag->setRoles([]);
        $roles = ["ROLE_USER"];
        if ($stag->isAdministrateur()) $roles[] = "ROLE_ADMIN";
        $stag->setRoles($roles);
        parent::persistEntity($entityManager, $stag);
    }
}
