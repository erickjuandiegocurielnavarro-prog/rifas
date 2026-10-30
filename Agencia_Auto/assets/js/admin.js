/**
 * AutoElite Motors - Panel Admin & CRUD JS
 * Lógica dedicada para la administración, tablas CRUD y actualización instantánea de autos agotados
 */

document.addEventListener('DOMContentLoaded', () => {
    if (document.getElementById('adminDashboard')) {
        initAdminDashboard();
    }
});

function initAdminDashboard() {
    loadAdminStats();
    loadAdminVehicles();
    loadAdminReservations();
    loadAdminBranches();
    loadAdminEmployees();
    loadAdminAppointments();
    loadAdminBrands();
    loadAdminClients();

    // Navegación de pestañas Admin
    const adminTabs = document.querySelectorAll('.admin-nav-btn');
    adminTabs.forEach(btn => {
        btn.addEventListener('click', () => {
            adminTabs.forEach(b => b.classList.remove('active'));
            btn.classList.add('active');

            const targetSection = btn.dataset.target;
            document.querySelectorAll('.admin-tab-content').forEach(section => {
                section.style.display = 'none';
            });
            const target = document.getElementById(targetSection);
            if (target) target.style.display = 'block';
        });
    });

    const adminVehicleForm = document.getElementById('adminVehicleForm');
    if (adminVehicleForm) {
        adminVehicleForm.addEventListener('submit', handleSaveAdminVehicle);
    }

    const adminResForm = document.getElementById('adminReservationForm');
    if (adminResForm) {
        adminResForm.addEventListener('submit', handleSaveAdminReservation);
    }
}

async function loadAdminStats() {
    try {
        const [resVeh, resRes, resApp, resCli] = await Promise.all([
            fetch('crud/vehicles.php?action=list'),
            fetch('crud/reservations.php?action=list'),
            fetch('crud/appointments.php?action=list'),
            fetch('crud/clients.php?action=list')
        ]);
        const vehData = await resVeh.json();
        const resData = await resRes.json();
        const appData = await resApp.json();
        const cliData = await resCli.json();

        if (vehData.success && vehData.data) {
            document.getElementById('statTotalVehicles').textContent = vehData.data.length;
            const totalAgotados = vehData.data.filter(v => v.status === 'Agotado').length;
            const statAgotadosElem = document.getElementById('statTotalAgotados');
            if (statAgotadosElem) statAgotadosElem.textContent = totalAgotados;
        }

        if (resData.success && document.getElementById('statTotalReservations')) {
            document.getElementById('statTotalReservations').textContent = resData.data.length;
        }
        if (appData.success && document.getElementById('statTotalAppointments')) {
            document.getElementById('statTotalAppointments').textContent = appData.data.length;
        }
        if (cliData.success && document.getElementById('statTotalClients')) {
            document.getElementById('statTotalClients').textContent = cliData.data.length;
        }
    } catch (err) {
        console.error('Error al cargar estadísticas admin:', err);
    }
}

async function loadAdminVehicles() {
    const tbody = document.getElementById('adminVehiclesTableBody');
    if (!tbody) return;

    try {
        const res = await fetch('crud/vehicles.php?action=list');
        const data = await res.json();

        if (!data.success || !data.data || data.data.length === 0) {
            tbody.innerHTML = `<tr><td colspan="8" style="text-align:center;">No hay vehículos registrados en inventario.</td></tr>`;
            return;
        }

        tbody.innerHTML = data.data.map(car => renderVehicleRowHTML(car)).join('');
    } catch (err) {
        tbody.innerHTML = `<tr><td colspan="8" style="text-align:center; color:var(--accent-red);">Error al cargar inventario de vehículos.</td></tr>`;
    }
}

