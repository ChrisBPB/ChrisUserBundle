<?php

namespace Chris\ChrisUserBundle\Entity;

use phpDocumentor\Reflection\Types\Boolean;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\EquatableInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Doctrine\ORM\Mapping as ORM;


abstract class User implements UserInterface, EquatableInterface
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="json")
     */
    protected $roles = [];

    /**
     * @ORM\Column(type="string", length=32)
     */
    protected $username;

    /**
     * @ORM\Column(type="string", length=45)
     */
    protected $ip;

    /**
     * @ORM\Column(type="string", length=255)
     */
    protected $email;

    /**
     * @ORM\Column(type="string", length=72)
     */
    protected $password;

    /**
     * @ORM\Column(type="string", length=48, nullable=true)
     */
    protected $passwordToken;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $passwordTokenExpire;

    /**
     * @ORM\Column(type="boolean")
     */
    protected $emailValidated = false;

    /**
     * @ORM\Column(type="boolean")
     */
    protected $agreeMarketing = false;

    /**
     * @ORM\Column(type="boolean")
     */
    protected $agreeTerms = false;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $createdAt = false;

    /**
     * @ORM\Column(type="string", length=32, nullable=true)
     */
    protected $emailValidationCode;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    public function getIp(): ?string
    {
        return $this->ip;
    }

    public function setip(string $ip): self
    {
        $this->ip = $ip;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
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

    public function getAgreeTerms(): ?bool
    {
        return $this->agreeTerms;
    }

    public function setAgreeTerms(bool $agreeTerms): self
    {
        $this->agreeTerms = $agreeTerms;

        return $this;
    }

    public function getAgreeMarketing(): ?bool
    {
        return $this->agreeMarketing;
    }

    public function setAgreeMarketing(bool $agreeMarketing): self
    {
        $this->agreeMarketing = $agreeMarketing;

        return $this;
    }

    public function setEmailValidationCode(String $emailValidationCode): self
    {
        $this->emailValidationCode = $emailValidationCode;

        return $this;
    }

    public function getEmailValidationCode(): ?string
    {
        return $this->emailValidationCode;
    }

    public function setEmailValidated(bool $emailValidated): self
    {
        $this->emailValidated = $emailValidated;

        return $this;
    }

    public function getEmailValidated(): ?bool
    {
        return $this->emailValidated;
    }


    /**
     * @ORM\PrePersist
     */
    public function onPre()
    {
        $this->createdAt = new \DateTime();
        $this->emailValidationCode = md5(uniqid());
        $this->setRoles(['ROLE_PENDING']);
    }

    public function performEmailChange(string $email)
    {
        $this->email = trim($email);
        $this->emailValidated = false;
        $this->emailValidationCode = md5(uniqid());
        $this->addRole(['ROLE_PENDING']);
    }

    /**
     * Returns the roles granted to the user.
     *
     *     public function getRoles()
     *     {
     *         return ['ROLE_USER'];
     *     }
     *
     * Alternatively, the roles might be stored on a ``roles`` property,
     * and populated in any number of different ways when the user object
     * is created.
     *
     * @return (Role|string)[] The user roles
     */
    public function getRoles()
    {

        $roles = $this->roles;
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    /**
     * Returns the salt that was originally used to encode the password.
     *
     * This can return null if the password was not encoded using a salt.
     *
     * @return string|null The salt
     */
    public function getSalt()
    {
        return null;
    }

    /**
     * Removes sensitive data from the user.
     *
     * This is important if, at any given point, sensitive information like
     * the plain-text password is stored on this object.
     */
    public function eraseCredentials()
    {
        // TODO: Implement eraseCredentials() method.
    }

    public function getPasswordToken(): ?string
    {
        return $this->passwordToken;
    }

    public function setPasswordToken(string $passwordToken): self
    {
        $this->passwordToken = $passwordToken;

        return $this;
    }

    public function getPasswordTokenExpire(): ?\DateTimeInterface
    {
        return $this->passwordTokenExpire;
    }

    public function setPasswordTokenExpire(\DateTimeInterface $passwordTokenExpire): self
    {
        $this->passwordTokenExpire = $passwordTokenExpire;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function hasRole(string $role): bool
    {
        if(in_array($role, $this->roles)){
            return true;
        } else {
            return false;
        }
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    public function addRole(string $role): self
    {
        $this->roles[] = $role;

        return $this;
    }

    public function removeRole(string $role): self
    {
        $roles = $this->roles;
        $del = array($role);
        $new = array_values(array_diff($roles,$del));
        $this->roles = $new;

        return $this;
    }


    /**
     * The equality comparison should neither be done by referential equality
     * nor by comparing identities (i.e. getId() === getId()).
     *
     * However, you do not need to compare every attribute, but only those that
     * are relevant for assessing whether re-authentication is required.
     *
     * @return bool
     */
    public function isEqualTo(UserInterface $user)
    {
        if ($user instanceof User) {
        // Check that the roles are the same, in any order
        $isEqual = count($this->getRoles()) == count($user->getRoles());
        if ($isEqual) {
            foreach($this->getRoles() as $role) {
                $isEqual = $isEqual && in_array($role, $user->getRoles());
            }
        }
        return $isEqual;
    }

        return false;
    }
}
