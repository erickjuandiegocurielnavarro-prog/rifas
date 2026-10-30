<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/db.php';

$db = getDBConnection();
$action = $_GET['action'] ?? $_POST['action'] ?? 'list';

try {
    if ($action === 'list') {
        $stmt = $db->query("SELECT e.*, b.name as branch_name FROM employees e LEFT JOIN branches b ON e.branch_id = b.id ORDER BY e.id DESC");
        $employees = $stmt->fetchAll();
        echo json_encode(['success' => true, 'data' => $employees]);
        exit;
    }

    if ($action === 'save') {
        $id = (int)($_POST['id'] ?? 0);
        $branch_id = (int)($_POST['branch_id'] ?? 1);
        $full_name = trim($_POST['full_name'] ?? '');
        $role = trim($_POST['role'] ?? 'Asesor de Ventas');
        $phone = trim($_POST['phone'] ?? '');
        $email = trim($_POST['email'] ?? '');

        if (empty($full_name) || empty($email)) {
            echo json_encode(['success' => false, 'message' => 'Nombre y correo son requeridos.']);
            exit;
        }

        if ($id > 0) {
            $stmt = $db->prepare("UPDATE employees SET branch_id=?, full_name=?, role=?, phone=?, email=? WHERE id=?");
            $stmt->execute([$branch_id, $full_name, $role, $phone, $email, $id]);
            echo json_encode(['success' => true, 'message' => 'Empleado actualizado.']);
        } else {
            $stmt = $db->prepare("INSERT INTO employees (branch_id, full_name, role, phone, email) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$branch_id, $full_name, $role, $phone, $email]);
            echo json_encode(['success' => true, 'message' => 'Empleado registrado.']);
        }
        exit;
    }

    if ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        $stmt = $db->prepare("DELETE FROM employees WHERE id = ?");
        $stmt->execute([$id]);
        echo json_encode(['success' => true, 'message' => 'Empleado eliminado.']);
        exit;
    }

    echo json_encode(['success' => false, 'message' => 'Acción no válida.']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