function renderVehicleRowHTML(car) {
    const isAgotado = car.status === 'Agotado';

    let badgeStatusHTML = '';
    if (isAgotado) {
        badgeStatusHTML = `<span class="badge badge-agotado" id="status-badge-${car.id}"><i class="fa-solid fa-ban"></i> AGOTADO</span>`;
    } else if (car.status === 'Reservado') {
        badgeStatusHTML = `<span class="badge" style="background:rgba(255,183,3,0.15); color:var(--accent-gold);" id="status-badge-${car.id}"><i class="fa-solid fa-bookmark"></i> RESERVADO</span>`;
    } else if (car.status === 'Vendido') {
        badgeStatusHTML = `<span class="badge" style="background:rgba(255,255,255,0.1); color:var(--text-muted);" id="status-badge-${car.id}"><i class="fa-solid fa-check-double"></i> VENDIDO</span>`;
    } else {
        badgeStatusHTML = `<span class="badge badge-disponible" id="status-badge-${car.id}"><i class="fa-solid fa-circle-check"></i> DISPONIBLE</span>`;
    }

    const toggleBtnHTML = isAgotado ? `
        <button class="btn-agotado-toggle active" id="btn-toggle-${car.id}" onclick="toggleAgotadoInstant(${car.id})" title="Auto actualmente agotado. Haz clic para restaurar disponibilidad.">
            <i class="fa-solid fa-rotate-left"></i> Restablecer Disponible
        </button>
    ` : `
        <button class="btn-agotado-toggle" id="btn-toggle-${car.id}" onclick="toggleAgotadoInstant(${car.id})" title="Haz clic para marcar este auto como agotado de inmediato.">
            <i class="fa-solid fa-ban"></i> Marcar Agotado
        </button>
    `;

    return `
        <tr id="veh-row-${car.id}" class="${isAgotado ? 'row-agotado' : ''}">
            <td><img src="${escapeHtml(car.image_url)}" class="table-img" onerror="this.src='assets/images/car_1.png'"></td>
            <td>
                <strong>${escapeHtml(car.title)}</strong><br>
                <small style="color:var(--text-muted);">${escapeHtml(car.brand_name)} • ${car.model_year}</small>
            </td>
            <td><span class="badge" style="background:rgba(0,242,254,0.08); color:var(--primary-glow);"><i class="fa-solid fa-location-dot"></i> ${escapeHtml(car.branch_name || 'Matriz')}</span></td>
            <td><span class="badge" style="background:rgba(255,255,255,0.06);">${escapeHtml(car.category)}</span></td>
            <td><strong class="gold-text">$${formatPrice(car.price)}</strong></td>
            <td>
                <div style="display:flex; flex-direction:column; gap:6px;">
                    <div id="badge-container-${car.id}">${badgeStatusHTML}</div>
                    <select id="select-status-${car.id}" onchange="changeVehicleStatus(${car.id}, this.value)" class="form-control" style="padding: 4px 8px; font-size: 0.78rem;">
                        <option value="Disponible" ${car.status === 'Disponible' ? 'selected' : ''}>Disponible</option>
                        <option value="Agotado" ${car.status === 'Agotado' ? 'selected' : ''}>Agotado</option>
                        <option value="Reservado" ${car.status === 'Reservado' ? 'selected' : ''}>Reservado</option>
                        <option value="Vendido" ${car.status === 'Vendido' ? 'selected' : ''}>Vendido</option>
                    </select>
                </div>
            </td>
            <td>
                ${toggleBtnHTML}
            </td>
            <td>
                <div class="table-actions">
                    <button class="btn-icon edit" title="Editar Vehículo" onclick="openEditVehicleModal(${car.id})"><i class="fa-solid fa-pen-to-square"></i></button>
                    <button class="btn-icon delete" title="Eliminar Vehículo" onclick="deleteVehicle(${car.id})"><i class="fa-solid fa-trash-can"></i></button>
                </div>
            </td>
        </tr>
    `;
}

/**
 * Acción instantánea para alternar "Agotado" <-> "Disponible" inmediatamente
 */
