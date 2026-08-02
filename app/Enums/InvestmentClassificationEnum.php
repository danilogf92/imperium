<?php

namespace App\Enums;

enum InvestmentClassificationEnum: string
{
    case Buildings = 'Buildings';
    case Furniture = 'Furniture';
    case GeneralInstall = 'General Install';
    case Land = 'Land';
    case MachinesEquipment = 'Machines & Equipm';
    case OfficeHardwareSoftware = 'Office Hardware Software';
    case Other = 'Other';
    case Vehicles = 'Vehicles';
    case VesselFishingEquipment = 'Vessel & Fishing Equipment';
    case WarehouseDistribution = 'Warenhouse & Distrib';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
