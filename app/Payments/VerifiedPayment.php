<?php

namespace App\Payments;

final readonly class VerifiedPayment
{
    public function __construct(
        public string $providerReference,
        public bool $successful,
    ) {}
}
