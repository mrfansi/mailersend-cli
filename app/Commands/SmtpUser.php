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
use App\Data\SmtpUserData;
use App\Data\SmtpUserResponse;
use App\Generator;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use InvalidArgumentException;
use LaravelZero\Framework\Commands\Command;
use RuntimeException;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\form;
use function Laravel\Prompts\spin;
use function Laravel\Prompts\text;

/**
 * SmtpUser Command
 *
 * This command provides functionality to manage Mailersend smtpUsers
 * through the command line interface.
 */
class SmtpUser extends Command implements CommandInterface
{
    use HasHandle;
    use HasHelpers;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'smtp
                         {action=list : Action to perform (list/show/new/edit/delete)}
                         {--id= : SmtpUser ID for edit/delete actions}
                         {--name= : SmtpUser name for new/edit actions}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Manage Mailersend smtpUsers for your account';

    /**
     * Mailersend factory instance
     */
    private MailersendFactoryInterface $mailersend;

    /**
     * Generator instance for output formatting
     */
    private Generator $generator;

    private string $domainId;

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
     * List all smtpUsers
     */
    public function list(): void
    {
        $this->setDomainID();

        /** @var Collection<SmtpUserResponse> $smtpUsers */
        $smtpUsers = spin(
            fn () => $this->mailersend->smtpUser($this->domainId)->all(),
            'Fetching smtp users...'
        );

        // Mask sensitive data
        $smtpUsers = $smtpUsers->map(function (SmtpUserResponse $smtpUser) {
            return [
                Str::upper('id') => $smtpUser->id,
                Str::headline('name') => $smtpUser->name,
                Str::headline('username') => $smtpUser->username,
                Str::headline('domain') => $smtpUser->domain,
                Str::headline('enabled') => $smtpUser->enabled ? 'Yes' : 'No',
                Str::headline('server') => $smtpUser->server,
                Str::headline('port') => $smtpUser->port,
                Str::headline('password') => $smtpUser->password,
                Str::headline('accessed_at') => isset($smtpUser->accessed_at) ? Carbon::parse($smtpUser->accessed_at)->diffForHumans() : 'N/A',
            ];
        });

        $table = $this->generator->getTable($smtpUsers);
        $this->table(...$table);
    }

    /**
     * Create a new smtpUser
     */
    public function new(): void
    {
        $this->setDomainID();

        $data = $this->getData();

        /** @var SmtpUserResponse $smtpUser */
        $smtpUser = spin(
            fn () => $this->mailersend->smtpUser($this->domainId)->create($data),
            'Creating smtp user...'
        );

        $this->info('Smtp user created successfully!');
        $this->displayDetails($smtpUser);
    }

    /**
     * Show smtpUser details
     */
    public function show(): void
    {
        $this->setDomainID();

        $id = $this->getSmtpUserID();

        /** @var SmtpUserResponse $smtpUser */
        $smtpUser = spin(
            fn () => $this->mailersend->smtpUser($this->domainId)->find($id),
            'Fetching smtp user details...'
        );

        $this->displayDetails($smtpUser);
    }

    /**
     * Edit smtpUser details
     */
    public function edit(): void
    {
        $this->setDomainID();

        $id = $this->getSmtpUserID();
        if (! $id) {
            throw new InvalidArgumentException('Smtp use ID is required for edit action');
        }

        /** @var SmtpUserResponse $smtpUser */
        $smtpUser = spin(
            fn () => $this->mailersend->smtpUser($this->domainId)->find($id),
            'Fetching smtp user details...'
        );

        $data = $this->getData($smtpUser);

        /** @var SmtpUserResponse $updatedSmtpUser */
        $updatedSmtpUser = spin(
            fn () => $this->mailersend->smtpUser($this->domainId)->update($id, $data),
            'Updating smtp user...'
        );

        $this->info('Smtp user updated successfully!');
        $this->notify('Success', 'Smtp user updated successfully!');

        $this->displayDetails($updatedSmtpUser);
    }

    /**
     * Delete a smtpUser
     */
    public function delete(): void
    {
        $this->setDomainID();

        $id = $this->getSmtpUserID();
        if (! $id) {
            throw new InvalidArgumentException('Smtp user ID is required for delete action');
        }

        /** @var SmtpUserResponse $smtpUser */
        $smtpUser = spin(
            fn () => $this->mailersend->smtpUser($this->domainId)->find($id),
            'Fetching smtp user details...'
        );

        $this->displayDetails($smtpUser);

        if (! confirm(
            "Do you want to delete the smtp user ID $id?",
            hint: 'Deleting a smtp user cannot be undone! Any emails being sent through this smtp will be immediately rejected.'
        )) {
            return;
        }

        $success = spin(
            fn () => $this->mailersend->smtpUser($this->domainId)->delete($id),
            'Deleting smtp user...'
        );

        if ($success) {
            $this->info('Smtp user deleted successfully!');
        } else {
            throw new RuntimeException('Failed to delete smtpUser');
        }
    }

    /**
     * Get smtpUser data from form input
     */
    private function getData(?SmtpUserResponse $current = null): SmtpUserData
    {
        $formData = form()
            ->addIf($this->option('name') == null, function () {
                return text(
                    label: 'Smtp User Name',
                    default: $current->name ?? '',
                    required: true,
                    hint: 'Name of the smtp user',
                );
            })
            ->submit();

        $formData['domain_id'] = $this->domainId;
        $formData = array_filter($formData);

        return new SmtpUserData(
            ...$formData
        );
    }

    /**
     * Display smtpUser details in a table
     */
    private function displayDetails(SmtpUserResponse $smtpUser): void
    {
        $detail = $this->generator->getDetailTable(collect([
            Str::upper('id') => $smtpUser->id,
            Str::headline('name') => $smtpUser->name,
            Str::headline('username') => $smtpUser->username,
            Str::headline('domain') => $smtpUser->domain,
            Str::headline('enabled') => $smtpUser->enabled ? 'Yes' : 'No',
            Str::headline('server') => $smtpUser->server,
            Str::headline('port') => $smtpUser->port,
            Str::headline('password') => $smtpUser->password,
            Str::headline('accessed_at') => isset($smtpUser->accessed_at) ? Carbon::parse($smtpUser->accessed_at)->diffForHumans() : 'N/A',
        ]));

        $this->info('Smtp User Detail:');
        $this->table(...$detail);
        $this->line('');
    }
}
