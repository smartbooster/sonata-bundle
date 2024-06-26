<?php

namespace Smart\SonataBundle\Entity\User;

use DateTime;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Basic methods to implements Symfony\Component\Security\Core\User\UserInterface and
 * Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface
 *
 * @author Nicolas Bastien <nicolas.bastien@smartbooster.io>
 */
trait UserTrait
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    protected ?int $id = null;

    /**
     * @ORM\Column(length=255, unique=true, nullable=false)
     * @Assert\NotBlank
     * @Assert\Email
     */
    #[ORM\Column(length: 255, unique: true, nullable: false)]
    #[Assert\NotBlank]
    #[Assert\Email]
    private ?string $email = null;

    /**
     * @ORM\Column(name="password", length=100, nullable=false)
     */
    #[ORM\Column(name: "password", length: 100, nullable: false)]
    private ?string $password = null;

    private ?string $plainPassword = null;

    /**
     * @ORM\Column(length=255, nullable=true)
     */
    #[ORM\Column(length: 255, nullable: true)]
    protected ?string $firstName = null;

    /**
     * @ORM\Column(length=255, nullable=true)
     */
    #[ORM\Column(length: 255, nullable: true)]
    protected ?string $lastName = null;

    /**
     * @ORM\Column(type=Types::DATETIME_MUTABLE, nullable=true)
     */
    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    protected ?DateTime $lastLogin = null;

    public function __toString()
    {
        return $this->getListDisplay();
    }

    /**
     * On list we want to be able to sort by Lastname
     * @return string
     */
    public function getListDisplay()
    {
        if (strlen(trim($this->getLastName())) > 0) {
            return $this->getListFullName();
        }

        return (string) $this->getEmail();
    }

    /**
     * @return int
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return  string
     * @inheritdoc
     */
    public function getUsername()
    {
        return $this->email;
    }

    /**
     * @inheritdoc
     */
    public function getEmail(): ?string
    {
        return $this->email;
    }

    /**
     * @param string $email
     *
     * @return $this
     */
    public function setEmail($email)
    {
        $this->email = strtolower($email);

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    /**
     * @param string $password
     *
     * @return $this
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * The public representation of the user (e.g. a username, an email address, etc.)
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * Returning a salt is only needed if you are not using a modern
     * hashing algorithm (e.g. bcrypt or sodium) in your security.yaml.
     *
     * @see UserInterface
     */
    public function getSalt(): ?string
    {
        return null;
    }

    /**
     * @return string
     */
    public function getPlainPassword()
    {
        return $this->plainPassword;
    }

    /**
     * @param string $plainPassword
     *
     * @return $this
     */
    public function setPlainPassword($plainPassword)
    {
        $this->plainPassword = $plainPassword;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        $this->plainPassword = null;
    }

    /**
     * @return string
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * @param string|null $firstName
     *
     * @return $this
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;

        return $this;
    }

    /**
     * @return string
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * @param string|null $lastName
     *
     * @return $this
     */
    public function setLastName($lastName)
    {
        $this->lastName = $lastName;

        return $this;
    }

    /**
     * @return string
     */
    public function getFullName()
    {
        return sprintf('%s %s', (string)$this->getFirstName(), (string)$this->getLastName());
    }

    /**
     * @return string
     */
    public function getListFullName()
    {
        return sprintf('%s %s', (string)$this->getLastName(), (string)$this->getFirstName());
    }

    /**
     * @return string
     */
    public function getFullNameAndEmail()
    {
        if (strlen(trim($this->getFullName())) > 0) {
            return sprintf('%s - %s', $this->getFullName(), $this->getEmail());
        }

        return $this->getEmail();
    }

    public function __serialize(): array
    {
        return [
            'id' => $this->id,
            'email' => $this->email,
            'pass' => $this->password,
        ];
    }

    public function __unserialize(array $data): void
    {
        $this->id = $data['id'];
        $this->email = $data['email'];
        $this->password = $data['pass'];
    }

    /**
     * @return null|DateTime
     */
    public function getLastLogin()
    {
        return $this->lastLogin;
    }

    /**
     * @param null|DateTime $lastLogin
     *
     * @return $this
     */
    public function setLastLogin($lastLogin = null)
    {
        $this->lastLogin = $lastLogin;

        return $this;
    }
}
