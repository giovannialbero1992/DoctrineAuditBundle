<?php

declare(strict_types=1);

namespace DH\DoctrineAuditBundle\Tests\Fixtures\IssueX;

use DH\DoctrineAuditBundle\Annotation as Audit;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;

/**
 * @ORM\Entity
 * @ORM\Table(name="fixture_tag")
 * @Audit\Auditable()
 */
class Tag
{
    /**
     * @ORM\Id
     * @ORM\Column(type="uuid", unique=true)
     */
    protected $id;

    /**
     * @var Post
     *
     * @ORM\ManyToOne(targetEntity="Post")
     */
    protected $post;

    public function __construct()
    {
        $this->id = Uuid::uuid4()->toString();
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id->toString();
    }

    /**
     * @return Post
     */
    public function getPost(): Post
    {
        return $this->post;
    }

    /**
     * @param Post $post
     */
    public function setPost(Post $post): void
    {
        $this->post = $post;
    }
}