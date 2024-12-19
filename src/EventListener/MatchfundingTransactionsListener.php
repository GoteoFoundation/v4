<?php

namespace App\EventListener;

use App\Entity\Accounting\Transaction;
use App\Entity\Matchfunding\MatchSubmissionStatus;
use App\Entity\Project\Project;
use App\Matchfunding\MatchStrategy\MatchStrategyLocator;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Event\PostPersistEventArgs;
use Doctrine\ORM\Events;

#[AsEntityListener(
    event: Events::postPersist,
    method: 'processTransaction',
    entity: Transaction::class
)]
final class MatchfundingTransactionsListener
{
    public function __construct(
        private MatchStrategyLocator $matchStrategyLocator,
    ) {}

    /**
     * Generates matched Transactions for Transactions inside a MatchCall.
     */
    public function processTransaction(
        Transaction $transaction,
        PostPersistEventArgs $event,
    ) {
        $target = $transaction->getTarget()->getOwner();

        if (!$target instanceof Project) {
            return;
        }

        $submissions = $target->getMatchSubmissions();

        foreach ($submissions as $submission) {
            if ($submission->getStatus() !== MatchSubmissionStatus::Accepted) {
                continue;
            }

            $call = $submission->getMatchCall();
            $strategy = $this->matchStrategyLocator->getForCall($call);
            $match = $strategy->match($transaction);

            if ($match->getId() !== null) {
                return;
            }

            $event->getObjectManager()->persist($match);
            $event->getObjectManager()->flush();
        }
    }
}