async function toggleAgotadoInstant(id) {
    const btn = document.getElementById(`btn-toggle-${id}`);
    if (btn) {
        btn.disabled = true;
        btn.innerHTML = `<i class="fa-solid fa-spinner fa-spin"></i> Actualizando...`;
    }

    try {
        const formData = new FormData();
        formData.append('action', 'toggle_agotado');
        formData.append('id', id);

        const res = await fetch('crud/vehicles.php', { method: 'POST', body: formData });
        const result = await res.json();

        if (result.success) {
            const newStatus = result.new_status;
            updateRowUI(id, newStatus);
            loadAdminStats();
        } else {
            alert(result.message || 'Error al actualizar estado.');
        }
    } catch (err) {
        console.error('Error al toggle de agotado:', err);
        alert('Error de conexión con el servidor.');
    } finally {
        if (btn) btn.disabled = false;
    }
}

async function changeVehicleStatus(id, newStatus) {
    try {
        const formData = new FormData();
        formData.append('action', 'toggle_status');
        formData.append('id', id);
        formData.append('status', newStatus);

        const res = await fetch('crud/vehicles.php', { method: 'POST', body: formData });
        const result = await res.json();

        if (result.success) {
            updateRowUI(id, newStatus);
            loadAdminStats();
        } else {
            alert(result.message);
        }
    } catch (err) {
        console.error(err);
    }
}

function updateRowUI(id, newStatus) {
    const row = document.getElementById(`veh-row-${id}`);
    const badgeContainer = document.getElementById(`badge-container-${id}`);
    const select = document.getElementById(`select-status-${id}`);
    const btn = document.getElementById(`btn-toggle-${id}`);

    if (select) select.value = newStatus;

    if (newStatus === 'Agotado') {
        if (row) row.classList.add('row-agotado');
        if (badgeContainer) {
            badgeContainer.innerHTML = `<span class="badge badge-agotado" id="status-badge-${id}"><i class="fa-solid fa-ban"></i> AGOTADO</span>`;
        }
        if (btn) {
            btn.className = 'btn-agotado-toggle active';
            btn.innerHTML = `<i class="fa-solid fa-rotate-left"></i> Restablecer Disponible`;
        }
    } else if (newStatus === 'Reservado') {
        if (row) row.classList.remove('row-agotado');
        if (badgeContainer) {
            badgeContainer.innerHTML = `<span class="badge" style="background:rgba(255,183,3,0.15); color:var(--accent-gold);" id="status-badge-${id}"><i class="fa-solid fa-bookmark"></i> RESERVADO</span>`;
        }
        if (btn) {
            btn.className = 'btn-agotado-toggle';
            btn.innerHTML = `<i class="fa-solid fa-ban"></i> Marcar Agotado`;
        }
    } else if (newStatus === 'Vendido') {
        if (row) row.classList.remove('row-agotado');
        if (badgeContainer) {
            badgeContainer.innerHTML = `<span class="badge" style="background:rgba(255,255,255,0.1); color:var(--text-muted);" id="status-badge-${id}"><i class="fa-solid fa-check-double"></i> VENDIDO</span>`;
        }
        if (btn) {
            btn.className = 'btn-agotado-toggle';
            btn.innerHTML = `<i class="fa-solid fa-ban"></i> Marcar Agotado`;
        }
    } else {
        if (row) row.classList.remove('row-agotado');
        if (badgeContainer) {
            badgeContainer.innerHTML = `<span class="badge badge-disponible" id="status-badge-${id}"><i class="fa-solid fa-circle-check"></i> DISPONIBLE</span>`;
        }
        if (btn) {
            btn.className = 'btn-agotado-toggle';
            btn.innerHTML = `<i class="fa-solid fa-ban"></i> Marcar Agotado`;
        }
    }
}

