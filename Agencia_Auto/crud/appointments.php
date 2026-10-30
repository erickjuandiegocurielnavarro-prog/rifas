<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/db.php';

$db = getDBConnection();
$action = $_GET['action'] ?? $_POST['action'] ?? 'list';

try {
    if ($action === 'list') {
        $stmt = $db->query("SELECT * FROM appointments ORDER BY id DESC");
        $appointments = $stmt->fetchAll();
        echo json_encode(['success' => true, 'data' => $appointments]);
        exit;
    }

    if ($action === 'create') {
        $vehicle_id = (int)($_POST['vehicle_id'] ?? 0);
        $vehicle_title = trim($_POST['vehicle_title'] ?? 'Consulta General');
        $client_name = trim($_POST['client_name'] ?? '');
        $client_email = trim($_POST['client_email'] ?? '');
        $client_phone = trim($_POST['client_phone'] ?? '');
        $appointment_date = trim($_POST['appointment_date'] ?? date('Y-m-d'));
        $type = trim($_POST['type'] ?? 'Prueba de Manejo');
        $notes = trim($_POST['notes'] ?? '');

        if (empty($client_name) || empty($client_email) || empty($client_phone)) {
            echo json_encode(['success' => false, 'message' => 'Por favor complete todos los datos de contacto.']);
            exit;
        }

        // Registrar la cita
        $stmt = $db->prepare("INSERT INTO appointments 
            (vehicle_id, vehicle_title, client_name, client_email, client_phone, appointment_date, type, notes, status) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'Pendiente')");
        $stmt->execute([$vehicle_id, $vehicle_title, $client_name, $client_email, $client_phone, $appointment_date, $type, $notes]);

        // Registrar o actualizar cliente automáticamente en la lista de clientes
        $stmtCheckClient = $db->prepare("SELECT id FROM clients WHERE email = ?");
        $stmtCheckClient->execute([$client_email]);
        if (!$stmtCheckClient->fetch()) {
            $stmtClient = $db->prepare("INSERT INTO clients (full_name, email, phone, status) VALUES (?, ?, ?, 'Nuevo')");
            $stmtClient->execute([$client_name, $client_email, $client_phone]);
        }

        echo json_encode(['success' => true, 'message' => '¡Tu solicitud ha sido enviada con éxito! Un asesor te contactará pronto.']);
        exit;
    }

    if ($action === 'update_status') {
        $id = (int)($_POST['id'] ?? 0);
        $status = trim($_POST['status'] ?? 'Pendiente');
        $stmt = $db->prepare("UPDATE appointments SET status = ? WHERE id = ?");
        $stmt->execute([$status, $id]);
        echo json_encode(['success' => true, 'message' => 'Estado de cita actualizado.']);
        exit;
    }

    if ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        $stmt = $db->prepare("DELETE FROM appointments WHERE id = ?");
        $stmt->execute([$id]);
        echo json_encode(['success' => true, 'message' => 'Cita eliminada.']);
        exit;
    }

    echo json_encode(['success' => false, 'message' => 'Acción no válida.']);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error en el servidor: ' . $e->getMessage()]);
}
