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

namespace App\Commands\Traits;

use InvalidArgumentException;
use RuntimeException;
use Throwable;

trait HasHandle
{
    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        try {
            $action = $this->argument('action');
            $this->dispatchAction($action);
        } catch (InvalidArgumentException $e) {
            $this->error('[INVALID_INPUT] '.$e->getMessage());
        } catch (RuntimeException $e) {
            $this->error('[API_ERROR] '.$e->getMessage());
        } catch (Throwable $e) {
            $this->error('[UNEXPECTED_ERROR] '.$e->getMessage());
            if (app()->environment('local')) {
                $this->error($e->getTraceAsString());
            }
        }
    }
}
