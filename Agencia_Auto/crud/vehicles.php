<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../setup.php';

$db = getDBConnection();
$action = $_GET['action'] ?? $_POST['action'] ?? 'list';

try {
    if ($action === 'list') {
        $category = $_GET['category'] ?? 'todos';
        $brand = $_GET['brand'] ?? 'todas';
        $search = trim($_GET['search'] ?? '');
        $status = $_GET['status'] ?? '';

        $sql = "SELECT v.*, b.name as branch_name, b.city as branch_city 
                FROM vehicles v 
                LEFT JOIN branches b ON v.branch_id = b.id 
                WHERE 1=1";
        $params = [];

        if ($category !== 'todos' && !empty($category)) {
            $sql .= " AND v.category = ?";
            $params[] = $category;
        }
        if ($brand !== 'todas' && !empty($brand)) {
            $sql .= " AND v.brand_name = ?";
            $params[] = $brand;
        }
        if (!empty($status)) {
            $sql .= " AND v.status = ?";
            $params[] = $status;
        }
        if (!empty($search)) {
            $sql .= " AND (v.title LIKE ? OR v.description LIKE ? OR v.brand_name LIKE ?)";
            $searchTerm = "%$search%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }

        $sql .= " ORDER BY v.id DESC";
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $vehicles = $stmt->fetchAll();

        echo json_encode(['success' => true, 'data' => $vehicles]);
        exit;
    }

    if ($action === 'get') {
        $id = (int)($_GET['id'] ?? 0);
        $stmt = $db->prepare("SELECT v.*, b.name as branch_name, b.city as branch_city 
                              FROM vehicles v 
                              LEFT JOIN branches b ON v.branch_id = b.id 
                              WHERE v.id = ?");
        $stmt->execute([$id]);
        $vehicle = $stmt->fetch();

        if ($vehicle) {
            $stmtFeat = $db->prepare("SELECT fm.feature_name, fm.feature_category, fm.icon 
                                      FROM vehicle_features vf 
                                      JOIN features_master fm ON vf.feature_id = fm.id 
                                      WHERE vf.vehicle_id = ?");
            $stmtFeat->execute([$id]);
            $vehicle['features'] = $stmtFeat->fetchAll();

            echo json_encode(['success' => true, 'data' => $vehicle]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Vehículo no encontrado.']);
        }
        exit;
    }

    if ($action === 'save') {
        $id = (int)($_POST['id'] ?? 0);
        $title = trim($_POST['title'] ?? '');
        $brand_name = trim($_POST['brand_name'] ?? '');
        $category = trim($_POST['category'] ?? '');
        $branch_id = (int)($_POST['branch_id'] ?? 1);
        $model_year = (int)($_POST['model_year'] ?? date('Y'));
        $price = (float)($_POST['price'] ?? 0);
        $mileage = (int)($_POST['mileage'] ?? 0);
        $transmission = trim($_POST['transmission'] ?? 'Automática');
        $fuel_type = trim($_POST['fuel_type'] ?? 'Gasolina');
        $engine = trim($_POST['engine'] ?? '2.0L Turbo');
        $horsepower = (int)($_POST['horsepower'] ?? 200);
        $status = trim($_POST['status'] ?? 'Disponible');
        $image_url = trim($_POST['image_url'] ?? 'assets/images/car_1.png');
        $video_url = trim($_POST['video_url'] ?? '');
        $description = trim($_POST['description'] ?? '');

        if (empty($title) || empty($brand_name) || empty($category) || $price <= 0) {
            echo json_encode(['success' => false, 'message' => 'Todos los campos obligatorios deben completarse.']);
            exit;
        }

        if ($id > 0) {
            $stmt = $db->prepare("UPDATE vehicles SET 
                title=?, brand_name=?, category=?, branch_id=?, model_year=?, price=?, mileage=?, 
                transmission=?, fuel_type=?, engine=?, horsepower=?, status=?, image_url=?, video_url=?, description=?
                WHERE id=?");
            $stmt->execute([
                $title, $brand_name, $category, $branch_id, $model_year, $price, $mileage,
                $transmission, $fuel_type, $engine, $horsepower, $status, $image_url, $video_url, $description, $id
            ]);
            echo json_encode(['success' => true, 'message' => 'Vehículo actualizado con éxito.']);
        } else {
            $stmt = $db->prepare("INSERT INTO vehicles 
                (title, brand_name, category, branch_id, model_year, price, mileage, transmission, fuel_type, engine, horsepower, status, image_url, video_url, description) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $title, $brand_name, $category, $branch_id, $model_year, $price, $mileage,
                $transmission, $fuel_type, $engine, $horsepower, $status, $image_url, $video_url, $description
            ]);
            echo json_encode(['success' => true, 'message' => 'Vehículo registrado correctamente.']);
        }
        exit;
    }

    if ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        $stmt = $db->prepare("DELETE FROM vehicles WHERE id = ?");
        $stmt->execute([$id]);
        echo json_encode(['success' => true, 'message' => 'Vehículo eliminado correctamente.']);
        exit;
    }

    if ($action === 'toggle_status' || $action === 'toggle_agotado') {
        $id = (int)($_POST['id'] ?? 0);
        $status = trim($_POST['status'] ?? '');

        if (empty($status)) {
            // Si no viene status explícito, alterna entre Agotado y Disponible
            $stmtCurrent = $db->prepare("SELECT status FROM vehicles WHERE id = ?");
            $stmtCurrent->execute([$id]);
            $currentStatus = $stmtCurrent->fetchColumn();
            $status = ($currentStatus === 'Agotado') ? 'Disponible' : 'Agotado';
        }

        $stmt = $db->prepare("UPDATE vehicles SET status = ? WHERE id = ?");
        $stmt->execute([$status, $id]);

        echo json_encode([
            'success' => true, 
            'message' => "Estado del vehículo #{$id} cambiado a '{$status}' correctamente.",
            'new_status' => $status
        ]);
        exit;
    }

    echo json_encode(['success' => false, 'message' => 'Acción no válida.']);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error en el servidor: ' . $e->getMessage()]);
}
