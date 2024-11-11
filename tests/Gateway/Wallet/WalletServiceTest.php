<?php

namespace App\Tests\Gateway\Wallet;

use App\Entity\Accounting\Transaction;
use App\Entity\Money;
use App\Entity\Tipjar;
use App\Entity\User;
use App\Gateway\Wallet\StatementDirection;
use App\Gateway\Wallet\WalletService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Zenstruck\Foundry\Test\ResetDatabase;

class WalletServiceTest extends KernelTestCase
{
    use ResetDatabase;

    private EntityManagerInterface $entityManager;
    private WalletService $walletService;

    public function setUp(): void
    {
        self::bootKernel();

        $this->entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $this->walletService = static::getContainer()->get(WalletService::class);
    }

    private function getUser(): User
    {
        $user = new User();
        $user->setUsername('wallettestuser');
        $user->setEmail('testuser@wallet.com');
        $user->setPassword('wallettestpassword');

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $user;
    }

    private function getTipjar(): Tipjar
    {
        $tipjar = new Tipjar();
        $tipjar->setName('WALLET_TEST_TIPJAR');

        $this->entityManager->persist($tipjar);
        $this->entityManager->flush();

        return $tipjar;
    }

    public function testTransactionsAddFunds()
    {
        $tipjar = $this->getTipjar()->getAccounting();
        $user = $this->getUser()->getAccounting();

        $statements = $this->walletService->getStatements($user);

        $this->assertCount(0, $statements);

        $balance = $this->walletService->getBalance($user);

        $this->assertEquals(0, $balance->amount);
        $this->assertEquals($user->getCurrency(), $balance->currency);

        $incoming = new Transaction();
        $incoming->setMoney(new Money(100, 'EUR'));
        $incoming->setOrigin($tipjar);
        $incoming->setTarget($user);

        /*
         * $this->walletService->save($incoming);
         * called automatically on Transaction persist.
         *
         * @see \App\EventListener\WalletTransactionsListener
         */
        $this->entityManager->persist($incoming);
        $this->entityManager->flush();

        $balance = $this->walletService->getBalance($user);

        $this->assertEquals(100, $balance->amount);
        $this->assertEquals('EUR', $balance->currency);

        $statements = $this->walletService->getStatements($user);

        $this->assertCount(1, $statements);

        $statement = $statements[0];
        $this->assertEquals(100, $statement->getBalance()->amount);
        $this->assertEquals(StatementDirection::Incoming->value, $statement->getDirection()->value);
        $this->assertEquals('EUR', $balance->currency);
        $this->assertCount(0, $statement->getFinancesTo());
        $this->assertCount(0, $statement->getFinancedBy());
    }

    public function testTransactionsGetFinanced()
    {
        $tipjar = $this->getTipjar()->getAccounting();
        $user = $this->getUser()->getAccounting();

        $incoming = new Transaction();
        $incoming->setMoney(new Money(100, 'EUR'));
        $incoming->setOrigin($tipjar);
        $incoming->setTarget($user);

        $this->entityManager->persist($incoming);
        $this->entityManager->flush();

        $outgoing = new Transaction();
        $outgoing->setMoney(new Money(20, 'EUR'));
        $outgoing->setOrigin($user);
        $outgoing->setTarget($tipjar);

        $this->walletService->spend($outgoing);

        $balance = $this->walletService->getBalance($user);

        $this->assertEquals(80, $balance->amount);
        $this->assertEquals('EUR', $balance->currency);

        $statements = $this->walletService->getStatements($user);

        $this->assertCount(2, $statements);

        $statement = $statements[0];
        $this->assertEquals(80, $statement->getBalance()->amount);
        $this->assertEquals(StatementDirection::Incoming->value, $statement->getDirection()->value);
        $this->assertEquals('EUR', $balance->currency);
        $this->assertCount(1, $statement->getFinancesTo());
        $this->assertCount(0, $statement->getFinancedBy());

        $statement = $statements[1];
        $this->assertEquals(20, $statement->getBalance()->amount);
        $this->assertEquals(StatementDirection::Outgoing->value, $statement->getDirection()->value);
        $this->assertEquals('EUR', $balance->currency);
        $this->assertCount(0, $statement->getFinancesTo());
        $this->assertCount(1, $statement->getFinancedBy());

        $outgoing = new Transaction();
        $outgoing->setMoney(new Money(30, 'EUR'));
        $outgoing->setOrigin($user);
        $outgoing->setTarget($tipjar);

        $this->walletService->spend($outgoing);

        $balance = $this->walletService->getBalance($user);

        $this->assertEquals(50, $balance->amount);
        $this->assertEquals('EUR', $balance->currency);

        $statements = $this->walletService->getStatements($user);

        $this->assertCount(3, $statements);

        $statement = $statements[0];
        $this->assertEquals(50, $statement->getBalance()->amount);
        $this->assertEquals(StatementDirection::Incoming->value, $statement->getDirection()->value);
        $this->assertEquals('EUR', $balance->currency);
        $this->assertCount(2, $statement->getFinancesTo());
        $this->assertCount(0, $statement->getFinancedBy());

        $statement = $statements[1];
        $this->assertEquals(20, $statement->getBalance()->amount);
        $this->assertEquals(StatementDirection::Outgoing->value, $statement->getDirection()->value);
        $this->assertEquals('EUR', $balance->currency);
        $this->assertCount(0, $statement->getFinancesTo());
        $this->assertCount(1, $statement->getFinancedBy());

        $statement = $statements[2];
        $this->assertEquals(30, $statement->getBalance()->amount);
        $this->assertEquals(StatementDirection::Outgoing->value, $statement->getDirection()->value);
        $this->assertEquals('EUR', $balance->currency);
        $this->assertCount(0, $statement->getFinancesTo());
        $this->assertCount(1, $statement->getFinancedBy());
    }

