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

use App\Data\DomainResponse;
use App\Data\SenderResponse;
use App\Data\SmtpUserResponse;
use App\Data\TemplateResponse;
use App\Data\TokenResponse;
use Illuminate\Support\Collection;
use RuntimeException;

use function Laravel\Prompts\search;
use function Laravel\Prompts\spin;

trait HasHelpers
{
    /**
     * Get domain ID from option or search
     *
     * @throws RuntimeException When domain ID cannot be found
     */
    private function getDomainID(): string
    {
        /** @var Collection<DomainResponse> $domains */
        $domains = spin(
            fn () => $this->mailersend->domain()->all(),
            'Fetching domains...'
        );

        if ($id = $this->option('id')) {
            $domain = $domains->firstWhere('id', (int) $id);
            if ($domain) {
                return $domain->id;
            }
        }

        $id = search(
            'Search domain by name',
            fn (string $value) => strlen($value) > 0
                ? $domains->filter(
                    fn (DomainResponse $domain) => str_contains(
                        strtolower($domain->name),
                        strtolower($value)
                    )
                )->pluck('name', 'id')->toArray()
                : []
        );

        if (! $id) {
            throw new RuntimeException('Domain not found');
        }

        return $id;
    }

    private function setDomainID(): void
    {
        $this->domainId = $this->getDomainID();
    }

    /**
     * Get sender ID from option or search
     *
     * @throws RuntimeException When sender ID cannot be found
     */
    private function getSenderID(): string
    {
        /** @var Collection<SenderResponse> $senders */
        $senders = spin(
            fn () => $this->mailersend->sender()->all(),
            'Fetching senders...'
        );

        if ($id = $this->option('id')) {
            $sender = $senders->firstWhere('id', $id);
            if ($sender) {
                return $sender->id;
            }
        }

        $id = search(
            'Search sender by email',
            fn (string $value) => strlen($value) > 0
                ? $senders->filter(
                    fn (SenderResponse $sender) => str_contains(
                        strtolower($sender->email),
                        strtolower($value)
                    )
                )->pluck('email', 'id')->toArray()
                : []
        );

        if (! $id) {
            throw new RuntimeException('Sender not found');
        }

        return $id;
    }

    /**
     * Get token ID from option or search
     *
     * @throws RuntimeException When token ID cannot be found
     */
    private function getTokenID(): string
    {
        /** @var Collection<TokenResponse> $tokens */
        $tokens = spin(
            fn () => $this->mailersend->token()->all(),
            'Fetching tokens...'
        );

        if ($id = $this->option('id')) {
            $token = $tokens->firstWhere('id', $id);
            if ($token) {
                return $token->id;
            }
        }

        $id = search(
            'Search token by name',
            fn (string $value) => strlen($value) > 0
                ? $tokens->filter(
                    fn (TokenResponse $token) => str_contains(
                        strtolower($token->name),
                        strtolower($value)
                    )
                )->pluck('name', 'id')->toArray()
                : []
        );

        if (! $id) {
            throw new RuntimeException('Token not found');
        }

        return $id;
    }

    /**
     * Get template ID from option or search
     *
     * @throws RuntimeException When template ID cannot be found
     */
    private function getTemplateID(): string
    {
        /** @var Collection<TemplateResponse> $templates */
        $templates = spin(
            fn () => $this->mailersend->template()->all(),
            'Fetching templates...'
        );

        if ($id = $this->option('id')) {
            $template = $templates->firstWhere('id', $id);
            if ($template) {
                return $template->id;
            }
        }

        $id = search(
            'Search template by name',
            fn (string $value) => strlen($value) > 0
                ? $templates->filter(
                    fn (TemplateResponse $template) => str_contains(
                        strtolower($template->name),
                        strtolower($value)
                    )
                )->pluck('name', 'id')->toArray()
                : []
        );

        if (! $id) {
            throw new RuntimeException('Template not found');
        }

        return $id;
    }

    /**
     * Get smtp-user ID from option or search
     *
     * @throws RuntimeException When smtpUser ID cannot be found
     */
    private function getSmtpUserID(): string
    {
        /** @var Collection<SmtpUserResponse> $smtpUsers */
        $smtpUsers = spin(
            fn () => $this->mailersend->smtpUser($this->domainId)->all(),
            'Fetching smtp users...'
        );

        if ($id = $this->option('id')) {
            $smtpUser = $smtpUsers->firstWhere('id', $id);
            if ($smtpUser) {
                return $smtpUser->id;
            }
        }

        $id = search(
            'Search smtp user by name',
            fn (string $value) => strlen($value) > 0
                ? $smtpUsers->filter(
                    fn (SmtpUserResponse $smtpUser) => str_contains(
                        strtolower($smtpUser->name),
                        strtolower($value)
                    )
                )->pluck('name', 'id')->toArray()
                : []
        );

        if (! $id) {
            throw new RuntimeException('Smtp user not found');
        }

        return $id;
    }
}
