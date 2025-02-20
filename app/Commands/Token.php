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

namespace App\Commands;

use App\Commands\Contracts\CommandInterface;
use App\Commands\Traits\HasHandle;
use App\Commands\Traits\HasHelpers;
use App\Contracts\MailersendFactoryInterface;
use App\Data\DomainResponse;
use App\Data\TokenCreateResponse;
use App\Data\TokenData;
use App\Data\TokenEditData;
use App\Data\TokenResponse;
use App\Generator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use InvalidArgumentException;
use LaravelZero\Framework\Commands\Command;
use RuntimeException;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\form;
use function Laravel\Prompts\spin;

/**
 * Token Command
 *
 * This command provides functionality to manage Mailersend tokens
 * through the command line interface.
 */
class Token extends Command implements CommandInterface
{
    use HasHandle;
    use HasHelpers;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'token
                         {action=list : Action to perform (list/show/new/edit/delete)}
                         {--id= : Token ID for edit/delete actions}
                         {--name= : Token name for new/edit actions}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Manage Mailersend tokens for your account';

    /**
     * Mailersend factory instance
     */
    private MailersendFactoryInterface $mailersend;

    /**
     * Generator instance for output formatting
     */
    private Generator $generator;

    /**
     * Create a new command instance.
     */
    public function __construct(MailersendFactoryInterface $mailersend, Generator $generator)
    {
        parent::__construct();

        $this->mailersend = $mailersend;
        $this->generator = $generator;
    }

    /**
     * List all tokens
     */
    public function list(): void
    {

        /** @var Collection<TokenResponse> $tokens */
        $tokens = spin(
            fn () => $this->mailersend->token()->all(),
            'Fetching tokens...'
        );

        // Mask sensitive data
        $tokens = $tokens->map(function (TokenResponse $token) {
            return [
                //                Str::upper('id') => $token->id,
                Str::headline('name') => $token->name,
                Str::headline('status') => $token->status,
                Str::headline('created_at') => Carbon::parse($token->created_at)->diffForHumans(),
                //                Str::headline('scopes') => $token->scopes,
            ];
        });

        $table = $this->generator->getTable($tokens);
        $this->table(...$table);
    }

    /**
     * Create a new token
     */
    public function new(): void
    {
        $data = $this->getData();

        /** @var TokenCreateResponse $token */
        $token = spin(
            fn () => $this->mailersend->token()->create($data),
            'Creating token...'
        );

        $this->info('Token created successfully!');
        $this->displayCreateDetails($token);
    }

    /**
     * Show token details
     */
    public function show(): void
    {
        $id = $this->getTokenID();

        /** @var TokenResponse $token */
        $token = spin(
            fn () => $this->mailersend->token()->find($id),
            'Fetching token details...'
        );

        $this->displayDetails($token);
    }

    /**
     * Edit token details
     */
    public function edit(): void
    {
        $id = $this->getTokenID();
        if (! $id) {
            throw new InvalidArgumentException('Token ID is required for edit action');
        }

        /** @var TokenResponse $token */
        $token = spin(
            fn () => $this->mailersend->token()->find($id),
            'Fetching token details...'
        );

        $data = $this->getEditData($token);

        /** @var TokenResponse $updatedToken */
        $updatedToken = spin(
            fn () => $this->mailersend->token()->update($id, $data),
            'Updating token...'
        );

        $this->info('Token updated successfully!');
        $this->notify('Success', 'Token updated successfully!');

        $this->displayDetails($updatedToken);
    }

    /**
     * Delete a token
     */
    public function delete(): void
    {
        $id = $this->getTokenID();
        if (! $id) {
            throw new InvalidArgumentException('Token ID is required for delete action');
        }

        /** @var TokenResponse $token */
        $token = spin(
            fn () => $this->mailersend->token()->find($id),
            'Fetching token details...'
        );

        $this->displayDetails($token);

        if (! confirm(
            "Do you want to delete the token ID $id?",
            hint: 'Deleting a token cannot be undone! Any emails being sent through this token will be immediately rejected.'
        )) {
            return;
        }

        $success = spin(
            fn () => $this->mailersend->token()->delete($id),
            'Deleting token...'
        );

        if ($success) {
            $this->info('Token deleted successfully!');
        } else {
            throw new RuntimeException('Failed to delete token');
        }
    }

    /**
     * Get token data from form input
     */
    private function getData(?TokenResponse $current = null): TokenData
    {
        /** @var Collection<DomainResponse> $domains */
        $domains = spin(
            fn () => $this->mailersend->domain()->all(),
            'Fetching domains...'
        );

        $formData = form()
            ->search(
                'Domain',
                fn (string $value) => strlen($value) > 0
                    ? $domains->filter(
                        fn (DomainResponse $domain) => str_contains(
                            strtolower($domain->name),
                            strtolower($value)
                        )
                    )->pluck('name', 'id')->toArray()
                    : [],
                hint: 'Domain ID',
                name: 'domain_id',
            )
            ->text(
                label: 'Token Name',
                default: $current->name ?? '',
                name: 'name'
            )
            ->multiselect(
                label: 'Scopes',
                options: [
                    'email_full',
                    'domains_read',
                    'domains_full',
                    'activity_read',
                    'activity_full',
                    'analytics_read',
                    'analytics_full',
                    'tokens_full',
                    'webhooks_full',
                    'templates_full',
                    'suppressions_read',
                    'suppressions_full',
                    'sms_full',
                    'sms_read',
                    'email_verification_read',
                    'email_verification_full',
                    'inbounds_full',
                    'recipients_read',
                    'recipients_full',
                ],
                default: $current->scopes ?? ['email_full'],
                name: 'scopes'
            )
            ->submit();

        $formData = array_filter($formData);

        return new TokenData(
            ...$formData
        );
    }

    /**
     * Get token data from form input
     */
    private function getEditData(?TokenResponse $current = null): TokenEditData
    {

        $formData = form()
            ->text(
                label: 'Token Name',
                default: $current->name ?? '',
                name: 'name'
            )
            ->select(
                label: 'Status',
                options: ['pause', 'unpause'],
                default: $current->status ?? 'unpause',
                name: 'status'
            )
            ->submit();

        $formData = array_filter($formData);

        return new TokenEditData(
            ...$formData
        );
    }

    /**
     * Display token details in a table
     */
    private function displayDetails(TokenResponse $token): void
    {
        $data = $this->generator->getDetailTable(collect([
            Str::upper('id') => $token->id,
            Str::headline('name') => $token->name,
            Str::headline('status') => $token->status,
            Str::headline('created_at') => Carbon::parse($token->created_at)->diffForHumans(),
        ]));

        $this->table(...$data);
    }

    /**
     * Display token create details in a table
     */
    private function displayCreateDetails(TokenCreateResponse $token): void
    {
        $data = $this->generator->getDetailTable(collect([
            Str::headline('id') => $token->id,
            Str::headline('name') => $token->name,
            Str::headline('has_full') => $token->has_full ? 'Yes' : 'No',
            Str::headline('preview') => $token->preview,
            Str::headline('expires_at') => $token->expires_at,
            Str::headline('created_at') => Carbon::parse($token->created_at)->diffForHumans(),
        ]));

        $this->table(...$data);
    }
}
