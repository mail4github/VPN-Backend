<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Scheb\TwoFactorBundle\Model\Google\TwoFactorInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
#[ORM\HasLifecycleCallbacks]
#[UniqueEntity('login')]
#[UniqueEntity('email')]
class User implements UserInterface, PasswordAuthenticatedUserInterface, TwoFactorInterface, \JsonSerializable
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(unique: true)]
    #[Assert\Length(min: 6, max: 16)]
    private ?string $login = null;

    #[ORM\Column(length: 180, nullable: true)]
    private ?string $email = null;

    #[ORM\Column(options: ['default' => 0])]
    private ?bool $isEmailVerified = null;

    #[ORM\Column(options: ['default' => 0])]
    private bool $isTwoFactorAuthEnabled = false;

    /**
     * @var string[]
     */
    #[ORM\Column]
    private array $roles = [];

    /**
     * @var ?string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    #[Assert\Length(min: 8)]
    private ?string $plainPassword = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $googleAuthenticatorSecret = null;

    /**
     * @var string|null
     *
     * @ORM\Column(name="picture", type="text", nullable=true, options={"comment"="User picture in BASE64 format like: `data:image/png;base64, iVBORw0KG...`"})
     */
    #[ORM\Column(nullable: true)]
    private ?string $picture = null;

    /**
     * @var Collection<int, RecoveryCode>
     */
    #[ORM\OneToMany(mappedBy: 'owner', targetEntity: RecoveryCode::class, cascade: ['persist'], orphanRemoval: true)]
    private Collection $recoveryCodes;

    /**
     * @var Collection<int, EmailVerificationCode>
     */
    #[ORM\OneToMany(mappedBy: 'owner', targetEntity: EmailVerificationCode::class, cascade: ['persist'])]
    private Collection $emailVerificationCodes;

    /**
     * @var Collection<int, ResetPasswordCode>
     */
    #[ORM\OneToMany(mappedBy: 'owner', targetEntity: ResetPasswordCode::class, cascade: ['persist'])]
    private Collection $resetPasswordCodes;

    /**
     * @var int|7
     *
     * @ORM\Column(name="deactivate_sessions_after_days", type="integer", nullable=false, default=7, options={"comment"="Make user sessions not active after that number of days"})
     */
    private ?int $deactivateSessionsAfterDays = 7;

    public function __construct()
    {
        $this->recoveryCodes = new ArrayCollection();
        $this->emailVerificationCodes = new ArrayCollection();
        $this->resetPasswordCodes = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLogin(): ?string
    {
        return $this->login;
    }

    public function setLogin(string $login): self
    {
        $this->login = $login;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getIsEmailVerified(): ?bool
    {
        return $this->isEmailVerified;
    }

    public function setIsEmailVerified(bool $isVerified = true): self
    {
        $this->isEmailVerified = $isVerified;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->login;
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

    /**
     * @param string[] $roles
     */
    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getPlainPassword(): ?string
    {
        return $this->plainPassword;
    }

    public function setPlainPassword(string $plainPassword): self
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

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt ?? new \DateTimeImmutable();
    }

    #[ORM\PrePersist]
    public function setCreatedAt(): self
    {
        $this->createdAt = $this->updatedAt = new \DateTimeImmutable();

        return $this;
    }

    public function getUpdatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt ?? new \DateTimeImmutable();
    }

    #[ORM\PreUpdate()]
    public function setUpdatedAt(): self
    {
        $this->updatedAt = new \DateTimeImmutable();

        return $this;
    }

    public function getIsTwoFactorAuthEnabled(): bool
    {
        return $this->isTwoFactorAuthEnabled;
    }

    public function setIsTwoFactorAuthEnabled(bool $state): self
    {
        $this->isTwoFactorAuthEnabled = $state;

        return $this;
    }

    public function isGoogleAuthenticatorEnabled(): bool
    {
        return $this->getIsTwoFactorAuthEnabled();
    }

    public function getGoogleAuthenticatorUsername(): string
    {
        if (null === $this->login) {
            throw new \Exception('User does not have requested attribute');
        }

        return $this->login;
    }

    public function getGoogleAuthenticatorSecret(): ?string
    {
        return $this->googleAuthenticatorSecret;
    }

    public function setGoogleAuthenticatorSecret(?string $googleAuthenticatorSecret): self
    {
        $this->googleAuthenticatorSecret = $googleAuthenticatorSecret;

        return $this;
    }

    public function jsonSerialize(): mixed
    {
        return [
            'id' => $this->id,
            'username' => $this->login,
            'email' => $this->email,
            'email_is_verified' => $this->isEmailVerified,
            'roles' => $this->getRoles(),
            '2fa_enabled' => $this->isGoogleAuthenticatorEnabled(),
            '2fa_secret' => $this->googleAuthenticatorSecret,
            '2fa_recovery_codes' => $this->recoveryCodes,
            'created_at' => $this->getCreatedAt()->format(\DateTimeInterface::RFC3339),
            'updated_at' => $this->getUpdatedAt()->format(\DateTimeInterface::RFC3339),
            'picture' => $this->picture,
        ];
    }

    /**
     * @return Collection<int, RecoveryCode>
     */
    public function getRecoveryCodes(): Collection
    {
        return $this->recoveryCodes;
    }

    public function addRecoveryCode(RecoveryCode $recoveryCode): self
    {
        if (!$this->recoveryCodes->contains($recoveryCode)) {
            $this->recoveryCodes->add($recoveryCode);
            $recoveryCode->setOwner($this);
        }

        return $this;
    }

    public function removeRecoveryCode(RecoveryCode $recoveryCode): self
    {
        if ($this->recoveryCodes->removeElement($recoveryCode)) {
            // set the owning side to null (unless already changed)
            if ($recoveryCode->getOwner() === $this) {
                $recoveryCode->setOwner(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, EmailVerificationCode>
     */
    public function getEmailVerificationCodes(): Collection
    {
        return $this->emailVerificationCodes;
    }

    public function addEmailVerificationCode(EmailVerificationCode $emailVerificationCode): self
    {
        if (!$this->emailVerificationCodes->contains($emailVerificationCode)) {
            $this->emailVerificationCodes->add($emailVerificationCode);
            $emailVerificationCode->setOwner($this);
        }

        return $this;
    }

    public function removeEmailVerificationCode(EmailVerificationCode $emailVerificationCode): self
    {
        if ($this->emailVerificationCodes->removeElement($emailVerificationCode)) {
            // set the owning side to null (unless already changed)
            if ($emailVerificationCode->getOwner() === $this) {
                $emailVerificationCode->setOwner(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, ResetPasswordCode>
     */
    public function getResetPasswordCodes(): Collection
    {
        return $this->resetPasswordCodes;
    }

    public function addResetPasswordCode(ResetPasswordCode $resetPasswordCode): self
    {
        if (!$this->resetPasswordCodes->contains($resetPasswordCode)) {
            $this->resetPasswordCodes->add($resetPasswordCode);
            $resetPasswordCode->setOwner($this);
        }

        return $this;
    }

    public function removeResetPasswordCode(ResetPasswordCode $resetPasswordCode): self
    {
        if ($this->resetPasswordCodes->removeElement($resetPasswordCode)) {
            // set the owning side to null (unless already changed)
            if ($resetPasswordCode->getOwner() === $this) {
                $resetPasswordCode->setOwner(null);
            }
        }

        return $this;
    }

    public function hasTwoFactorConfiguration(): bool
    {
        $hasTotpSecret = null !== $this->googleAuthenticatorSecret;
        $hasRecoveryCodes = 0 !== $this->getRecoveryCodes()->count();

        return $hasTotpSecret && $hasRecoveryCodes;
    }

    public function getPicture(): ?string
    {
        return $this->picture;
    }

    public function setPicture(?string $picture): static
    {
        $this->picture = $picture;

        return $this;
    }

    public function getDeactivateSessionsAfterDays(): ?int
    {
        return $this->deactivateSessionsAfterDays;
    }

    public function setDeactivateSessionsAfterDays(?int $deactivateSessionsAfterDays): static
    {
        $this->deactivateSessionsAfterDays = $deactivateSessionsAfterDays;

        return $this;
    }
}