async function loadAdminReservations() {
    const tbody = document.getElementById('adminReservationsTableBody');
    if (!tbody) return;

    try {
        const res = await fetch('crud/reservations.php?action=list');
        const data = await res.json();

        if (!data.success || !data.data || data.data.length === 0) {
            tbody.innerHTML = `<tr><td colspan="7" style="text-align:center;">No hay personas con autos apartados registradas.</td></tr>`;
            return;
        }

        tbody.innerHTML = data.data.map(r => `
            <tr>
                <td>#${r.id}</td>
                <td><strong>${escapeHtml(r.client_name)}</strong><br><small style="color:var(--text-muted);">${escapeHtml(r.client_email)} | ${escapeHtml(r.client_phone)}</small></td>
                <td><span class="badge" style="background:rgba(255,183,3,0.15); color:var(--accent-gold);"><i class="fa-solid fa-car"></i> ${escapeHtml(r.vehicle_title)}</span></td>
                <td><strong class="gold-text">$${formatPrice(r.deposit_amount)} USD</strong></td>
                <td>${escapeHtml(r.estimated_delivery_date || 'Pendiente')}</td>
                <td>
                    <select onchange="changeReservationStatus(${r.id}, this.value)" class="form-control" style="padding: 4px 8px; font-size: 0.8rem;">
                        <option value="Activo" ${r.status === 'Activo' ? 'selected' : ''}>Activo (Reservado)</option>
                        <option value="Concluido" ${r.status === 'Concluido' ? 'selected' : ''}>Concluido (Vendido)</option>
                        <option value="Cancelado" ${r.status === 'Cancelado' ? 'selected' : ''}>Cancelado</option>
                    </select>
                </td>
                <td>
                    <button class="btn-icon delete" title="Eliminar Apartado" onclick="deleteReservation(${r.id})"><i class="fa-solid fa-trash-can"></i></button>
                </td>
            </tr>
        `).join('');
    } catch (err) {
        tbody.innerHTML = `<tr><td colspan="7" style="text-align:center; color:var(--accent-red);">Error al cargar apartados.</td></tr>`;
    }
}

async function loadAdminBranches() {
    const tbody = document.getElementById('adminBranchesTableBody');
    if (!tbody) return;

    try {
        const res = await fetch('crud/branches.php?action=list');
        const data = await res.json();

        if (!data.success || !data.data || data.data.length === 0) {
            tbody.innerHTML = `<tr><td colspan="6" style="text-align:center;">No hay sucursales registradas.</td></tr>`;
            return;
        }

        tbody.innerHTML = data.data.map(b => `
            <tr>
                <td>#${b.id}</td>
                <td><strong>${escapeHtml(b.name)}</strong></td>
                <td><span class="badge" style="background:rgba(0,242,254,0.1); color:var(--primary-glow);">${escapeHtml(b.city)}</span></td>
                <td>${escapeHtml(b.address)}</td>
                <td>${escapeHtml(b.phone)}<br><small style="color:var(--text-muted);">${escapeHtml(b.email)}</small></td>
                <td><button class="btn-icon delete" onclick="deleteBranch(${b.id})"><i class="fa-solid fa-trash-can"></i></button></td>
            </tr>
        `).join('');
    } catch (err) {
        tbody.innerHTML = `<tr><td colspan="6" style="text-align:center;">Error al cargar sucursales.</td></tr>`;
    }
}

async function loadAdminEmployees() {
    const tbody = document.getElementById('adminEmployeesTableBody');
    if (!tbody) return;

    try {
        const res = await fetch('crud/employees.php?action=list');
        const data = await res.json();

        if (!data.success || !data.data || data.data.length === 0) {
            tbody.innerHTML = `<tr><td colspan="6" style="text-align:center;">No hay empleados registrados.</td></tr>`;
            return;
        }

        tbody.innerHTML = data.data.map(e => `
            <tr>
                <td>#${e.id}</td>
                <td><strong>${escapeHtml(e.full_name)}</strong></td>
                <td><span class="badge" style="background:rgba(255,255,255,0.06);">${escapeHtml(e.role)}</span></td>
                <td>${escapeHtml(e.branch_name || 'Showroom Matriz')}</td>
                <td>${escapeHtml(e.email)}<br><small style="color:var(--text-muted);">${escapeHtml(e.phone)}</small></td>
                <td><span class="badge badge-disponible">${escapeHtml(e.status)}</span></td>
            </tr>
        `).join('');
    } catch (err) {
        tbody.innerHTML = `<tr><td colspan="6" style="text-align:center;">Error al cargar empleados.</td></tr>`;
    }
}

