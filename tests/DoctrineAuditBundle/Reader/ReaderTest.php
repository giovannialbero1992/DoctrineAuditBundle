<?php

namespace DH\DoctrineAuditBundle\Tests\Reader;

use DateTime;
use DH\DoctrineAuditBundle\Configuration;
use DH\DoctrineAuditBundle\Model\Entry;
use DH\DoctrineAuditBundle\Reader\Reader;
use DH\DoctrineAuditBundle\Tests\CoreTest;
use DH\DoctrineAuditBundle\Tests\Fixtures\Core\Annotation\AuditedEntity;
use DH\DoctrineAuditBundle\Tests\Fixtures\Core\Annotation\UnauditedEntity;
use DH\DoctrineAuditBundle\Tests\Fixtures\Core\Standard\Author;
use DH\DoctrineAuditBundle\Tests\Fixtures\Core\Standard\Comment;
use DH\DoctrineAuditBundle\Tests\Fixtures\Core\Standard\Post;
use DH\DoctrineAuditBundle\Tests\Fixtures\Core\Standard\Tag;
use InvalidArgumentException;

/**
 * @internal
 */
final class ReaderTest extends CoreTest
{
    public function testGetAuditConfiguration(): void
    {
        $reader = $this->getReader();

        self::assertInstanceOf(Configuration::class, $reader->getConfiguration(), 'configuration instanceof AuditConfiguration::class');
    }

    public function testFilterIsEmptyByDefault(): void
    {
        $reader = $this->getReader();

        self::assertSame([], $reader->getFilters(), 'filters is empty by default.');
    }

    public function testFilterIsEmptyIfNotPartOfAllowedValues(): void
    {
        $reader = $this->getReader();

        $reader->filterBy('UNKNOWN');
        self::assertSame([], $reader->getFilters(), 'filters is empty when AuditReader::filterBy() parameter is not an allowed value.');

        $reader->filterBy(['UNKNOWN1', 'UNKNOWN2']);
        self::assertSame([], $reader->getFilters(), 'filters is empty when AuditReader::filterBy() parameter is not an allowed value.');
    }

    public function testFilterSingleValue(): void
    {
        $reader = $this->getReader();

        $reader->filterBy(Reader::ASSOCIATE);
        self::assertSame([Reader::ASSOCIATE], $reader->getFilters(), 'filter is not empty when AuditReader::filterBy() parameter is an allowed value.');

        $reader->filterBy(Reader::DISSOCIATE);
        self::assertSame([Reader::DISSOCIATE], $reader->getFilters(), 'filter is not empty when AuditReader::filterBy() parameter is an allowed value.');

        $reader->filterBy(Reader::INSERT);
        self::assertSame([Reader::INSERT], $reader->getFilters(), 'filter is not empty when AuditReader::filterBy() parameter is an allowed value.');

        $reader->filterBy(Reader::REMOVE);
        self::assertSame([Reader::REMOVE], $reader->getFilters(), 'filter is not empty when AuditReader::filterBy() parameter is an allowed value.');

        $reader->filterBy(Reader::UPDATE);
        self::assertSame([Reader::UPDATE], $reader->getFilters(), 'filter is not empty when AuditReader::filterBy() parameter is an allowed value.');
    }

    public function testFilterMultipleValues(): void
    {
        $reader = $this->getReader();

        $reader->filterBy([Reader::ASSOCIATE, Reader::DISSOCIATE]);
        self::assertSame([Reader::ASSOCIATE, Reader::DISSOCIATE], $reader->getFilters(), 'filter is not null when AuditReader::filterBy() parameter is composed of allowed value.');
    }

    public function testGetEntityTableName(): void
    {
        $entities = [
            Post::class => null,
            Comment::class => null,
        ];

        $configuration = $this->createAuditConfiguration([
            'entities' => $entities,
        ]);

        $reader = $this->getReader($configuration);

        self::assertSame('post', $reader->getEntityTableName(Post::class), 'tablename is ok.');
        self::assertSame('comment', $reader->getEntityTableName(Comment::class), 'tablename is ok.');
    }

