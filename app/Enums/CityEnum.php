<?php

namespace App\Enums;

enum CityEnum: string
{
    case BuenosAires = 'AR-BUE';
    case LaPaz = 'BO-LPB';
    case SantaCruz = 'BO-SCZ';
    case SaoPaulo = 'BR-SAO';
    case RioDeJaneiro = 'BR-RIO';
    case Toronto = 'CA-TOR';
    case Vancouver = 'CA-VAN';
    case Santiago = 'CL-SCL';
    case Bogota = 'CO-BOG';
    case Medellin = 'CO-MDE';
    case Cali = 'CO-CLO';
    case SanJose = 'CR-SJO';
    case SantoDomingo = 'DO-SDQ';
    case Quito = 'EC-UIO';
    case Guayaquil = 'EC-GYE';
    case Cuenca = 'EC-CUE';
    case Manta = 'EC-MEC';
    case SanSalvador = 'SV-SAL';
    case Paris = 'FR-PAR';
    case Lyon = 'FR-LYS';
    case Berlin = 'DE-BER';
    case Munich = 'DE-MUC';
    case GuatemalaCity = 'GT-GUA';
    case Tegucigalpa = 'HN-TGU';
    case Rome = 'IT-ROM';
    case Milan = 'IT-MIL';
    case MexicoCity = 'MX-MEX';
    case Monterrey = 'MX-MTY';
    case Amsterdam = 'NL-AMS';
    case Managua = 'NI-MGA';
    case PanamaCity = 'PA-PTY';
    case Asuncion = 'PY-ASU';
    case Lima = 'PE-LIM';
    case Arequipa = 'PE-AQP';
    case Lisbon = 'PT-LIS';
    case Madrid = 'ES-MAD';
    case Barcelona = 'ES-BCN';
    case London = 'GB-LON';
    case Manchester = 'GB-MAN';
    case NewYork = 'US-NYC';
    case Miami = 'US-MIA';
    case Houston = 'US-HOU';
    case Montevideo = 'UY-MVD';
    case Caracas = 'VE-CCS';
    case Cordoba = 'AR-COR';
    case Cochabamba = 'BO-CBB';
    case Brasilia = 'BR-BSB';
    case Montreal = 'CA-YMQ';
    case Valparaiso = 'CL-VAP';
    case Barranquilla = 'CO-BAQ';
    case Cartagena = 'CO-CTG';
    case Alajuela = 'CR-ALA';
    case SantiagoDeLosCaballeros = 'DO-STI';
    case Loja = 'EC-LOH';
    case SantaAna = 'SV-STA';
    case Marseille = 'FR-MRS';
    case Hamburg = 'DE-HAM';
    case Quetzaltenango = 'GT-AAZ';
    case SanPedroSula = 'HN-SAP';
    case Naples = 'IT-NAP';
    case Guadalajara = 'MX-GDL';
    case Rotterdam = 'NL-RTM';
    case Leon = 'NI-LEO';
    case Colon = 'PA-ONX';
    case CiudadDelEste = 'PY-CDE';
    case Cusco = 'PE-CUZ';
    case Porto = 'PT-OPO';
    case Valencia = 'ES-VLC';
    case Birmingham = 'GB-BHX';
    case LosAngeles = 'US-LAX';
    case Chicago = 'US-CHI';
    case PuntaDelEste = 'UY-PDP';
    case Maracaibo = 'VE-MAR';

    public function countryCode(): string
    {
        return substr($this->value, 0, 2);
    }

    public function cityCode(): string
    {
        return substr($this->value, 3);
    }

    public function label(): string
    {
        return match ($this) {
            self::BuenosAires => 'Buenos Aires',
            self::LaPaz => 'La Paz',
            self::SantaCruz => 'Santa Cruz',
            self::SaoPaulo => 'São Paulo',
            self::RioDeJaneiro => 'Rio de Janeiro',
            self::SanJose => 'San José',
            self::SantoDomingo => 'Santo Domingo',
            self::SanSalvador => 'San Salvador',
            self::GuatemalaCity => 'Guatemala City',
            self::MexicoCity => 'Mexico City',
            self::PanamaCity => 'Panama City',
            self::NewYork => 'New York',
            self::SantiagoDeLosCaballeros => 'Santiago de los Caballeros',
            self::SanPedroSula => 'San Pedro Sula',
            self::CiudadDelEste => 'Ciudad del Este',
            self::LosAngeles => 'Los Angeles',
            self::PuntaDelEste => 'Punta del Este',
            default => trim((string) preg_replace(
                '/(?<!^)[A-Z]/',
                ' $0',
                $this->name,
            )),
        };
    }

