<?php

declare(strict_types=1);

namespace App\BookStore\Application\EventListener;

use App\BookStore\Domain\Event\BookDiscounted;
use App\Shared\Application\Event\AsEventListener;
use App\Shared\Application\Event\EventBusInterface;
use App\Shared\Domain\Event\Integration\BookDiscounted as BookDiscountedIntegrationEvent;

#[AsEventListener]
final readonly class AnnounceBookDiscountedListener
{
    public function __construct(
        private EventBusInterface $eventBus,
    ) {
    }

    public function __invoke(BookDiscounted $event): void
    {
        $this->eventBus->dispatch(new BookDiscountedIntegrationEvent(
            (string) $event->bookId,
            $event->name->value,
            $event->previousPrice->value,
            $event->price->value,
        ));
    }
}
