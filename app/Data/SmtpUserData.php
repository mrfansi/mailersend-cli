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
 * Data Transfer Object for SMTP User creation/update
 *
 * This class represents the data structure for creating or updating SMTP users
 * in the Mailersend API.
 */
class SmtpUserData
{
    /**
     * Create a new SMTP user data instance
     *
     * @param  string  $name  Name of the SMTP user
     * @param  string|null  $domain_id  Domain ID for the SMTP user
     * @param  bool|null  $enabled  Whether the SMTP user is enabled
     */
    public function __construct(
        public string $name,
        public ?string $domain_id = null,
        public ?bool $enabled = true,
    ) {}

    /**
     * Convert the data object to an array
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return array_filter([
            'name' => $this->name,
            'domain_id' => $this->domain_id,
            'enabled' => $this->enabled,
        ], fn ($value) => ! is_null($value));
    }
}
