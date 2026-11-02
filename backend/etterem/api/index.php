<?php
session_start();
require_once __DIR__ . '/DataRepository.php';

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PATCH, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

$repo = new DataRepository();
$method = $_SERVER['REQUEST_METHOD'];

// URL felbontása: pl. /api/index.php/foglalasok/1 vagy /api/foglalasok/1
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uriParts = array_values(array_filter(explode('/', trim($uri, '/')), fn($p) => $p !== '' && $p !== 'index.php'));
$apiIndex = array_search('api', $uriParts);

$resource = $apiIndex !== false ? ($uriParts[$apiIndex + 1] ?? '') : ($uriParts[0] ?? '');
$id = ($apiIndex !== false && isset($uriParts[$apiIndex + 2])) ? (int)$uriParts[$apiIndex + 2] : null;

// JSON body beolvasása
$body = json_decode(file_get_contents('php://input'), true) ?? $_POST;

try {
    switch ($resource) {
        // 1. ÉTELEK VÉGPONT (GET /api/etelek, POST /api/etelek)
        case 'etelek':
            if ($method === 'GET') {
                echo json_encode(['status' => 'success', 'data' => $repo->getEtelek()]);
            } elseif ($method === 'POST') {
                if (empty($body['food_name']) && empty($body['nev'])) {
                    http_response_code(400);
                    echo json_encode(['status' => 'error', 'message' => 'Az étel neve kötelező!']);
                    exit;
                }
                $created = $repo->addEtel($body);
                http_response_code(201);
                echo json_encode(['status' => 'success', 'message' => 'Új étel hozzáadva!', 'data' => $created]);
            } else {
                http_response_code(405);
                echo json_encode(['status' => 'error', 'message' => 'Nem támogatott HTTP metódus.']);
            }
            break;

        // 2. FOGLALÁSOK VÉGPONT (GET /api/foglalasok, POST /api/foglalasok, PATCH /api/foglalasok/{id})
        case 'foglalasok':
            if ($method === 'GET') {
                echo json_encode(['status' => 'success', 'data' => $repo->getFoglalasok()]);
            } elseif ($method === 'POST') {
                if (empty($body['guest_name']) || empty($body['email']) || empty($body['date']) || empty($body['time'])) {
                    http_response_code(400);
                    echo json_encode(['status' => 'error', 'message' => 'Minden kötelező mezőt tölts ki!']);
                    exit;
                }
                $created = $repo->addFoglalas($body);
                http_response_code(201);
                echo json_encode(['status' => 'success', 'message' => 'Foglalás sikeresen rögzítve!', 'data' => $created]);
            } elseif ($method === 'PATCH' && $id) {
                $allapot = $body['allapot'] ?? 'Visszaigazolva';
                $updated = $repo->updateFoglalasStatus($id, $allapot);
                if ($updated) {
                    echo json_encode(['status' => 'success', 'message' => 'Foglalás állapota frissítve!']);
                } else {
                    http_response_code(404);
                    echo json_encode(['status' => 'error', 'message' => 'A foglalás nem található.']);
                }
            } else {
                http_response_code(405);
                echo json_encode(['status' => 'error', 'message' => 'Nem támogatott metódus vagy hiányzó ID.']);
            }
            break;

        // 3. STATISZTIKÁK VÉGPONT (GET /api/statisztikak)
        case 'statisztikak':
            if ($method === 'GET') {
                echo json_encode(['status' => 'success', 'data' => $repo->getStatisztikak()]);
            } else {
                http_response_code(405);
            }
            break;

        // 4. BEJELENTKEZÉS VÉGPONT (POST /api/login)
        case 'login':
            if ($method === 'POST') {
                $email = trim($body['email'] ?? '');
                $password = trim($body['password'] ?? '');
                $role = $body['role'] ?? 'guest';

                if ($email === '' || $password === '') {
                    http_response_code(400);
                    echo json_encode(['status' => 'error', 'message' => 'E-mail és jelszó megadása kötelező!']);
                    exit;
                }

                if ($role === 'admin' && $email !== 'admin@etterem.hu') {
                    http_response_code(401);
                    echo json_encode(['status' => 'error', 'message' => 'Hibás admin e-mail! (Demó: admin@etterem.hu)']);
                    exit;
                }

                $_SESSION['user'] = ['email' => $email, 'role' => $role];
                echo json_encode(['status' => 'success', 'message' => 'Sikeres bejelentkezés!', 'user' => $_SESSION['user']]);
            } else {
                http_response_code(405);
            }
            break;

        default:
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => 'Ismeretlen REST API végpont: ' . htmlspecialchars($resource)]);
            break;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Szerverhiba: ' . $e->getMessage()]);
}
