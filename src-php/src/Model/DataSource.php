<?php

declare(strict_types=1);

namespace App\Model;


class DataSource
{
    public const SOURCE_CSV_UIT = 0;
    public const SOURCE_CSV_SUEZ = 1;
    public const SOURCE_CSV_PLATFORM_FILE_UPLAOD = 10;
    public const SOURCE_CSV_CLI_IMPORT = 11;
    public const SOURCE_HTTP_CLI_IMPORT = 12;
    public const SOURCE_OTHER = 30;

    private int $value;

    public static function fromCsvUit(): self
    {
        return new self(self::SOURCE_CSV_UIT);
    }

    public static function fromCsvSuez(): self
    {
        return new self(self::SOURCE_CSV_SUEZ);
    }

    public static function fromCsvPlatformFileUpload(): self
    {
        return new self(self::SOURCE_CSV_PLATFORM_FILE_UPLAOD);
    }

    public static function fromCsvCliImport(): self
    {
        return new self(self::SOURCE_CSV_CLI_IMPORT);
    }

    public static function fromHttpCliImport(): self
    {
        return new self(self::SOURCE_HTTP_CLI_IMPORT);
    }

    public static function fromInt(int $value)
    {
        return new self($value);
    }

    private function __construct(int $value)
    {
        $this->value = $value;
    }

    public function toInt(): int
    {
        return $this->value;
    }
}
