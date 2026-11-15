<?php

declare(strict_types=1);

namespace App\BookStore\Application\Command;

use App\BookStore\Domain\Repository\BookRepositoryInterface;
use App\Shared\Application\Command\AsCommandHandler;
use App\Shared\Application\Event\EventBusInterface;

#[AsCommandHandler]
final readonly class DiscountBookCommandHandler
{
    public function __construct(
        private BookRepositoryInterface $bookRepository,
        private EventBusInterface $eventBus,
    ) {
    }

    public function __invoke(DiscountBookCommand $command): void
    {
        $book = $this->bookRepository->get($command->id);

        $book->applyDiscount($command->discount);

        foreach ($book->releaseEvents() as $event) {
            $this->eventBus->dispatch($event);
        }
    }
}
