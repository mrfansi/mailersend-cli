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
 * Token Response Data Transfer Object
 *
 * This class represents the response data structure from the Mailersend API
 * for token-related operations.
 */
readonly class TokenResponse
{
    /**
     * Create a new TokenResponse instance
     *
     * This constructor initializes a new instance of the TokenResponse class
     * which represents the response data structure from the Mailersend API
     * for token-related operations.
     *
     * @param  string  $id  The unique identifier of the token
     * @param  string  $name  The name of the token
     * @param  string  $status  The status of the token (e.g., unpause)
     * @param  string  $created_at  The creation timestamp of the token
     * @param  array  $scopes  List of permission scopes assigned to the token
     */
    public function __construct(
        public string $id,
        public string $name,
        public string $status,
        public string $created_at,
        public array $scopes,
    ) {
        // Initialize the TokenResponse object with provided parameters
    }

    /**
     * Create a TokenResponse instance from an array
     *
     * @param  array  $data  Token data from API response
     * @return self New TokenResponse instance
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            name: $data['name'],
            status: $data['status'],
            created_at: $data['created_at'],
            scopes: $data['scopes'],
        );
    }

    /**
     * Convert the DTO to an array
     *
     * @return array<string, string|array>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'scopes' => $this->scopes,
        ];
    }
}
