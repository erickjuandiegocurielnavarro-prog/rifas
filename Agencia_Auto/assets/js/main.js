/**
 * AutoElite Motors - Main JS Public Catalog
 * Manejo del catálogo público, filtros, modal de vehículo y actualización visual de Autos Agotados
 */

document.addEventListener('DOMContentLoaded', () => {
    if (document.getElementById('vehiclesCatalog')) {
        initCatalog();
    }
});

let currentCategory = 'todos';
let currentBrand = 'todas';

function initCatalog() {
    loadVehicles();

    // Filtros por Categoría
    const tabBtns = document.querySelectorAll('.category-tabs .tab-btn');
    tabBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            tabBtns.forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            currentCategory = btn.dataset.category || 'todos';
            loadVehicles();
        });
    });

    // Formulario de Búsqueda Rápida
    const filterForm = document.getElementById('heroFilterForm');
    if (filterForm) {
        filterForm.addEventListener('submit', (e) => {
            e.preventDefault();
            const catElem = document.getElementById('searchCategory');
            const brandElem = document.getElementById('searchBrand');
            if (catElem) currentCategory = catElem.value;
            if (brandElem) currentBrand = brandElem.value;
            const searchKeyword = document.getElementById('searchKeyword') ? document.getElementById('searchKeyword').value : '';
            loadVehicles(searchKeyword);

            const catalogElem = document.getElementById('catalogo');
            if (catalogElem) catalogElem.scrollIntoView({ behavior: 'smooth' });
        });
    }

    const closeModalBtn = document.getElementById('closeModalBtn');
    if (closeModalBtn) closeModalBtn.addEventListener('click', closeModal);

    const modalOverlay = document.getElementById('vehicleModalOverlay');
    if (modalOverlay) {
        modalOverlay.addEventListener('click', (e) => {
            if (e.target === modalOverlay) closeModal();
        });
    }

    const appointmentForm = document.getElementById('appointmentForm');
    if (appointmentForm) appointmentForm.addEventListener('submit', handleAppointmentSubmit);
}

async function loadVehicles(searchKeyword = '') {
    const catalogGrid = document.getElementById('vehiclesCatalog');
    if (!catalogGrid) return;

    catalogGrid.innerHTML = `
        <div style="grid-column: 1/-1; text-align: center; padding: 50px; color: var(--text-muted);">
            <i class="fa-solid fa-spinner fa-spin fa-2x"></i>
            <p style="margin-top: 10px;">Cargando inventario de lujo...</p>
        </div>
    `;

    try {
        const url = `crud/vehicles.php?action=list&category=${encodeURIComponent(currentCategory)}&brand=${encodeURIComponent(currentBrand)}&search=${encodeURIComponent(searchKeyword)}`;
        const res = await fetch(url);
        const data = await res.json();

        if (!data.success || !data.data || data.data.length === 0) {
            catalogGrid.innerHTML = `
                <div style="grid-column: 1/-1; text-align: center; padding: 60px; background: var(--bg-card); border-radius: var(--radius-lg); border: 1px solid var(--border-color);">
                    <i class="fa-solid fa-car-side fa-3x" style="color: var(--text-dim); margin-bottom: 15px;"></i>
                    <h3>No se encontraron vehículos</h3>
                    <p style="color: var(--text-muted);">Intenta ajustando los filtros o el término de búsqueda.</p>
                </div>
            `;
            return;
        }

        catalogGrid.innerHTML = data.data.map(car => renderCarCard(car)).join('');

    } catch (err) {
        console.error(err);
        catalogGrid.innerHTML = `<p style="grid-column: 1/-1; text-align: center; color: var(--accent-red);">Error al cargar inventario de vehículos.</p>`;
    }
}

