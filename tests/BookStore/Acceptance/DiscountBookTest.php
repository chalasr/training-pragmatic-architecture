<?php

declare(strict_types=1);

namespace App\Tests\BookStore\Acceptance;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use App\BookStore\Domain\Repository\BookRepositoryInterface;
use App\BookStore\Domain\ValueObject\Price;
use App\BookStore\Infrastructure\ApiPlatform\Resource\BookResource;
use App\Shared\Domain\Event\Integration\BookDiscounted;
use App\Tests\BookStore\Factory\BookFactory;
use App\Tests\Shared\Spy\EventBusSpy;
use PHPUnit\Framework\Attributes\Group;

final class DiscountBookTest extends ApiTestCase
{
    public function testApplyADiscountOnBook(): void
    {
        $client = self::createClient();

        /** @var BookRepositoryInterface $bookRepository */
        $bookRepository = self::getContainer()->get(BookRepositoryInterface::class);

        $book = BookFactory::create(price: 1000);
        $bookRepository->add($book);

        $client->request('POST', \sprintf('/api/books/%s/discount', $book->id), [
            'json' => [
                'discountPercentage' => 20,
            ],
        ]);

        self::assertResponseIsSuccessful();
        self::assertMatchesResourceItemJsonSchema(BookResource::class);
        self::assertJsonContains(['price' => 800]);

        self::assertEquals(new Price(800), $bookRepository->get($book->id)->price);
    }

    public function testValidateDiscountAmount(): void
    {
        $client = self::createClient();

        /** @var BookRepositoryInterface $bookRepository */
        $bookRepository = self::getContainer()->get(BookRepositoryInterface::class);

        $book = BookFactory::create(price: 1000);
        $bookRepository->add($book);

        $client->request('POST', \sprintf('/api/books/%s/discount', $book->id), [
            'json' => [
                'discountPercentage' => 200,
            ],
        ]);

        self::assertResponseIsUnprocessable();
        self::assertJsonContains([
            'violations' => [
                ['propertyPath' => 'discountPercentage', 'message' => 'Expected a value between 0 and 100. Got: 200'],
            ],
        ]);

        self::assertEquals(new Price(1000), $bookRepository->get($book->id)->price);
    }

    #[Group('tp')]
    public function testADiscountIsPublishedAsAnIntegrationEvent(): void
    {
        $client = self::createClient();

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

        /** @var EventBusSpy $eventBus */
        $eventBus = self::getContainer()->get(EventBusSpy::class);

        self::assertEquals(
            new BookDiscounted((string) $book->id, 'Dune', 1500, 1200),
            $eventBus->findDispatched(BookDiscounted::class),
        );
    }

    #[Group('tp')]
    public function testListTheDiscountedBooks(): void
    {
        $client = self::createClient();
        $client->disableReboot();

        /** @var BookRepositoryInterface $bookRepository */
        $bookRepository = self::getContainer()->get(BookRepositoryInterface::class);

        $book = BookFactory::create(name: 'Dune', price: 1500);
        $bookRepository->add($book);
        $bookRepository->add(BookFactory::create(name: 'Neuromancer', price: 1000));

        $client->request('POST', \sprintf('/api/books/%s/discount', $book->id), [
            'json' => [
                'discountPercentage' => 20,
            ],
        ]);

        $response = $client->request('GET', '/api/books/discounted');

        self::assertResponseIsSuccessful();
        self::assertSame(1, $response->toArray()['totalItems']);
        self::assertJsonContains([
            'member' => [
                [
                    '@id' => \sprintf('/api/books/%s', $book->id),
                    'name' => 'Dune',
                    'previousPrice' => 1500,
                    'price' => 1200,
                ],
            ],
        ]);
    }
}
