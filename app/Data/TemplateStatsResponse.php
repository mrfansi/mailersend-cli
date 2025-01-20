<?php

namespace App\Data;

/**
 * Template Stats Response Data Transfer Object
 *
 * This class represents the response data structure from the Mailersend API
 * for template statistics operations.
 */
readonly class TemplateStatsResponse
{
    /**
     * Create a new TemplateStatsResponse instance
     *
     * This constructor initializes a new instance of the TemplateStatsResponse class
     * which represents the statistics data for a template.
     *
     * @param  int  $total  Total number of emails
     * @param  int  $queued  Number of queued emails
     * @param  int  $sent  Number of sent emails
     * @param  int  $rejected  Number of rejected emails
     * @param  int  $delivered  Number of delivered emails
     * @param  int|null  $opened  Number of opened emails
     * @param  string|null  $open_rate  Number of open rate emails
     * @param  int|null  $clicked  Number of clicked emails
     * @param  string|null  $click_rate  Number of click rate emails
     * @param  int|null  $unsubscribed  Number of unsubscribed emails
     * @param  string|null  $unsubscribe_rate  Number of unsubscribe rate emails
     * @param  int|null  $complained  Number of complained emails
     * @param  string|null  $complain_rate  Number of complain rate emails
     * @param  string|null  $last_email_sent_at  Timestamp of the last sent email
     */
    public function __construct(
        public int $total,
        public int $queued,
        public int $sent,
        public int $rejected,
        public int $delivered,
        public ?int $opened,
        public ?string $open_rate,
        public ?int $clicked,
        public ?string $click_rate,
        public ?int $unsubscribed,
        public ?string $unsubscribe_rate,
        public ?int $complained,
        public ?string $complain_rate,
        public ?string $last_email_sent_at,
    ) {
        // Initialize the TemplateStatsResponse object with provided parameters
    }

    /**
     * Create a TemplateStatsResponse instance from an array
     *
     * @param  array  $data  Template statistics data from API response
     * @return self New TemplateStatsResponse instance
     */
    public static function fromArray(array $data): self
    {
        return new self(
            total: $data['total'],
            queued: $data['queued'],
            sent: $data['sent'],
            rejected: $data['rejected'],
            delivered: $data['delivered'],
            opened: $data['opened'],
            open_rate: $data['open_rate'],
            clicked: $data['clicked'],
            click_rate: $data['click_rate'],
            unsubscribed: $data['unsubscribed'],
            unsubscribe_rate: $data['unsubscribe_rate'],
            complained: $data['complained'],
            complain_rate: $data['complain_rate'],
            last_email_sent_at: $data['last_email_sent_at'],
        );
    }

    /**
     * Convert the DTO to an array
     *
     * @return array<string, int|string|null>
     */
    public function toArray(): array
    {
        return [
            'total' => $this->total,
            'queued' => $this->queued,
            'sent' => $this->sent,
            'rejected' => $this->rejected,
            'delivered' => $this->delivered,
            'opened' => $this->opened,
            'open_rate' => $this->open_rate,
            'clicked' => $this->clicked,
            'click_rate' => $this->click_rate,
            'unsubscribed' => $this->unsubscribed,
            'unsubscribe_rate' => $this->unsubscribe_rate,
            'complained' => $this->complained,
            'complain_rate' => $this->complain_rate,
            'last_email_sent_at' => $this->last_email_sent_at,
        ];
    }
}
