<?php

declare(strict_types=1);

namespace App\BookStore\Infrastructure\ReadModel;

use App\BookStore\Domain\Event\BookDiscounted;
use App\Shared\Application\Event\AsEventListener;

#[AsEventListener]
final readonly class ProjectDiscountedBookListener
{
    public function __construct(
        private DiscountedBookProjection $projection,
    ) {
    }

    public function __invoke(BookDiscounted $event): void
    {
        $this->projection->save(new DiscountedBookView(
            $event->bookId,
            $event->name->value,
            $event->previousPrice->value,
            $event->price->value,
        ));
    }
}
