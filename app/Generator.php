<?php

/*
 * Copyright (c) 2025 Muhammad irfan.
 * All rights reserved.
 *
 * This project is created and maintained by Muhammad Irfan. Redistribution or modification
 * of this code is permitted only under the terms specified in the license.
 *
 * @package    postmark-cli
 * @license    MIT
 * @author     Muhammad Irfan <mrfansi@outlook.com>
 * @version    1.0.0
 * @since      2025-01-18
 */

namespace App;

use Illuminate\Support\Collection;

class Generator
{
    /**
     * Transform a collection of servers into a table, with optional filtering
     *
     * @return array The table with headers and rows
     */
    public static function getTable(Collection $collection): array
    {

        $headers = $collection->first() ? collect($collection->first())
            ->keys()
            ->toArray() : [];

        $rows = $collection->map(function ($server) {

            return collect($server)
                ->map(function ($value) {
                    if (is_array($value)) {
                        return implode(', ', $value); // Flatten arrays into strings
                    }

                    return $value;
                })
                ->values() // Ensure values align with headers
                ->toArray();
        })->toArray();

        return [$headers, $rows];

    }

    public static function getDetailTable(Collection $collection, array $filteredKeys = []): array
    {

        $headers = ['Key', 'Value'];

        $rows = $collection
            ->when(! empty($filteredKeys), function ($collection) use ($filteredKeys) {
                return $collection->only($filteredKeys); // Filter keys if $filteredKeys is provided
            })
            ->map(function ($value, $key) {

                return [
                    'key' => $key,
                    'value' => is_array($value) ? implode(', ', $value) : $value, // Flatten arrays into strings
                ];
            })
            ->values() // Ensure sequential numeric keys
            ->all();

        return [$headers, $rows];
    }

    public static function generateTestEmailContent(): array
    {
        $plainText = <<<'EOD'
            Hello,

            This is a test email to verify that the email system is working correctly.

            Thank you,
            EOD;

        $htmlBody = <<<'HTML'
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <title>Test Email</title>
        </head>
        <body style="font-family: Arial, sans-serif; line-height: 1.6;">
            <h1>Test Email</h1>
            <p>This is a test email to verify that the email system is working correctly.</p>
            <p>Thank you</p>
        </body>
        </html>
        HTML;

        return [
            'plain_text' => $plainText,
            'html_body' => $htmlBody,
        ];
    }
}
