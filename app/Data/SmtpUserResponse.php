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
 * Data Transfer Object for SMTP User API responses
 *
 * This class represents the response structure when interacting with
 * SMTP users in the Mailersend API.
 */
class SmtpUserResponse
{
    /**
     * Create a new SMTP user response instance
     *
     * @param  string  $id  Unique identifier for the SMTP user
     * @param  string  $name  Name of the SMTP user
     * @param  string  $username  SMTP username
     * @param  string  $domain  Associated domain ID
     * @param  bool  $enabled  Status of the SMTP user
     * @param  string  $server  SMTP server address
     * @param  int  $port  SMTP server port
     * @param  string|null  $password  Generated password (only available on creation)
     * @param  string|null  $accessed_at  Last access timestamp
     */
    public function __construct(
        public string $id,
        public string $name,
        public string $username,
        public string $domain,
        public bool $enabled,
        public string $server,
        public int $port,
        public ?string $password = null,
        public ?string $accessed_at = null,
    ) {}

    /**
     * Create an instance from API response array
     *
     * @param  array<string, mixed>  $data  API response data
     */
    public static function fromArray(array $data): static
    {
        return new static(
            id: $data['id'],
            name: $data['name'],
            username: $data['username'],
            domain: $data['domain'],
            enabled: $data['enabled'],
            server: $data['server'],
            port: $data['port'],
            password: $data['password'] ?? null,
            accessed_at: $data['accessed_at'] ?? null,
        );
    }

    /**
     * Convert the response to an array
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return array_filter([
            'id' => $this->id,
            'name' => $this->name,
            'username' => $this->username,
            '$domain' => $this->domain,
            'enabled' => $this->enabled,
            'server' => $this->server,
            'port' => $this->port,
            'password' => $this->password,
            'accessed_at' => $this->accessed_at,
        ], fn ($value) => ! is_null($value));
    }
}
