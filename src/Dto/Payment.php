<?php

namespace App\Dto;

final class Payment
{
    public function __construct(
        private float $amount,
        private string $currency,
        private ?string $id = null
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
