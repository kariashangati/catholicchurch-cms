<?php

namespace App\Exceptions\Communication;

use RuntimeException;

class BeemRequestException extends RuntimeException
{
    public static function missingConfiguration(string $field): self
    {
        return new self("Missing Beem SMS configuration value: {$field}.");
    }

    public static function transportError(string $message): self
    {
        return new self("Beem SMS transport error: {$message}");
    }

    public static function invalidResponse(string $message = 'Invalid response returned from Beem SMS API.'): self
    {
        return new self($message);
    }
}
