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
 * Email Attachment Data Transfer Object
 *
 * This class represents the data structure for email attachments.
 * It ensures type safety and data validation.
 */
readonly class EmailAttachmentData
{
    public const DISPOSITION_INLINE = 'inline';

    public const DISPOSITION_ATTACHMENT = 'attachment';

    /**
     * Create a new EmailAttachmentData instance
     *
     * This constructor initializes a new instance of the EmailAttachmentData class,
     * which represents an email attachment with its properties.
     *
     * @param  string  $content  Base64 encoded content of the attachment
     * @param  string  $disposition  Must be one of: inline, attachment
     * @param  string  $filename  The name of the file
     * @param  string|null  $id  Content ID for inline attachments
     */
    public function __construct(
        public string $content,
        public string $disposition,
        public string $filename,
        public ?string $id = null,
    ) {
        // Initialize the EmailAttachmentData object with provided parameters
    }

    /**
     * Convert the DTO to an array for API requests
     *
     * @return array<string, string|null>
     */
    public function toArray(): array
    {
        return array_filter([
            'content' => $this->content,
            'disposition' => $this->disposition,
            'filename' => $this->filename,
            'id' => $this->id,
        ], fn ($value) => $value !== null);
    }
}
