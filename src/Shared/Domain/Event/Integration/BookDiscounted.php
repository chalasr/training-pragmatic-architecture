<?php

declare(strict_types=1);

namespace App\Shared\Domain\Event\Integration;

use App\Shared\Domain\Event\EventInterface;

final readonly class BookDiscounted implements EventInterface
{
    public function __construct(
        public string $bookId,
        public string $title,
        public int $previousPrice,
        public int $price,
    ) {
    }
}
