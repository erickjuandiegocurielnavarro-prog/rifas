<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/db.php';

$db = getDBConnection();
$action = $_GET['action'] ?? $_POST['action'] ?? 'list';

try {
    if ($action === 'list') {
        $stmt = $db->query("SELECT * FROM branches ORDER BY id ASC");
        $branches = $stmt->fetchAll();
        echo json_encode(['success' => true, 'data' => $branches]);
        exit;
    }

    if ($action === 'save') {
        $id = (int)($_POST['id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $address = trim($_POST['address'] ?? '');
        $city = trim($_POST['city'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $email = trim($_POST['email'] ?? '');

        if (empty($name) || empty($city)) {
            echo json_encode(['success' => false, 'message' => 'Nombre y ciudad de la sucursal son requeridos.']);
            exit;
        }

        if ($id > 0) {
            $stmt = $db->prepare("UPDATE branches SET name=?, address=?, city=?, phone=?, email=? WHERE id=?");
            $stmt->execute([$name, $address, $city, $phone, $email, $id]);
            echo json_encode(['success' => true, 'message' => 'Sucursal actualizada.']);
        } else {
            $stmt = $db->prepare("INSERT INTO branches (name, address, city, phone, email) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$name, $address, $city, $phone, $email]);
            echo json_encode(['success' => true, 'message' => 'Sucursal creada exitosamente.']);
        }
        exit;
    }

    if ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        $stmt = $db->prepare("DELETE FROM branches WHERE id = ?");
        $stmt->execute([$id]);
        echo json_encode(['success' => true, 'message' => 'Sucursal eliminada.']);
        exit;
    }

    echo json_encode(['success' => false, 'message' => 'Acción no válida.']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
