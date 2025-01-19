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
 * Token Data Transfer Object
 *
 * This class represents the data structure for creating or updating a token.
 * It ensures type safety and data validation.
 */
readonly class TokenData
{
    /**
     * Create a new TokenData instance
     *
     * This constructor initializes a new instance of the TokenData class,
     * which represents the data structure for creating or updating a token.
     *
     * @param  string  $name  The name of the token
     * @param  string  $domain_id  The domain identifier
     * @param  array  $scopes  List of permission scopes for the token
     */
    public function __construct(
        public string $name,
        public string $domain_id,
        public array $scopes,
    ) {
        // Initialize the TokenData object with provided parameters
    }

    /**
     * Convert the DTO to an array for API requests
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'domain_id' => $this->domain_id,
            'scopes' => $this->scopes,
        ];
    }
}
