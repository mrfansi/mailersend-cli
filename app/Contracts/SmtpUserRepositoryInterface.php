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

use App\Data\SmtpUserData;
use App\Data\SmtpUserResponse;
use Illuminate\Support\Collection;

/**
 * Interface for SmtpUser Repository
 *
 * This interface defines the contract for interacting with Mailersend SmtpUsers.
 */
interface SmtpUserRepositoryInterface
{
    /**
     * Get all senders with pagination
     *
     * @param  int  $limit  Items per page
     * @param  int  $page  Pagination offset
     * @return Collection<SmtpUserResponse>
     */
    public function all(int $limit = 10, int $page = 1): Collection;

    /**
     * Find sender by ID
     *
     * @param  string  $id  SmtpUser ID
     */
    public function find(string $id): SmtpUserResponse;

    /**
     * Create new sender
     *
     * @param  SmtpUserData  $data  SmtpUser data
     */
    public function create(SmtpUserData $data): SmtpUserResponse;

    /**
     * Update sender
     *
     * @param  string  $id  SmtpUser ID
     * @param  SmtpUserData  $data  SmtpUser data
     */
    public function update(string $id, SmtpUserData $data): SmtpUserResponse;

    /**
     * Delete sender
     *
     * @param  string  $id  SmtpUser ID
     */
    public function delete(string $id): bool;
}
