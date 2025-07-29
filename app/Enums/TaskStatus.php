<?php

namespace App\Enums;

enum TaskStatus: string
{
    case TODO = 'to-do';
    case IN_PROGRESS = 'in-progress';
    case DONE = 'done';

    /**
     * Get all enum values as array
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Get enum options for forms
     */
    public static function options(): array
    {
        return [
            ['value' => self::TODO->value, 'label' => 'To Do'],
            ['value' => self::IN_PROGRESS->value, 'label' => 'In Progress'],
            ['value' => self::DONE->value, 'label' => 'Done'],
        ];
    }

    /**
     * Get human readable label
     */
    public function label(): string
    {
        return match ($this) {
            self::TODO => 'To Do',
            self::IN_PROGRESS => 'In Progress',
            self::DONE => 'Done',
        };
    }
}
