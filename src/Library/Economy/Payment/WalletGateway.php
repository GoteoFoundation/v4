<?php

namespace App\Library\Economy\Payment;

use App\Entity\Accounting\Transaction;
use App\Entity\Gateway\ChargeType;
use App\Entity\Gateway\Checkout;
use App\Entity\Money;
use App\Entity\WalletStatement;
use App\Entity\WalletStatementDirection;
use App\Library\Economy\MoneyService;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class WalletGateway implements GatewayInterface
{
    public static function getName(): string
    {
        return 'wallet';
    }

    public static function getSupportedChargeTypes(): array
    {
        return [
            ChargeType::Single,
        ];
    }

    public function __construct(
        private WalletGatewayService $wallet,
        private MoneyService $money,
    ) {}

    public function process(Checkout $checkout): Checkout
    {
        $origin = $checkout->getOrigin();
        $available = $this->wallet->getBalance($origin);
        $chargeTotal = $this->getChargeTotal($checkout);

        if ($this->money->isLess($available, $chargeTotal)) {
            throw new \Exception("Can't spend more than what you have!");
        }

        $charges = $checkout->getCharges();
        foreach ($charges as $charge) {
            $transaction = new Transaction();
            $transaction->setMoney($charge->getMoney());
            $transaction->setOrigin($origin);
            $transaction->setTarget($charge->getTarget());

            $outgoing = new WalletStatement();
            $outgoing->setTransaction($transaction);
            $outgoing->setDirection(WalletStatementDirection::Outgoing);

            $outgoing = $this->wallet->spend($transaction);
        }

        return $checkout;
    }

    public function handleRedirect(Request $request): RedirectResponse
    {
        return new RedirectResponse('');
    }

    public function handleWebhook(Request $request): Response
    {
        return new Response();
    }

    private function getChargeTotal(Checkout $checkout): Money
    {
        $total = new Money(0, $checkout->getOrigin()->getCurrency());

        $charges = $checkout->getCharges();
        foreach ($charges as $charge) {
            $total = $this->money->add($charge->getMoney(), $total);
        }

        return $total;
    }
}
