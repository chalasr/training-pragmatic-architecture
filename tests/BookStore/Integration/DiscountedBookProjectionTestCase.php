<?php

declare(strict_types=1);

namespace App\Tests\BookStore\Integration;

use App\BookStore\Domain\ValueObject\BookId;
use App\BookStore\Infrastructure\ReadModel\DiscountedBookProjection;
use App\BookStore\Infrastructure\ReadModel\DiscountedBookView;

abstract class DiscountedBookProjectionTestCase extends RepositoryTestCase
{
    public function testItListsTheDiscountedBooksByName(): void
    {
        $this->projection()->save(new DiscountedBookView(new BookId(), 'Neuromancer', 1000, 900));
        $this->projection()->save(new DiscountedBookView(new BookId(), 'Dune', 1500, 1200));

        $names = \array_map(static fn (DiscountedBookView $view): string => $view->name, $this->projection()->all());

        self::assertSame(['Dune', 'Neuromancer'], $names);
    }

    public function testItKeepsOneLinePerBook(): void
    {
        $id = new BookId();

        $this->projection()->save(new DiscountedBookView($id, 'Dune', 1500, 1200));
        $this->projection()->save(new DiscountedBookView($id, 'Dune', 1500, 1200));
        $this->projection()->save(new DiscountedBookView($id, 'Dune', 1200, 1000));

        $views = $this->projection()->all();

        self::assertCount(1, $views);
        self::assertSame((string) $id, (string) $views[0]->id);
        self::assertSame(1200, $views[0]->previousPrice);
        self::assertSame(1000, $views[0]->price);
    }

    abstract protected function projection(): DiscountedBookProjection;
}
