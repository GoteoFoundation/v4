<?php

namespace App\Tests\Library\Economy\Payment;

use App\Entity\Accounting;
use App\Entity\AccountingTransaction;
use App\Entity\Money;
use App\Entity\Tipjar;
use App\Entity\User;
use App\Entity\WalletStatementDirection;
use App\Library\Economy\Payment\WalletGatewayService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Zenstruck\Foundry\Test\ResetDatabase;

class WalletGatewayServiceTest extends KernelTestCase
{
    use ResetDatabase;

    private EntityManagerInterface $entityManager;
    private WalletGatewayService $walletService;

    public function setUp(): void
    {
        self::bootKernel();

        $this->entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $this->walletService = static::getContainer()->get(WalletGatewayService::class);
    }

    private function getUserAccounting(): Accounting
    {
        $user = new User();
        $user->setUsername('wallettestuser');
        $user->setEmail('testuser@wallet.com');
        $user->setPassword('wallettestpassword');

        $accounting = new Accounting();
        $accounting->setCurrency('EUR');
        $accounting->setUser($user);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $accounting;
    }

    private function getTipjarAccounting(): Accounting
    {
        $tipjar = new Tipjar();
        $tipjar->setName('WALLET_TEST_TIPJAR');

        $accounting = new Accounting();
        $accounting->setTipjar($tipjar);

        $this->entityManager->persist($tipjar);
        $this->entityManager->flush();

        return $accounting;
    }

    public function testTransactionsAddFunds()
    {
        $tipjar = $this->getTipjarAccounting();
        $user = $this->getUserAccounting();

        $statements = $this->walletService->getStatements($user);

        $this->assertCount(0, $statements);

        $balance = $this->walletService->getBalance($user);

        $this->assertEquals(0, $balance->amount);
        $this->assertEquals($user->getCurrency(), $balance->currency);

        $incoming = new AccountingTransaction();
        $incoming->setMoney(new Money(100, 'EUR'));
        $incoming->setOrigin($tipjar);
        $incoming->setTarget($user);

        $this->entityManager->persist($incoming);
        $this->entityManager->flush();

        /**
         * $this->walletService->save($incoming);
         * called automatically on Transaction persist.
         *
         * @see \App\EventListener\WalletTransactionsListener
         */
        $statements = $this->walletService->getStatements($user);

        $this->assertCount(1, $statements);

        $balance = $this->walletService->getBalance($user);

        $this->assertEquals(100, $balance->amount);
        $this->assertEquals('EUR', $balance->currency);
    }

    public function testTransactionsGetFinanced()
    {
        $tipjar = $this->getTipjarAccounting();
        $user = $this->getUserAccounting();

        $incoming = new AccountingTransaction();
        $incoming->setMoney(new Money(100, 'EUR'));
        $incoming->setOrigin($tipjar);
        $incoming->setTarget($user);

        $this->entityManager->persist($incoming);
        $this->entityManager->flush();

        $outgoing = new AccountingTransaction();
        $outgoing->setMoney(new Money(20, 'EUR'));
        $outgoing->setOrigin($user);
        $outgoing->setTarget($tipjar);

        $this->walletService->spend($outgoing);

        $statements = $this->walletService->getStatements($user);

        $this->assertCount(2, $statements);

        $balance = $this->walletService->getBalance($user);

        $this->assertEquals(80, $balance->amount);
        $this->assertEquals('EUR', $balance->currency);

        $this->assertEquals(80, $statements[0]->getBalance()->amount);
        $this->assertEquals(WalletStatementDirection::Incoming->value, $statements[0]->getDirection()->value);
        $this->assertEquals('EUR', $balance->currency);
        $this->assertCount(1, $statements[0]->getFinancesTo());
        $this->assertCount(0, $statements[0]->getFinancedBy());

        $this->assertEquals(20, $statements[1]->getBalance()->amount);
        $this->assertEquals(WalletStatementDirection::Outgoing->value, $statements[1]->getDirection()->value);
        $this->assertEquals('EUR', $balance->currency);
        $this->assertCount(0, $statements[1]->getFinancesTo());
        $this->assertCount(1, $statements[1]->getFinancedBy());

        $outgoing = new AccountingTransaction();
        $outgoing->setMoney(new Money(30, 'EUR'));
        $outgoing->setOrigin($user);
        $outgoing->setTarget($tipjar);

        $this->walletService->spend($outgoing);

        $statements = $this->walletService->getStatements($user);

        $this->assertCount(3, $statements);

        $balance = $this->walletService->getBalance($user);

        $this->assertEquals(50, $balance->amount);
        $this->assertEquals('EUR', $balance->currency);

        $this->assertEquals(50, $statements[0]->getBalance()->amount);
        $this->assertEquals(WalletStatementDirection::Incoming->value, $statements[0]->getDirection()->value);
        $this->assertEquals('EUR', $balance->currency);
        $this->assertCount(2, $statements[0]->getFinancesTo());
        $this->assertCount(0, $statements[0]->getFinancedBy());

        $this->assertEquals(20, $statements[1]->getBalance()->amount);
        $this->assertEquals(WalletStatementDirection::Outgoing->value, $statements[1]->getDirection()->value);
        $this->assertEquals('EUR', $balance->currency);
        $this->assertCount(0, $statements[1]->getFinancesTo());
        $this->assertCount(1, $statements[1]->getFinancedBy());

        $this->assertEquals(30, $statements[2]->getBalance()->amount);
        $this->assertEquals(WalletStatementDirection::Outgoing->value, $statements[2]->getDirection()->value);
        $this->assertEquals('EUR', $balance->currency);
        $this->assertCount(0, $statements[2]->getFinancesTo());
        $this->assertCount(1, $statements[2]->getFinancedBy());
    }
}
