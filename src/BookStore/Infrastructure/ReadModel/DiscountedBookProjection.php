<?php

declare(strict_types=1);

namespace App\BookStore\Infrastructure\ReadModel;

interface DiscountedBookProjection
{
    public function save(DiscountedBookView $view): void;

    /**
     * @return list<DiscountedBookView>
     */
    public function all(): array;
}