    public function testGetEntityTableAuditName(): void
    {
        $entities = [
            Post::class => null,
            Comment::class => null,
        ];

        $configuration = $this->createAuditConfiguration([
            'entities' => $entities,
        ]);

        $reader = $this->getReader($configuration);

        self::assertSame('post_audit', $reader->getEntityAuditTableName(Post::class), 'tablename is ok.');
        self::assertSame('comment_audit', $reader->getEntityAuditTableName(Comment::class), 'tablename is ok.');
    }

    /**
     * @depends testGetEntityTableName
     */
    public function testGetEntities(): void
    {
        $entities = [
            AuditedEntity::class => null,
            UnauditedEntity::class => null,
            Post::class => null,
            Comment::class => null,
            \DH\DoctrineAuditBundle\Tests\Fixtures\IssueX\Comment::class => null,
            \DH\DoctrineAuditBundle\Tests\Fixtures\IssueX\Post::class => null,
            \DH\DoctrineAuditBundle\Tests\Fixtures\IssueX\Tag::class => null,
        ];

        $expected = array_combine(
            array_keys($entities),
            ['audited_entity', 'unaudited_entity', 'post', 'comment', 'fixture_comment', 'fixture_post', 'fixture_tag']
        );
        ksort($expected);

        $configuration = $this->createAuditConfiguration([
            'entities' => $entities,
        ]);

        $reader = $this->getReader($configuration);

        self::assertSame($expected, $reader->getEntities(), 'entities are sorted.');
    }

