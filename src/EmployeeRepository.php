<?php
/**
 * EmployeeRepository
 * ------------------
 * Satu-satunya lapisan yang berbicara dengan tabel `employees`.
 * Semua query memakai prepared statement (anti SQL Injection) dan
 * kolom yang boleh diurutkan dibatasi lewat whitelist.
 */
declare(strict_types=1);

final class EmployeeRepository
{
    private const SORTS = [
        'terbaru'    => 'id DESC',
        'nama_asc'   => 'nama ASC',
        'nama_desc'  => 'nama DESC',
        'masuk_asc'  => 'tanggal_masuk ASC',
        'masuk_desc' => 'tanggal_masuk DESC',
    ];

    private const SELECT_COLUMNS = 'id, nip, nama, jenis_kelamin, tanggal_lahir, pendidikan_terakhir,
        jabatan, departemen, email, telepon, alamat, tanggal_masuk, status, created_at, updated_at';

    public function __construct(private PDO $pdo)
    {
    }

    /**
     * Mengambil data pegawai dengan pencarian, filter, urutan, dan paginasi.
     *
     * @return array{items: array<int, array>, total: int, page: int, pages: int, from: int, to: int}
     */
    public function paginate(array $filters, int $page, int $perPage): array
    {
        [$where, $params] = $this->buildFilter($filters);
        $whereSql = $where === [] ? '' : ' WHERE ' . implode(' AND ', $where);

        $countStatement = $this->pdo->prepare('SELECT COUNT(*) FROM employees' . $whereSql);
        $this->bindParams($countStatement, $params);
        $countStatement->execute();
        $total = (int) $countStatement->fetchColumn();

        $pages = max(1, (int) ceil($total / $perPage));
        $page  = max(1, min($page, $pages));
        $offset = ($page - 1) * $perPage;

        $sort = self::SORTS[$filters['sort'] ?? 'terbaru'] ?? self::SORTS['terbaru'];

        $sql = 'SELECT ' . self::SELECT_COLUMNS . ' FROM employees' . $whereSql
            . ' ORDER BY ' . $sort . ' LIMIT :limit OFFSET :offset';

        $statement = $this->pdo->prepare($sql);
        $this->bindParams($statement, $params);
        $statement->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $statement->bindValue(':offset', $offset, PDO::PARAM_INT);
        $statement->execute();

        return [
            'items' => $statement->fetchAll(),
            'total' => $total,
            'page'  => $page,
            'pages' => $pages,
            'from'  => $total === 0 ? 0 : $offset + 1,
            'to'    => min($offset + $perPage, $total),
        ];
    }

    /** @return array<string, mixed>|null */
    public function find(int $id): ?array
    {
        $statement = $this->pdo->prepare('SELECT ' . self::SELECT_COLUMNS . ' FROM employees WHERE id = :id LIMIT 1');
        $statement->execute([':id' => $id]);
        $employee = $statement->fetch();

        return $employee === false ? null : $employee;
    }

    public function create(array $data): int
    {
        $sql = 'INSERT INTO employees
                (nip, nama, jenis_kelamin, tanggal_lahir, pendidikan_terakhir, jabatan, departemen,
                 email, telepon, alamat, tanggal_masuk, status)
                VALUES
                (:nip, :nama, :jenis_kelamin, :tanggal_lahir, :pendidikan_terakhir, :jabatan, :departemen,
                 :email, :telepon, :alamat, :tanggal_masuk, :status)';

        $statement = $this->pdo->prepare($sql);
        $statement->execute($this->writeParams($data));

        return (int) $this->pdo->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $sql = 'UPDATE employees SET
                    nip = :nip,
                    nama = :nama,
                    jenis_kelamin = :jenis_kelamin,
                    tanggal_lahir = :tanggal_lahir,
                    pendidikan_terakhir = :pendidikan_terakhir,
                    jabatan = :jabatan,
                    departemen = :departemen,
                    email = :email,
                    telepon = :telepon,
                    alamat = :alamat,
                    tanggal_masuk = :tanggal_masuk,
                    status = :status
                WHERE id = :id';

        $statement = $this->pdo->prepare($sql);
        $params = $this->writeParams($data);
        $params[':id'] = $id;
        $statement->execute($params);

        return $statement->rowCount() > 0;
    }

    public function delete(int $id): bool
    {
        $statement = $this->pdo->prepare('DELETE FROM employees WHERE id = :id');
        $statement->execute([':id' => $id]);

        return $statement->rowCount() > 0;
    }

    public function nipExists(string $nip, ?int $ignoreId = null): bool
    {
        return $this->columnExists('nip', $nip, $ignoreId);
    }

    public function emailExists(string $email, ?int $ignoreId = null): bool
    {
        $sql = 'SELECT COUNT(*) FROM employees WHERE LOWER(email) = LOWER(:value)';
        $params = [':value' => $email];

        if ($ignoreId !== null) {
            $sql .= ' AND id <> :ignore_id';
            $params[':ignore_id'] = $ignoreId;
        }

        $statement = $this->pdo->prepare($sql);
        $statement->execute($params);

        return (int) $statement->fetchColumn() > 0;
    }

