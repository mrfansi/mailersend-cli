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
 * Domain Response Data Transfer Object
 *
 * This class represents the response data structure from the Mailersend API
 * for domain-related operations.
 */
readonly class DomainResponse
{
    /**
     * Create a new DomainResponse instance
     *
     * This constructor initializes a new instance of the DomainResponse class
     * which represents the response data structure from the Mailersend API
     * for domain-related operations.
     *
     * @param  string  $id  The unique identifier of the domain
     * @param  string  $name  The name of the domain
     * @param  bool  $dkim  DKIM status
     * @param  bool  $spf  SPF status
     * @param  bool  $tracking  Tracking status
     * @param  bool  $is_verified  Domain verification status
     * @param  bool  $is_cname_verified  CNAME verification status
     * @param  bool  $is_dns_active  DNS active status
     * @param  bool  $is_cname_active  CNAME active status
     * @param  bool  $is_tracking_allowed  Tracking permission status
     * @param  bool  $has_not_queued_messages  Status of enqueued messages
     * @param  int  $not_queued_messages_count  Count of enqueued messages
     * @param  DomainSettingResponse  $domain_settings  Domain settings configuration
     * @param  string  $created_at  Creation timestamp
     * @param  string  $updated_at  Last update timestamp
     */
    public function __construct(
        public string $id,
        public string $name,
        public bool $dkim,
        public bool $spf,
        public bool $tracking,
        public bool $is_verified,
        public bool $is_cname_verified,
        public bool $is_dns_active,
        public bool $is_cname_active,
        public bool $is_tracking_allowed,
        public bool $has_not_queued_messages,
        public int $not_queued_messages_count,
        public DomainSettingResponse $domain_settings,
        public string $created_at,
        public string $updated_at,
    ) {
        // Initialize the DomainResponse object with provided parameters
    }

    /**
     * Create a DomainResponse instance from an array
     *
     * @param  array  $data  Domain data from API response
     * @return self New DomainResponse instance
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            name: $data['name'],
            dkim: $data['dkim'],
            spf: $data['spf'],
            tracking: $data['tracking'],
            is_verified: $data['is_verified'],
            is_cname_verified: $data['is_cname_verified'],
            is_dns_active: $data['is_dns_active'],
            is_cname_active: $data['is_cname_active'],
            is_tracking_allowed: $data['is_tracking_allowed'],
            has_not_queued_messages: $data['has_not_queued_messages'],
            not_queued_messages_count: $data['not_queued_messages_count'],
            domain_settings: DomainSettingResponse::fromArray($data['domain_settings']),
            created_at: $data['created_at'],
            updated_at: $data['updated_at'],
        );
    }

    /**
     * Convert the DTO to an array
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'dkim' => $this->dkim,
            'spf' => $this->spf,
            'tracking' => $this->tracking,
            'is_verified' => $this->is_verified,
            'is_cname_verified' => $this->is_cname_verified,
            'is_dns_active' => $this->is_dns_active,
            'is_cname_active' => $this->is_cname_active,
            'is_tracking_allowed' => $this->is_tracking_allowed,
            'has_not_queued_messages' => $this->has_not_queued_messages,
            'not_queued_messages_count' => $this->not_queued_messages_count,
            'domain_settings' => $this->domain_settings,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
