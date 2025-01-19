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
use App\Data\SenderData;
use App\Data\SenderResponse;
use App\Generator;
use Faker\Factory;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use InvalidArgumentException;
use LaravelZero\Framework\Commands\Command;
use RuntimeException;
use Throwable;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\form;
use function Laravel\Prompts\spin;
use function Laravel\Prompts\text;

/**
 * Sender Command
 *
 * This command provides functionality to manage Mailersend senders
 * through the command line interface.
 */
class Sender extends Command implements CommandInterface
{
    use HasHandle;
    use HasHelpers;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sender
                         {action=list : Action to perform (list/show/new/edit/delete)}
                         {--id= : Sender ID for edit/delete actions}
                         {--generate-random : Action for create new sender randomly}
                         {--delete-all : Action for delete all senders}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Manage Mailersend senders for your account';

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
     * List all senders
     */
    public function list(): void
    {

        /** @var Collection<SenderResponse> $senders */
        $senders = spin(
            fn () => $this->mailersend->sender()->all(),
            'Fetching senders...'
        );

        // Mask sensitive data
        $senders = $senders->map(function (SenderResponse $sender) {
            return [
                Str::upper('id') => $sender->id,
                Str::headline('email') => $sender->email,
                Str::headline('name') => $sender->name,
                Str::headline('reply_to_email') => $sender->reply_to_email ?? 'N/A',
                Str::headline('reply_to_name') => $sender->reply_to_name ?? 'N/A',
                Str::headline('is_verified') => $sender->is_verified ? 'Yes' : 'No',
                Str::headline('resends') => $sender->resends,
            ];
        });

        $table = $this->generator->getTable($senders);
        $this->table(...$table);
        $this->notify('Success', 'Domain fetched successfully!');

    }

    /**
     * Create a new sender
     */
    public function new(): void
    {
        if ($this->option('generate-random')) {
            $max = text(
                label: 'How many senders do you want to generate?',
                default: 5,
                required: true,
                validate: ['numeric' => 'required|numeric|min:1|max:10'],
            );

            $this->generateRandomSender($max);

            return;
        }

        $data = $this->getData();

        /** @var SenderResponse $sender */
        $sender = spin(
            fn () => $this->mailersend->sender()->create($data),
            'Creating sender...'
        );

        $this->info('Sender created successfully!');
        $this->displayDetails($sender);
    }

    private function generateRandomSender(int $max): void
    {
        $faker = Factory::create();
        $domain_id = $this->getDomainID();

        if (! $domain_id) {
            throw new RuntimeException('Domain is required for create action');
        }

        /** @var DomainResponse $domain */
        $domain = spin(
            fn () => $this->mailersend->domain()->find($domain_id),
            'Fetching domain details...'
        );

        foreach (range(1, $max) as $i) {

            $firstName = $faker->firstName();
            $lastName = $faker->lastName();
            $name = "$firstName $lastName";

            $data = new SenderData(
                domain_id: $domain_id,
                email: Str::slug($name).'@'.$domain->name,
                name: $name,
            );

            /** @var SenderResponse $sender */
            $sender = spin(
                fn () => $this->mailersend->sender()->create($data),
                'Creating sender...'
            );

            $this->info("$i. Sender added successfully! $sender->name <$sender->email>");

        }
    }

    /**
     * Show sender details
     */
    public function show(): void
    {
        $id = $this->getSenderID();

        /** @var SenderResponse $sender */
        $sender = spin(
            fn () => $this->mailersend->sender()->find($id),
            'Fetching sender details...'
        );

        $this->title('Sender Details');
        $this->displayDetails($sender);
    }

    /**
     * Edit sender details
     */
    public function edit(): void
    {
        $id = $this->getSenderID();
        if (! $id) {
            throw new InvalidArgumentException('Sender ID is required for edit action');
        }

        /** @var SenderResponse $sender */
        $sender = spin(
            fn () => $this->mailersend->sender()->find($id),
            'Fetching sender details...'
        );

        $data = $this->getData($sender);

        /** @var SenderResponse $updatedSender */
        $updatedSender = spin(
            fn () => $this->mailersend->sender()->update($id, $data),
            'Updating sender...'
        );

        $this->info('Sender updated successfully!');
        $this->displayDetails($updatedSender);
    }

