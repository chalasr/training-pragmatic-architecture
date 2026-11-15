<?php

declare(strict_types=1);

namespace App\Tests\Subscription\Acceptance;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use App\Shared\Domain\Event\Integration\BookDiscounted;
use App\Subscription\EventListener\AnnounceDiscountToSubscribersListener;
use App\Tests\Subscription\SubscriptionFactory;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Monolog\Handler\TestHandler;
use PHPUnit\Framework\Attributes\Group;

#[Group('tp')]
final class AnnounceDiscountToSubscribersTest extends ApiTestCase
{
    private static Connection $connection;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        self::$connection = self::getContainer()->get(Connection::class);
    }

    protected function setUp(): void
    {
        self::$connection->executeStatement('TRUNCATE subscription');
    }

    public function testAnnounceADiscountToEverySubscriber(): void
    {
        $this->subscribe('reader@example.com', 'another@example.com');

        /** @var AnnounceDiscountToSubscribersListener $listener */
        $listener = self::getContainer()->get(AnnounceDiscountToSubscribersListener::class);
        $listener(new BookDiscounted('a-book', 'Dune', 1500, 1200));

        self::assertTrue($this->subscriptionLog()->hasInfoThatContains('Announcing "Dune" now at 1200 instead of 1500 to reader@example.com.'));
        self::assertTrue($this->subscriptionLog()->hasInfoThatContains('Announcing "Dune" now at 1200 instead of 1500 to another@example.com.'));
    }

    private function subscribe(string ...$emails): void
    {
        /** @var EntityManagerInterface $em */
        $em = self::getContainer()->get(EntityManagerInterface::class);

        foreach ($emails as $email) {
            $em->persist(SubscriptionFactory::create($email));
        }

        $em->flush();
    }

    private function subscriptionLog(): TestHandler
    {
        /** @var TestHandler $testHandler */
        $testHandler = self::getContainer()->get('monolog.handler.subscription');

        return $testHandler;
    }
}
