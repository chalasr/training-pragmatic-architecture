<?php

declare(strict_types=1);

namespace App\Tests\BookStore\Integration\Doctrine;

use App\BookStore\Infrastructure\Doctrine\DoctrineDiscountedBookProjection;
use App\BookStore\Infrastructure\ReadModel\DiscountedBookProjection;
use App\Tests\BookStore\Integration\DiscountedBookProjectionTestCase;
use PHPUnit\Framework\Attributes\Group;

#[Group('tp')]
final class DoctrineDiscountedBookProjectionTest extends DiscountedBookProjectionTestCase
{
    use RunsAgainstDoctrine;

    protected function projection(): DiscountedBookProjection
    {
        return self::getContainer()->get(DoctrineDiscountedBookProjection::class);
    }

    /**
     * @return list<string>
     */
    protected static function tablesToTruncate(): array
    {
        return ['discounted_book'];
    }
}
