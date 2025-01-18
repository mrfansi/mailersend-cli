<?php

namespace App\Data;

/**
 * Domain DNS Response Data Transfer Object
 *
 * This class represents the response data structure from the Mailersend API
 * for domain DNS-related operations.
 */
readonly class DomainDnsResponse
{
    /**
     * Create a new DomainDnsResponse instance
     *
     * This constructor initializes a new instance of the DomainDnsResponse class
     * which represents the response data structure from the Mailersend API
     * for domain DNS-related operations.
     *
     * @param  string  $id  The unique identifier of the DNS record set
     * @param  DomainDnsRecordData  $spf  SPF record configuration
     * @param  DomainDnsRecordData  $dkim  DKIM record configuration
     * @param  DomainDnsRecordData  $return_path  Return path record configuration
     * @param  DomainDnsRecordData  $custom_tracking  Custom tracking record configuration
     * @param  DomainDnsRecordData  $inbound_routing  Inbound routing record configuration
     */
    public function __construct(
        public string $id,
        public DomainDnsRecordData $spf,
        public DomainDnsRecordData $dkim,
        public DomainDnsRecordData $return_path,
        public DomainDnsRecordData $custom_tracking,
        public DomainDnsRecordData $inbound_routing,
    ) {
        // Initialize the DomainDnsResponse object with provided parameters
    }

    /**
     * Create a DomainDnsResponse instance from an array
     *
     * @param  array  $data  DNS response data from API
     * @return self New DomainDnsResponse instance
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            spf: new DomainDnsRecordData(
                hostname: $data['spf']['hostname'],
                type: $data['spf']['type'],
                value: $data['spf']['value'],
            ),
            dkim: new DomainDnsRecordData(
                hostname: $data['dkim']['hostname'],
                type: $data['dkim']['type'],
                value: $data['dkim']['value'],
            ),
            return_path: new DomainDnsRecordData(
                hostname: $data['return_path']['hostname'],
                type: $data['return_path']['type'],
                value: $data['return_path']['value'],
            ),
            custom_tracking: new DomainDnsRecordData(
                hostname: $data['custom_tracking']['hostname'],
                type: $data['custom_tracking']['type'],
                value: $data['custom_tracking']['value'],
            ),
            inbound_routing: new DomainDnsRecordData(
                hostname: $data['inbound_routing']['hostname'],
                type: $data['inbound_routing']['type'],
                value: $data['inbound_routing']['value'],
                priority: $data['inbound_routing']['priority'],
            ),
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
            'spf' => $this->spf->toArray(),
            'dkim' => $this->dkim->toArray(),
            'return_path' => $this->return_path->toArray(),
            'custom_tracking' => $this->custom_tracking->toArray(),
            'inbound_routing' => $this->inbound_routing->toArray(),
        ];
    }
}