    /**
     * Ringkasan angka untuk kartu statistik dashboard.
     *
     * @return array{total:int, laki_laki:int, perempuan:int, aktif:int, cuti:int, nonaktif:int}
     */
    public function statistics(): array
    {
        $row = $this->pdo->query(
            "SELECT
                COUNT(*) AS total,
                COALESCE(SUM(jenis_kelamin = 'L'), 0) AS laki_laki,
                COALESCE(SUM(jenis_kelamin = 'P'), 0) AS perempuan,
                COALESCE(SUM(status = 'Aktif'), 0) AS aktif,
                COALESCE(SUM(status = 'Cuti'), 0) AS cuti,
                COALESCE(SUM(status = 'Nonaktif'), 0) AS nonaktif
             FROM employees"
        )->fetch();

        return [
            'total'      => (int) ($row['total'] ?? 0),
            'laki_laki'  => (int) ($row['laki_laki'] ?? 0),
            'perempuan'  => (int) ($row['perempuan'] ?? 0),
            'aktif'      => (int) ($row['aktif'] ?? 0),
            'cuti'       => (int) ($row['cuti'] ?? 0),
            'nonaktif'   => (int) ($row['nonaktif'] ?? 0),
        ];
    }

    /**
     * Jumlah pegawai per pendidikan, selalu lengkap sesuai urutan pilihan.
     *
     * @return array<string, int>
     */
    public function educationCounts(): array
    {
        $counts = array_fill_keys(EmployeeOptions::EDUCATIONS, 0);

        $rows = $this->pdo->query(
            'SELECT pendidikan_terakhir, COUNT(*) AS total FROM employees GROUP BY pendidikan_terakhir'
        )->fetchAll();

        foreach ($rows as $row) {
            $key = (string) $row['pendidikan_terakhir'];
            if (array_key_exists($key, $counts)) {
                $counts[$key] = (int) $row['total'];
            }
        }

        return $counts;
    }

    /** @return array<int, string> daftar tanggal lahir seluruh pegawai */
    public function allBirthdates(): array
    {
        return array_map(
            static fn (array $row): string => (string) $row['tanggal_lahir'],
            $this->pdo->query('SELECT tanggal_lahir FROM employees')->fetchAll()
        );
    }

    /** @return array<int, array> */
    public function recent(int $limit = 5): array
    {
        $statement = $this->pdo->prepare(
            'SELECT id, nip, nama, jabatan, departemen, pendidikan_terakhir, jenis_kelamin, tanggal_masuk, status
             FROM employees ORDER BY created_at DESC, id DESC LIMIT :limit'
        );
        $statement->bindValue(':limit', $limit, PDO::PARAM_INT);
        $statement->execute();

        return $statement->fetchAll();
    }

    /**
     * @return array{0: array<int, string>, 1: array<string, mixed>}
     */
    private function buildFilter(array $filters): array
    {
        $where  = [];
        $params = [];

        $keyword = trim((string) ($filters['q'] ?? ''));
        if ($keyword !== '') {
            // Native prepared statement MySQL tidak mengizinkan satu placeholder
            // dipakai berulang, sehingga setiap kolom memakai placeholder sendiri.
            $where[] = '(nama LIKE :keyword_nama OR nip LIKE :keyword_nip OR email LIKE :keyword_email
                         OR jabatan LIKE :keyword_jabatan OR departemen LIKE :keyword_departemen)';
            $keywordParam = '%' . $keyword . '%';
            $params[':keyword_nama']       = $keywordParam;
            $params[':keyword_nip']        = $keywordParam;
            $params[':keyword_email']      = $keywordParam;
            $params[':keyword_jabatan']    = $keywordParam;
            $params[':keyword_departemen'] = $keywordParam;
        }

        $gender = (string) ($filters['gender'] ?? '');
        if (EmployeeOptions::isGender($gender)) {
            $where[] = 'jenis_kelamin = :gender';
            $params[':gender'] = $gender;
        }

        $education = (string) ($filters['education'] ?? '');
        if (EmployeeOptions::isEducation($education)) {
            $where[] = 'pendidikan_terakhir = :education';
            $params[':education'] = $education;
        }

        $status = (string) ($filters['status'] ?? '');
        if (EmployeeOptions::isStatus($status)) {
            $where[] = 'status = :status';
            $params[':status'] = $status;
        }

        return [$where, $params];
    }

    private function bindParams(PDOStatement $statement, array $params): void
    {
        foreach ($params as $key => $value) {
            $statement->bindValue($key, $value);
        }
    }

    private function columnExists(string $column, string $value, ?int $ignoreId): bool
    {
        // Nama kolom berasal dari kode (bukan input user), jadi aman diinterpolasi.
        $sql = 'SELECT COUNT(*) FROM employees WHERE ' . $column . ' = :value';
        $params = [':value' => $value];

        if ($ignoreId !== null) {
            $sql .= ' AND id <> :ignore_id';
            $params[':ignore_id'] = $ignoreId;
        }

        $statement = $this->pdo->prepare($sql);
        $statement->execute($params);

        return (int) $statement->fetchColumn() > 0;
    }

    /** @return array<string, mixed> */
    private function writeParams(array $data): array
    {
        return [
            ':nip'                => $data['nip'],
            ':nama'               => $data['nama'],
            ':jenis_kelamin'      => $data['jenis_kelamin'],
            ':tanggal_lahir'      => $data['tanggal_lahir'],
            ':pendidikan_terakhir'=> $data['pendidikan_terakhir'],
            ':jabatan'            => $data['jabatan'],
            ':departemen'         => $data['departemen'],
            ':email'              => $data['email'],
            ':telepon'            => $data['telepon'],
            ':alamat'             => $data['alamat'] !== '' ? $data['alamat'] : null,
            ':tanggal_masuk'      => $data['tanggal_masuk'],
            ':status'             => $data['status'],
        ];
    }
}
