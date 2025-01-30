<?php

namespace App\Data;

/**
 * Domain Setting Data Transfer Object
 *
 * This class represents the data structure for domain settings configuration
 * in the Mailersend API.
 */
readonly class DomainSettingData
{
    /**
     * Create a new DomainSettingData instance
     *
     * This constructor initializes a new instance of the DomainSettingData class
     * which represents the domain settings configuration data structure.
     *
     * @param  bool  $send_paused  Whether sending is paused
     * @param  bool  $track_clicks  Whether to track clicks
     * @param  bool  $track_opens  Whether to track opens
     * @param  bool  $track_unsubscribe  Whether to track unsubscribes
     * @param  string  $track_unsubscribe_html  HTML unsubscribe template
     * @param  string  $track_unsubscribe_plain  Plain text unsubscribe template
     * @param  bool  $track_content  Whether to track content
     * @param  bool  $custom_tracking_enabled  Whether custom tracking is enabled
     * @param  string  $custom_tracking_subdomain  Custom tracking subdomain
     * @param  bool  $precedence_bulk  Whether to mark as bulk mail
     * @param  bool  $ignore_duplicated_recipients  Whether to ignore duplicate recipients
     */
    public function __construct(
        public bool $send_paused,
        public bool $track_clicks,
        public bool $track_opens,
        public bool $track_unsubscribe,
        public string $track_unsubscribe_html,
        public string $track_unsubscribe_plain,
        public bool $track_content,
        public bool $custom_tracking_enabled,
        public string $custom_tracking_subdomain,
        public bool $precedence_bulk,
        public bool $ignore_duplicated_recipients,
    ) {
        // Initialize the DomainSettingData object with provided parameters
    }

    /**
     * Convert the DTO to an array
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'send_paused' => $this->send_paused,
            'track_clicks' => $this->track_clicks,
            'track_opens' => $this->track_opens,
            'track_unsubscribe' => $this->track_unsubscribe,
            'track_unsubscribe_html' => $this->track_unsubscribe_html,
            'track_unsubscribe_plain' => $this->track_unsubscribe_plain,
            'track_content' => $this->track_content,
            'custom_tracking_enabled' => $this->custom_tracking_enabled,
            'custom_tracking_subdomain' => $this->custom_tracking_subdomain,
            'precedence_bulk' => $this->precedence_bulk,
            'ignore_duplicated_recipients' => $this->ignore_duplicated_recipients,
        ];
    }
}
