<?php

namespace App\Enums;

enum EquipmentCondition: string
{
    case Working = 'Working';
    case NotWorking = 'Not Working';

    public function label(): string
    {
        return $this->value;
    }

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