    public function testGetAudits(): void
    {
        $reader = $this->getReader($this->getAuditConfiguration());

        /** @var Entry[] $audits */
        $audits = $reader->getAudits(Author::class, null, 1, 50);

        $i = 0;
        self::assertCount(5, $audits, 'result count is ok.');
        self::assertSame(Reader::REMOVE, $audits[$i++]->getType(), 'entry'.$i.' is a remove operation.');
        self::assertSame(Reader::UPDATE, $audits[$i++]->getType(), 'entry'.$i.' is an update operation.');
        self::assertSame(Reader::INSERT, $audits[$i++]->getType(), 'entry'.$i.' is an insert operation.');
        self::assertSame(Reader::INSERT, $audits[$i++]->getType(), 'entry'.$i.' is an insert operation.');
        self::assertSame(Reader::INSERT, $audits[$i++]->getType(), 'entry'.$i.' is an insert operation.');

        /** @var Entry[] $audits */
        $audits = $reader->getAudits(Post::class, null, 1, 50);

        $i = 0;
        self::assertCount(15, $audits, 'result count is ok.');
        self::assertSame(Reader::UPDATE, $audits[$i++]->getType(), 'entry'.$i.' is an update operation.');
        self::assertSame(Reader::DISSOCIATE, $audits[$i++]->getType(), 'entry'.$i.' is a dissociate operation.');
        self::assertSame(Reader::DISSOCIATE, $audits[$i++]->getType(), 'entry'.$i.' is a dissociate operation.');
        self::assertSame(Reader::ASSOCIATE, $audits[$i++]->getType(), 'entry'.$i.' is an associate operation.');
        self::assertSame(Reader::ASSOCIATE, $audits[$i++]->getType(), 'entry'.$i.' is an associate operation.');
        self::assertSame(Reader::ASSOCIATE, $audits[$i++]->getType(), 'entry'.$i.' is an associate operation.');
        self::assertSame(Reader::ASSOCIATE, $audits[$i++]->getType(), 'entry'.$i.' is an associate operation.');
        self::assertSame(Reader::ASSOCIATE, $audits[$i++]->getType(), 'entry'.$i.' is an associate operation.');
        self::assertSame(Reader::ASSOCIATE, $audits[$i++]->getType(), 'entry'.$i.' is an associate operation.');
        self::assertSame(Reader::ASSOCIATE, $audits[$i++]->getType(), 'entry'.$i.' is an associate operation.');
        self::assertSame(Reader::ASSOCIATE, $audits[$i++]->getType(), 'entry'.$i.' is an associate operation.');
        self::assertSame(Reader::INSERT, $audits[$i++]->getType(), 'entry'.$i.' is an insert operation.');
        self::assertSame(Reader::INSERT, $audits[$i++]->getType(), 'entry'.$i.' is an insert operation.');
        self::assertSame(Reader::INSERT, $audits[$i++]->getType(), 'entry'.$i.' is an insert operation.');
        self::assertSame(Reader::INSERT, $audits[$i++]->getType(), 'entry'.$i.' is an insert operation.');

        /** @var Entry[] $audits */
        $audits = $reader->getAudits(Comment::class, null, 1, 50);

        $i = 0;
        self::assertCount(3, $audits, 'result count is ok.');
        self::assertSame(Reader::INSERT, $audits[$i++]->getType(), 'entry'.$i.' is an insert operation.');
        self::assertSame(Reader::INSERT, $audits[$i++]->getType(), 'entry'.$i.' is an insert operation.');
        self::assertSame(Reader::INSERT, $audits[$i++]->getType(), 'entry'.$i.' is an insert operation.');

        /** @var Entry[] $audits */
        $audits = $reader->getAudits(Tag::class, null, 1, 50);

        $i = 0;
        self::assertCount(15, $audits, 'result count is ok.');
        self::assertSame(Reader::DISSOCIATE, $audits[$i++]->getType(), 'entry'.$i.' is a dissociate operation.');
        self::assertSame(Reader::DISSOCIATE, $audits[$i++]->getType(), 'entry'.$i.' is a dissociate operation.');
        self::assertSame(Reader::ASSOCIATE, $audits[$i++]->getType(), 'entry'.$i.' is an associate operation.');
        self::assertSame(Reader::ASSOCIATE, $audits[$i++]->getType(), 'entry'.$i.' is an associate operation.');
        self::assertSame(Reader::ASSOCIATE, $audits[$i++]->getType(), 'entry'.$i.' is an associate operation.');
        self::assertSame(Reader::ASSOCIATE, $audits[$i++]->getType(), 'entry'.$i.' is an associate operation.');
        self::assertSame(Reader::ASSOCIATE, $audits[$i++]->getType(), 'entry'.$i.' is an associate operation.');
        self::assertSame(Reader::ASSOCIATE, $audits[$i++]->getType(), 'entry'.$i.' is an associate operation.');
        self::assertSame(Reader::ASSOCIATE, $audits[$i++]->getType(), 'entry'.$i.' is an associate operation.');
        self::assertSame(Reader::ASSOCIATE, $audits[$i++]->getType(), 'entry'.$i.' is an associate operation.');
        self::assertSame(Reader::INSERT, $audits[$i++]->getType(), 'entry'.$i.' is an insert operation.');
        self::assertSame(Reader::INSERT, $audits[$i++]->getType(), 'entry'.$i.' is an insert operation.');
        self::assertSame(Reader::INSERT, $audits[$i++]->getType(), 'entry'.$i.' is an insert operation.');
        self::assertSame(Reader::INSERT, $audits[$i++]->getType(), 'entry'.$i.' is an insert operation.');
        self::assertSame(Reader::INSERT, $audits[$i++]->getType(), 'entry'.$i.' is an insert operation.');

        $this->expectException(InvalidArgumentException::class);
        $reader->getAudits(Post::class, null, 0, 50);
        $reader->getAudits(Post::class, null, -1, 50);
    }

    public function testGetAuditsPager(): void
    {
        $reader = $this->getReader($this->getAuditConfiguration());

        /** @var Entry[] $audits */
        $pager = $reader->getAuditsPager(Author::class, null, 1, 3);

        self::assertIsArray($pager);
        self::assertTrue($pager['haveToPaginate'], 'pager has to paginate.');
    }

