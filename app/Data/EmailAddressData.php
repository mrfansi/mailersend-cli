<?php

/*
 * Copyright (c) 2025 Muhammad Irfan.
 *  All rights reserved.
 *
 *  This project is created and maintained by Muhammad Irfan. Redistribution or modification
 *  of this code is permitted only under the terms specified in the license.
 *
 *  @author    Muhammad Irfan <mrfansi@outlook.com>
 *  @license    MIT
 */

namespace App\Data;

/**
 * Email Address Data Transfer Object
 *
 * This class represents the data structure for email address with name.
 * It ensures type safety and data validation.
 */
readonly class EmailAddressData
{
    /**
     * Create a new EmailAddressData instance
     *
     * This constructor initializes a new instance of the EmailAddressData class,
     * which represents an email address with optional name.
     *
     * @param  string  $email  The email address
     * @param  string|null  $name  The name associated with the email address
     */
    public function __construct(
        public string $email,
        public ?string $name = null,
    ) {
        // Initialize the EmailAddressData object with provided parameters
    }

    /**
     * Convert the DTO to an array for API requests
     *
     * @return array<string, string|null>
     */
    public function toArray(): array
    {
        return array_filter([
            'email' => $this->email,
            'name' => $this->name,
        ], fn ($value) => $value !== null);
    }
}
