<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/db.php';

$db = getDBConnection();
$action = $_GET['action'] ?? $_POST['action'] ?? 'list';

try {
    if ($action === 'list') {
        $stmt = $db->query("SELECT * FROM clients ORDER BY id DESC");
        $clients = $stmt->fetchAll();
        echo json_encode(['success' => true, 'data' => $clients]);
        exit;
    }

    if ($action === 'save') {
        $id = (int)($_POST['id'] ?? 0);
        $full_name = trim($_POST['full_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $interested_category = trim($_POST['interested_category'] ?? 'Cualquiera');
        $budget = (float)($_POST['budget'] ?? 0);
        $status = trim($_POST['status'] ?? 'Nuevo');

        if (empty($full_name) || empty($email)) {
            echo json_encode(['success' => false, 'message' => 'Nombre y correo son requeridos.']);
            exit;
        }

        if ($id > 0) {
            $stmt = $db->prepare("UPDATE clients SET full_name=?, email=?, phone=?, interested_category=?, budget=?, status=? WHERE id=?");
            $stmt->execute([$full_name, $email, $phone, $interested_category, $budget, $status, $id]);
            echo json_encode(['success' => true, 'message' => 'Cliente actualizado.']);
        } else {
            $stmt = $db->prepare("INSERT INTO clients (full_name, email, phone, interested_category, budget, status) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$full_name, $email, $phone, $interested_category, $budget, $status]);
            echo json_encode(['success' => true, 'message' => 'Cliente registrado correctamente.']);
        }
        exit;
    }

    if ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        $stmt = $db->prepare("DELETE FROM clients WHERE id = ?");
        $stmt->execute([$id]);
        echo json_encode(['success' => true, 'message' => 'Cliente eliminado.']);
        exit;
    }

    echo json_encode(['success' => false, 'message' => 'Acción no válida.']);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
