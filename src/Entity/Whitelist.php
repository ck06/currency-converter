<?php

namespace App\Entity;

use App\Repository\WhitelistRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\UniqueConstraint(columns: ['user_id', 'address'])]
#[ORM\Entity(repositoryClass: WhitelistRepository::class)]
class Whitelist
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'whitelist')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $User = null;

    /**
     * A length of 15 fits the whole range of 0.0.0.0 ~ 255.255.255.255
     * Subnet masks are automatically determined by absence of data;
     * "192"         => "192/8"
     * "192.168"     => "192.168/16"
     * "192.168.0"   => "192.168.0/24"
     * "192.168.0.1" => "192.168.0.1" (no change)
     *
     * More specific masks are not supported.
     */
    #[ORM\Column(length: 15)]
    private ?string $address = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->User;
    }

    public function setUser(?User $User): static
    {
        $this->User = $User;

        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(string $address): static
    {
        $this->address = $address;

        return $this;
    }
}