    public function testBalanceIsFIFO()
    {
        $tipjar = $this->getTipjar()->getAccounting();
        $user = $this->getUser()->getAccounting();

        $incoming = new Transaction();
        $incoming->setMoney(new Money(10, 'EUR'));
        $incoming->setOrigin($tipjar);
        $incoming->setTarget($user);

        $this->entityManager->persist($incoming);
        $this->entityManager->flush();

        $incoming = new Transaction();
        $incoming->setMoney(new Money(11, 'EUR'));
        $incoming->setOrigin($tipjar);
        $incoming->setTarget($user);

        $this->entityManager->persist($incoming);
        $this->entityManager->flush();

        $outgoing = new Transaction();
        $outgoing->setMoney(new Money(12, 'EUR'));
        $outgoing->setOrigin($user);
        $outgoing->setTarget($tipjar);

        $this->walletService->spend($outgoing);

        $incoming = new Transaction();
        $incoming->setMoney(new Money(13, 'EUR'));
        $incoming->setOrigin($tipjar);
        $incoming->setTarget($user);

        $this->entityManager->persist($incoming);
        $this->entityManager->flush();

        $balance = $this->walletService->getBalance($user);

        $this->assertEquals(22, $balance->amount);
        $this->assertEquals('EUR', $balance->currency);

        $statements = $this->walletService->getStatements($user);

        $this->assertCount(4, $statements);

        $statement = $statements[0];
        $this->assertEquals(0, $statement->getBalance()->amount);
        $this->assertEquals(StatementDirection::Incoming->value, $statement->getDirection()->value);
        $this->assertEquals('EUR', $balance->currency);
        $this->assertCount(1, $statement->getFinancesTo());
        $this->assertCount(0, $statement->getFinancedBy());

        $statement = $statements[1];
        $this->assertEquals(9, $statement->getBalance()->amount);
        $this->assertEquals(StatementDirection::Incoming->value, $statement->getDirection()->value);
        $this->assertEquals('EUR', $balance->currency);
        $this->assertCount(1, $statement->getFinancesTo());
        $this->assertCount(0, $statement->getFinancedBy());

        $statement = $statements[2];
        $this->assertEquals(12, $statement->getBalance()->amount);
        $this->assertEquals(StatementDirection::Outgoing->value, $statement->getDirection()->value);
        $this->assertEquals('EUR', $balance->currency);
        $this->assertCount(0, $statement->getFinancesTo());
        $this->assertCount(2, $statement->getFinancedBy());

        $statement = $statements[3];
        $this->assertEquals(13, $statement->getBalance()->amount);
        $this->assertEquals(StatementDirection::Incoming->value, $statement->getDirection()->value);
        $this->assertEquals('EUR', $balance->currency);
        $this->assertCount(0, $statement->getFinancesTo());
        $this->assertCount(0, $statement->getFinancedBy());
    }
}
