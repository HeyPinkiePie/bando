<?php

namespace App\Entity;

use App\Repository\StagiaireRepository;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\IntegerType;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Security\Core\Validator\Constraints as SecurityAssert;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\ComponentWithFormTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;

//comment
#[Vich\Uploadable]
#[ORM\Entity(repositoryClass: StagiaireRepository::class)]
class Stagiaire implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Assert\Type('int')]
    #[Assert\NotNull]
    private ?int $id = null;

    #[ORM\Column(length: 180, unique: true)]
    #[Assert\Email(
        message: 'Le format de l\'email n\'est pas valide',
    )]
    #[Assert\NotBlank]
    #[Assert\Length(
        min: 3,
        max: 255,
        minMessage: 'Votre email trop court, limite: {{ limit }}',
        maxMessage: 'Votre email trop long, limite: {{ limit }}',
    )]
    private ?string $email = null;

    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    #[Assert\Regex(['pattern'=>'/^(?=.{8,}$)(?=.*?[a-z])(?=.*?[A-Z])(?=.*?[0-9])(?=.*?\W).*$/'],match: true)]
    #[Assert\NotCompromisedPassword(message: 'Le mot de passe a été compromis.')]
    #[Assert\NotEqualTo(propertyPath: "nom",message: 'Votre mot de passe ne doit âs être identique à votre nom')]
    #[Assert\NotEqualTo(propertyPath: "prenom",message: 'Votre mot de passe ne doit âs être identique à votre prénom')]
    #[Assert\NotEqualTo(propertyPath: "email",message: 'Votre mot de passe ne doit âs être identique à votre email')]
    private ?string $password = null;


    #[ORM\Column(length: 150)]
    #[Assert\NotBlank]
    #[Assert\Type('string')]
    #[Assert\Length(
        min: 1,
        max: 150,
        minMessage: 'Votre nom est trop court, limite: {{ limit }}',
        maxMessage: 'Votre nom est trop long, limite: {{ limit }}',
    )]
    private ?string $nom = null;

    #[ORM\Column(length: 150)]
    #[Assert\NotBlank]
    #[Assert\Type('string')]
    #[Assert\Length(
        min: 1,
        max: 150,
        minMessage: 'Votre prénom est trop court, limite: {{ limit }}',
        maxMessage: 'Votre prénom est trop long, limite: {{ limit }}',
    )]
    private ?string $prenom = null;

    #[ORM\Column(length: 10, nullable: true)]
    #[Assert\NotBlank]
    #[Assert\Type('string')]
    #[Assert\Length(10)]
    private ?string $telephone = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $urlPhoto = null;

    #[ORM\Column]
    #[Assert\Type('boolean')]
    private ?bool $administrateur = null;

    #[ORM\Column]
    #[Assert\Type('boolean')]
    private ?bool $actif = null;

    #[ORM\Column]
    #[Assert\Type('boolean')]
    private ?bool $premiereConnexion = null;

    #[ORM\ManyToOne(inversedBy: 'stagiaires')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Campus $campus = null;

    #[ORM\OneToMany(mappedBy: 'organisateur', targetEntity: Sortie::class)]
    private Collection $organiseSorties;

    #[ORM\ManyToMany(targetEntity: Sortie::class, mappedBy: 'participants')]
    private Collection $participeSorties;


    #[ORM\Column(type: 'string', length: 255, nullable: 'true')]
    private $image;

    #[Vich\UploadableField(mapping: 'product_image', fileNameProperty: 'image')]
    private $imageFile;

    #[ORM\Column(nullable: true)]
    private ?DateTime $updatedAt = null;

    public function __construct()
    {
        $this->organiseSorties = new ArrayCollection();
        $this->participeSorties = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string)$this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password = null): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): self
    {
        $this->nom = $nom;

        return $this;
    }

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(string $prenom): self
    {
        $this->prenom = $prenom;

        return $this;
    }

    public function getTelephone(): ?string
    {
        return $this->telephone;
    }

    public function setTelephone(?string $telephone): self
    {
        $this->telephone = $telephone;

        return $this;
    }

    public function getUrlPhoto(): ?string
    {
        return $this->urlPhoto;
    }

    public function setUrlPhoto(?string $urlPhoto): self
    {
        $this->urlPhoto = $urlPhoto;

        return $this;
    }

    public function isAdministrateur(): ?bool
    {
        return $this->administrateur;
    }

    public function setAdministrateur(bool $administrateur): self
    {
        $this->administrateur = $administrateur;

        return $this;
    }

    public function isActif(): ?bool
    {
        return $this->actif;
    }

    public function setActif(bool $actif): self
    {
        $this->actif = $actif;

        return $this;
    }

    public function isPremiereConnexion(): ?bool
    {
        return $this->premiereConnexion;
    }

    public function setPremiereConnexion(bool $premiereConnexion): self
    {
        $this->premiereConnexion = $premiereConnexion;

        return $this;
    }

    public function getCampus(): ?campus
    {
        return $this->campus;
    }

    public function setCampus(?campus $campus): self
    {
        $this->campus = $campus;

        return $this;
    }

    /**
     * @return Collection<int, Sortie>
     */
    public function getOrganiseSorties(): Collection
    {
        return $this->organiseSorties;
    }

    public function addOrganiseSorty(Sortie $organiseSorty): self
    {
        if (!$this->organiseSorties->contains($organiseSorty)) {
            $this->organiseSorties->add($organiseSorty);
            $organiseSorty->setOrganisateur($this);
        }

        return $this;
    }

    public function removeOrganiseSorty(Sortie $organiseSorty): self
    {
        if ($this->organiseSorties->removeElement($organiseSorty)) {
            // set the owning side to null (unless already changed)
            if ($organiseSorty->getOrganisateur() === $this) {
                $organiseSorty->setOrganisateur(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Sortie>
     */
    public function getParticipeSorties(): Collection
    {
        return $this->participeSorties;
    }

    public function addParticipeSorty(Sortie $participeSorty): self
    {
        if (!$this->participeSorties->contains($participeSorty)) {
            $this->participeSorties->add($participeSorty);
            $participeSorty->addParticipant($this);
        }

        return $this;
    }

    public function removeParticipeSorty(Sortie $participeSorty): self
    {
        if ($this->participeSorties->removeElement($participeSorty)) {
            $participeSorty->removeParticipant($this);
        }

        return $this;
    }

    //Utilisation de du bundle vich pour upload les images

    public function getImageFile()
    {
        return $this->imageFile;
    }

    public function setImageFile(File $image = null)
    {
        $this->imageFile = $image;

        if ($image) {
            $this->updatedAt = new DateTime('now');
        }
    }

    public function getImage()
    {
        return $this->image;
    }

    public function setImage($image)
    {
        $this->image = $image;
    }

    public function getUpdatedAt(): ?DateTime
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?DateTime $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function __serialize(): array
    {
        return [
            'id' => $this->id,
            'email' => $this->email,
            'roles' => $this->roles,
            'nom' => $this->nom,
            'prenom' => $this->prenom,
            'telephone' => $this->telephone,
            'url_photo' => $this->urlPhoto,
            'administrateur' => $this->administrateur,
            'actif' => $this->actif,
            'premiere_connexion' => $this->premiereConnexion,
            'image' => $this->image,
            'update_at' => $this->updatedAt,
            'imageFile' => base64_encode($this->imageFile),
            'password' => $this->password,
        ];
    }

    public function __unserialize(array $serialized)
    {
        $this->id = $serialized['id'];
        $this->email = $serialized['email'];
        $this->roles = $serialized['roles'];
        $this->nom = $serialized['nom'];
        $this->telephone = $serialized['telephone'];
        $this->urlPhoto = $serialized['url_photo'];
        $this->administrateur = $serialized['administrateur'];
        $this->premiereConnexion = $serialized['premiere_connexion'];
        $this->image = $serialized['image'];
        $this->updatedAt = $serialized['update_at'];
        $this->imageFile = base64_decode($serialized['imageFile']);
        $this->password = $serialized['password'];
        return $this;
    }

}
