<?php

declare(strict_types=1);

namespace DH\DoctrineAuditBundle\Tests\Fixtures\IssueX;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class DependentDataFixture extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $post = new Post();
        $post->setTitle('I\'m a title');
        $post->setBody('I\'m a post\'s body');

        $manager->persist($post);

        $this->addReference('post_1', $post);

        $manager->flush();
    }
}