async function loadAdminAppointments() {
    const tbody = document.getElementById('adminAppointmentsTableBody');
    if (!tbody) return;

    try {
        const res = await fetch('crud/appointments.php?action=list');
        const data = await res.json();

        if (!data.success || !data.data || data.data.length === 0) {
            tbody.innerHTML = `<tr><td colspan="7" style="text-align:center;">No hay citas registradas.</td></tr>`;
            return;
        }

        tbody.innerHTML = data.data.map(app => `
            <tr>
                <td>#${app.id}</td>
                <td><strong>${escapeHtml(app.client_name)}</strong><br><small style="color:var(--text-muted);">${escapeHtml(app.client_email)} | ${escapeHtml(app.client_phone)}</small></td>
                <td><span class="badge" style="background:rgba(0,242,254,0.1); color:var(--primary-glow);">${escapeHtml(app.vehicle_title)}</span></td>
                <td>${escapeHtml(app.appointment_date)}</td>
                <td>${escapeHtml(app.type)}</td>
                <td>
                    <select onchange="changeAppointmentStatus(${app.id}, this.value)" class="form-control" style="padding: 4px 8px; font-size: 0.8rem;">
                        <option value="Pendiente" ${app.status === 'Pendiente' ? 'selected' : ''}>Pendiente</option>
                        <option value="Confirmada" ${app.status === 'Confirmada' ? 'selected' : ''}>Confirmada</option>
                        <option value="Completada" ${app.status === 'Completada' ? 'selected' : ''}>Completada</option>
                        <option value="Cancelada" ${app.status === 'Cancelada' ? 'selected' : ''}>Cancelada</option>
                    </select>
                </td>
                <td>
                    <button class="btn-icon delete" title="Eliminar Cita" onclick="deleteAppointment(${app.id})"><i class="fa-solid fa-trash-can"></i></button>
                </td>
            </tr>
        `).join('');
    } catch (err) {
        tbody.innerHTML = `<tr><td colspan="7" style="text-align:center; color:var(--accent-red);">Error al cargar citas.</td></tr>`;
    }
}

async function loadAdminBrands() {
    const tbody = document.getElementById('adminBrandsTableBody');
    if (!tbody) return;

    try {
        const res = await fetch('crud/brands.php?action=list');
        const data = await res.json();

        if (!data.success || !data.data || data.data.length === 0) {
            tbody.innerHTML = `<tr><td colspan="4" style="text-align:center;">No hay marcas registradas.</td></tr>`;
            return;
        }

        tbody.innerHTML = data.data.map(b => `
            <tr>
                <td>#${b.id}</td>
                <td><strong><i class="fa-solid ${escapeHtml(b.logo_icon)}"></i> ${escapeHtml(b.name)}</strong></td>
                <td>${escapeHtml(b.country)}</td>
                <td><button class="btn-icon delete" onclick="deleteBrand(${b.id})"><i class="fa-solid fa-trash-can"></i></button></td>
            </tr>
        `).join('');
    } catch (err) {
        tbody.innerHTML = `<tr><td colspan="4" style="text-align:center;">Error al cargar marcas.</td></tr>`;
    }
}

