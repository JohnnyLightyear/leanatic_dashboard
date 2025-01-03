<?php

namespace App\RemoteEvent;

use App\Entity\WebhookDashboard;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\RemoteEvent\Attribute\AsRemoteEventConsumer;
use Symfony\Component\RemoteEvent\Consumer\ConsumerInterface;
use Symfony\Component\RemoteEvent\RemoteEvent;

#[AsRemoteEventConsumer('ws_dashboard')]
final class WsDashboardWebhookConsumer implements ConsumerInterface
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    public function consume(RemoteEvent $event): void
    {
        // TODO: Ideally, push the webhook into a messenger queue and let it be consumed asynchronously

        $entry = new WebhookDashboard();
        $entry->setAction($event->getName());
        $entry->setOrigin($event->getId());
        $entry->setPayload($event->getPayload());
        $entry->setCreationDate(new \DateTime(timezone: new \DateTimeZone('UTC')));

        $this->entityManager->persist($entry);

        $this->entityManager->flush();
    }
}