    public function state(): ?string
    {
        return match ($this) {
            self::BuenosAires => 'Buenos Aires',
            self::LaPaz => 'La Paz',
            self::SantaCruz => 'Santa Cruz',
            self::SaoPaulo => 'São Paulo',
            self::RioDeJaneiro => 'Rio de Janeiro',
            self::Toronto => 'Ontario',
            self::Vancouver => 'British Columbia',
            self::Santiago => 'Santiago Metropolitan',
            self::Bogota => 'Bogotá D.C.',
            self::Medellin => 'Antioquia',
            self::Cali => 'Valle del Cauca',
            self::SanJose => 'San José',
            self::SantoDomingo => 'Distrito Nacional',
            self::Quito => 'Pichincha',
            self::Guayaquil => 'Guayas',
            self::Cuenca => 'Azuay',
            self::Manta => 'Manabí',
            self::SanSalvador => 'San Salvador',
            self::Paris => 'Île-de-France',
            self::Lyon => 'Auvergne-Rhône-Alpes',
            self::Berlin => 'Berlin',
            self::Munich => 'Bavaria',
            self::GuatemalaCity => 'Guatemala',
            self::Tegucigalpa => 'Francisco Morazán',
            self::Rome => 'Lazio',
            self::Milan => 'Lombardy',
            self::MexicoCity => 'Ciudad de México',
            self::Monterrey => 'Nuevo León',
            self::Amsterdam => 'North Holland',
            self::Managua => 'Managua',
            self::PanamaCity => 'Panamá',
            self::Asuncion => 'Asunción',
            self::Lima => 'Lima',
            self::Arequipa => 'Arequipa',
            self::Lisbon => 'Lisbon',
            self::Madrid => 'Community of Madrid',
            self::Barcelona => 'Catalonia',
            self::London => 'England',
            self::Manchester => 'England',
            self::NewYork => 'New York',
            self::Miami => 'Florida',
            self::Houston => 'Texas',
            self::Montevideo => 'Montevideo',
            self::Caracas => 'Capital District',
            self::Cordoba => 'Córdoba',
            self::Cochabamba => 'Cochabamba',
            self::Brasilia => 'Federal District',
            self::Montreal => 'Quebec',
            self::Valparaiso => 'Valparaíso',
            self::Barranquilla => 'Atlántico',
            self::Cartagena => 'Bolívar',
            self::Alajuela => 'Alajuela',
            self::SantiagoDeLosCaballeros => 'Santiago',
            self::Loja => 'Loja',
            self::SantaAna => 'Santa Ana',
            self::Marseille => 'Provence-Alpes-Côte d’Azur',
            self::Hamburg => 'Hamburg',
            self::Quetzaltenango => 'Quetzaltenango',
            self::SanPedroSula => 'Cortés',
            self::Naples => 'Campania',
            self::Guadalajara => 'Jalisco',
            self::Rotterdam => 'South Holland',
            self::Leon => 'León',
            self::Colon => 'Colón',
            self::CiudadDelEste => 'Alto Paraná',
            self::Cusco => 'Cusco',
            self::Porto => 'Porto',
            self::Valencia => 'Valencian Community',
            self::Birmingham => 'England',
            self::LosAngeles => 'California',
            self::Chicago => 'Illinois',
            self::PuntaDelEste => 'Maldonado',
            self::Maracaibo => 'Zulia',
        };
    }

    /**
     * @param array<int, string> $excludedCityCodes
     * @return array<string, string>
     */
    public static function optionsForCountry(
        ?string $countryCode,
        array $excludedCityCodes = [],
    ): array {
        $excludedCityCodes = array_map('strtoupper', $excludedCityCodes);

        return collect(self::cases())
            ->filter(fn(self $city): bool => $city->countryCode() === $countryCode)
            ->reject(fn(self $city): bool => in_array(
                $city->cityCode(),
                $excludedCityCodes,
                true,
            ))
            ->mapWithKeys(fn(self $city): array => [
                $city->value => $city->label(),
            ])
            ->all();
    }

    public static function find(?string $countryCode, ?string $cityCode): ?self
    {
        return self::tryFrom(
            strtoupper((string) $countryCode) . '-' . strtoupper((string) $cityCode),
        );
    }
}
