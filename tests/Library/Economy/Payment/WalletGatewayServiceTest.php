<?php

namespace App\Tests\Library\Economy\Payment;

use App\Entity\Accounting;
use App\Entity\AccountingTransaction;
use App\Entity\Money;
use App\Entity\Tipjar;
use App\Entity\User;
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

        $balance = $this->walletService->calcBalance($user);

        $this->assertEquals(0, $balance->amount);
        $this->assertEquals($user->getCurrency(), $balance->currency);

        $transaction = new AccountingTransaction();
        $transaction->setMoney(new Money(100, 'EUR'));
        $transaction->setOrigin($tipjar);
        $transaction->setTarget($user);

        $this->entityManager->persist($transaction);
        $this->entityManager->flush();

        $balance = $this->walletService->calcBalance($user);

        $this->assertEquals(100, $balance->amount);
        $this->assertEquals('EUR', $balance->currency);
    }
}
