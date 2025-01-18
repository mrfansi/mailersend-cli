<?php

namespace App\Data;

/**
 * Sender Data Transfer Object
 *
 * This class represents the data structure for creating or updating a server.
 * It ensures type safety and data validation.
 */
readonly class SenderData
{
    /**
     * Create a new SenderData instance
     *
     * This constructor initializes a new instance of the SenderData class,
     * which represents the data structure for creating or updating a server.
     *
     * @param  string  $domain_id  The domain identifier
     * @param  string  $email  The sender's email address
     * @param  string  $name  The sender's name
     * @param  string|null  $personal_note  An optional personal note
     * @param  string|null  $reply_to_name  The name for reply-to address
     * @param  string|null  $reply_to_email  The reply-to email address
     * @param  bool  $add_note  Whether to add a personal note
     */
    public function __construct(
        public string $domain_id,
        public string $email,
        public string $name,
        public ?string $personal_note = '',
        public ?string $reply_to_name = '',
        public ?string $reply_to_email = '',
        public bool $add_note = false,
    ) {
        // Initialize the SenderData object with provided parameters
    }

    /**
     * Convert the DTO to an array for API requests
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'domain_id' => $this->domain_id,
            'email' => $this->email,
            'name' => $this->name,
            'personal_note' => $this->personal_note,
            'reply_to_name' => $this->reply_to_name,
            'reply_to_email' => $this->reply_to_email,
            'add_note' => $this->add_note,
        ];
    }
}
