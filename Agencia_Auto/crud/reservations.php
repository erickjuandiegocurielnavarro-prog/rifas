<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../setup.php';

$db = getDBConnection();
$action = $_GET['action'] ?? $_POST['action'] ?? 'list';

try {
    if ($action === 'list') {
        $stmt = $db->query("SELECT r.*, v.image_url, v.price as vehicle_price 
                            FROM reservations r 
                            LEFT JOIN vehicles v ON r.vehicle_id = v.id 
                            ORDER BY r.id DESC");
        $reservations = $stmt->fetchAll();
        echo json_encode(['success' => true, 'data' => $reservations]);
        exit;
    }

    if ($action === 'create' || $action === 'save') {
        $id = (int)($_POST['id'] ?? 0);
        $vehicle_id = (int)($_POST['vehicle_id'] ?? 0);
        $vehicle_title = trim($_POST['vehicle_title'] ?? '');
        $client_name = trim($_POST['client_name'] ?? '');
        $client_email = trim($_POST['client_email'] ?? '');
        $client_phone = trim($_POST['client_phone'] ?? '');
        $deposit_amount = (float)($_POST['deposit_amount'] ?? 5000);
        $estimated_delivery_date = trim($_POST['estimated_delivery_date'] ?? date('Y-m-d', strtotime('+15 days')));
        $notes = trim($_POST['notes'] ?? '');
        $status = trim($_POST['status'] ?? 'Activo');

        if (empty($client_name) || empty($client_email) || empty($client_phone) || $vehicle_id <= 0) {
            echo json_encode(['success' => false, 'message' => 'Selecciona un vehículo e ingresa los datos completos del cliente.']);
            exit;
        }

        // Verificar si el vehículo ya está marcado como Agotado
        $stmtVehCheck = $db->prepare("SELECT status FROM vehicles WHERE id = ?");
        $stmtVehCheck->execute([$vehicle_id]);
        $vehStatus = $stmtVehCheck->fetchColumn();
        if ($vehStatus === 'Agotado') {
            echo json_encode(['success' => false, 'message' => 'Este vehículo se encuentra AGOTADO y no puede ser reservado.']);
            exit;
        }

        if ($id > 0) {
            $stmt = $db->prepare("UPDATE reservations SET 
                vehicle_id=?, vehicle_title=?, client_name=?, client_email=?, client_phone=?, 
                deposit_amount=?, estimated_delivery_date=?, notes=?, status=? 
                WHERE id=?");
            $stmt->execute([
                $vehicle_id, $vehicle_title, $client_name, $client_email, $client_phone, 
                $deposit_amount, $estimated_delivery_date, $notes, $status, $id
            ]);
            echo json_encode(['success' => true, 'message' => 'Apartado actualizado correctamente.']);
        } else {
            $stmt = $db->prepare("INSERT INTO reservations 
                (vehicle_id, vehicle_title, client_name, client_email, client_phone, deposit_amount, estimated_delivery_date, notes, status) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $vehicle_id, $vehicle_title, $client_name, $client_email, $client_phone, 
                $deposit_amount, $estimated_delivery_date, $notes, $status
            ]);

            // Actualizar el estado del auto a "Reservado" en la tabla vehicles
            $stmtVeh = $db->prepare("UPDATE vehicles SET status = 'Reservado' WHERE id = ?");
            $stmtVeh->execute([$vehicle_id]);

            // Guardar cliente en directorio si no existe
            $stmtCheckClient = $db->prepare("SELECT id FROM clients WHERE email = ?");
            $stmtCheckClient->execute([$client_email]);
            if (!$stmtCheckClient->fetch()) {
                $stmtClient = $db->prepare("INSERT INTO clients (full_name, email, phone, status) VALUES (?, ?, ?, 'En Seguimiento')");
                $stmtClient->execute([$client_name, $client_email, $client_phone]);
            }

            echo json_encode(['success' => true, 'message' => '¡Felicidades! El apartado del vehículo ha sido registrado con éxito.']);
        }
        exit;
    }

    if ($action === 'update_status') {
        $id = (int)($_POST['id'] ?? 0);
        $status = trim($_POST['status'] ?? 'Activo');

        // Obtener vehicle_id asociado al apartado
        $stmtGet = $db->prepare("SELECT vehicle_id FROM reservations WHERE id = ?");
        $stmtGet->execute([$id]);
        $res = $stmtGet->fetch();

        $stmt = $db->prepare("UPDATE reservations SET status = ? WHERE id = ?");
        $stmt->execute([$status, $id]);

        if ($res && $res['vehicle_id']) {
            if ($status === 'Concluido' || $status === 'Vendido') {
                $db->prepare("UPDATE vehicles SET status = 'Vendido' WHERE id = ?")->execute([$res['vehicle_id']]);
            } else if ($status === 'Cancelado') {
                $db->prepare("UPDATE vehicles SET status = 'Disponible' WHERE id = ?")->execute([$res['vehicle_id']]);
            } else if ($status === 'Activo') {
                $db->prepare("UPDATE vehicles SET status = 'Reservado' WHERE id = ?")->execute([$res['vehicle_id']]);
            }
        }

        echo json_encode(['success' => true, 'message' => 'Estado del apartado actualizado.']);
        exit;
    }

    if ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        $stmt = $db->prepare("DELETE FROM reservations WHERE id = ?");
        $stmt->execute([$id]);
        echo json_encode(['success' => true, 'message' => 'Registro de apartado eliminado.']);
        exit;
    }

    echo json_encode(['success' => false, 'message' => 'Acción no válida.']);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
