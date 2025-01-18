<?php

namespace App\Data;

/**
 * Domain DNS Record Data Transfer Object
 *
 * This class represents the data structure for a DNS record
 * in the Mailersend API.
 */
readonly class DomainDnsRecordData
{
    /**
     * Create a new DomainDnsRecordData instance
     *
     * This constructor initializes a new instance of the DomainDnsRecordData class
     * which represents a single DNS record configuration.
     *
     * @param  string  $hostname  The hostname for the DNS record
     * @param  string  $type  The type of DNS record (e.g., TXT, CNAME, MX)
     * @param  string  $value  The value of the DNS record
     * @param  string|null  $priority  The priority of the DNS record (for MX records)
     */
    public function __construct(
        public string $hostname,
        public string $type,
        public string $value,
        public ?string $priority = null,
    ) {
        // Initialize the DomainDnsRecordData object with provided parameters
    }

    /**
     * Convert the DTO to an array
     *
     * @return array<string, string|null>
     */
    public function toArray(): array
    {
        return array_filter([
            'hostname' => $this->hostname,
            'type' => $this->type,
            'value' => $this->value,
            'priority' => $this->priority,
        ], fn ($value) => $value !== null);
    }
}
