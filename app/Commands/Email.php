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

use App\Commands\Traits\HasHandle;
use App\Commands\Traits\HasHelpers;
use App\Contracts\MailersendFactoryInterface;
use App\Data\EmailAddressData;
use App\Data\EmailData;
use App\Data\EmailResponse;
use App\Data\SenderResponse;
use App\Data\TemplateResponse;
use App\Generator;
use Illuminate\Support\Collection;
use LaravelZero\Framework\Commands\Command;
use RuntimeException;
use Throwable;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\form;
use function Laravel\Prompts\search;
use function Laravel\Prompts\spin;
use function Laravel\Prompts\text;
use function Laravel\Prompts\textarea;

/**
 * Email Command
 *
 * This command provides functionality to manage Mailersend emails
 * through the command line interface.
 */
class Email extends Command
{
    use HasHandle;
    use HasHelpers;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'email
                         {action=list : Action to perform (list/show/send)}
                         {--bulk : Send emails in bulk mode}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Manage Mailersend emails for your account';

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
     * Execute the console command.
     *
     * This method handles the main command execution flow, routing to appropriate
     * action handlers based on the provided action argument. It includes comprehensive
     * error handling and user feedback.
     *
     * @return int Command exit code (0: success, 1: failure)
     *
     * @throws RuntimeException When an invalid action is provided
     */
    public function handle(): int
    {
        try {
            $action = $this->argument('action');

            if (! in_array($action, ['list', 'show', 'send'])) {
                throw new RuntimeException(
                    sprintf(
                        'Invalid action "%s". Available actions: list, show, send',
                        $action
                    )
                );
            }

            $this->info("Executing {$action} action...");

            $result = match ($action) {
                'list' => $this->list(),
                'show' => $this->show(),
                'send' => $this->send(),
                default => $this->invalidAction($action)
            };

            if ($result === self::SUCCESS) {
                $this->info("Action {$action} completed successfully!");
            }

            return $result;
        } catch (RuntimeException $e) {
            $this->error("Command failed: {$e->getMessage()}");
            $this->line('For help, run: mailersend email --help');

            return self::FAILURE;
        } catch (Throwable $e) {
            $this->error("Unexpected error: {$e->getMessage()}");
            if ($this->getOutput()->isVerbose()) {
                $this->error($e->getTraceAsString());
            }

            return self::FAILURE;
        }
    }

    /**
     * Send new email(s)
     *
     * @return int Command exit code
     *
     * @throws RuntimeException If email sending fails
     */
    protected function send(): int
    {
        try {
            $isBulk = $this->option('bulk');

            if ($isBulk) {
                return $this->sendBulk();
            }

            $emailData = $this->collectEmailData();

            /** @var EmailResponse $response */
            $response = spin(
                fn () => $this->mailersend->email()->send($emailData),
                'Sending email...'
            );

            $this->info('Email sent successfully!');
            $this->displayEmailSummary($response);

            return self::SUCCESS;
        } catch (RuntimeException $e) {
            $this->error("Failed to send email: {$e->getMessage()}");

            return self::FAILURE;
        }
    }

    /**
     * Send bulk emails
     *
     * @return int Command exit code
     *
     * @throws RuntimeException If bulk email sending fails
     */
    protected function sendBulk(): int
    {
        try {
            $emails = new Collection;
            $failedEmails = new Collection;

            do {
                try {
                    $emailData = $this->collectEmailData();
                    $emails->push($emailData);
                } catch (RuntimeException $e) {
                    $this->warn("Skipping email: {$e->getMessage()}");
                    $failedEmails->push($emailData ?? null);
                    if (! confirm('Do you want to continue with remaining emails?')) {
                        break;
                    }
                }

                $continue = confirm('Do you want to add another email?');
            } while ($continue);

            if ($emails->isEmpty()) {
                $this->error('No valid emails to send');

                return self::FAILURE;
            }

            /** @var Collection<EmailResponse> $responses */
            $responses = spin(
                fn () => $this->mailersend->email()->bulkSend($emails),
                'Sending bulk emails...'
            );

            $this->displayBulkEmailSummary($responses, $failedEmails);

            return $failedEmails->isEmpty() ? self::SUCCESS : self::FAILURE;
        } catch (RuntimeException $e) {
            $this->error("Failed to send bulk emails: {$e->getMessage()}");

            return self::FAILURE;
        }
    }

    /**
     * Display summary of single email sending
     */
    private function displayEmailSummary(EmailResponse $response): void
    {
        $this->table(
            ['Email ID', 'Status', 'Message'],
            [
                [
                    $response->id ?? 'N/A',
                    'Success',
                    'Email queued for delivery',
                ],
            ]
        );
    }