async function loadAdminClients() {
    const tbody = document.getElementById('adminClientsTableBody');
    if (!tbody) return;

    try {
        const res = await fetch('crud/clients.php?action=list');
        const data = await res.json();

        if (!data.success || !data.data || data.data.length === 0) {
            tbody.innerHTML = `<tr><td colspan="6" style="text-align:center;">No hay clientes registrados.</td></tr>`;
            return;
        }

        tbody.innerHTML = data.data.map(cli => `
            <tr>
                <td>#${cli.id}</td>
                <td><strong>${escapeHtml(cli.full_name)}</strong></td>
                <td>${escapeHtml(cli.email)}<br><small style="color:var(--text-muted);">${escapeHtml(cli.phone)}</small></td>
                <td>${escapeHtml(cli.interested_category)}</td>
                <td>$${formatPrice(cli.budget)}</td>
                <td><span class="badge" style="background:rgba(255,255,255,0.06);">${escapeHtml(cli.status)}</span></td>
            </tr>
        `).join('');
    } catch (err) {
        tbody.innerHTML = `<tr><td colspan="6" style="text-align:center;">Error al cargar clientes.</td></tr>`;
    }
}

/* Modales y Operaciones CRUD Admin */
function openNewVehicleModal() {
    const form = document.getElementById('adminVehicleForm');
    form.reset();
    document.getElementById('adminVehicleId').value = 0;
    document.getElementById('adminModalHeaderTitle').textContent = 'Registrar Nuevo Vehículo';
    document.getElementById('adminVehicleModalOverlay').classList.add('active');
}

function openNewReservationModal() {
    const form = document.getElementById('adminReservationForm');
    form.reset();
    document.getElementById('resId').value = 0;
    document.getElementById('adminReservationModalOverlay').classList.add('active');
}

async function handleSaveAdminReservation(e) {
    e.preventDefault();
    const formData = new FormData(e.target);
    formData.append('action', 'save');

    try {
        const res = await fetch('crud/reservations.php', { method: 'POST', body: formData });
        const result = await res.json();
        if (result.success) {
            alert(result.message);
            closeModal();
            loadAdminReservations();
            loadAdminVehicles();
            loadAdminStats();
        } else {
            alert(result.message);
        }
    } catch (err) {
        alert('Error al guardar el apartado.');
    }
}

async function changeReservationStatus(id, status) {
    const formData = new FormData();
    formData.append('action', 'update_status');
    formData.append('id', id);
    formData.append('status', status);

    await fetch('crud/reservations.php', { method: 'POST', body: formData });
    loadAdminReservations();
    loadAdminVehicles();
    loadAdminStats();
}

async function deleteReservation(id) {
    if (!confirm('¿Eliminar este registro de apartado?')) return;
    const formData = new FormData();
    formData.append('action', 'delete');
    formData.append('id', id);

    await fetch('crud/reservations.php', { method: 'POST', body: formData });
    loadAdminReservations();
    loadAdminVehicles();
    loadAdminStats();
}

async function openEditVehicleModal(id) {
    try {
        const res = await fetch(`crud/vehicles.php?action=get&id=${id}`);
        const result = await res.json();
        if (!result.success) return;

        const car = result.data;
        document.getElementById('adminVehicleId').value = car.id;
        document.getElementById('adminTitle').value = car.title;
        document.getElementById('adminBrandName').value = car.brand_name;
        if (document.getElementById('adminBranchId')) document.getElementById('adminBranchId').value = car.branch_id || 1;
        document.getElementById('adminCategory').value = car.category;
        document.getElementById('adminModelYear').value = car.model_year;
        document.getElementById('adminPrice').value = car.price;
        document.getElementById('adminMileage').value = car.mileage;
        document.getElementById('adminTransmission').value = car.transmission;
        document.getElementById('adminFuelType').value = car.fuel_type;
        document.getElementById('adminEngine').value = car.engine;
        document.getElementById('adminHorsepower').value = car.horsepower;
        document.getElementById('adminStatus').value = car.status;
        document.getElementById('adminImageUrl').value = car.image_url;
        if (document.getElementById('adminVideoUrl')) document.getElementById('adminVideoUrl').value = car.video_url || '';
        document.getElementById('adminDescription').value = car.description;


        document.getElementById('adminModalHeaderTitle').textContent = 'Editar Vehículo #' + car.id;
        document.getElementById('adminVehicleModalOverlay').classList.add('active');
    } catch (err) {
        alert('Error al cargar datos del vehículo.');
    }
}

