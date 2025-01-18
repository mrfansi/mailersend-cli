<?php

namespace App\Data;

/**
 * Domain Setting Data Transfer Object
 *
 * This class represents the data structure for domain settings configuration
 * in the Mailersend API.
 */
readonly class DomainSettingResponse
{
    /**
     * Create a new DomainSettingResponse instance
     *
     * This constructor initializes a new instance of the DomainSettingResponse class
     * which represents the domain settings configuration data structure.
     *
     * @param  bool  $send_paused  Whether sending is paused for this domain
     * @param  bool  $track_clicks  Whether click tracking is enabled
     * @param  bool  $track_opens  Whether open tracking is enabled
     * @param  bool  $track_unsubscribe  Whether unsubscribe tracking is enabled
     * @param  string  $track_unsubscribe_html  HTML template for unsubscribe link
     * @param  bool  $track_unsubscribe_html_enabled  Whether HTML unsubscribe template is enabled
     * @param  string  $track_unsubscribe_plain  Plain text template for unsubscribe link
     * @param  bool  $track_unsubscribe_plain_enabled  Whether plain text unsubscribe template is enabled
     * @param  bool  $track_content  Whether content tracking is enabled
     * @param  bool  $custom_tracking_enabled  Whether custom tracking is enabled
     * @param  string  $custom_tracking_subdomain  Custom tracking subdomain
     * @param  string  $return_path_subdomain  Return path subdomain
     * @param  bool  $inbound_routing_enabled  Whether inbound routing is enabled
     * @param  string  $inbound_routing_subdomain  Inbound routing subdomain
     * @param  bool  $precedence_bulk  Whether bulk precedence is enabled
     * @param  bool  $ignore_duplicated_recipients  Whether to ignore duplicated recipients
     * @param  bool  $show_dmarc  Whether to show DMARC record
     */
    public function __construct(
        public bool $send_paused,
        public bool $track_clicks,
        public bool $track_opens,
        public bool $track_unsubscribe,
        public string $track_unsubscribe_html,
        public bool $track_unsubscribe_html_enabled,
        public string $track_unsubscribe_plain,
        public bool $track_unsubscribe_plain_enabled,
        public bool $track_content,
        public bool $custom_tracking_enabled,
        public string $custom_tracking_subdomain,
        public string $return_path_subdomain,
        public bool $inbound_routing_enabled,
        public string $inbound_routing_subdomain,
        public bool $precedence_bulk,
        public bool $ignore_duplicated_recipients,
        public bool $show_dmarc,
    ) {
        // Initialize the DomainSettingResponse object with provided parameters
    }

    /**
     * Create a DomainSettingResponse instance from an array
     *
     * @param  array  $data  Domain data from API response
     * @return self New DomainSettingResponse instance
     */
    public static function fromArray(array $data): self
    {
        return new self(
            send_paused: $data['send_paused'],
            track_clicks: $data['track_clicks'],
            track_opens: $data['track_opens'],
            track_unsubscribe: $data['track_unsubscribe'],
            track_unsubscribe_html: $data['track_unsubscribe_html'],
            track_unsubscribe_html_enabled: $data['track_unsubscribe_html_enabled'],
            track_unsubscribe_plain: $data['track_unsubscribe_plain'],
            track_unsubscribe_plain_enabled: $data['track_unsubscribe_plain_enabled'],
            track_content: $data['track_content'],
            custom_tracking_enabled: $data['custom_tracking_enabled'],
            custom_tracking_subdomain: $data['custom_tracking_subdomain'],
            return_path_subdomain: $data['return_path_subdomain'],
            inbound_routing_enabled: $data['inbound_routing_enabled'],
            inbound_routing_subdomain: $data['inbound_routing_subdomain'],
            precedence_bulk: $data['precedence_bulk'],
            ignore_duplicated_recipients: $data['ignore_duplicated_recipients'],
            show_dmarc: $data['show_dmarc'],
        );
    }

    /**
     * Convert the DTO to an array
     *
     * @return array<string, bool|string>
     */
    public function toArray(): array
    {
        return [
            'send_paused' => $this->send_paused,
            'track_clicks' => $this->track_clicks,
            'track_opens' => $this->track_opens,
            'track_unsubscribe' => $this->track_unsubscribe,
            'track_unsubscribe_html' => $this->track_unsubscribe_html,
            'track_unsubscribe_html_enabled' => $this->track_unsubscribe_html_enabled,
            'track_unsubscribe_plain' => $this->track_unsubscribe_plain,
            'track_unsubscribe_plain_enabled' => $this->track_unsubscribe_plain_enabled,
            'track_content' => $this->track_content,
            'custom_tracking_enabled' => $this->custom_tracking_enabled,
            'custom_tracking_subdomain' => $this->custom_tracking_subdomain,
            'return_path_subdomain' => $this->return_path_subdomain,
            'inbound_routing_enabled' => $this->inbound_routing_enabled,
            'inbound_routing_subdomain' => $this->inbound_routing_subdomain,
            'precedence_bulk' => $this->precedence_bulk,
            'ignore_duplicated_recipients' => $this->ignore_duplicated_recipients,
            'show_dmarc' => $this->show_dmarc,
        ];
    }
}
