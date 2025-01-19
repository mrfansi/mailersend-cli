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
 * Email Personalization Data Transfer Object
 *
 * This class represents the data structure for email personalization.
 * It ensures type safety and data validation.
 */
readonly class EmailPersonalizationData
{
    /**
     * Create a new EmailPersonalizationData instance
     *
     * This constructor initializes a new instance of the EmailPersonalizationData class,
     * which represents personalization data for a specific email address.
     *
     * @param  string  $email  The email address to apply personalization to
     * @param  array  $data  Key-value pairs for personalization variables
     */
    public function __construct(
        public string $email,
        public array $data,
    ) {
        // Initialize the EmailPersonalizationData object with provided parameters
    }

    /**
     * Convert the DTO to an array for API requests
     *
     * @return array<string, string|array>
     */
    public function toArray(): array
    {
        return [
            'email' => $this->email,
            'data' => $this->data,
        ];
    }
}
