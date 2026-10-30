<?php
require_once __DIR__ . '/config/db.php';

function runSetup() {
    $db = getDBConnection();
    $driver = $db->getAttribute(PDO::ATTR_DRIVER_NAME);

    $pkAuto = ($driver === 'sqlite') ? "INTEGER PRIMARY KEY AUTOINCREMENT" : "INT AUTO_INCREMENT PRIMARY KEY";

    // 1. Tablas Core
    $db->exec("CREATE TABLE IF NOT EXISTS brands (
        id $pkAuto,
        name VARCHAR(100) NOT NULL,
        country VARCHAR(100) DEFAULT 'Internacional',
        website VARCHAR(150) DEFAULT NULL,
        logo_icon VARCHAR(50) DEFAULT 'fa-car'
    )");

    $db->exec("CREATE TABLE IF NOT EXISTS models (
        id $pkAuto,
        brand_id INT NOT NULL,
        name VARCHAR(100) NOT NULL,
        type VARCHAR(50) DEFAULT 'Deportivo'
    )");

    $db->exec("CREATE TABLE IF NOT EXISTS categories (
        id $pkAuto,
        name VARCHAR(50) NOT NULL,
        description VARCHAR(255) DEFAULT NULL
    )");

    $db->exec("CREATE TABLE IF NOT EXISTS branches (
        id $pkAuto,
        name VARCHAR(100) NOT NULL,
        address VARCHAR(200) NOT NULL,
        city VARCHAR(100) NOT NULL,
        phone VARCHAR(50) NOT NULL,
        email VARCHAR(100) NOT NULL
    )");

    $db->exec("CREATE TABLE IF NOT EXISTS employees (
        id $pkAuto,
        branch_id INT DEFAULT NULL,
        full_name VARCHAR(100) NOT NULL,
        role VARCHAR(50) DEFAULT 'Asesor de Ventas',
        phone VARCHAR(50) NOT NULL,
        email VARCHAR(100) NOT NULL,
        status VARCHAR(20) DEFAULT 'Activo'
    )");

    // 2. Tabla Vehículos e Inventario
    $db->exec("CREATE TABLE IF NOT EXISTS vehicles (
        id $pkAuto,
        vin VARCHAR(50) DEFAULT NULL,
        stock_number VARCHAR(50) DEFAULT NULL,
        brand_name VARCHAR(100) NOT NULL,
        category VARCHAR(50) NOT NULL,
        branch_id INT DEFAULT 1,
        title VARCHAR(150) NOT NULL,
        model_year INT NOT NULL,
        price DECIMAL(12, 2) NOT NULL,
        mileage INT DEFAULT 0,
        transmission VARCHAR(50) DEFAULT 'Automática',
        fuel_type VARCHAR(50) DEFAULT 'Gasolina',
        engine VARCHAR(100) DEFAULT '2.0L Turbo',
        horsepower INT DEFAULT 250,
        status VARCHAR(20) DEFAULT 'Disponible',
        featured INT DEFAULT 1,
        image_url VARCHAR(255) NOT NULL,
        video_url VARCHAR(255) DEFAULT NULL,
        description TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");

    try {
        $db->exec("ALTER TABLE vehicles ADD COLUMN video_url VARCHAR(255) DEFAULT NULL");
    } catch (Exception $e) {}


    $db->exec("CREATE TABLE IF NOT EXISTS vehicle_images (
        id $pkAuto,
        vehicle_id INT NOT NULL,
        image_url VARCHAR(255) NOT NULL,
        caption VARCHAR(100) DEFAULT NULL,
        is_cover INT DEFAULT 0
    )");

    $db->exec("CREATE TABLE IF NOT EXISTS features_master (
        id $pkAuto,
        feature_name VARCHAR(100) NOT NULL,
        feature_category VARCHAR(50) DEFAULT 'Interior',
        icon VARCHAR(50) DEFAULT 'fa-check'
    )");

    $db->exec("CREATE TABLE IF NOT EXISTS vehicle_features (
        vehicle_id INT NOT NULL,
        feature_id INT NOT NULL,
        PRIMARY KEY (vehicle_id, feature_id)
    )");

    // 3. Tablas de Clientes, Citas y RESERVAS / APARTADOS
    $db->exec("CREATE TABLE IF NOT EXISTS clients (
        id $pkAuto,
        full_name VARCHAR(100) NOT NULL,
        email VARCHAR(100) NOT NULL,
        phone VARCHAR(50) NOT NULL,
        address VARCHAR(200) DEFAULT NULL,
        tax_id VARCHAR(50) DEFAULT NULL,
        interested_category VARCHAR(50) DEFAULT 'Cualquiera',
        budget DECIMAL(12,2) DEFAULT 0.00,
        status VARCHAR(50) DEFAULT 'Nuevo',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");

    $db->exec("CREATE TABLE IF NOT EXISTS appointments (
        id $pkAuto,
        vehicle_id INT DEFAULT NULL,
        vehicle_title VARCHAR(150) DEFAULT 'Consulta General',
        client_name VARCHAR(100) NOT NULL,
        client_email VARCHAR(100) NOT NULL,
        client_phone VARCHAR(50) NOT NULL,
        appointment_date VARCHAR(50) NOT NULL,
        type VARCHAR(50) DEFAULT 'Prueba de Manejo',
        notes TEXT,
        status VARCHAR(50) DEFAULT 'Pendiente',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");

    // NUEVA TABLA: APARTADOS DE AUTOS (RESERVATIONS)
    $db->exec("CREATE TABLE IF NOT EXISTS reservations (
        id $pkAuto,
        vehicle_id INT NOT NULL,
        vehicle_title VARCHAR(150) NOT NULL,
        client_name VARCHAR(100) NOT NULL,
        client_email VARCHAR(100) NOT NULL,
        client_phone VARCHAR(50) NOT NULL,
        deposit_amount DECIMAL(12,2) DEFAULT 5000.00,
        estimated_delivery_date VARCHAR(50) DEFAULT NULL,
        notes TEXT,
        status VARCHAR(50) DEFAULT 'Activo',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");

    // Poblar Marcas
    $countBrand = $db->query("SELECT COUNT(*) FROM brands")->fetchColumn();
    if ($countBrand == 0) {
        $db->exec("INSERT INTO brands (name, country, website, logo_icon) VALUES
            ('Porsche', 'Alemania', 'https://porsche.com', 'fa-shield-halved'),
            ('BMW', 'Alemania', 'https://bmw.com', 'fa-circle-notch'),
            ('Audi', 'Alemania', 'https://audi.com', 'fa-ring'),
            ('Tesla', 'Estados Unidos', 'https://tesla.com', 'fa-bolt')
        ");
    }

    // Poblar Sucursales
    $countBranch = $db->query("SELECT COUNT(*) FROM branches")->fetchColumn();
    if ($countBranch == 0) {
        $db->exec("INSERT INTO branches (name, address, city, phone, email) VALUES
            ('Showroom Reforma (Matriz)', 'Av. Paseo de la Reforma 450', 'Ciudad de México', '+52 55 8000 2886', 'reforma@autoelite.com'),
            ('Showroom Valle', 'Av. José Vasconcelos 1200', 'Monterrey', '+52 81 4000 9900', 'monterrey@autoelite.com'),
            ('Showroom Zapopan', 'Av. Puerta de Hierro 5000', 'Guadalajara', '+52 33 3000 7711', 'gdl@autoelite.com')
        ");
    }

    // Poblar Empleados
    $countEmp = $db->query("SELECT COUNT(*) FROM employees")->fetchColumn();
    if ($countEmp == 0) {
        $db->exec("INSERT INTO employees (branch_id, full_name, role, phone, email, status) VALUES
            (1, 'Alejandro Valverde', 'Gerente General', '+52 55 1111 2222', 'avalverde@autoelite.com', 'Activo'),
            (1, 'Sofía Castro', 'Asesor de Ventas Senior', '+52 55 3333 4444', 'scastro@autoelite.com', 'Activo'),
            (2, 'Diego Fernández', 'Especialista en Financiamiento', '+52 81 5555 6666', 'dfernandez@autoelite.com', 'Activo')
        ");
    }

    // Poblar Autos
    $countVeh = $db->query("SELECT COUNT(*) FROM vehicles")->fetchColumn();
    if ($countVeh == 0) {
        $stmtVeh = $db->prepare("INSERT INTO vehicles 
            (vin, stock_number, brand_name, category, branch_id, title, model_year, price, mileage, transmission, fuel_type, engine, horsepower, status, featured, image_url, video_url, description) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

        $stmtVeh->execute([
            'WP0ZZZ99ZLS123456', 'STK-911-01', 'Porsche', 'Deportivo', 1,
            'Porsche 911 GT3 Super Sport', 2024, 245000.00, 1200,
            'Automática PDK 8 Vel', 'Gasolina Premium', '4.0L Boxer 6 CIL', 510,
            'Disponible', 1, 'assets/images/car_1.png', 'https://www.youtube.com/embed/39V_5dM2q_4',
            'Edición especial de alto rendimiento con aerodinámica activa, interior en fibra de carbono y aceleración de 0 a 100 km/h en 3.4 segundos.'
        ]);

        $stmtVeh->execute([
            'WBAX7M50i88990011', 'STK-X7-02', 'BMW', 'SUV', 2,
            'BMW X7 M50i Executive SUV', 2023, 118900.00, 14500,
            'Automática Steptronic', 'Híbrido Mild-Hybrid', '4.4L Twin-Turbo V8', 523,
            'Disponible', 1, 'assets/images/car_2.png', 'https://www.youtube.com/embed/69z2wU27Lls',
            'SUV de lujo con 3 filas de asientos en piel Vernasca, techo panorámico Sky Lounge y sistema de sonido Bowers & Wilkins.'
        ]);

        $stmtVeh->execute([
            'WAUA8LEXEC554433', 'STK-A8-03', 'Audi', 'Sedán', 1,
            'Audi A8 L Executive Edition', 2024, 135000.00, 50,
            'Tiptronic 8 Vel', 'Gasolina', '3.0L TFSI V6 Quattro', 335,
            'Disponible', 1, 'assets/images/car_3.png', 'https://www.youtube.com/embed/v24tLgD-f38',
            'Sedán ejecutivo con tracción integral Quattro, asientos traseros con masaje térmico y suspensión neumática adaptativa.'
        ]);

        $stmtVeh->execute([
            '5YJTSLROADSTER01', 'STK-TSL-04', 'Tesla', 'Eléctrico', 3,
            'Tesla Roadster Hyper Edition', 2025, 210000.00, 0,
            'Direct Drive 1-Speed', '100% Eléctrico', 'Tri-Motor All-Wheel Drive', 1020,
            'Reservado', 1, 'assets/images/car_4.png', 'https://www.youtube.com/embed/tw4jkyfY4HE',
            'El vehículo eléctrico más veloz del mundo con más de 1,000 HP y una autonomía estimada de 1,000 km por carga.'
        ]);
    }

    // Asegurar que autos existentes tengan video
    $db->exec("UPDATE vehicles SET video_url = 'https://www.youtube.com/embed/39V_5dM2q_4' WHERE video_url IS NULL OR video_url = ''");


    // Poblar Apartados Demo si está vacía
    $countRes = $db->query("SELECT COUNT(*) FROM reservations")->fetchColumn();
    if ($countRes == 0) {
        $stmtRes = $db->prepare("INSERT INTO reservations 
            (vehicle_id, vehicle_title, client_name, client_email, client_phone, deposit_amount, estimated_delivery_date, notes, status) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
        $stmtRes->execute([
            4,
            'Tesla Roadster Hyper Edition',
            'Enrique Ramos',
            'enrique.ramos@email.com',
            '+52 55 9988 7766',
            10000.00,
            '2026-08-25',
            'Apartado realizado con enganche inicial de $10,000 USD. Pendiente de entrega.',
            'Activo'
        ]);

        $stmtRes->execute([
            1,
            'Porsche 911 GT3 Super Sport',
            'Fernando Garza',
            'fgarza@email.com',
            '+52 81 1234 9999',
            15000.00,
            '2026-08-30',
            'Apartado mediante transferencia bancaria. Solicitó placas de NL.',
            'Activo'
        ]);
    }
}

// Ejecutar setup
runSetup();
