<?php

namespace App\Dto;

final class Payment
{
    public function __construct(
        private readonly float $amount,
        private readonly string $currency,
        private readonly ?string $id = null
    ) {
    }
    public function getAmount(): float
    {
        return $this->amount;
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }

    public function getId(): ?string
    {
        return $this->id;
    }
}
