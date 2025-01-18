<?php

namespace App\Data;

/**
 * Domain Verification Data Transfer Object
 *
 * This class represents the data structure for domain verification status
 * in the Mailersend API.
 */
readonly class DomainVerificationData
{
    /**
     * Create a new DomainVerificationData instance
     *
     * This constructor initializes a new instance of the DomainVerificationData class
     * which represents the domain verification status data structure.
     *
     * @param  bool  $dkim  DKIM verification status
     * @param  bool  $spf  SPF verification status
     * @param  bool  $mx  MX record verification status
     * @param  bool  $tracking  Tracking record verification status
     * @param  bool  $cname  CNAME record verification status
     * @param  bool  $rp_cname  Return Path CNAME verification status
     */
    public function __construct(
        public bool $dkim,
        public bool $spf,
        public bool $mx,
        public bool $tracking,
        public bool $cname,
        public bool $rp_cname,
    ) {
        // Initialize the DomainVerificationData object with provided parameters
    }

    /**
     * Create a DomainVerificationResponse instance from an array
     *
     * @param  array  $data  Verification response data from API
     * @return self New DomainVerificationResponse instance
     */
    public static function fromArray(array $data): self
    {
        return new self(
            dkim: $data['data']['dkim'],
            spf: $data['data']['spf'],
            mx: $data['data']['mx'],
            tracking: $data['data']['tracking'],
            cname: $data['data']['cname'],
            rp_cname: $data['data']['rp_cname'],
        );
    }

    /**
     * Convert the DTO to an array
     *
     * @return array<string, bool>
     */
    public function toArray(): array
    {
        return [
            'dkim' => $this->dkim,
            'spf' => $this->spf,
            'mx' => $this->mx,
            'tracking' => $this->tracking,
            'cname' => $this->cname,
            'rp_cname' => $this->rp_cname,
        ];
    }
}
