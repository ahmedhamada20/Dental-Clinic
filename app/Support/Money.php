<?php

namespace App\Support;

use InvalidArgumentException;

class Money
{
    private int $amount; // Amount in cents

    /**
     * Create money instance from amount in major units (e.g., dollars)
     */
    public function __construct(int|float $amount)
    {
        // Convert to cents and round
        $this->amount = (int) round($amount * 100);
    }

    /**
     * Create money instance from cents
     */
    public static function fromCents(int $amount): self
    {
        $instance = new self(0);
        $instance->amount = $amount;

        return $instance;
    }

    /**
     * Get amount in cents
     */
    public function cents(): int
    {
        return $this->amount;
    }

    /**
     * Get amount in major units
     */
    public function amount(): float
    {
        return round($this->amount / 100, 2);
    }

    /**
     * Get formatted amount for display (e.g., 10.50)
     */
    public function format(): string
    {
        return number_format($this->amount() / 1, 2, '.', '');
    }

    /**
     * Get formatted amount with currency symbol
     */
    public function formatWithCurrency(string $currency = '$'): string
    {
        return $currency . $this->format();
    }

    /**
     * Add money
     */
    public function add(Money|int|float $amount): self
    {
        if ($amount instanceof Money) {
            $this->amount += $amount->cents();
        } else {
            $this->amount += (int) round($amount * 100);
        }

        return $this;
    }

    /**
     * Subtract money
     */
    public function subtract(Money|int|float $amount): self
    {
        if ($amount instanceof Money) {
            $this->amount -= $amount->cents();
        } else {
            $this->amount -= (int) round($amount * 100);
        }

        return $this;
    }

    /**
     * Multiply money
     */
    public function multiply(int|float $multiplier): self
    {
        $this->amount = (int) round($this->amount * $multiplier);

        return $this;
    }

    /**
     * Divide money
     */
    public function divide(int|float $divisor): self
    {
        if ($divisor == 0) {
            throw new InvalidArgumentException('Division by zero');
        }

        $this->amount = (int) round($this->amount / $divisor);

        return $this;
    }

    /**
     * Check if amount equals another amount
     */
    public function equals(Money|int|float $amount): bool
    {
        if ($amount instanceof Money) {
            return $this->amount === $amount->cents();
        }

        return $this->amount === (int) round($amount * 100);
    }

    /**
     * Check if amount is greater than another amount
     */
    public function greaterThan(Money|int|float $amount): bool
    {
        if ($amount instanceof Money) {
            return $this->amount > $amount->cents();
        }

        return $this->amount > (int) round($amount * 100);
    }

    /**
     * Check if amount is less than another amount
     */
    public function lessThan(Money|int|float $amount): bool
    {
        if ($amount instanceof Money) {
            return $this->amount < $amount->cents();
        }

        return $this->amount < (int) round($amount * 100);
    }

    /**
     * Check if amount is zero
     */
    public function isZero(): bool
    {
        return $this->amount === 0;
    }

    /**
     * Check if amount is positive
     */
    public function isPositive(): bool
    {
        return $this->amount > 0;
    }

    /**
     * Check if amount is negative
     */
    public function isNegative(): bool
    {
        return $this->amount < 0;
    }

    /**
     * Get absolute amount
     */
    public function abs(): self
    {
        $this->amount = abs($this->amount);

        return $this;
    }

    /**
     * Get percentage of amount
     */
    public function percentage(int|float $percent): self
    {
        $this->amount = (int) round($this->amount * ($percent / 100));

        return $this;
    }

    /**
     * String representation
     */
    public function __toString(): string
    {
        return $this->format();
    }
}

