<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\BooleanFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\RangeFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Core\Serializer\Filter\PropertyFilter;
use App\Doctrine\CheeseListingSetOwnerListener;
use App\Repository\CheeseListingRepository;
use Carbon\Carbon;
use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\EntityListeners;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\SerializedName;
use Symfony\Component\Validator\Constraints\NotBlank;
use App\Validator\IsValidOwner;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource(
    collectionOperations: [
        "get",
        // "post"
        "post" => ["security" => "is_granted('ROLE_USER')"]
    ],
    itemOperations: [
        "get" => [
            // 'path' => "/qaz/{id}/"
            "normalization_context" => ["groups" => ["cheese:read", "cheese:item:get"]],
        ],
        "put" => [
            "security" => "is_granted('EDIT',object)",
            "security_message" => "Sorry, but you are not the book owner.",
        ],
        "delete" => ["security" => "is_granted('ROLE_ADMIN')"]
    ],
    shortName: "cheese",

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
#[ORM\EntityListeners(["App\Doctrine\CheeseListingSetOwnerListener"])]
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
    #[Groups(['cheese:read', 'cheese:write', 'user:read', 'user:write'])]
    private $title;

    /**
     * @ORM\Column(type="text")
     * @Assert\NotBlank()
     */
    #[Groups(['cheese:read', 'user:write'])]
    private $description;

    /**
     * @ORM\Column(type="integer")
     * @Assert\NotBlank()
     */
    #[Groups(['cheese:read', 'cheese:write', 'user:read', 'user:write'])]
    private $price = 0;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\Column(type="boolean")
     * 
     */
    #[Groups(['cheese:read'])]
    private $isPublished = false;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="cheeseListings")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"cheese:read", "cheese:collection:post"})
     * @IsValidOwner()
     
     */
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
     * @Groups("cheese:read")
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

    #[Groups(['cheese:read'])]
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
     * @Groups("cheese:write")
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
