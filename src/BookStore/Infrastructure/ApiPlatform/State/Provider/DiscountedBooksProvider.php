<?php

declare(strict_types=1);

namespace App\BookStore\Infrastructure\ApiPlatform\State\Provider;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\BookStore\Infrastructure\ReadModel\DiscountedBookProjection;
use App\BookStore\Infrastructure\ReadModel\DiscountedBookView;

/**
 * @implements ProviderInterface<DiscountedBookView>
 */
final readonly class DiscountedBooksProvider implements ProviderInterface
{
    public function __construct(
        private DiscountedBookProjection $discountedBooks,
    ) {
    }

    /**
     * @return list<DiscountedBookView>
     */
    #[\Override]
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): array
    {
        return $this->discountedBooks->all();
    }
}
