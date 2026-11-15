<?php

declare(strict_types=1);

namespace App\BookStore\Infrastructure\ApiPlatform\Output;

use App\BookStore\Infrastructure\ReadModel\DiscountedBookView;
use App\Shared\Infrastructure\ApiPlatform\ObjectMapper\ScalarTransform;
use Symfony\Component\ObjectMapper\Attribute\Map;

#[Map(source: DiscountedBookView::class)]
final class DiscountedBookOutput
{
    #[Map(source: 'id', transform: ScalarTransform::class)]
    public string $id;

    public string $name;

    public int $previousPrice;

    public int $price;
}
