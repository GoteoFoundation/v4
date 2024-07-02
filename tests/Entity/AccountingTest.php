<?php

namespace App\Tests\Entity;

use App\Entity\Accounting;
use App\Entity\AccountingTransaction;
use App\Entity\Money;
use App\Entity\Tipjar;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class AccountingTest extends KernelTestCase
{
    private EntityManagerInterface $entityManager;

    public function setUp(): void
    {
        self::bootKernel();

        $this->entityManager = static::getContainer()->get(EntityManagerInterface::class);
    }

    public function testAccountingGetsUpdatedByPersistingTransactions()
    {
        $tipjarA = new Tipjar();
        $tipjarA->setName('TEST_TIPJAR_A');

        $accA = new Accounting();
        $accA->setTipjar($tipjarA);


        $tipjarB = new Tipjar();
        $tipjarB->setName('TEST_TIPJAR_B');

        $accB = new Accounting();
        $accB->setTipjar($tipjarB);

        $trx = new AccountingTransaction();
        $trx->setMoney(new Money(120, 'EUR'));
        $trx->setOrigin($accA);
        $trx->setTarget($accB);

        $this->entityManager->persist($trx);
        $this->entityManager->flush();

        /** @var Accounting */
        $accA = $this->entityManager->getRepository(Tipjar::class)
            ->findOneBy(['name' => 'TEST_TIPJAR_A'])
            ->getAccounting();

        /** @var Accounting */
        $accB = $this->entityManager->getRepository(Tipjar::class)
            ->findOneBy(['name' => 'TEST_TIPJAR_B'])
            ->getAccounting();

        $this->assertNotNull($accA->getId());
        $this->assertNotNull($accB->getId());

        $this->assertCount(1, $accA->getTransactionsIssued());
        $this->assertCount(1, $accB->getTransactionsReceived());

        $this->assertEquals(120, $accA->getTransactionsIssued()[0]->getMoney()->amount);
        $this->assertEquals('EUR', $accA->getTransactionsIssued()[0]->getMoney()->currency);

        $this->assertEquals(120, $accB->getTransactionsReceived()[0]->getMoney()->amount);
        $this->assertEquals('EUR', $accB->getTransactionsReceived()[0]->getMoney()->currency);
    }
}
