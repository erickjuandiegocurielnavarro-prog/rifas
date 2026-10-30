<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../setup.php';

$db = getDBConnection();
$brands = $db->query("SELECT * FROM brands ORDER BY name ASC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AutoElite Motors | Agencia de Autos de Lujo y Deportivos</title>
    <meta name="description" content="Encuentra el auto de tus sueños. Vehículos deportivos, SUVs y sedanes ejecutivos de las mejores marcas del mundo.">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body>

    <!-- NAVBAR -->
    <nav class="navbar">
        <a href="index.php" class="nav-brand">
            <i class="fa-solid fa-car-burst"></i>
            <span>AUTO<span class="gradient-text">ELITE</span></span>
        </a>

        <ul class="nav-links">
            <li><a href="#inicio" class="active">Inicio</a></li>
            <li><a href="#catalogo">Catálogo</a></li>
            <li><a href="#nosotros">Nosotros</a></li>
            <li><a href="#contacto">Contacto</a></li>
        </ul>

        <div class="nav-actions">
            <a href="admin.php" class="btn-secondary" title="Acceder al Panel CRUD">
                <i class="fa-solid fa-sliders"></i> Panel CRUD Admin
            </a>
            <a href="#catalogo" class="btn-primary">
                Ver Inventario <i class="fa-solid fa-arrow-right"></i>
            </a>
        </div>
    </nav>

    <!-- HERO SECTION -->
    <section class="hero" id="inicio">
        <div class="hero-content">
            <div class="hero-tag">
                <i class="fa-solid fa-shield-halved"></i> AGENCIA DE AUTOS PREMIUM
            </div>
            <h1 class="hero-title">
                Descubre la <span class="gradient-text">Excelencia</span> en Cada Kilómetro
            </h1>
            <p class="hero-subtitle">
                Los vehículos más exclusivos del mercado con garantía certificada, planes de financiamiento a tu medida y servicio post-venta VIP.
            </p>

            <!-- BUSCADOR RÁPIDO EN HERO -->
            <form id="heroFilterForm" class="search-filter-box">
                <div class="filter-group">
                    <label><i class="fa-solid fa-car"></i> Categoría</label>
                    <select id="searchCategory">
                        <option value="todos">Todas las categorías</option>
                        <option value="Deportivo">Deportivo</option>
                        <option value="SUV">SUV</option>
                        <option value="Sedán">Sedán</option>
                        <option value="Eléctrico">Eléctrico</option>
                    </select>
                </div>

                <div class="filter-group">
                    <label><i class="fa-solid fa-copyright"></i> Marca</label>
                    <select id="searchBrand">
                        <option value="todas">Todas las marcas</option>
                        <?php foreach($brands as $b): ?>
                            <option value="<?= htmlspecialchars($b['name']) ?>"><?= htmlspecialchars($b['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="filter-group">
                    <label><i class="fa-solid fa-magnifying-glass"></i> Modelo o Nombre</label>
                    <input type="text" id="searchKeyword" placeholder="Ej. Porsche, GT3, V8...">
                </div>

                <div class="filter-group" style="justify-content: flex-end;">
                    <button type="submit" class="btn-primary" style="height: 42px; justify-content: center;">
                        <i class="fa-solid fa-search"></i> Buscar
                    </button>
                </div>
            </form>
        </div>

        <div class="hero-image-container">
            <img src="assets/images/car_1.png" alt="Porsche 911 GT3 AutoElite">
        </div>
    </section>

    <!-- INVENTARIO / CATÁLOGO -->
    <section class="catalog-section" id="catalogo">
        <div class="section-header">
            <h2>Inventario de <span class="gradient-text">Vehículos</span></h2>
            <p>Explora nuestro catálogo seleccionado de unidades cero kilómetros, seminuevos y disponibilidad en tiempo real.</p>
        </div>

        <!-- PESTAÑAS DE CATEGORÍAS -->
        <div class="category-tabs">
            <button class="tab-btn active" data-category="todos">
                <i class="fa-solid fa-border-all"></i> Todos los Autos
            </button>
            <button class="tab-btn" data-category="Deportivo">
                <i class="fa-solid fa-gauge-high"></i> Deportivos
            </button>
            <button class="tab-btn" data-category="SUV">
                <i class="fa-solid fa-truck-monster"></i> SUVs
            </button>
            <button class="tab-btn" data-category="Sedán">
                <i class="fa-solid fa-car-side"></i> Sedanes
            </button>
            <button class="tab-btn" data-category="Eléctrico">
                <i class="fa-solid fa-bolt"></i> Eléctricos
            </button>
        </div>

        <!-- GRID DE VEHÍCULOS (CARGADO DINÁMICAMENTE POR AJAX DESDE CRUD) -->
        <div class="vehicles-grid" id="vehiclesCatalog">
            <!-- JS AJAX -->
        </div>
    </section>

    <!-- SECCIÓN DE VENTAJAS / NOSOTROS -->
    <section style="padding: 80px 5%; background: var(--bg-card); border-top: 1px solid var(--border-color); border-bottom: 1px solid var(--border-color);" id="nosotros">
        <div class="section-header">
            <h2>¿Por qué elegir <span class="gradient-text">AutoElite Motors</span>?</h2>
            <p>Ofrecemos una experiencia de compra transparente, rápida y sin complicaciones.</p>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 30px;">
            <div style="background: rgba(255,255,255,0.02); border: 1px solid var(--border-color); padding: 30px; border-radius: var(--radius-lg); text-align: center;">
                <div style="width: 60px; height: 60px; border-radius: 50%; background: rgba(0,242,254,0.1); color: var(--primary-glow); display: flex; align-items: center; justify-content: center; font-size: 1.8rem; margin: 0 auto 20px auto;">
                    <i class="fa-solid fa-certificate"></i>
                </div>
                <h3>Garantía Extendida</h3>
                <p style="color: var(--text-muted); margin-top: 10px;">Todas nuestras unidades cuentan con inspección técnica de 150 puntos y garantía de agencia.</p>
            </div>

            <div style="background: rgba(255,255,255,0.02); border: 1px solid var(--border-color); padding: 30px; border-radius: var(--radius-lg); text-align: center;">
                <div style="width: 60px; height: 60px; border-radius: 50%; background: rgba(255,183,3,0.1); color: var(--accent-gold); display: flex; align-items: center; justify-content: center; font-size: 1.8rem; margin: 0 auto 20px auto;">
                    <i class="fa-solid fa-coins"></i>
                </div>
                <h3>Financiamiento a Medida</h3>
                <p style="color: var(--text-muted); margin-top: 10px;">Tasa de interés preferencial, arrendamiento puro y opciones flexibles hasta 60 meses.</p>
            </div>

            <div style="background: rgba(255,255,255,0.02); border: 1px solid var(--border-color); padding: 30px; border-radius: var(--radius-lg); text-align: center;">
                <div style="width: 60px; height: 60px; border-radius: 50%; background: rgba(46,213,115,0.1); color: var(--accent-green); display: flex; align-items: center; justify-content: center; font-size: 1.8rem; margin: 0 auto 20px auto;">
                    <i class="fa-solid fa-key"></i>
                </div>
                <h3>Entrega Inmediata</h3>
                <p style="color: var(--text-muted); margin-top: 10px;">Trámite de placas y entrega en la puerta de tu domicilio o en nuestro showroom exclusivo.</p>
            </div>
        </div>
    </section>

    <!-- MODAL DE DETALLE DE VEHÍCULO Y SOLICITUD DE CITA -->
    <div class="modal-overlay" id="vehicleModalOverlay">
        <div class="modal-container">
            <button class="modal-close" id="closeModalBtn"><i class="fa-solid fa-xmark"></i></button>

            <div class="modal-content">
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 30px;">
                    <!-- Columna Izquierda: Galería e Información del Auto -->
                    <div>
                        <!-- BARRA DE SELECCIÓN DE MEDIOS (FOTO / VIDEO) -->
                        <div class="media-selector-bar" style="display: flex; gap: 10px; margin-bottom: 15px;">
                            <button type="button" class="media-tab-btn active" id="btnMediaPhoto" onclick="switchModalMedia('photo')">
                                <i class="fa-solid fa-camera"></i> Fotografía HD
                            </button>
                            <button type="button" class="media-tab-btn" id="btnMediaVideo" onclick="switchModalMedia('video')">
                                <i class="fa-solid fa-circle-play" style="color:var(--accent-red);"></i> Video Promocional HD
                            </button>
                        </div>

                        <!-- CONTENEDOR DE MEDIOS: FOTO -->
                        <div id="modalPhotoContainer" style="position: relative;">
                            <img id="modalImage" src="" alt="Auto" style="width: 100%; border-radius: var(--radius-md); box-shadow: 0 10px 25px rgba(0,0,0,0.5); border: 1px solid var(--border-color); margin-bottom: 20px;">
                            <div id="modalAgotadoBadge" style="display:none; position: absolute; top: 15px; right: 15px; background: #ff4757; color: white; padding: 6px 14px; border-radius: 20px; font-weight: 800; font-size: 0.85rem; box-shadow: 0 4px 15px rgba(255,71,87,0.5);">
                                <i class="fa-solid fa-ban"></i> AUTO AGOTADO
                            </div>
                        </div>

                        <!-- CONTENEDOR DE MEDIOS: VIDEO PLAYER -->
                        <div id="modalVideoContainer" style="display: none; margin-bottom: 20px;">
                            <div class="video-wrapper">
                                <iframe id="modalVideoIframe" src="" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                            </div>
                        </div>

                        
                        <h2 id="modalTitle" style="font-size: 1.8rem; margin-bottom: 10px;">-</h2>
                        <div style="display: flex; gap: 10px; margin-bottom: 20px; flex-wrap: wrap;" id="modalBadgesContainer">
                            <span class="badge badge-disponible" id="modalBrand">-</span>
                            <span class="badge" style="background: rgba(255,255,255,0.08);" id="modalCategory">-</span>
                            <span class="badge" style="background: rgba(0,242,254,0.1); color: var(--primary-glow);" id="modalYear">-</span>
                        </div>

                        <div style="font-size: 2rem; font-weight: 800; margin-bottom: 20px;" class="gold-text" id="modalPrice">$0.00 USD</div>

                        <p style="color: var(--text-muted); font-size: 0.95rem; margin-bottom: 20px;" id="modalDescription">-</p>

                        <!-- Equipamiento Destacado -->
                        <h4 style="margin-bottom: 10px; text-transform: uppercase; font-size: 0.85rem; color: var(--text-dim);">Equipamiento de Serie</h4>
                        <div id="modalFeaturesContainer" style="display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 20px;"></div>

                        <!-- Ficha Técnica -->
                        <h4 style="margin-bottom: 10px; text-transform: uppercase; font-size: 0.85rem; color: var(--text-dim);">Especificaciones Clave</h4>
                        <div class="card-specs" style="margin-bottom: 0;">
                            <div class="spec-item"><i class="fa-solid fa-microchip"></i> Motor: <span id="modalEngine" style="color: var(--text-main); font-weight: 600;">-</span></div>
                            <div class="spec-item"><i class="fa-solid fa-bolt"></i> Potencia: <span id="modalHP" style="color: var(--text-main); font-weight: 600;">-</span></div>
                            <div class="spec-item"><i class="fa-solid fa-gears"></i> Transmisión: <span id="modalTransmission" style="color: var(--text-main); font-weight: 600;">-</span></div>
                            <div class="spec-item"><i class="fa-solid fa-gauge-high"></i> Recorrido: <span id="modalMileage" style="color: var(--text-main); font-weight: 600;">-</span></div>
                        </div>
                    </div>

                    <!-- Columna Derecha: Formulario para Agendar Prueba de Manejo / Cotizar -->
                    <div style="background: rgba(255,255,255,0.02); padding: 24px; border-radius: var(--radius-md); border: 1px solid var(--border-color);" id="modalFormContainer">
                        <h3 style="margin-bottom: 6px;">Agendar Cita o Cotización</h3>
                        <p style="color: var(--text-muted); font-size: 0.85rem; margin-bottom: 20px;">Completa tus datos y nos pondremos en contacto contigo para agendar tu prueba de manejo.</p>

                        <form id="appointmentForm">
                            <input type="hidden" name="vehicle_id" id="appointmentVehicleId">
                            <input type="hidden" name="vehicle_title" id="appointmentVehicleTitle">

                            <div class="form-group">
                                <label>Nombre Completo *</label>
                                <input type="text" name="client_name" class="form-control" required placeholder="Ej. Juan Pérez">
                            </div>

                            <div class="form-group">
                                <label>Correo Electrónico *</label>
                                <input type="email" name="client_email" class="form-control" required placeholder="juan@ejemplo.com">
                            </div>

                            <div class="form-group">
                                <label>Teléfono de Contacto *</label>
                                <input type="tel" name="client_phone" class="form-control" required placeholder="+52 55 1234 5678">
                            </div>

                            <div class="form-grid">
                                <div class="form-group">
                                    <label>Fecha Preferida</label>
                                    <input type="date" name="appointment_date" class="form-control" value="<?= date('Y-m-d', strtotime('+1 day')) ?>">
                                </div>
                                <div class="form-group">
                                    <label>Tipo de Solicitud</label>
                                    <select name="type" id="appointmentTypeSelect" class="form-control">
                                        <option value="Prueba de Manejo">Prueba de Manejo</option>
                                        <option value="Cotización / Financiamiento">Cotización</option>
                                        <option value="Apartado del Vehículo">Apartar Unidad</option>
                                    </select>
                                </div>
                            </div>

                            <div class="form-group">
                                <label>Comentarios adicionales</label>
                                <textarea name="notes" class="form-control" rows="3" placeholder="¿Tienes alguna preferencia de horario o pregunta?"></textarea>
                            </div>

                            <button type="submit" id="appointmentSubmitBtn" class="btn-primary" style="width: 100%; justify-content: center; margin-top: 10px;">
                                <i class="fa-solid fa-calendar-check"></i> Confirmar Solicitud
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- FOOTER -->
    <footer id="contacto">
        <div class="footer-grid">
            <div class="footer-col">
                <a href="#" class="nav-brand" style="margin-bottom: 16px; display: inline-flex;">
                    <i class="fa-solid fa-car-burst"></i>
                    <span>AUTO<span class="gradient-text">ELITE</span></span>
                </a>
                <p>Tu agencia de confianza especializada en la compra, venta y consignación de vehículos de alta gama.</p>
            </div>

            <div class="footer-col">
                <h4>Contacto</h4>
                <p><i class="fa-solid fa-location-dot" style="color:var(--primary-glow); width:20px;"></i> Av. de la Reforma 450, CDMX</p>
                <p><i class="fa-solid fa-phone" style="color:var(--primary-glow); width:20px;"></i> +52 (55) 8000-AUTO</p>
                <p><i class="fa-solid fa-envelope" style="color:var(--primary-glow); width:20px;"></i> ventas@autoelitemotors.com</p>
            </div>

            <div class="footer-col">
                <h4>Horarios de Showroom</h4>
                <p>Lunes a Viernes: 9:00 AM - 8:00 PM</p>
                <p>Sábados: 10:00 AM - 6:00 PM</p>
                <p>Domingos: Cita Previa</p>
            </div>
        </div>

        <div class="footer-bottom">
            <p>&copy; <?= date('Y') ?> AutoElite Motors. Todos los derechos reservados. Desarrollado para Agencia de Autos.</p>
        </div>
    </footer>

    <script src="assets/js/main.js"></script>
</body>
</html>