function renderCarCard(car) {
    const isAgotado = car.status === 'Agotado';

    let ribbonHTML = '';
    let badgeHTML = '';
    let actionBtnHTML = '';

    if (isAgotado) {
        ribbonHTML = `<div class="ribbon-agotado"><i class="fa-solid fa-ban"></i> AUTO AGOTADO</div>`;
        badgeHTML = `<span class="badge badge-agotado"><i class="fa-solid fa-ban"></i> AGOTADO</span>`;
        actionBtnHTML = `
            <button class="btn-secondary btn-agotado-disabled" onclick="openVehicleModal(${car.id})">
                <i class="fa-solid fa-eye"></i> Ver Detalle (Agotado)
            </button>
        `;
    } else if (car.status === 'Reservado') {
        badgeHTML = `<span class="badge" style="background: rgba(255,183,3,0.2); color: var(--accent-gold); border: 1px solid rgba(255,183,3,0.4);"><i class="fa-solid fa-bookmark"></i> RESERVADO</span>`;
        actionBtnHTML = `
            <button class="btn-secondary" onclick="openVehicleModal(${car.id})">
                Ver Detalles <i class="fa-solid fa-arrow-right"></i>
            </button>
        `;
    } else if (car.status === 'Vendido') {
        badgeHTML = `<span class="badge" style="background: rgba(255,255,255,0.1); color: var(--text-muted);"><i class="fa-solid fa-check-double"></i> VENDIDO</span>`;
        actionBtnHTML = `
            <button class="btn-secondary" onclick="openVehicleModal(${car.id})">
                Ver Ficha <i class="fa-solid fa-arrow-right"></i>
            </button>
        `;
    } else {
        badgeHTML = `<span class="badge badge-disponible"><i class="fa-solid fa-circle-check"></i> DISPONIBLE</span>`;
        actionBtnHTML = `
            <button class="btn-primary" onclick="openVehicleModal(${car.id})">
                Detalles / Apartar <i class="fa-solid fa-arrow-right"></i>
            </button>
        `;
    }

    return `
        <div class="vehicle-card ${isAgotado ? 'card-agotado' : ''}">
            <div class="card-img-wrapper">
                ${ribbonHTML}
                <img src="${escapeHtml(car.image_url)}" alt="${escapeHtml(car.title)}" onerror="this.src='assets/images/car_1.png'">
                <div class="card-badges">
                    ${badgeHTML}
                    <span class="badge" style="background: rgba(0,242,254,0.15); color: var(--primary-glow); border: 1px solid rgba(0,242,254,0.3);">${car.model_year}</span>
                </div>
            </div>
            <div class="card-body">
                <div class="card-brand">${escapeHtml(car.brand_name)} • ${escapeHtml(car.category)} ${car.branch_name ? ' • <i class="fa-solid fa-location-dot"></i> ' + escapeHtml(car.branch_city || car.branch_name) : ''}</div>
                <h3 class="card-title">${escapeHtml(car.title)}</h3>
                
                <div class="card-specs">
                    <div class="spec-item"><i class="fa-solid fa-gauge-high"></i> ${formatNumber(car.mileage)} km</div>
                    <div class="spec-item"><i class="fa-solid fa-gears"></i> ${escapeHtml(car.transmission)}</div>
                    <div class="spec-item"><i class="fa-solid fa-gas-pump"></i> ${escapeHtml(car.fuel_type)}</div>
                    <div class="spec-item"><i class="fa-solid fa-bolt"></i> ${car.horsepower} HP</div>
                </div>

                <div class="card-footer">
                    <div>
                        <span style="font-size: 0.75rem; color: var(--text-muted); display: block;">PRECIO DE CONTADO</span>
                        <span class="card-price gold-text">$${formatPrice(car.price)} USD</span>
                    </div>
                    ${actionBtnHTML}
                </div>
            </div>
        </div>
    `;
}

