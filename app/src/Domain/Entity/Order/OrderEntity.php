<?php

namespace App\Domain\Entity\Order;

use App\Domain\Entity\Client\ClientEntity;
use App\Infrastructure\Persistence\Doctrine\Order\OrderEntityRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: OrderEntityRepository::class)]
#[ORM\Table(name: '`order`')]
class OrderEntity
{
    const ORDER_STATUS_NEW = 'new';
    const ORDER_STATUS_PENDING = 'pending';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?\DateTime $createdAt = null;

    #[ORM\ManyToOne(inversedBy: 'orders')]
    #[ORM\JoinColumn(nullable: false)]
    private ?ClientEntity $createdBy = null;

    #[ORM\Column]
    private array $orderContent = [];

    #[ORM\Column(length: 255)]
    private ?string $status = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCreatedAt(): ?\DateTime
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTime $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getCreatedBy(): ?ClientEntity
    {
        return $this->createdBy;
    }

    public function setCreatedBy(?ClientEntity $createBy): static
    {
        $this->createdBy = $createBy;

        return $this;
    }

    public function getOrderContent(): array
    {
        return $this->orderContent;
    }

    public function setOrderContent(array $orderContent): static
    {
        $this->orderContent = $orderContent;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;

        return $this;
    }
}
