<?php

declare(strict_types=1);

namespace App\BookStore\Infrastructure\ReadModel;

use App\BookStore\Domain\ValueObject\BookId;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'discounted_book')]
final readonly class DiscountedBookView
{
    public function __construct(
        public BookId $id,
        #[ORM\Column]
        public string $name,
        #[ORM\Column]
        public int $previousPrice,
        #[ORM\Column]
        public int $price,
    ) {
    }
}
