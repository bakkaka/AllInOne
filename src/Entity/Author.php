<?php

namespace App\Entity;



use DateTime;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\ExecutionContextInterface;


/**
 * @ORM\Entity(repositoryClass="App\Repository\AuthorRepository")
 */
class Author
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;
	
	/**
     * @ORM\Column(type="string", length=255)
     */
    private $pseudo;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $fullName;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $updatedAt;

    /**
     * @ORM\Column(type="text")
     */
    private $bio;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $phone;
	
	/**
     * @ORM\OneToOne(targetEntity="App\Entity\Image", cascade={"persist", "remove"})y
	 
     * @ORM\JoinColumn(nullable=true)
    /* @Assert\Valid()
     */
    private $image;
	
	/**
     * @ORM\OneToOne(targetEntity="App\Entity\User", inversedBy="author")
     *
     */
    private $user;
	
	/**
     * @ORM\OneToMany(targetEntity="App\Entity\Article", mappedBy="author")
     * 
     */
    private $articles;
	
	/**
     * @ORM\OneToMany(targetEntity="App\Entity\Comment", mappedBy="author")
     * 
     */
    private $comments;


    public function __construct()
    {
	    $this->date = new DateTime();
        $this->createdAt = new DateTime();
        $this->updatedAt = new DateTime();

        $this->articles = new ArrayCollection();
        $this->comments = new ArrayCollection();
        
		
    }


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFullName(): ?string
    {
        return $this->fullName;
    }

    public function setFullName(string $fullName): self
    {
        $this->fullName = $fullName;

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

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getBio(): ?string
    {
        return $this->bio;
    }

    public function setBio(string $bio): self
    {
        $this->bio = $bio;

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): self
    {
        $this->phone = $phone;

        return $this;
    }

    public function getImage(): ?Image
    {
        return $this->image;
    }

    public function setImage(?Image $image): self
    {
        $this->image = $image;

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

    public function getArticles(): ?self
    {
        return $this->articles;
    }

    public function setArticles(?self $articles): self
    {
        $this->articles = $articles;

        return $this;
    }

    public function addArticle(Article $article): self
    {
        if (!$this->articles->contains($article)) {
            $this->articles[] = $article;
            $article->setAuthor($this);
        }

        return $this;
    }

    public function removeArticle(Article $article): self
    {
        if ($this->articles->contains($article)) {
            $this->articles->removeElement($article);
            // set the owning side to null (unless already changed)
            if ($article->getAuthor() === $this) {
                $article->setAuthor(null);
            }
        }

        return $this;
    }
	 public function __toString()
    {
        return $this->getFullName();
		return $this;

        
    }

  public function getPseudo(): ?string
  {
      return $this->pseudo;
  }

  public function setPseudo(string $pseudo): self
  {
      $this->pseudo = $pseudo;

      return $this;
  }

  /**
   * @return Collection|Comment[]
   */
  public function getComments(): Collection
  {
      return $this->comments;
  }

  public function addComment(Comment $comment): self
  {
      if (!$this->comments->contains($comment)) {
          $this->comments[] = $comment;
          $comment->setAuthor($this);
      }

      return $this;
  }

  public function removeComment(Comment $comment): self
  {
      if ($this->comments->contains($comment)) {
          $this->comments->removeElement($comment);
          // set the owning side to null (unless already changed)
          if ($comment->getAuthor() === $this) {
              $comment->setAuthor(null);
          }
      }

      return $this;
  }

  
}
