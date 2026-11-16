<?php

declare(strict_types=1);

namespace App\BookStore\Application\Command;

use App\BookStore\Domain\Model\Order;
use App\BookStore\Domain\Repository\OrderRepositoryInterface;
use App\BookStore\Domain\ValueObject\BookName;
use App\BookStore\Domain\ValueObject\Price;
use App\BookStore\Domain\ValueObject\PurchasedBook;
use App\BookStore\Infrastructure\ReadModel\BookViewFinder;
use App\Shared\Application\Command\AsCommandHandler;
use Psr\Clock\ClockInterface;

#[AsCommandHandler]
final readonly class PlaceOrderCommandHandler
{
    public function __construct(
        private BookViewFinder $bookViewFinder,
        private OrderRepositoryInterface $orderRepository,
        private ClockInterface $clock,
    ) {
    }

    public function __invoke(PlaceOrderCommand $command): void
    {
        $purchases = [];
        foreach ($command->items as $item) {
            $book = $this->bookViewFinder->get($item->bookId);

            $purchases[] = new PurchasedBook($book->id, new BookName($book->name), new Price($book->price), $item->quantity);
        }

        $this->orderRepository->add(Order::place($command->id, $this->clock->now(), ...$purchases));
    }
}
