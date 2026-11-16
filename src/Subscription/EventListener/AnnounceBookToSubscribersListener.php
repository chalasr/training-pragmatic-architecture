<?php

declare(strict_types=1);

namespace App\Subscription\EventListener;

use App\BookStore\Domain\Repository\BookRepositoryInterface;
use App\BookStore\Domain\ValueObject\BookId;
use App\Shared\Application\Event\AsEventListener;
use App\Shared\Domain\Event\Integration\BookPublished;
use App\Subscription\Entity\Subscription;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Target;

#[AsEventListener]
final readonly class AnnounceBookToSubscribersListener
{
    public function __construct(
        private EntityManagerInterface $em,
        private BookRepositoryInterface $bookRepository,
        #[Target('subscriptionLogger')] private LoggerInterface $logger,
    ) {
    }

    public function __invoke(BookPublished $event): void
    {
        $book = $this->bookRepository->get(new BookId($event->bookId));

        foreach ($this->em->getRepository(Subscription::class)->findAll() as $subscription) {
            $this->logger->info('Announcing "{title}" ({price}) to {email}.', [
                'title' => $book->name->value,
                'price' => $book->price->value,
                'email' => $subscription->email,
            ]);
        }
    }
}
