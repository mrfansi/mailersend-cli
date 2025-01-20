<?php

/*
 * Copyright (c) 2025 Muhammad Irfan.
 *  All rights reserved.
 *
 *  This project is created and maintained by Muhammad Irfan. Redistribution or modification
 *  of this code is permitted only under the terms specified in the license.
 *
 *  @author    Muhammad Irfan <mrfansi@outlook.com>
 *  @license    MIT
 */

namespace App\Data;

/**
 * Email Data Transfer Object
 *
 * This class represents the data structure for sending an email through the Mailersend API.
 * It ensures type safety and data validation.
 */
readonly class EmailData
{
    /**
     * Create a new EmailData instance
     *
     * This constructor initializes a new instance of the EmailData class,
     * which represents all the data needed to send an email.
     *
     * @param  EmailAddressData|null  $from  Sender information (required if no template_id with default sender)
     * @param  EmailAddressData[]  $to  List of recipients (max 50)
     * @param  EmailAddressData[]|null  $cc  List of CC recipients (max 10)
     * @param  EmailAddressData[]|null  $bcc  List of BCC recipients (max 10)
     * @param  EmailAddressData|null  $reply_to  Reply-to information
     * @param  string|null  $subject  Email subject (required if no template_id with default subject)
     * @param  string|null  $text  Plain text content (required if no html or template_id)
     * @param  string|null  $html  HTML content (required if no text or template_id)
     * @param  EmailAttachmentData[]|null  $attachments  List of attachments
     * @param  string|null  $template_id  Template ID (required if no text or html)
     * @param  string[]|null  $tags  List of tags (max 5)
     * @param  EmailPersonalizationData[]|null  $personalization  List of personalization data
     * @param  bool|null  $precedence_bulk  Override domain's advanced settings
     * @param  int|null  $send_at  Unix timestamp for scheduled sending
     * @param  string|null  $in_reply_to  Message-ID being replied to
     * @param  string[]|null  $references  List of referenced Message-IDs
     * @param  EmailSettingsData|null  $settings  Email tracking settings
     * @param  EmailHeaderData[]|null  $headers  Custom headers (Professional and Enterprise only)
     */
    public function __construct(
        public ?EmailAddressData $from,
        /** @var EmailAddressData[] */
        public array $to,
        public ?array $cc = null,
        public ?array $bcc = null,
        public ?EmailAddressData $reply_to = null,
        public ?string $subject = null,
        public ?string $text = null,
        public ?string $html = null,
        public ?array $attachments = null,
        public ?string $template_id = null,
        public ?array $tags = null,
        public ?array $personalization = null,
        public ?bool $precedence_bulk = null,
        public ?int $send_at = null,
        public ?string $in_reply_to = null,
        public ?array $references = null,
        public ?EmailSettingsData $settings = null,
        public ?array $headers = null,
    ) {
        // Initialize the EmailData object with provided parameters
    }

    /**
     * Convert the DTO to an array for API requests
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $data = [
            'to' => array_map(fn (EmailAddressData $recipient) => $recipient->toArray(), $this->to),
        ];

        if ($this->from !== null) {
            $data['from'] = $this->from->toArray();
        }

        if ($this->cc !== null) {
            $data['cc'] = array_map(fn (EmailAddressData $recipient) => $recipient->toArray(), $this->cc);
        }

        if ($this->bcc !== null) {
            $data['bcc'] = array_map(fn (EmailAddressData $recipient) => $recipient->toArray(), $this->bcc);
        }

        if ($this->reply_to !== null) {
            $data['reply_to'] = $this->reply_to->toArray();
        }

        if ($this->subject !== null) {
            $data['subject'] = $this->subject;
        }

        if ($this->text !== null) {
            $data['text'] = $this->text;
        }

        if ($this->html !== null) {
            $data['html'] = $this->html;
        }

        if ($this->attachments !== null) {
            $data['attachments'] = array_map(fn (EmailAttachmentData $attachment) => $attachment->toArray(), $this->attachments);
        }

        if ($this->template_id !== null) {
            $data['template_id'] = $this->template_id;
        }

        if ($this->tags !== null) {
            $data['tags'] = $this->tags;
        }

        if ($this->personalization !== null) {
            $data['personalization'] = array_map(fn (EmailPersonalizationData $personalization) => $personalization->toArray(), $this->personalization);
        }

        if ($this->precedence_bulk !== null) {
            $data['precedence_bulk'] = $this->precedence_bulk;
        }

        if ($this->send_at !== null) {
            $data['send_at'] = $this->send_at;
        }

        if ($this->in_reply_to !== null) {
            $data['in_reply_to'] = $this->in_reply_to;
        }

        if ($this->references !== null) {
            $data['references'] = $this->references;
        }

        if ($this->settings !== null) {
            $data['settings'] = $this->settings->toArray();
        }

        if ($this->headers !== null) {
            $data['headers'] = array_map(fn (EmailHeaderData $header) => $header->toArray(), $this->headers);
        }

        return $data;
    }
}