async function handleSaveAdminVehicle(e) {
    e.preventDefault();
    const formData = new FormData(e.target);
    formData.append('action', 'save');

    try {
        const res = await fetch('crud/vehicles.php', { method: 'POST', body: formData });
        const result = await res.json();
        if (result.success) {
            alert(result.message);
            closeModal();
            loadAdminVehicles();
            loadAdminStats();
        } else {
            alert(result.message);
        }
    } catch (err) {
        alert('Error al guardar vehículo.');
    }
}

async function deleteVehicle(id) {
    if (!confirm('¿Estás seguro de que deseas eliminar este vehículo?')) return;
    const formData = new FormData();
    formData.append('action', 'delete');
    formData.append('id', id);

    const res = await fetch('crud/vehicles.php', { method: 'POST', body: formData });
    const result = await res.json();
    if (result.success) {
        loadAdminVehicles();
        loadAdminStats();
    }
}

async function changeAppointmentStatus(id, status) {
    const formData = new FormData();
    formData.append('action', 'update_status');
    formData.append('id', id);
    formData.append('status', status);

    await fetch('crud/appointments.php', { method: 'POST', body: formData });
}

async function deleteAppointment(id) {
    if (!confirm('¿Eliminar esta solicitud de cita?')) return;
    const formData = new FormData();
    formData.append('action', 'delete');
    formData.append('id', id);
    await fetch('crud/appointments.php', { method: 'POST', body: formData });
    loadAdminAppointments();
    loadAdminStats();
}

async function addBranchPrompt() {
    const name = prompt('Nombre de la Sucursal (Ej. Showroom Polanco):');
    if (!name) return;
    const city = prompt('Ciudad:', 'Ciudad de México') || 'CDMX';
    const address = prompt('Dirección:', 'Av. Presidente Masaryk 100') || '';
    const phone = prompt('Teléfono:', '+52 55 5000 1122') || '';
    const email = prompt('Email:', 'polanco@autoelite.com') || '';

    const formData = new FormData();
    formData.append('action', 'save');
    formData.append('name', name);
    formData.append('city', city);
    formData.append('address', address);
    formData.append('phone', phone);
    formData.append('email', email);

    await fetch('crud/branches.php', { method: 'POST', body: formData });
    loadAdminBranches();
}

async function deleteBranch(id) {
    if (!confirm('¿Eliminar esta sucursal?')) return;
    const formData = new FormData();
    formData.append('action', 'delete');
    formData.append('id', id);
    await fetch('crud/branches.php', { method: 'POST', body: formData });
    loadAdminBranches();
}

async function addEmployeePrompt() {
    const full_name = prompt('Nombre Completo del Asesor:');
    if (!full_name) return;
    const email = prompt('Correo Electrónico:', 'asesor@autoelite.com');
    const role = prompt('Puesto:', 'Asesor de Ventas Senior') || 'Asesor de Ventas';
    const phone = prompt('Teléfono:', '+52 55 0000 0000') || '';

    const formData = new FormData();
    formData.append('action', 'save');
    formData.append('full_name', full_name);
    formData.append('email', email);
    formData.append('role', role);
    formData.append('phone', phone);

    await fetch('crud/employees.php', { method: 'POST', body: formData });
    loadAdminEmployees();
    loadAdminStats();
}

async function addBrandPrompt() {
    const name = prompt('Nombre de la Marca (ej. Ferrari):');
    if (!name) return;
    const country = prompt('País de Origen:', 'Italia') || 'Internacional';

    const formData = new FormData();
    formData.append('action', 'save');
    formData.append('name', name);
    formData.append('country', country);

    await fetch('crud/brands.php', { method: 'POST', body: formData });
    loadAdminBrands();
}

async function deleteBrand(id) {
    if (!confirm('¿Eliminar esta marca?')) return;
    const formData = new FormData();
    formData.append('action', 'delete');
    formData.append('id', id);
    await fetch('crud/brands.php', { method: 'POST', body: formData });
    loadAdminBrands();
}
