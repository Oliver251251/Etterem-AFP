<?php

class DataRepository {
    
    
   
    private bool $useDatabase = false; // ÁLLÍTSD TRUE-RA, HA BEKÖTÖTTED A DB-T!
    private ?PDO $pdo = null;
    private string $storageFile = __DIR__ . '/storage.json';

    public function __construct() {
        if ($this->useDatabase) {
            $dsn = 'mysql:host=localhost;dbname=etterem_db;charset=utf8mb4';
            $this->pdo = new PDO($dsn, 'root', '', [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
        } else {
            $this->initJsonStorage();
        }
    }

    private function initJsonStorage(): void {
        if (!file_exists($this->storageFile)) {
            $initialData = [
                'etelek' => [
                    ['id' => 1, 'nev' => 'Bolognai', 'kategoria' => 'foetel', 'ar' => 3490, 'leiras' => 'étel leírása ._.', 'elerheto' => true],
                    ['id' => 2, 'nev' => 'Grillezett marhaszelet', 'kategoria' => 'foetel', 'ar' => 5990, 'leiras' => 'étel leírása 2', 'elerheto' => true],
                    ['id' => 3, 'nev' => 'Friss saláta', 'kategoria' => 'eloetel', 'ar' => 2490, 'leiras' => 'mcdonalds hamburger alapanyagok', 'elerheto' => true],
                    ['id' => 4, 'nev' => 'Eper torta', 'kategoria' => 'desszert', 'ar' => 1690, 'leiras' => 'Fincsi süti', 'elerheto' => true],
                    ['id' => 5, 'nev' => 'Pia', 'kategoria' => 'ital', 'ar' => 600, 'leiras' => 'jó sok lesz', 'elerheto' => true],
                    ['id' => 6, 'nev' => 'Leves', 'kategoria' => 'eloetel', 'ar' => 2750, 'leiras' => 'példa a másik designra', 'elerheto' => true]
                ],
                'foglalasok' => [
                    ['id' => 1, 'vendeg_nev' => 'Kovács Ádám', 'email' => 'adam@email.hu', 'datum' => '2026-10-22', 'idopont' => '18:00', 'vendegek_szama' => 2, 'asztal_tipus' => 'normal', 'megjegyzes' => '', 'allapot' => 'Függőben'],
                    ['id' => 2, 'vendeg_nev' => 'Király Sándor', 'email' => 'sandor@email.hu', 'datum' => '2026-10-22', 'idopont' => '19:00', 'vendegek_szama' => 4, 'asztal_tipus' => 'window', 'megjegyzes' => '', 'allapot' => 'Visszaigazolva'],
                    ['id' => 3, 'vendeg_nev' => 'Koczka Ferenc', 'email' => 'ferenc@email.hu', 'datum' => '2026-10-23', 'idopont' => '13:00', 'vendegek_szama' => 3, 'asztal_tipus' => 'normal', 'megjegyzes' => '', 'allapot' => 'Függőben']
                ],
                'idosavok' => ['12:00', '13:00', '14:00', '18:00', '19:00', '20:00'],
                'asztalok_szama' => 8
            ];
            file_put_contents($this->storageFile, json_encode($initialData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        }
    }

    private function readJson(): array {
        return json_decode(file_get_contents($this->storageFile), true);
    }

    private function writeJson(array $data): void {
        file_put_contents($this->storageFile, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }

    // --- ÉTELEK ---
    public function getEtelek(): array {
        if ($this->useDatabase) {
            return $this->pdo->query("SELECT * FROM etelek WHERE elerheto = 1")->fetchAll();
        }
        $data = $this->readJson();
        return array_values(array_filter($data['etelek'], fn($e) => $e['elerheto']));
    }

    public function addEtel(array $input): array {
        $nev = trim($input['food_name'] ?? $input['nev'] ?? '');
        $kategoria = trim($input['category'] ?? $input['kategoria'] ?? '');
        $ar = (int)($input['price'] ?? $input['ar'] ?? 0);
        $leiras = trim($input['description'] ?? $input['leiras'] ?? '');
        $elerheto = isset($input['available']) ? (bool)$input['available'] : true;

        if ($this->useDatabase) {
            $stmt = $this->pdo->prepare("INSERT INTO etelek (nev, kategoria, ar, leiras, elerheto) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$nev, $kategoria, $ar, $leiras, $elerheto ? 1 : 0]);
            return ['id' => (int)$this->pdo->lastInsertId(), 'nev' => $nev, 'kategoria' => $kategoria, 'ar' => $ar, 'leiras' => $leiras, 'elerheto' => $elerheto];
        }

        $data = $this->readJson();
        $newId = count($data['etelek']) > 0 ? max(array_column($data['etelek'], 'id')) + 1 : 1;
        $ujEtel = ['id' => $newId, 'nev' => $nev, 'kategoria' => $kategoria, 'ar' => $ar, 'leiras' => $leiras, 'elerheto' => $elerheto];
        $data['etelek'][] = $ujEtel;
        $this->writeJson($data);
        return $ujEtel;
    }

    // --- FOGLALÁSOK ---
    public function getFoglalasok(): array {
        if ($this->useDatabase) {
            return $this->pdo->query("SELECT * FROM foglalasok ORDER BY datum DESC, idopont ASC")->fetchAll();
        }
        return $this->readJson()['foglalasok'];
    }

    public function addFoglalas(array $input): array {
        $uj = [
            'vendeg_nev' => trim($input['guest_name'] ?? ''),
            'email' => trim($input['email'] ?? ''),
            'datum' => trim($input['date'] ?? ''),
            'idopont' => trim($input['time'] ?? ''),
            'vendegek_szama' => (int)($input['guests'] ?? 2),
            'asztal_tipus' => trim($input['table_type'] ?? 'normal'),
            'megjegyzes' => trim($input['note'] ?? ''),
            'allapot' => 'Függőben'
        ];

        if ($this->useDatabase) {
            $stmt = $this->pdo->prepare("INSERT INTO foglalasok (vendeg_nev, email, datum, idopont, vendegek_szama, asztal_tipus, megjegyzes, allapot) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute(array_values($uj));
            $uj['id'] = (int)$this->pdo->lastInsertId();
            return $uj;
        }

        $data = $this->readJson();
        $uj['id'] = count($data['foglalasok']) > 0 ? max(array_column($data['foglalasok'], 'id')) + 1 : 1;
        $data['foglalasok'][] = $uj;
        $this->writeJson($data);
        return $uj;
    }

    public function updateFoglalasStatus(int $id, string $allapot): bool {
        if ($this->useDatabase) {
            $stmt = $this->pdo->prepare("UPDATE foglalasok SET allapot = ? WHERE id = ?");
            return $stmt->execute([$allapot, $id]);
        }

        $data = $this->readJson();
        foreach ($data['foglalasok'] as &$f) {
            if ($f['id'] === $id) {
                $f['allapot'] = $allapot;
                $this->writeJson($data);
                return true;
            }
        }
        return false;
    }

    // --- STATISZTIKÁK ---
    public function getStatisztikak(): array {
        if ($this->useDatabase) {
            return [
                'maiFoglalasok' => (int)$this->pdo->query("SELECT COUNT(*) FROM foglalasok")->fetchColumn(),
                'szabadAsztalok' => (int)$this->pdo->query("SELECT COUNT(*) FROM asztalok")->fetchColumn(),
                'etlaponEtelek' => (int)$this->pdo->query("SELECT COUNT(*) FROM etelek WHERE elerheto = 1")->fetchColumn()
            ];
        }

        $data = $this->readJson();
        return [
            'maiFoglalasok' => count($data['foglalasok']),
            'szabadAsztalok' => $data['asztalok_szama'],
            'etlaponEtelek' => count(array_filter($data['etelek'], fn($e) => $e['elerheto']))
        ];
    }
}