    /**
     * Display summary of bulk email sending
     *
     * @param  Collection<EmailResponse>  $responses
     */
    private function displayBulkEmailSummary(Collection $responses, Collection $failedEmails): void
    {
        $this->info(sprintf(
            'Bulk email summary: %d sent, %d failed',
            $responses->count(),
            $failedEmails->count()
        ));

        if ($responses->isNotEmpty()) {
            $this->table(
                ['Email ID', 'Status', 'Message'],
                $responses->map(fn (EmailResponse $response) => [
                    $response->id ?? 'N/A',
                    'Success',
                    'Email queued for delivery',
                ])->toArray()
            );
        }

        if ($failedEmails->isNotEmpty()) {
            $this->error('Failed Emails:');
            $this->table(
                ['Index', 'Reason'],
                $failedEmails->map(fn ($email, $index) => [
                    $index + 1,
                    $email ? 'Invalid email data' : 'Collection error',
                ])->toArray()
            );
        }
    }

    /**
     * Collect email data from user input
     */
    protected function collectEmailData(): EmailData
    {
        $formData = form()
            ->addIf(fn () => confirm('Do you want to use a template?', false), function () {
                /** @var Collection<TemplateResponse> $templates */
                $templates = spin(
                    fn () => $this->mailersend->template()->all(),
                    'Fetching templates...'
                );

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
            }, 'template_id')
            ->add(function () {
                if (confirm('Do you want to use from sender identities?', false)) {
                    /** @var Collection<SenderResponse> $senders */
                    $senders = spin(
                        fn () => $this->mailersend->sender()->all(),
                        'Fetching senders...'
                    );

                    if (! $senders->count()) {
                        throw new RuntimeException('There are no sender identities');
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

                    /** @var SenderResponse $sender */
                    $sender = spin(
                        fn () => $this->mailersend->sender()->find($id),
                        'Fetching sender details...'
                    );

                    return EmailAddressData::fromArray($sender->toArray());
                }

                $from = form()
                    ->text(
                        label: 'From Email',
                        required: true,
                        validate: ['from_email' => 'required|email:rfc,dns,spoof'],
                        hint: 'Sender email address',
                        name: 'email',
                    )
                    ->addIf(fn () => confirm('Do you want to add From Name?', false), function () {
                        return text(
                            label: 'From Name',
                            hint: 'Sender name',
                        );
                    }, 'name')
                    ->submit();

                return EmailAddressData::fromArray(array_filter($from));
            }, 'from')
            ->add(function () {
                $recipients = [];
                do {
                    $to = form()
                        ->text(
                            label: 'To Email',
                            required: true,
                            validate: ['from_email' => 'required|email:rfc,dns,spoof'],
                            hint: 'Recipient email address',
                            name: 'email',
                        )
                        ->addIf(fn () => confirm('Do you want to to add From Name?', false), function () {
                            return text(
                                label: 'To Name',
                                required: true,
                                hint: 'Recipient name',
                            );
                        }, 'name')
                        ->submit();

                    $recipients[] = EmailAddressData::fromArray(array_filter($to));
                } while (confirm('Do you want to add another recipient?', false));

                return $recipients;
            }, 'to')
            ->addIf(fn () => confirm('Do you want to add cc?', false), function () {
                $recipients = [];
                do {
                    $cc = form()
                        ->text(
                            label: 'Cc Email',
                            required: true,
                            validate: ['from_email' => 'required|email:rfc,dns,spoof'],
                            hint: 'Carbon copy email address',
                            name: 'email',
                        )
                        ->addIf(fn () => confirm('Do you want to to add From Name?', false), function () {
                            return text(
                                label: 'Cc Name',
                                required: true,
                                hint: 'Carbon copy name',
                            );
                        }, 'name')
                        ->submit();

                    $recipients[] = EmailAddressData::fromArray(array_filter($cc));
                } while (confirm('Do you want to add another cc?', false));

                return $recipients;
            }, 'cc')
            ->addIf(fn () => confirm('Do you want to add bcc?', false), function () {
                $recipients = [];
                do {
                    $bcc = form()
                        ->text(
                            label: 'Bcc Email',
                            required: true,
                            validate: ['from_email' => 'required|email:rfc,dns,spoof'],
                            hint: 'Bcc email address',
                            name: 'email',
                        )
                        ->addIf(fn () => confirm('Do you want to to add From Name?', false), function () {
                            return text(
                                label: 'Bcc Name',
                                required: true,
                                hint: 'Bcc name',
                            );
                        }, 'name')
                        ->submit();

                    $recipients[] = EmailAddressData::fromArray(array_filter($bcc));
                } while (confirm('Do you want to add another bcc?', false));

                return $recipients;
            }, 'bcc')
            ->text(
                label: 'Subject',
                required: true,
                hint: 'Email subject',
                name: 'subject',
            )
            ->addIf(fn ($response) => ! $response['template_id'], function () {
                return textarea(
                    label: 'Text Content',
                    required: true,
                    hint: 'Email content in text format',
                );
            }, 'text')
            ->addIf(fn ($response) => ! $response['template_id'] && confirm('Do you want to add HTML content?', false), function () {
                return text(
                    label: 'HTML Content',
                    hint: 'Email content in HTML format',
                );
            }, 'html')
            ->submit();

        $formData = array_filter($formData);

        return new EmailData(
            ...$formData
        );
    }
}
