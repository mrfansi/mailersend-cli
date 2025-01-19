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
readonly class TokenCreateResponse
{
    /**
     * Create a new TokenCreateResponse instance
     *
     * This constructor initializes a new instance of the TokenCreateResponse class
     * which represents the response data structure from the Mailersend API
     * for token-related operations.
     *
     * @param  string  $id  The unique identifier of the token
     * @param  string  $accessToken  The access token associated with the token
     * @param  string  $name  The name of the token
     * @param  string  $status  The status of the token (e.g., unpause)
     * @param  string  $created_at  The creation timestamp of the token
     * @param  array  $scopes  List of permission scopes assigned to the token
     * @param  bool  $has_full  Whether the token has full permissions
     * @param  DomainResponse  $domain  The domain associated with the token
     * @param  string  $preview  The preview for the token
     * @param  ?string  $expires_at  The expiration timestamp of the token
     */
    public function __construct(
        public string $id,
        public string $accessToken,
        public string $name,
        public string $status,
        public string $created_at,
        public array $scopes,
        public bool $has_full,
        public DomainResponse $domain,
        public string $preview,
        public ?string $expires_at = null
    ) {
        // Initialize the TokenCreateResponse object with provided parameters
    }

    /**
     * Create a TokenCreateResponse instance from an array
     *
     * @param  array  $data  Token data from API response
     * @return self New TokenCreateResponse instance
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            accessToken: $data['access_token'],
            name: $data['name'],
            status: $data['status'],
            created_at: $data['created_at'],
            scopes: $data['scopes'],
            has_full: $data['has_full'],
            domain: DomainResponse::fromArray($data['domain']),
            preview: $data['preview'],
            expires_at: $data['expires_at']
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
            'access_token' => $this->accessToken,
            'name' => $this->name,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'scopes' => $this->scopes,
            'has_full' => $this->has_full,
            'domain' => $this->domain->toArray(),
            'preview' => $this->preview,
            'expires_at' => $this->expires_at,
        ];
    }
}
