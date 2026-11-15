<?php

declare(strict_types=1);

namespace App\Subscription\EventListener;

use App\Shared\Application\Event\AsEventListener;
use App\Shared\Domain\Event\Integration\BookDiscounted;
use App\Subscription\Entity\Subscription;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Target;

#[AsEventListener]
final readonly class AnnounceDiscountToSubscribersListener
{
    public function __construct(
        private EntityManagerInterface $em,
        #[Target('subscriptionLogger')] private LoggerInterface $logger,
    ) {
    }

    public function __invoke(BookDiscounted $event): void
    {
        foreach ($this->em->getRepository(Subscription::class)->findAll() as $subscription) {
            $this->logger->info('Announcing "{title}" now at {price} instead of {previousPrice} to {email}.', [
                'title' => $event->title,
                'price' => $event->price,
                'previousPrice' => $event->previousPrice,
                'email' => $subscription->email,
            ]);
        }
    }
}
