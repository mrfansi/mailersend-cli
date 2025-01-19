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

use Illuminate\Contracts\Support\Arrayable;
use JsonSerializable;

/**
 * Data Transfer Object for Email API Response
 *
 * This class represents the response received from the MailerSend API
 * after sending an email. It includes the message ID and status information.
 */
class EmailResponse implements Arrayable, JsonSerializable
{
    /**
     * The unique message ID from MailerSend
     */
    private string $messageId;

    /**
     * Constructor for EmailResponse
     *
     * @param  array  $data  Response data from the API
     */
    public function __construct(array $data)
    {
        $this->messageId = $data['message_id'] ?? '';
    }

    /**
     * Get the message ID
     */
    public function getMessageId(): string
    {
        return $this->messageId;
    }

    /**
     * Convert the object to JSON
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    /**
     * Convert the object to an array
     */
    public function toArray(): array
    {
        return [
            'message_id' => $this->messageId,
        ];
    }
}