async function openVehicleModal(id) {
    try {
        const res = await fetch(`crud/vehicles.php?action=get&id=${id}`);
        const result = await res.json();
        if (!result.success) return;

        const car = result.data;
        document.getElementById('modalTitle').textContent = car.title;
        document.getElementById('modalImage').src = car.image_url;
        document.getElementById('modalPrice').textContent = '$' + formatPrice(car.price) + ' USD';
        document.getElementById('modalYear').textContent = car.model_year;
        document.getElementById('modalBrand').textContent = car.brand_name;
        document.getElementById('modalCategory').textContent = car.category;
        document.getElementById('modalEngine').textContent = car.engine;
        document.getElementById('modalHP').textContent = car.horsepower + ' HP';
        document.getElementById('modalTransmission').textContent = car.transmission;
        document.getElementById('modalMileage').textContent = formatNumber(car.mileage) + ' km';
        document.getElementById('modalDescription').textContent = car.description || 'Sin descripción adicional.';

        // Cargar Video
        const iframe = document.getElementById('modalVideoIframe');
        const videoBtn = document.getElementById('btnMediaVideo');
        const defaultVideo = car.video_url || 'https://www.youtube.com/embed/39V_5dM2q_4';
        
        if (iframe) {
            iframe.src = defaultVideo;
        }

        // Siempre iniciar en la pestaña de foto
        switchModalMedia('photo');

        const agotadoBadge = document.getElementById('modalAgotadoBadge');
        const submitBtn = document.getElementById('appointmentSubmitBtn');
        const isAgotado = car.status === 'Agotado';

        if (isAgotado) {
            if (agotadoBadge) agotadoBadge.style.display = 'block';
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.style.background = '#3a3b3c';
                submitBtn.style.color = '#888';
                submitBtn.style.cursor = 'not-allowed';
                submitBtn.innerHTML = `<i class="fa-solid fa-ban"></i> UNIDAD AGOTADA`;
            }
        } else {
            if (agotadoBadge) agotadoBadge.style.display = 'none';
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.style.background = '';
                submitBtn.style.color = '';
                submitBtn.style.cursor = 'pointer';
                submitBtn.innerHTML = `<i class="fa-solid fa-calendar-check"></i> Confirmar Solicitud`;
            }
        }

        const featContainer = document.getElementById('modalFeaturesContainer');
        if (featContainer) {
            if (car.features && car.features.length > 0) {
                featContainer.innerHTML = car.features.map(f => `
                    <span class="badge" style="background: rgba(255,255,255,0.06); border: 1px solid var(--border-color); color: var(--text-main);">
                        <i class="fa-solid ${f.icon || 'fa-check'}"></i> ${escapeHtml(f.feature_name)}
                    </span>
                `).join('');
            } else {
                featContainer.innerHTML = '<small style="color:var(--text-muted);">Equipamiento de serie estándar.</small>';
            }
        }

        document.getElementById('appointmentVehicleId').value = car.id;
        document.getElementById('appointmentVehicleTitle').value = car.title;

        document.getElementById('vehicleModalOverlay').classList.add('active');
    } catch (err) {
        console.error(err);
    }
}

function switchModalMedia(type) {
    const photoContainer = document.getElementById('modalPhotoContainer');
    const videoContainer = document.getElementById('modalVideoContainer');
    const photoBtn = document.getElementById('btnMediaPhoto');
    const videoBtn = document.getElementById('btnMediaVideo');

    if (type === 'video') {
        if (photoContainer) photoContainer.style.display = 'none';
        if (videoContainer) videoContainer.style.display = 'block';
        if (photoBtn) photoBtn.classList.remove('active');
        if (videoBtn) videoBtn.classList.add('active');
    } else {
        if (photoContainer) photoContainer.style.display = 'block';
        if (videoContainer) videoContainer.style.display = 'none';
        if (photoBtn) photoBtn.classList.add('active');
        if (videoBtn) videoBtn.classList.remove('active');
    }
}

function closeModal() {
    const modalOverlay = document.getElementById('vehicleModalOverlay');
    if (modalOverlay) modalOverlay.classList.remove('active');
    
    // Detener reproducción de video al cerrar modal
    const iframe = document.getElementById('modalVideoIframe');
    if (iframe) {
        const currentSrc = iframe.src;
        iframe.src = '';
    }

    
    const adminModalOverlay = document.getElementById('adminVehicleModalOverlay');
    if (adminModalOverlay) adminModalOverlay.classList.remove('active');

    const adminResOverlay = document.getElementById('adminReservationModalOverlay');
    if (adminResOverlay) adminResOverlay.classList.remove('active');
}

async function handleAppointmentSubmit(e) {
    e.preventDefault();
    const form = e.target;
    const formData = new FormData(form);
    const type = formData.get('type');

    const submitBtn = form.querySelector('button[type="submit"]');
    submitBtn.disabled = true;
    submitBtn.innerHTML = `<i class="fa-solid fa-spinner fa-spin"></i> Procesando...`;

    try {
        let apiUrl = 'crud/appointments.php';
        if (type === 'Apartado del Vehículo') {
            apiUrl = 'crud/reservations.php';
            formData.append('deposit_amount', '5000');
        }
        formData.append('action', 'create');

        const res = await fetch(apiUrl, { method: 'POST', body: formData });
        const result = await res.json();

        if (result.success) {
            alert(result.message);
            form.reset();
            closeModal();
            loadVehicles(); // Recargar catálogo para actualizar estados
        } else {
            alert(result.message || 'Error al procesar solicitud.');
        }
    } catch (err) {
        alert('Error de conexión con el servidor.');
    } finally {
        submitBtn.disabled = false;
        submitBtn.innerHTML = `<i class="fa-solid fa-calendar-check"></i> Confirmar Solicitud`;
    }
}

/* Helper formatters */
function formatPrice(num) {
    return Number(num).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

function formatNumber(num) {
    return Number(num).toLocaleString('en-US');
}

function escapeHtml(str) {
    if (!str) return '';
    return str.replace(/[&<>"']/g, function(m) {
        return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' }[m];
    });
}
