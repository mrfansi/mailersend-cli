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

namespace App\Mailersend;

use App\Contracts\EmailRepositoryInterface;
use App\Data\EmailData;
use App\Data\EmailResponse;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
use RuntimeException;
use Throwable;

/**
 * Email API Client
 *
 * This class handles all email-related operations with the Mailersend API.
 * It implements the EmailRepositoryInterface for standardized email operations.
 */
class Email implements EmailRepositoryInterface
{
    /**
     * HTTP client instance for making API requests
     */
    private PendingRequest $client;

    /**
     * Constructor for Email class
     *
     * @param  PendingRequest  $client  HTTP client for making API requests
     */
    public function __construct(PendingRequest $client)
    {
        $this->client = $client;
    }

    /**
     * Bulk send emails using the Mailersend API
     *
     * @param  Collection  $emailDataCollection  Collection of EmailData objects
     * @return Collection Collection of EmailResponse objects
     *
     * @throws RuntimeException If there's an error processing the request
     */
    public function bulkSend(Collection $emailDataCollection): Collection
    {
        return $emailDataCollection->map(function (EmailData $emailData) {
            return $this->send($emailData);
        });
    }

    /**
     * Send an email using the Mailersend API
     *
     * @param  EmailData  $emailData  Data for sending the email
     * @return EmailResponse Response from the API
     *
     * @throws ConnectionException If there's a connection error
     * @throws RuntimeException If there's an error processing the request
     * @throws InvalidArgumentException If the input data is invalid
     * @throws Throwable
     */
    public function send(EmailData $emailData): EmailResponse
    {
        try {
            $response = $this->client->post('/email', $emailData->toArray());

            if ($response->successful()) {
                $messageId = $response->header('x-message-id');

                return new EmailResponse(['message_id' => $messageId]);
            }

            throw new RuntimeException(
                'Failed to send email: '.$response->body(),
                $response->status()
            );
        } catch (ConnectionException $e) {
            Log::error('Connection error while sending email', [
                'error' => $e->getMessage(),
                'data' => $emailData->toArray(),
            ]);
            throw $e;
        } catch (Throwable $e) {
            Log::error('Error sending email', [
                'error' => $e->getMessage(),
                'data' => $emailData->toArray(),
            ]);
            throw $e;
        }
    }
}
