<?php
namespace App\Entity;

use DateTime;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
//use Gedmo\Timestampable\Traits\TimestampableEntity;


/**
 * @ORM\Entity(repositoryClass="App\Repository\CommentRepository")
 */
class Comment
{

    //use TimestampableEntity;


    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;
    /**
     * @ORM\Column(type="string", length=255)
     */
    private $authorName;

    /**
     * @ORM\Column(type="text")
     */
    private $content;

    /**
     * @var Datetime
     * @Assert\Date
     * @ORM\Column(name="updated_at", type="datetime", nullable=true)
     */
    private $updatedAt;

    /**
     * @var Datetime
     * @Assert\Date
     * @ORM\Column(name="created_at", type="datetime", nullable=true)
     */
    private $createdAt;


    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="comments", cascade={"persist","remove"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;



    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Article", inversedBy="comments", cascade={"persist"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $article;

    /**
     * Comment constructor.
     * @throws \Exception
     */
    public function __construct()
    {
        // $this->roles = array('ROLE_MEDECIN');
        $this->date = new \DateTime("now");
        $this->setCreatedAt = new DateTime("now");
        $this->setUpdatedAt = new DateTime("now");

    }


    public function getId()
    {
        return $this->id;
    }
    public function getAuthorName(): ?string
    {
        return $this->authorName;
    }
    public function setAuthorName(string $authorName): self
    {
        $this->authorName = $authorName;
        return $this;
    }
    public function getContent(): ?string
    {
        return $this->content;
    }
    public function setContent(string $content): self
    {
        $this->content = $content;
        return $this;
    }


    public function getArticle(): ?Article
    {
        return $this->article;
    }

    public function setArticle(?Article $article): self
    {
        $this->article = $article;

        return $this;
    }

    public function getUpdatedAt(): ?DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = new DateTime("now");

        return $this;
    }

    public function getCreatedAt(): ?DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?\DateTimeInterface $createdAt): self
    {
        $this->createdAt = new DateTime("now");

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }
}