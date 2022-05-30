<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\BooleanFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\RangeFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Core\Serializer\Filter\PropertyFilter;
use App\Repository\CheeseListingRepository;
use Carbon\Carbon;
use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\SerializedName;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Constraints\Valid;

#[ApiResource(
    collectionOperations: ["get", "post"],
    itemOperations: [
        "get" => [
            // 'path' => "/qaz/{id}/"
            "normalization_context" => ["groups" => ["cheese_listing:read", "cheese_listing:item:get"]],
        ], "put"
    ],
    shortName: "cheeses",
    normalizationContext: ["groups" => ["cheese_listing:read"], "swagger_definition_name" => "Read"],
    denormalizationContext: ["groups" => ["cheese_listing:write"], "swagger_definition_name" => "Write"],
    attributes: [
        "pagination_items_per_page" => 10,
        "formats" => ["jsonld", "json", "html", "jsonhal", "csv" => ["text/csv"]]
    ]

)]
#[ApiFilter(BooleanFilter::class, properties: ["isPublished"])]
#[ApiFilter(RangeFilter::class, properties: ['price'])]
#[ApiFilter(
    PropertyFilter::class
)]
/**
 * @ORM\Entity(repositoryClass=CheeseListingRepository::class)
 * @ApiFilter(SearchFilter::class, properties={
 * "title": "partial", 
 * "description": "partial",  
 * "owner": "exact",
 * "owner.username": "partial"
 * })
 */
class CheeseListing
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank()
     * @Assert\Length(
     *     min=2,
     *     max=50,
     *     maxMessage="Describe your cheese in 50 chars or less"
     * )
     */
    #[Groups(['cheese_listing:read', 'cheese_listing:write', 'user:read', 'user:write'])]
    private $title;

    /**
     * @ORM\Column(type="text")
     * @Assert\NotBlank()
     */
    #[Groups(['cheese_listing:read', 'user:write'])]
    private $description;

    /**
     * @ORM\Column(type="integer")
     * @Assert\NotBlank()
     */
    #[Groups(['cheese_listing:read', 'cheese_listing:write', 'user:read', 'user:write'])]
    private $price = 0;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\Column(type="boolean")
     * 
     */
    #[Groups(['cheese_listing:read'])]
    private $isPublished = false;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="cheeseListings")
     * @ORM\JoinColumn(nullable=false)
     */
    #[Groups(['cheese_listing:read', 'cheese_listing:write', 'user:write'])]
    #[Valid()]
    private $owner;


    public function __construct(string $title)
    {
        $this->createdAt = new DateTime();
        $this->title = $title;
    }


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }


    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @Groups("cheese_listing:read")
     */
    public function getShortDescription(): ?string
    {

        if (strlen($this->description) < 40) {
            return $this->description;
        }
        return substr($this->description, 0, 40) . '...';
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getPrice(): ?int
    {
        return $this->price;
    }

    public function setPrice(int $price): self
    {
        $this->price = $price;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    #[Groups(['cheese_listing:read'])]
    public function getCreatedAtAgo(): string
    {
        return Carbon::instance($this->getCreatedAt())->diffForHumans();
    }

    public function getIsPublished(): ?bool
    {
        return $this->isPublished;
    }

    public function setIsPublished(bool $isPublished): self
    {
        $this->isPublished = $isPublished;

        return $this;
    }

    /**
     * The description of the cheese as raw text.
     *
     * @Groups("cheese_listing:write")
     */
    #[SerializedName('description')]
    public function setTextDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getOwner(): ?User
    {
        return $this->owner;
    }

    public function setOwner(?User $owner): self
    {
        $this->owner = $owner;

        return $this;
    }
}
