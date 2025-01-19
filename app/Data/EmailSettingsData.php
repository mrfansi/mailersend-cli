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
 * Email Settings Data Transfer Object
 *
 * This class represents the data structure for email tracking settings.
 * It ensures type safety and data validation.
 */
readonly class EmailSettingsData
{
    /**
     * Create a new EmailSettingsData instance
     *
     * This constructor initializes a new instance of the EmailSettingsData class,
     * which represents email tracking settings.
     *
     * @param  bool|null  $track_clicks  Whether to track clicks
     * @param  bool|null  $track_opens  Whether to track opens
     * @param  bool|null  $track_content  Whether to track content
     */
    public function __construct(
        public ?bool $track_clicks = null,
        public ?bool $track_opens = null,
        public ?bool $track_content = null,
    ) {
        // Initialize the EmailSettingsData object with provided parameters
    }

    /**
     * Convert the DTO to an array for API requests
     *
     * @return array<string, bool>
     */
    public function toArray(): array
    {
        return array_filter([
            'track_clicks' => $this->track_clicks,
            'track_opens' => $this->track_opens,
            'track_content' => $this->track_content,
        ], fn ($value) => $value !== null);
    }
}
