<?php

namespace App\Library\Economy;

abstract class AbstractMonetizable
{
    abstract public function getAmount(): int;

    abstract public function setAmount(int $amount): static;

    abstract public function getCurrency(): string;

    abstract public function setCurrency(string $currency): static;

    public function hasCurrencyOf(AbstractMonetizable $money): bool
    {
        return $this->getCurrency() === $money->getCurrency();
    }

    protected function toBrickMoney(): \Brick\Money\Money
    {
        return \Brick\Money\Money::ofMinor(
            $this->getAmount(),
            $this->getCurrency()
        );
    }

    public function isLessThan(AbstractMonetizable $money): bool
    {
        return $this->toBrickMoney()->isLessThan($money->toBrickMoney());
    }

    public function isGreaterThanOrEqualTo(AbstractMonetizable $money): bool
    {
        return $this->toBrickMoney()->isGreaterThanOrEqualTo($money->toBrickMoney());
    }
}
