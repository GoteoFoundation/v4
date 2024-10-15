<?php

namespace App\Tests\Entity;

use App\Entity\Accounting;
use App\Entity\AccountingTransaction;
use App\Entity\Money;
use App\Entity\Tipjar;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Zenstruck\Foundry\Test\ResetDatabase;

class AccountingTest extends KernelTestCase
{
    use ResetDatabase;

    private EntityManagerInterface $entityManager;

    public function setUp(): void
    {
        self::bootKernel();

        $this->entityManager = static::getContainer()->get(EntityManagerInterface::class);
    }

    public function testAccountingGetsUpdatedByCascade()
    {
        $tipjarA = new Tipjar();
        $tipjarA->setName('TEST_TIPJAR_A');

        $accountingA = new Accounting();
        $accountingA->setTipjar($tipjarA);

        $this->entityManager->persist($tipjarA);
        $this->entityManager->flush();

        /** @var Accounting */
        $accountingA = $this->entityManager->getRepository(Tipjar::class)
            ->findOneBy(['name' => 'TEST_TIPJAR_A'])
            ->getAccounting();

        $this->assertNotNull($accountingA->getId());
        $this->assertEquals(Tipjar::class, $accountingA->getOwnerClass());
        $this->assertEquals('TEST_TIPJAR_A', $accountingA->getTipjar()->getName());

        $tipjarB = new Tipjar();
        $tipjarB->setName('TEST_TIPJAR_B');

        $accountingB = new Accounting();
        $accountingB->setTipjar($tipjarB);

        $this->entityManager->persist($tipjarB);
        $this->entityManager->flush();

        /** @var Accounting */
        $accountingB = $this->entityManager->getRepository(Tipjar::class)
            ->findOneBy(['name' => 'TEST_TIPJAR_B'])
            ->getAccounting();

        $this->assertNotNull($accountingB->getId());
        $this->assertEquals(Tipjar::class, $accountingB->getOwnerClass());
        $this->assertEquals('TEST_TIPJAR_B', $accountingB->getTipjar()->getName());
    }

    public function testAccountingGetsUpdatedByTransactions()
    {
        $tipjarA = new Tipjar();
        $tipjarA->setName('TEST_TIPJAR_A');

        $accountingA = new Accounting();
        $accountingA->setTipjar($tipjarA);

        $this->entityManager->persist($accountingA);

        $tipjarB = new Tipjar();
        $tipjarB->setName('TEST_TIPJAR_B');

        $accountingB = new Accounting();
        $accountingB->setTipjar($tipjarB);

        $this->entityManager->persist($accountingB);
        $this->entityManager->flush();

        $transaction = new AccountingTransaction();
        $transaction->setMoney(new Money(120, 'EUR'));
        $transaction->setOrigin($accountingA);
        $transaction->setTarget($accountingB);

        $this->entityManager->persist($transaction);
        $this->entityManager->flush();

        /** @var Accounting */
        $accountingA = $this->entityManager
            ->getRepository(Tipjar::class)
            ->findOneBy(['name' => 'TEST_TIPJAR_A'])
            ->getAccounting();

        /** @var Accounting */
        $accountingB = $this->entityManager
            ->getRepository(Tipjar::class)
            ->findOneBy(['name' => 'TEST_TIPJAR_B'])
            ->getAccounting();

        $this->assertCount(1, $accountingA->getTransactionsOutgoing());
        $this->assertCount(0, $accountingA->getTransactionsIncoming());

        $this->assertCount(0, $accountingB->getTransactionsOutgoing());
        $this->assertCount(1, $accountingB->getTransactionsIncoming());

        $this->assertEquals(120, $accountingA->getTransactionsOutgoing()[0]->getMoney()->amount);
        $this->assertEquals('EUR', $accountingA->getTransactionsOutgoing()[0]->getMoney()->currency);

        $this->assertEquals(120, $accountingB->getTransactionsIncoming()[0]->getMoney()->amount);
        $this->assertEquals('EUR', $accountingB->getTransactionsIncoming()[0]->getMoney()->currency);
    }
}
