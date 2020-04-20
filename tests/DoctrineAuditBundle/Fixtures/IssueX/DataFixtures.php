<?php

declare(strict_types=1);

namespace DH\DoctrineAuditBundle\Tests\Fixtures\IssueX;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class DataFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $tag = new Tag();
        $tag->setPost($this->getReference('post_1'));

        $manager->persist($tag);
        $this->addReference('tag', $tag);
        $manager->flush();
        $manager->clear();

        $comment1 = new Comment();
        $comment1->setBody('Comment One');
        $comment1->setPost($this->getReference('post_1'));
        $comment1->setAuthor('John Doe');

        $manager->persist($comment1);
        $this->addReference('comment_1', $comment1);

        $comment2 = new Comment();
        $comment2->setBody('Comment Two');
        $comment2->setPost($this->getReference('post_1'));
        $comment2->setAuthor('Charlie Brown');

        $manager->persist($comment2);
        $this->addReference('comment_2', $comment2);

        $manager->flush();
    }

    public function getDependencies()
    {
        return [DependentDataFixture::class];
    }
}
