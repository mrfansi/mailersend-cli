<?php

namespace App\Data;

/**
 * Domain Data Transfer Object
 *
 * This class represents the data structure for domain configuration
 * in the Mailersend API.
 */
readonly class DomainData
{
    /**
     * Create a new DomainData instance
     *
     * This constructor initializes a new instance of the DomainData class
     * which represents the domain configuration data structure.
     *
     * @param  string  $name  The domain name
     * @param  string|null  $return_path_subdomain  The return path subdomain
     * @param  string|null  $custom_tracking_subdomain  The custom tracking subdomain
     * @param  string|null  $inbound_routing_subdomain  The inbound routing subdomain
     */
    public function __construct(
        public string $name,
        public ?string $return_path_subdomain = null,
        public ?string $custom_tracking_subdomain = null,
        public ?string $inbound_routing_subdomain = null,
    ) {
        // Initialize the DomainData object with provided parameters
    }

    /**
     * Convert the DTO to an array
     *
     * @return array<string, string>
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'return_path_subdomain' => $this->return_path_subdomain,
            'custom_tracking_subdomain' => $this->custom_tracking_subdomain,
            'inbound_routing_subdomain' => $this->inbound_routing_subdomain,
        ];
    }
}
