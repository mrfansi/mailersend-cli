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

namespace App\Contracts;

use App\Data\EmailData;
use App\Data\EmailResponse;
use Illuminate\Support\Collection;

/**
 * Interface for Email operations
 *
 * This interface defines the standard operations for sending emails through the Mailersend API.
 */
interface EmailRepositoryInterface
{
    /**
     * Send a single email
     *
     * @param  EmailData  $emailData  Data for sending the email
     * @return EmailResponse Response from the API
     */
    public function send(EmailData $emailData): EmailResponse;

    /**
     * Send multiple emails in bulk
     *
     * @param  Collection  $emailDataCollection  Collection of EmailData objects
     * @return Collection Collection of EmailResponse objects
     */
    public function bulkSend(Collection $emailDataCollection): Collection;
}
