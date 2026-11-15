<?php

declare(strict_types=1);

namespace App\BookStore\Infrastructure\InMemory;

use App\BookStore\Infrastructure\ReadModel\DiscountedBookProjection;
use App\BookStore\Infrastructure\ReadModel\DiscountedBookView;
use Symfony\Component\DependencyInjection\Attribute\AsAlias;
use Symfony\Component\DependencyInjection\Attribute\When;

#[AsAlias(DiscountedBookProjection::class, public: true, when: ['test'])]
#[When('test')]
final class InMemoryDiscountedBookProjection implements DiscountedBookProjection
{
    /** @var array<string, DiscountedBookView> */
    private array $views = [];

    #[\Override]
    public function save(DiscountedBookView $view): void
    {
        $this->views[(string) $view->id] = $view;
    }

    #[\Override]
    public function all(): array
    {
        $views = \array_values($this->views);
        \usort($views, static fn (DiscountedBookView $a, DiscountedBookView $b): int => $a->name <=> $b->name);

        return $views;
    }
}
