<?php

namespace App\Data;

/**
 * Server Response Data Transfer Object
 *
 * This class represents the response data structure from the Mailersend API
 * for server-related operations.
 */
readonly class SenderResponse
{
    /**
     * Create a new SenderResponse instance
     *
     * This constructor initializes a new instance of the SenderResponse class
     * which represents the response data structure from the Mailersend API
     * for server-related operations.
     *
     * @param  string  $id  The unique identifier of the sender
     * @param  string  $email  The email address of the sender
     * @param  string  $name  The name of the sender
     * @param  string|null  $reply_to_email  The reply-to email address
     * @param  string|null  $reply_to_name  The reply-to name
     * @param  bool  $is_verified  Indicates if the sender is verified
     * @param  int  $resends  The number of resend attempts
     */
    public function __construct(
        public string $id,
        public string $email,
        public string $name,
        public ?string $reply_to_email,
        public ?string $reply_to_name,
        public bool $is_verified,
        public int $resends,
    ) {
        // Initialize the SenderResponse object with provided parameters
    }

    /**
     * Create a SenderResponse instance from an array
     *
     * @param  array  $data  Server data from API response
     * @return self New SenderResponse instance
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            email: $data['email'],
            name: $data['name'],
            reply_to_email: $data['reply_to_email'],
            reply_to_name: $data['reply_to_name'],
            is_verified: $data['is_verified'],
            resends: $data['resends'],
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
            'email' => $this->email,
            'name' => $this->name,
            'reply_to_email' => $this->reply_to_email,
            'reply_to_name' => $this->reply_to_name,
            'is_verified' => $this->is_verified,
            'resends' => $this->resends,
        ];
    }
}
