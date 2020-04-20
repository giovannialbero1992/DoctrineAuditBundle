<?php

namespace DH\DoctrineAuditBundle\Tests\Fixtures\IssueX;

use Doctrine\ORM\Mapping as ORM;
use DH\DoctrineAuditBundle\Annotation as Audit;
use Ramsey\Uuid\Uuid;

/**
 * @ORM\Entity
 * @ORM\Table(name="`fixture_comment`")
 *
 * @Audit\Auditable()
 */
class Comment
{
    /**
     * @ORM\Id
     * @ORM\Column(type="uuid", unique=true)
     */
    protected $id;

    /**
     * @ORM\Column(type="text")
     */
    protected $body;

    /**
     * Comment author email.
     *
     * @ORM\Column(type="string", length=255)
     */
    protected $author;

    /**
     * @ORM\ManyToOne(targetEntity="Post", inversedBy="comments")
     */
    protected $post;

    public function __construct()
    {
        $this->id = Uuid::uuid4();
    }


    /**
     * Get the value of id.
     *
     * @return null|string
     */
    public function getId(): ?string
    {
        return $this->id->toString();
    }

    /**
     * Set the value of body.
     *
     * @param string $body
     *
     * @return Comment
     */
    public function setBody(string $body): self
    {
        $this->body = $body;

        return $this;
    }

    /**
     * Get the value of body.
     *
     * @return string
     */
    public function getBody(): ?string
    {
        return $this->body;
    }

    /**
     * Set the value of author.
     *
     * @param string $author
     *
     * @return Comment
     */
    public function setAuthor(string $author): self
    {
        $this->author = $author;

        return $this;
    }

    /**
     * Get the value of author.
     *
     * @return string
     */
    public function getAuthor(): ?string
    {
        return $this->author;
    }

    /**
     * Set Post entity (many to one).
     *
     * @param ?Post $post
     *
     * @return Comment
     */
    public function setPost(?Post $post): self
    {
        $this->post = $post;

        return $this;
    }

    /**
     * Get Post entity (many to one).
     *
     * @return ?Post
     */
    public function getPost(): ?Post
    {
        return $this->post;
    }
}
