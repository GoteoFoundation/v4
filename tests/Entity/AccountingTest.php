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
        $this->assertCount(0, $accA->getTransactionsReceived());
        
        $this->assertCount(0, $accB->getTransactionsIssued());
        $this->assertCount(1, $accB->getTransactionsReceived());

        $this->assertEquals(120, $accA->getTransactionsIssued()[0]->getMoney()->amount);
        $this->assertEquals('EUR', $accA->getTransactionsIssued()[0]->getMoney()->currency);

        $this->assertEquals(120, $accB->getTransactionsReceived()[0]->getMoney()->amount);
        $this->assertEquals('EUR', $accB->getTransactionsReceived()[0]->getMoney()->currency);
    }

    public function testAccountingSortsMixedTransactions()
    {
        $tipjarA = new Tipjar();
        $tipjarA->setName('TEST_TIPJAR_A');

        $accA = new Accounting();
        $accA->setTipjar($tipjarA);

        $tipjarB = new Tipjar();
        $tipjarB->setName('TEST_TIPJAR_B');

        $accB = new Accounting();
        $accB->setTipjar($tipjarB);

        $trx1 = new AccountingTransaction();
        $trx1->setMoney(new Money(1, 'EUR'));
        $trx1->setOrigin($accA);
        $trx1->setTarget($accB);

        $trx2 = new AccountingTransaction();
        $trx2->setMoney(new Money(2, 'USD'));
        $trx2->setOrigin($accB);
        $trx2->setTarget($accA);

        $this->entityManager->persist($trx1);
        $this->entityManager->persist($trx2);
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

        $this->assertCount(2, $accA->getTransactions());
        $this->assertCount(2, $accB->getTransactions());

        $this->assertEquals(1, $accA->getTransactions()[0]->getMoney()->amount);
        $this->assertEquals('EUR', $accA->getTransactions()[0]->getMoney()->currency);

        $this->assertEquals(1, $accB->getTransactions()[0]->getMoney()->amount);
        $this->assertEquals('EUR', $accB->getTransactions()[0]->getMoney()->currency);

        $this->assertEquals(2, $accA->getTransactions()[1]->getMoney()->amount);
        $this->assertEquals('USD', $accA->getTransactions()[1]->getMoney()->currency);

        $this->assertEquals(2, $accB->getTransactions()[1]->getMoney()->amount);
        $this->assertEquals('USD', $accB->getTransactions()[1]->getMoney()->currency);

        $this->assertEquals($accA, $accA->getTransactions()[0]->getOrigin());
        $this->assertEquals($accA, $accA->getTransactions()[1]->getTarget());

        $this->assertEquals($accB, $accB->getTransactions()[1]->getOrigin());
        $this->assertEquals($accB, $accB->getTransactions()[0]->getTarget());
    }
}
