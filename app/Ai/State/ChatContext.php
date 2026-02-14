<?php

declare(strict_types=1);

namespace App\Ai\State;

use Illuminate\Support\Collection;

class ChatContext
{
    protected static ?string $chatId = null;

    /** @var Collection<int, array{toolName: string, data: array<string, mixed>}>|null */
    protected static ?Collection $toolUses = null;

    public static function setChatId(string $chatId): void
    {
        self::$chatId = $chatId;
    }

    public static function getChatId(): ?string
    {
        return self::$chatId;
    }

    public static function addToolUse(string $toolName, array $data = []): void
    {
        if ( ! self::$toolUses) {
            self::$toolUses = collect();
        }

        self::$toolUses->push([
            'tool' => $toolName,
            'data' => $data,
        ]);
    }

    /** @return Collection<int, array{toolName: string, data: array<string, mixed>}> */
    public static function getToolUses(): Collection
    {
        return self::$toolUses ?? collect();
    }

    public static function clear(): void
    {
        self::$toolUses = collect();
        self::$chatId = null;
    }
}
