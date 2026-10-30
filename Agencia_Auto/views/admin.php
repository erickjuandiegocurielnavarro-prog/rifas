<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../setup.php';

$db = getDBConnection();
$brands = $db->query("SELECT * FROM brands ORDER BY name ASC")->fetchAll();
$branches = $db->query("SELECT * FROM branches ORDER BY name ASC")->fetchAll();
$vehicles = $db->query("SELECT * FROM vehicles ORDER BY title ASC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Administración CRUD | AutoElite Motors</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body id="adminDashboard">

    <!-- NAVBAR ADMIN -->
    <nav class="navbar">
        <a href="admin.php" class="nav-brand">
            <i class="fa-solid fa-gears" style="color:var(--accent-gold);"></i>
            <span>AUTO<span class="gradient-text">ELITE</span> <small style="font-size:0.5em; color:var(--accent-gold); letter-spacing:0.1em;">ADMIN PANEL CRUD</small></span>
        </a>

        <div class="nav-actions">
            <a href="index.php" class="btn-secondary" target="_blank">
                <i class="fa-solid fa-globe"></i> Ver Sitio Web
            </a>
            <button class="btn-primary" onclick="openNewVehicleModal()">
                <i class="fa-solid fa-plus"></i> Nuevo Vehículo
            </button>
        </div>
    </nav>

    <!-- ADMIN CONTAINER -->
    <div class="admin-container">
        
        <!-- HEADER Y STATS -->
        <div class="admin-header">
            <div>
                <h2>Panel de <span class="gradient-text">Gestión CRUD de la Agencia</span></h2>
                <p style="color: var(--text-muted);">Administración de vehículos, estatus de agotados, reservas de clientes, citas y catálogo general.</p>
            </div>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon"><i class="fa-solid fa-car-side"></i></div>
                <div class="stat-info">
                    <h3 id="statTotalVehicles">0</h3>
                    <p>Vehículos en Inventario</p>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon" style="background: rgba(255, 71, 87, 0.15); color: #ff4757;"><i class="fa-solid fa-ban"></i></div>
                <div class="stat-info">
                    <h3 id="statTotalAgotados">0</h3>
                    <p>Autos Agotados</p>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon" style="background: rgba(255, 183, 3, 0.15); color: var(--accent-gold);"><i class="fa-solid fa-bookmark"></i></div>
                <div class="stat-info">
                    <h3 id="statTotalReservations">0</h3>
                    <p>Autos Apartados</p>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon" style="background: rgba(0, 242, 254, 0.1); color: var(--primary-glow);"><i class="fa-solid fa-calendar-check"></i></div>
                <div class="stat-info">
                    <h3 id="statTotalAppointments">0</h3>
                    <p>Citas Agendadas</p>
                </div>
            </div>
        </div>

        <!-- PESTAÑAS DE NAVEGACIÓN ADMIN -->
        <div class="category-tabs" style="justify-content: flex-start; margin-bottom: 24px;">
            <button class="tab-btn admin-nav-btn active" data-target="tabVehicles">
                <i class="fa-solid fa-car"></i> Inventario de Autos (CRUD)
            </button>
            <button class="tab-btn admin-nav-btn" data-target="tabReservations">
                <i class="fa-solid fa-bookmark" style="color:var(--accent-gold);"></i> Personas que Apartaron Autos
            </button>
            <button class="tab-btn admin-nav-btn" data-target="tabAppointments">
                <i class="fa-solid fa-calendar-days"></i> Solicitudes de Citas
            </button>
            <button class="tab-btn admin-nav-btn" data-target="tabBranches">
                <i class="fa-solid fa-store"></i> Sucursales / Showrooms
            </button>
            <button class="tab-btn admin-nav-btn" data-target="tabEmployees">
                <i class="fa-solid fa-id-card"></i> Personal y Asesores
            </button>
            <button class="tab-btn admin-nav-btn" data-target="tabBrands">
                <i class="fa-solid fa-tags"></i> Marcas
            </button>
            <button class="tab-btn admin-nav-btn" data-target="tabClients">
                <i class="fa-solid fa-user-group"></i> Clientes / Prospectos
            </button>
        </div>

        <!-- PESTAÑA 1: CRUD AUTOS -->
        <div id="tabVehicles" class="admin-tab-content">
            <div style="margin-bottom: 16px; display: flex; justify-content: space-between; align-items: center; background: rgba(255,255,255,0.02); padding: 12px 20px; border-radius: var(--radius-md); border: 1px solid var(--border-color);">
                <div>
                    <strong style="color: var(--text-main);"><i class="fa-solid fa-bolt" style="color: var(--accent-gold);"></i> Gestión de Disponibilidad y Confirmación de Agotados</strong>
                    <p style="font-size: 0.85rem; color: var(--text-muted); margin: 0;">Haz clic en el botón de acción rápida para confirmar o restaurar el estado de <strong>"Auto Agotado"</strong> al instante.</p>
                </div>
                <button class="btn-primary" onclick="openNewVehicleModal()"><i class="fa-solid fa-plus"></i> Añadir Auto</button>
            </div>
            
            <div class="table-container">
                <table class="crud-table">
                    <thead>
                        <tr>
                            <th>Imagen</th>
                            <th>Vehículo / Modelo</th>
                            <th>Sucursal</th>
                            <th>Categoría</th>
                            <th>Precio (USD)</th>
                            <th>Estado Actual</th>
                            <th>Confirmar Agotado al Instante</th>
                            <th>Acciones CRUD</th>
                        </tr>
                    </thead>
                    <tbody id="adminVehiclesTableBody">
                        <!-- AJAX -->
                    </tbody>
                </table>
            </div>
        </div>

        <!-- PESTAÑA 2: PERSONAS QUE APARTARON AUTOS (RESERVAS) -->
        <div id="tabReservations" class="admin-tab-content" style="display: none;">
            <div style="margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
                <h3 style="font-size: 1.2rem;"><i class="fa-solid fa-users-rectangle" style="color:var(--accent-gold);"></i> Registro de Personas con Autos Apartados</h3>
                <button class="btn-primary" onclick="openNewReservationModal()"><i class="fa-solid fa-plus"></i> Registrar Nuevo Apartado</button>
            </div>
            <div class="table-container">
                <table class="crud-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Cliente que Apartó</th>
                            <th>Vehículo Reservado</th>
                            <th>Monto de Enganche</th>
                            <th>Fecha Est. Entrega</th>
                            <th>Estado del Apartado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="adminReservationsTableBody">
                        <!-- AJAX -->
                    </tbody>
                </table>
            </div>
        </div>

        <!-- PESTAÑA 3: SOLICITUDES DE CITAS -->
        <div id="tabAppointments" class="admin-tab-content" style="display: none;">
            <div class="table-container">
                <table class="crud-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Cliente</th>
                            <th>Vehículo de Interés</th>
                            <th>Fecha</th>
                            <th>Tipo</th>
                            <th>Estado Cita</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="adminAppointmentsTableBody">
                        <!-- AJAX -->
                    </tbody>
                </table>
            </div>
        </div>

        <!-- PESTAÑA 4: SUCURSALES -->
        <div id="tabBranches" class="admin-tab-content" style="display: none;">
            <div style="margin-bottom: 20px; display: flex; justify-content: flex-end;">
                <button class="btn-secondary" onclick="addBranchPrompt()"><i class="fa-solid fa-plus"></i> Nueva Sucursal</button>
            </div>
            <div class="table-container">
                <table class="crud-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Sucursal</th>
                            <th>Ciudad</th>
                            <th>Dirección</th>
                            <th>Contacto</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="adminBranchesTableBody">
                        <!-- AJAX -->
                    </tbody>
                </table>
            </div>
        </div>

        <!-- PESTAÑA 5: EMPLEADOS -->
        <div id="tabEmployees" class="admin-tab-content" style="display: none;">
            <div style="margin-bottom: 20px; display: flex; justify-content: flex-end;">
                <button class="btn-secondary" onclick="addEmployeePrompt()"><i class="fa-solid fa-plus"></i> Registrar Asesor</button>
            </div>
            <div class="table-container">
                <table class="crud-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre Completo</th>
                            <th>Puesto / Rol</th>
                            <th>Sucursal Asignada</th>
                            <th>Contacto</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody id="adminEmployeesTableBody">
                        <!-- AJAX -->
                    </tbody>
                </table>
            </div>
        </div>

        <!-- PESTAÑA 6: MARCAS -->
        <div id="tabBrands" class="admin-tab-content" style="display: none;">
            <div style="margin-bottom: 20px; display: flex; justify-content: flex-end;">
                <button class="btn-secondary" onclick="addBrandPrompt()"><i class="fa-solid fa-plus"></i> Agregar Marca</button>
            </div>
            <div class="table-container">
                <table class="crud-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre Marca</th>
                            <th>País de Origen</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="adminBrandsTableBody">
                        <!-- AJAX -->
                    </tbody>
                </table>
            </div>
        </div>

        <!-- PESTAÑA 7: CLIENTES -->
        <div id="tabClients" class="admin-tab-content" style="display: none;">
            <div class="table-container">
                <table class="crud-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre Completo</th>
                            <th>Contacto</th>
                            <th>Categoría de Interés</th>
                            <th>Presupuesto Est.</th>
                            <th>Estado Prospecto</th>
                        </tr>
                    </thead>
                    <tbody id="adminClientsTableBody">
                        <!-- AJAX -->
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <!-- MODAL REGISTRAR NUEVO APARTADO EN ADMIN -->
    <div class="modal-overlay" id="adminReservationModalOverlay">
        <div class="modal-container" style="max-width: 600px;">
            <button class="modal-close" onclick="closeModal()"><i class="fa-solid fa-xmark"></i></button>

            <div class="modal-content">
                <h2 style="margin-bottom: 20px;"><i class="fa-solid fa-bookmark" style="color:var(--accent-gold);"></i> Registrar Apartado de Vehículo</h2>

                <form id="adminReservationForm">
                    <input type="hidden" name="id" id="resId" value="0">

                    <div class="form-group">
                        <label>Seleccionar Vehículo a Apartar *</label>
                        <select name="vehicle_id" id="resVehicleId" class="form-control" required onchange="updateResVehicleTitle(this)">
                            <option value="">-- Selecciona un Auto --</option>
                            <?php foreach($vehicles as $v): ?>
                                <option value="<?= $v['id'] ?>" data-title="<?= htmlspecialchars($v['title']) ?>">
                                    <?= htmlspecialchars($v['title']) ?> - $<?= number_format($v['price'], 2) ?> USD (<?= $v['status'] ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <input type="hidden" name="vehicle_title" id="resVehicleTitle">
                    </div>

                    <div class="form-group">
                        <label>Nombre Completo del Cliente *</label>
                        <input type="text" name="client_name" id="resClientName" class="form-control" required placeholder="Ej. Roberto Garza">
                    </div>

                    <div class="form-grid">
                        <div class="form-group">
                            <label>Correo Electrónico *</label>
                            <input type="email" name="client_email" id="resClientEmail" class="form-control" required placeholder="roberto@ejemplo.com">
                        </div>
                        <div class="form-group">
                            <label>Teléfono de Contacto *</label>
                            <input type="tel" name="client_phone" id="resClientPhone" class="form-control" required placeholder="+52 55 1234 5678">
                        </div>
                    </div>

                    <div class="form-grid">
                        <div class="form-group">
                            <label>Monto de Enganche / Apartado ($ USD) *</label>
                            <input type="number" step="0.01" name="deposit_amount" id="resDepositAmount" class="form-control" required value="5000.00">
                        </div>
                        <div class="form-group">
                            <label>Fecha Estimada de Entrega</label>
                            <input type="date" name="estimated_delivery_date" id="resDeliveryDate" class="form-control" value="<?= date('Y-m-d', strtotime('+15 days')) ?>">
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Estado del Apartado</label>
                        <select name="status" id="resStatus" class="form-control">
                            <option value="Activo">Activo (Vehículo Reservado)</option>
                            <option value="Concluido">Concluido (Vehículo Vendido)</option>
                            <option value="Cancelado">Cancelado (Vehículo Vuelve a Disponible)</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Notas Adicionales</label>
                        <textarea name="notes" id="resNotes" class="form-control" rows="2" placeholder="Detalles de pago o acuerdos de entrega..."></textarea>
                    </div>

                    <div style="display: flex; justify-content: flex-end; gap: 12px; margin-top: 20px;">
                        <button type="button" class="btn-secondary" onclick="closeModal()">Cancelar</button>
                        <button type="submit" class="btn-primary"><i class="fa-solid fa-floppy-disk"></i> Guardar Apartado</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- MODAL CREAR / EDITAR VEHÍCULO EN ADMIN -->
    <div class="modal-overlay" id="adminVehicleModalOverlay">
        <div class="modal-container">
            <button class="modal-close" onclick="closeModal()"><i class="fa-solid fa-xmark"></i></button>

            <div class="modal-content">
                <h2 id="adminModalHeaderTitle" style="margin-bottom: 20px;">Registrar Vehículo</h2>

                <form id="adminVehicleForm">
                    <input type="hidden" name="id" id="adminVehicleId" value="0">

                    <div class="form-grid">
                        <div class="form-group full-width">
                            <label>Título / Nombre del Vehículo *</label>
                            <input type="text" name="title" id="adminTitle" class="form-control" required placeholder="Ej. Porsche 911 GT3 Super Sport">
                        </div>

                        <div class="form-group">
                            <label>Marca *</label>
                            <select name="brand_name" id="adminBrandName" class="form-control" required>
                                <?php foreach($brands as $b): ?>
                                    <option value="<?= htmlspecialchars($b['name']) ?>"><?= htmlspecialchars($b['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Sucursal de Exposición *</label>
                            <select name="branch_id" id="adminBranchId" class="form-control" required>
                                <?php foreach($branches as $br): ?>
                                    <option value="<?= $br['id'] ?>"><?= htmlspecialchars($br['name']) ?> (<?= htmlspecialchars($br['city']) ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Categoría *</label>
                            <select name="category" id="adminCategory" class="form-control" required>
                                <option value="Deportivo">Deportivo</option>
                                <option value="SUV">SUV</option>
                                <option value="Sedán">Sedán</option>
                                <option value="Eléctrico">Eléctrico</option>
                                <option value="Pickup">Pickup</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Año Modelo *</label>
                            <input type="number" name="model_year" id="adminModelYear" class="form-control" required value="<?= date('Y') ?>">
                        </div>

                        <div class="form-group">
                            <label>Precio (USD) *</label>
                            <input type="number" step="0.01" name="price" id="adminPrice" class="form-control" required placeholder="125000.00">
                        </div>

                        <div class="form-group">
                            <label>Kilometraje (km)</label>
                            <input type="number" name="mileage" id="adminMileage" class="form-control" value="0">
                        </div>

                        <div class="form-group">
                            <label>Transmisión</label>
                            <select name="transmission" id="adminTransmission" class="form-control">
                                <option value="Automática">Automática</option>
                                <option value="Manual">Manual</option>
                                <option value="Dual-Clutch / PDK">Dual-Clutch / PDK</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Tipo de Combustible</label>
                            <select name="fuel_type" id="adminFuelType" class="form-control">
                                <option value="Gasolina">Gasolina</option>
                                <option value="Híbrido">Híbrido</option>
                                <option value="Eléctrico">Eléctrico</option>
                                <option value="Diésel">Diésel</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Motor Especificación</label>
                            <input type="text" name="engine" id="adminEngine" class="form-control" placeholder="Ej. 4.0L Boxer 6 CIL">
                        </div>

                        <div class="form-group">
                            <label>Caballos de Fuerza (HP)</label>
                            <input type="number" name="horsepower" id="adminHorsepower" class="form-control" value="300">
                        </div>

                        <div class="form-group">
                            <label>Estado del Vehículo</label>
                            <select name="status" id="adminStatus" class="form-control">
                                <option value="Disponible">Disponible</option>
                                <option value="Agotado">Agotado</option>
                                <option value="Reservado">Reservado</option>
                                <option value="Vendido">Vendido</option>
                            </select>
                        </div>

                        <div class="form-group full-width">
                            <label>Ruta / URL de la Imagen *</label>
                            <input type="text" name="image_url" id="adminImageUrl" class="form-control" value="assets/images/car_1.png" placeholder="assets/images/mi_auto.jpg">
                        </div>

                        <div class="form-group full-width">
                            <label><i class="fa-solid fa-play" style="color:var(--accent-red);"></i> URL del Video Promocional (YouTube Embed o MP4)</label>
                            <input type="text" name="video_url" id="adminVideoUrl" class="form-control" placeholder="Ej. https://www.youtube.com/embed/39V_5dM2q_4">
                        </div>


                        <div class="form-group full-width">
                            <label>Descripción y Características</label>
                            <textarea name="description" id="adminDescription" class="form-control" rows="3" placeholder="Detalles de equipamiento, estado, etc."></textarea>
                        </div>
                    </div>

                    <div style="display: flex; justify-content: flex-end; gap: 12px; margin-top: 20px;">
                        <button type="button" class="btn-secondary" onclick="closeModal()">Cancelar</button>
                        <button type="submit" class="btn-primary"><i class="fa-solid fa-floppy-disk"></i> Guardar Vehículo</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="assets/js/main.js"></script>
    <script src="assets/js/admin.js"></script>
    <script>
        function updateResVehicleTitle(selectElem) {
            const selectedOption = selectElem.options[selectElem.selectedIndex];
            const title = selectedOption.getAttribute('data-title') || '';
            document.getElementById('resVehicleTitle').value = title;
        }
    </script>
</body>
</html>