    /**
     * Delete a sender
     *
     * This function deletes a sender identity. It prompts for confirmation before
     * proceeding with the deletion, and logs the operation. Deleting a sender is
     * irreversible.
     *
     * @throws InvalidArgumentException If the sender ID is invalid
     * @throws RuntimeException If an error occurs during the deletion process
     * @throws Throwable
     */
    public function delete(): void
    {
        if ($this->option('delete-all')) {
            $this->deleteAll();

            return;
        }

        $id = $this->getSenderID();

        if (! $id) {
            throw new InvalidArgumentException('Sender ID is required for delete action');
        }

        // Fetch the sender details before deleting it
        /** @var SenderResponse $sender */
        $sender = spin(
            fn () => $this->mailersend->sender()->find($id),
            'Fetching sender details...'
        );

        $this->displayDetails($sender);

        // Confirm the deletion operation with the user
        if (! confirm(
            "Do you really want to delete this sender identity fo ID $id?",
            hint: 'Deleting a sender identity cannot be undone!'
        )) {
            return;
        }

        // Attempt to delete the sender
        $success = spin(
            fn () => $this->mailersend->sender()->delete($id),
            'Deleting sender...'
        );

        if ($success) {
            $this->info('Sender deleted successfully!');
        } else {
            throw new RuntimeException('Failed to delete sender');
        }
    }

    /**
     * Delete all senders
     *
     * This function deletes all sender identities. It prompts for confirmation before
     * proceeding with the deletion, and logs the operation. Deleting senders is irreversible.
     *
     * @throws Throwable If an error occurs during the deletion process
     */
    private function deleteAll(): void
    {
        // Confirm the deletion operation with the user
        if (! confirm(
            'Do you really want to delete all sender identities?',
            hint: 'Deleting all sender identities cannot be undone!'
        )) {
            return;
        }

        // Fetch all senders
        /** @var Collection<SenderResponse> $senders */
        $senders = spin(
            fn () => $this->mailersend->sender()->all(),
            'Fetching senders...'
        );

        // Iterate over each sender and delete
        foreach ($senders as $sender) {
            // Attempt to delete the sender
            $success = spin(
                fn () => $this->mailersend->sender()->delete($sender->id),
                'Deleting sender...'
            );

            if ($success) {
                $this->info("Sender deleted successfully! $sender->name <$sender->email>");
            } else {
                throw new RuntimeException('Failed to delete sender');
            }
        }
    }

    /**
     * Get sender data from form input
     */
    private function getData(?SenderResponse $current = null): SenderData
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
                label: 'Email',
                default: $current->email ?? '',
                required: true,
                validate: ['email' => 'required|email:rfc,dns,spoof'],
                hint: 'Email address',
                name: 'email',
            )
            ->text(
                label: 'Name',
                default: $current->name ?? '',
                required: true,
                hint: 'Sender name',
                name: 'name',
            )
            ->addIf(fn () => confirm('Do you want to add Reply To Name?', false), function () {
                return text(
                    label: 'Reply To Name',
                    default: $current->reply_to_name ?? '',
                    hint: 'Reply to name',
                );
            }, 'reply_to_name')
            ->addIf(fn () => confirm('Do you want to add Reply To Email?', false), function () {
                return text(
                    label: 'Reply To Email',
                    default: $current->reply_to_email ?? '',
                    hint: 'Reply to email',
                );
            }, 'reply_to_email')
            ->confirm(
                label: 'Do you want to add personal note?',
                default: $current->add_note ?? false,
                hint: 'Add personal note',
                name: 'add_note'
            )
            ->addIf(fn ($response) => $response['add_note'], function () {
                return text(
                    label: 'Personal Note',
                    default: $current->personal_note ?? '',
                    hint: 'Personal note',
                );
            }, 'personal_note')
            ->submit();

        return new SenderData(
            ...$formData
        );
    }

    /**
     * Display sender details in a table
     */
    private function displayDetails(SenderResponse $sender): void
    {
        $detail = $this->generator->getDetailTable(collect([
            Str::upper('id') => $sender->id,
            Str::headline('email') => $sender->email,
            Str::headline('name') => $sender->name,
            Str::headline('reply_to_email') => $sender->reply_to_email ?? 'N/A',
            Str::headline('reply_to_name') => $sender->reply_to_name ?? 'N/A',
            Str::headline('is_verified') => $sender->is_verified ? 'Yes' : 'No',
            Str::headline('resends') => $sender->resends,
        ]));

        $this->table(...$detail);
    }
}
