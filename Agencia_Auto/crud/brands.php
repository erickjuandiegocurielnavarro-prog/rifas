<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/db.php';

$db = getDBConnection();
$action = $_GET['action'] ?? $_POST['action'] ?? 'list';

try {
    if ($action === 'list') {
        $stmt = $db->query("SELECT * FROM brands ORDER BY name ASC");
        $brands = $stmt->fetchAll();
        echo json_encode(['success' => true, 'data' => $brands]);
        exit;
    }

    if ($action === 'save') {
        $id = (int)($_POST['id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $country = trim($_POST['country'] ?? 'Internacional');
        $logo_icon = trim($_POST['logo_icon'] ?? 'fa-car');

        if (empty($name)) {
            echo json_encode(['success' => false, 'message' => 'El nombre de la marca es obligatorio.']);
            exit;
        }

        if ($id > 0) {
            $stmt = $db->prepare("UPDATE brands SET name=?, country=?, logo_icon=? WHERE id=?");
            $stmt->execute([$name, $country, $logo_icon, $id]);
            echo json_encode(['success' => true, 'message' => 'Marca actualizada.']);
        } else {
            $stmt = $db->prepare("INSERT INTO brands (name, country, logo_icon) VALUES (?, ?, ?)");
            $stmt->execute([$name, $country, $logo_icon]);
            echo json_encode(['success' => true, 'message' => 'Marca agregada correctamente.']);
        }
        exit;
    }

    if ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        $stmt = $db->prepare("DELETE FROM brands WHERE id = ?");
        $stmt->execute([$id]);
        echo json_encode(['success' => true, 'message' => 'Marca eliminada.']);
        exit;
    }

    echo json_encode(['success' => false, 'message' => 'Acción no válida.']);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