    public function testGetAuditsByDate(): void
    {
        $reader = $this->getReader($this->getAuditConfiguration());

        /** @var Entry[] $audits */
        $audits = $reader->getAuditsByDate(Author::class, null, new DateTime('-1 day'), null);

        $i = 0;
        self::assertCount(5, $audits, 'result count is ok.');
        self::assertSame(Reader::REMOVE, $audits[$i++]->getType(), 'entry'.$i.' is a remove operation.');
        self::assertSame(Reader::UPDATE, $audits[$i++]->getType(), 'entry'.$i.' is an update operation.');
        self::assertSame(Reader::INSERT, $audits[$i++]->getType(), 'entry'.$i.' is an insert operation.');
        self::assertSame(Reader::INSERT, $audits[$i++]->getType(), 'entry'.$i.' is an insert operation.');
        self::assertSame(Reader::INSERT, $audits[$i++]->getType(), 'entry'.$i.' is an insert operation.');

        /** @var Entry[] $audits */
        $audits = $reader->getAuditsByDate(Author::class, null, new DateTime('-5 days'), new DateTime('-4 days'));

        self::assertCount(0, $audits, 'result count is ok.');

        /** @var Entry[] $audits */
        $audits = $reader->getAuditsByDate(Author::class, null, new DateTime('-1 day'), null, 1, 2);

        self::assertCount(2, $audits, 'result count is ok.');

        $this->expectException(InvalidArgumentException::class);
        $reader->getAuditsByDate(Post::class, null, new DateTime('-1 day'), new DateTime('now'), 0, 50);
        $reader->getAuditsByDate(Post::class, null, new DateTime('-1 day'), new DateTime('now'), -1, 50);
        $reader->getAuditsByDate(Post::class, null, new DateTime('now'), new DateTime('-1 day'));
    }

    public function testGetAuditsCount(): void
    {
        $reader = $this->getReader($this->getAuditConfiguration());

        /** @var Entry[] $audits */
        $count = $reader->getAuditsCount(Author::class, null);

        self::assertSame(5, $count, 'count is ok.');
    }

    /**
     * @depends testGetAudits
     */
    public function testGetAuditsHonorsId(): void
    {
        $reader = $this->getReader($this->getAuditConfiguration());

        /** @var Entry[] $audits */
        $audits = $reader->getAudits(Author::class, 1, 1, 50);

        self::assertCount(2, $audits, 'result count is ok.');

        /** @var Entry[] $audits */
        $audits = $reader->getAudits(Post::class, 1, 1, 50);

        self::assertCount(3, $audits, 'result count is ok.');

        /** @var \DH\DoctrineAuditBundle\Model\Entry[] $audits */
        $audits = $reader->getAudits(Comment::class, 1, 1, 50);

        self::assertCount(1, $audits, 'result count is ok.');

        /** @var Entry[] $audits */
        $audits = $reader->getAudits(Post::class, 0, 1, 50);
        self::assertSame([], $audits, 'no result when id is invalid.');
    }

    /**
     * @depends testGetAudits
     */
    public function testGetAuditsHonorsPageSize(): void
    {
        $reader = $this->getReader($this->getAuditConfiguration());

        /** @var Entry[] $audits */
        $audits = $reader->getAudits(Author::class, null, 1, 2);

        self::assertCount(2, $audits, 'result count is ok.');

        /** @var Entry[] $audits */
        $audits = $reader->getAudits(Author::class, null, 2, 2);

        self::assertCount(2, $audits, 'result count is ok.');

        /** @var Entry[] $audits */
        $audits = $reader->getAudits(Author::class, null, 3, 2);

        self::assertCount(1, $audits, 'result count is ok.');

        $this->expectException(InvalidArgumentException::class);
        $reader->getAudits(Post::class, null, 1, 0);
        $reader->getAudits(Post::class, null, 1, -1);
    }

