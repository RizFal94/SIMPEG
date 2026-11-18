<?php
/**
 * DashboardService
 * ----------------
 * Menyusun data agregat untuk dashboard: kartu statistik, distribusi jenis
 * kelamin, pendidikan, dan kelompok umur. Perhitungan umur dilakukan di PHP
 * agar tidak bergantung pada fungsi tanggal khusus MySQL dan mudah diuji.
 */
declare(strict_types=1);

final class DashboardService
{
    public function __construct(private EmployeeRepository $repository)
    {
    }

    /**
     * @return array{
     *   statistics: array<string, int>,
     *   average_age: int,
     *   youngest: ?int,
     *   oldest: ?int,
     *   gender: array{labels: array<int,string>, values: array<int,int>},
     *   education: array{labels: array<int,string>, values: array<int,int>},
     *   age: array{labels: array<int,string>, values: array<int,int>},
     *   status: array<string,int>
     * }
     */
    public function summary(): array
    {
        $statistics = $this->repository->statistics();
        $education  = $this->repository->educationCounts();
        $ages       = $this->ages();

        return [
            'statistics' => $statistics,
            'average_age' => $ages === [] ? 0 : (int) round(array_sum($ages) / count($ages)),
            'youngest'    => $ages === [] ? null : min($ages),
            'oldest'      => $ages === [] ? null : max($ages),
            'gender' => [
                'labels' => ['Laki-laki', 'Perempuan'],
                'values' => [$statistics['laki_laki'], $statistics['perempuan']],
            ],
            'education' => [
                'labels' => array_keys($education),
                'values' => array_values($education),
            ],
            'age' => [
                'labels' => ['< 25', '25-34', '35-44', '45-54', '55+'],
                'values' => array_values($this->ageDistribution($ages)),
            ],
            'status' => [
                'Aktif'    => $statistics['aktif'],
                'Cuti'     => $statistics['cuti'],
                'Nonaktif' => $statistics['nonaktif'],
            ],
        ];
    }

    /** @return array<int, int> daftar umur pegawai yang valid */
    private function ages(): array
    {
        $ages = [];

        foreach ($this->repository->allBirthdates() as $birthdate) {
            $age = age_from_birthdate($birthdate);
            if ($age !== null) {
                $ages[] = $age;
            }
        }

        return $ages;
    }

    /**
     * @param array<int, int> $ages
     * @return array<string, int>
     */
    private function ageDistribution(array $ages): array
    {
        $groups = array_fill_keys(age_group_order(), 0);

        foreach ($ages as $age) {
            $groups[age_group_of($age)]++;
        }

        return $groups;
    }
}
