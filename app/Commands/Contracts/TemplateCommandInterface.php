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

namespace App\Commands\Contracts;

interface CommandInterface
{
    /**
     * List all items related to this command
     */
    public function list(): void;

    /**
     * Show the details of the selected item
     */
    public function show(): void;

    /**
     * Create a new item
     */
    public function new(): void;

    /**
     * Edit the selected item
     */
    public function edit(): void;

    /**
     * Delete the selected item
     */
    public function delete(): void;
}
