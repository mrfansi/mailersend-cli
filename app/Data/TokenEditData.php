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
 * Token Edit Data Transfer Object
 *
 * This class represents the data structure for updating a token status.
 * It ensures type safety and data validation.
 */
readonly class TokenEditData
{
    /**
     * Create a new TokenEditData instance
     *
     * This constructor initializes a new instance of the TokenEditData class,
     * which represents the data structure for updating a token status.
     *
     * @param  string  $name  The name of the token
     * @param  string  $status  The status of the token (e.g., pause, unpause)
     */
    public function __construct(
        public string $name,
        public string $status,
    ) {
        // Initialize the TokenEditData object with provided parameters
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
            'status' => $this->status,
        ];
    }
}
