<?php

declare(strict_types=1);

namespace App\Tests\Subscription\Acceptance;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use App\BookStore\Domain\Repository\BookRepositoryInterface;
use App\Shared\Domain\Event\Integration\BookDiscounted;
use App\Subscription\EventListener\AnnounceDiscountToSubscribersListener;
use App\Tests\BookStore\Factory\BookFactory;
use App\Tests\Subscription\SubscriptionFactory;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Monolog\Handler\TestHandler;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Messenger\Transport\InMemory\InMemoryTransport;

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

    public function testTheDiscountAnnouncementWaitsForTheWorker(): void
    {
        $client = self::createClient();
        $client->disableReboot();

        $this->subscribe('reader@example.com');

        /** @var BookRepositoryInterface $bookRepository */
        $bookRepository = self::getContainer()->get(BookRepositoryInterface::class);

        $book = BookFactory::create(name: 'Dune', price: 1500);
        $bookRepository->add($book);

        $client->request('POST', \sprintf('/api/books/%s/discount', $book->id), [
            'json' => [
                'discountPercentage' => 20,
            ],
        ]);

        self::assertResponseIsSuccessful();
        self::assertFalse($this->subscriptionLog()->hasInfoThatContains('Announcing "Dune" now at 1200'));

        /** @var InMemoryTransport $transport */
        $transport = self::getContainer()->get('messenger.transport.async');
        self::assertCount(1, $transport->getSent());
        self::assertInstanceOf(BookDiscounted::class, $transport->getSent()[0]->getMessage());

        /** @var Command $consume */
        $consume = self::getContainer()->get('console.command.messenger_consume_messages');
        new CommandTester($consume)->execute(['receivers' => ['async'], '--limit' => 1, '--time-limit' => 5, '--no-reset' => null]);

        self::assertTrue($this->subscriptionLog()->hasInfoThatContains('Announcing "Dune" now at 1200 instead of 1500 to reader@example.com.'));
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
