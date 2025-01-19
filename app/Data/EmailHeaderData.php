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
 * Email Header Data Transfer Object
 *
 * This class represents the data structure for custom email headers.
 * It ensures type safety and data validation.
 */
readonly class EmailHeaderData
{
    /**
     * Create a new EmailHeaderData instance
     *
     * This constructor initializes a new instance of the EmailHeaderData class,
     * which represents a custom email header.
     *
     * @param  string  $name  The name of the header (must be alphanumeric with optional -)
     * @param  string  $value  The value of the header
     */
    public function __construct(
        public string $name,
        public string $value,
    ) {
        // Initialize the EmailHeaderData object with provided parameters
    }

    /**
     * Convert the DTO to an array for API requests
     *
     * @return array<string, string>
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'value' => $this->value,
        ];
    }
}
