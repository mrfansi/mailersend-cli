<?php

namespace App\Data;

/**
 * Domain Verification Response Data Transfer Object
 *
 * This class represents the response data structure from the Mailersend API
 * for domain verification operations.
 */
readonly class DomainVerificationResponse
{
    /**
     * Create a new DomainVerificationResponse instance
     *
     * This constructor initializes a new instance of the DomainVerificationResponse class
     * which represents the response data structure from the Mailersend API
     * for domain verification operations.
     *
     * @param  string  $message  Response message from the API
     * @param  DomainVerificationData  $data  Domain verification status data
     */
    public function __construct(
        public string $message,
        public DomainVerificationData $data,
    ) {
        // Initialize the DomainVerificationResponse object with provided parameters
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
            message: $data['message'],
            data: new DomainVerificationData(
                dkim: $data['data']['dkim'],
                spf: $data['data']['spf'],
                mx: $data['data']['mx'],
                tracking: $data['data']['tracking'],
                cname: $data['data']['cname'],
                rp_cname: $data['data']['rp_cname'],
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
            'message' => $this->message,
            'data' => $this->data->toArray(),
        ];
    }
}
