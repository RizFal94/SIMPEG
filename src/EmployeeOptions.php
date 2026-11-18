<?php
/**
 * Daftar pilihan (enum) yang dipakai form, validasi, dan tampilan.
 * Disimpan di satu tempat agar tidak ada data yang "hardcode" berulang.
 */
declare(strict_types=1);

final class EmployeeOptions
{
    public const GENDERS = [
        'L' => 'Laki-laki',
        'P' => 'Perempuan',
    ];

    public const EDUCATIONS = [
        'SMA/SMK',
        'D3',
        'S1',
        'S2',
        'S3',
    ];

    public const STATUSES = [
        'Aktif',
        'Cuti',
        'Nonaktif',
    ];

    public const DEPARTMENTS = [
        'IT',
        'HRD',
        'Keuangan',
        'Marketing',
        'Operasional',
        'Produksi',
    ];

    public const POSITIONS = [
        'Software Engineer',
        'Backend Developer',
        'Frontend Developer',
        'Mobile Developer',
        'QA Engineer',
        'DevOps Engineer',
        'IT Support',
        'IT Manager',
        'HR Specialist',
        'HR Manager',
        'Recruiter',
        'Finance Analyst',
        'Accounting Staff',
        'Marketing Staff',
        'Marketing Manager',
        'Operations Manager',
        'Production Operator',
        'Customer Service',
    ];

    public static function genderLabel(string $value): string
    {
        return self::GENDERS[$value] ?? $value;
    }

    public static function isGender(string $value): bool
    {
        return array_key_exists($value, self::GENDERS);
    }

    public static function isEducation(string $value): bool
    {
        return in_array($value, self::EDUCATIONS, true);
    }

    public static function isStatus(string $value): bool
    {
        return in_array($value, self::STATUSES, true);
    }

    public static function isDepartment(string $value): bool
    {
        return in_array($value, self::DEPARTMENTS, true);
    }
}