    /**
     * @depends testGetAudits
     */
    public function testGetAuditsHonorsFilter(): void
    {
        $reader = $this->getReader($this->getAuditConfiguration());

        /** @var Entry[] $audits */
        $audits = $reader->filterBy(Reader::UPDATE)->getAudits(Author::class, null, 1, 50);

        self::assertCount(1, $audits, 'result count is ok.');

        /** @var Entry[] $audits */
        $audits = $reader->filterBy(Reader::INSERT)->getAudits(Author::class, null, 1, 50);

        self::assertCount(3, $audits, 'result count is ok.');

        /** @var Entry[] $audits */
        $audits = $reader->filterBy(Reader::REMOVE)->getAudits(Author::class, null, 1, 50);

        self::assertCount(1, $audits, 'result count is ok.');

        /** @var Entry[] $audits */
        $audits = $reader->filterBy(Reader::ASSOCIATE)->getAudits(Author::class, null, 1, 50);

        self::assertCount(0, $audits, 'result count is ok.');

        /** @var Entry[] $audits */
        $audits = $reader->filterBy(Reader::DISSOCIATE)->getAudits(Author::class, null, 1, 50);

        self::assertCount(0, $audits, 'result count is ok.');
    }

    /**
     * @depends testGetAudits
     */
    public function testGetAudit(): void
    {
        $reader = $this->getReader($this->getAuditConfiguration());

        $audits = $reader->getAudit(Author::class, 1);

        self::assertCount(1, $audits, 'result count is ok.');
    }

    /**
     * @depends testGetAudits
     */
    public function testGetAuditHonorsFilter(): void
    {
        $reader = $this->getReader($this->getAuditConfiguration());

        $audits = $reader->filterBy(Reader::UPDATE)->getAudit(Author::class, 1);

        self::assertCount(0, $audits, 'result count is ok.');
    }

    public function testGetAuditByTransactionHash(): void
    {
        $em = $this->getEntityManager();
        $reader = $this->getReader($this->getAuditConfiguration());

        $author = new Author();
        $author
            ->setFullname('John Doe')
            ->setEmail('john.doe@gmail.com')
        ;
        $em->persist($author);

        $post1 = new Post();
        $post1
            ->setAuthor($author)
            ->setTitle('First post')
            ->setBody('Here is the body')
            ->setCreatedAt(new DateTime())
        ;

        $post2 = new Post();
        $post2
            ->setAuthor($author)
            ->setTitle('Second post')
            ->setBody('Here is another body')
            ->setCreatedAt(new DateTime())
        ;

        $em->persist($post1);
        $em->persist($post2);
        $em->flush();

        /** @var Entry[] $audits */
        $audits = $reader->getAudits(Post::class);
        $hash = $audits[0]->getTransactionHash();

        /** @var Entry[] $audits */
        $audits = $reader->getAudits(Post::class, null, null, null, $hash);

        self::assertCount(2, $audits, 'result count is ok.');
    }

    public function testGetAllAuditsByTransactionHash(): void
    {
        $em = $this->getEntityManager();
        $reader = $this->getReader($this->getAuditConfiguration());

        $author = new Author();
        $author
            ->setFullname('John Doe')
            ->setEmail('john.doe@gmail.com')
        ;
        $em->persist($author);

        $post1 = new Post();
        $post1
            ->setAuthor($author)
            ->setTitle('First post')
            ->setBody('Here is the body')
            ->setCreatedAt(new DateTime())
        ;

        $post2 = new Post();
        $post2
            ->setAuthor($author)
            ->setTitle('Second post')
            ->setBody('Here is another body')
            ->setCreatedAt(new DateTime())
        ;

        $em->persist($post1);
        $em->persist($post2);
        $em->flush();

        /** @var Entry[] $audits */
        $audits = $reader->getAudits(Post::class);
        $hash = $audits[0]->getTransactionHash();

        $em->remove($post2);
        $em->flush();

        $reader = $this->getReader($this->getAuditConfiguration());
        $audits = $reader->getAuditsByTransactionHash($hash);

        self::assertCount(2, $audits, 'AuditReader::getAllAuditsByTransactionHash() is ok.');
        self::assertCount(1, $audits[Author::class], 'AuditReader::getAllAuditsByTransactionHash() is ok.');
        self::assertCount(2, $audits[Post::class], 'AuditReader::getAllAuditsByTransactionHash() is ok.');
    }
}
