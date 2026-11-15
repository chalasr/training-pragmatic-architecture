<?php

declare(strict_types=1);

namespace App\BookStore\Infrastructure\Doctrine;

use App\BookStore\Domain\ValueObject\BookId;
use App\BookStore\Infrastructure\ReadModel\DiscountedBookProjection;
use App\BookStore\Infrastructure\ReadModel\DiscountedBookView;
use Doctrine\DBAL\Connection;
use Symfony\Component\DependencyInjection\Attribute\AsAlias;

#[AsAlias(DiscountedBookProjection::class, when: ['dev', 'prod'])]
final readonly class DoctrineDiscountedBookProjection implements DiscountedBookProjection
{
    public function __construct(
        private Connection $connection,
    ) {
    }

    #[\Override]
    public function save(DiscountedBookView $view): void
    {
        $this->connection->executeStatement(
            'INSERT INTO discounted_book (id, name, previous_price, price) VALUES (:id, :name, :previousPrice, :price)
            ON CONFLICT (id) DO UPDATE SET name = excluded.name, previous_price = excluded.previous_price, price = excluded.price',
            [
                'id' => (string) $view->id,
                'name' => $view->name,
                'previousPrice' => $view->previousPrice,
                'price' => $view->price,
            ],
        );
    }

    #[\Override]
    public function all(): array
    {
        /** @var list<array{id: string, name: string, previous_price: int, price: int}> $rows */
        $rows = $this->connection->fetchAllAssociative('SELECT id, name, previous_price, price FROM discounted_book ORDER BY name');

        return \array_map(static fn (array $row): DiscountedBookView => new DiscountedBookView(
            new BookId($row['id']),
            $row['name'],
            $row['previous_price'],
            $row['price'],
        ), $rows);
    }
}
