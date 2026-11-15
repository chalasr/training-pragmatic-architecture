<?php

declare(strict_types=1);

namespace App\Tests\BookStore\Integration\InMemory;

use App\BookStore\Infrastructure\InMemory\InMemoryDiscountedBookProjection;
use App\BookStore\Infrastructure\ReadModel\DiscountedBookProjection;
use App\Tests\BookStore\Integration\DiscountedBookProjectionTestCase;
use PHPUnit\Framework\Attributes\Group;

#[Group('tp')]
final class InMemoryDiscountedBookProjectionTest extends DiscountedBookProjectionTestCase
{
    protected function projection(): DiscountedBookProjection
    {
        return self::getContainer()->get(InMemoryDiscountedBookProjection::class);
    }
}
