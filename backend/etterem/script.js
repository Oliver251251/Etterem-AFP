// Tiszta REST alapú végpontok
const API_URL = 'api/index.php';

document.addEventListener('DOMContentLoaded', () => {
    // 1. Étlap betöltése (GET /api/etelek)
    const menuGrid = document.querySelector('.menu-grid');
    if (menuGrid) loadMenu(menuGrid);

    // 2. Asztalfoglalás küldése (POST /api/foglalasok)
    const reservationForm = document.querySelector('#foglalas form');
    if (reservationForm) {
        reservationForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const payload = {
                guest_name: document.getElementById('guest-name').value,
                email: document.getElementById('guest-email').value,
                date: document.getElementById('date').value,
                time: document.getElementById('time').value,
                guests: document.getElementById('guests').value,
                table_type: document.getElementById('table').value,
                note: document.getElementById('note').value
            };

            const res = await fetch(`${API_URL}/foglalasok`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });
            const json = await res.json();
            alert(json.message);
            if (res.ok) reservationForm.reset();
        });
    }

    // 3. Vendég belépés (POST /api/login)
    const guestLoginForm = document.querySelector('#belepes form');
    if (guestLoginForm) {
        guestLoginForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const res = await fetch(`${API_URL}/login`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    email: document.getElementById('login-email').value,
                    password: document.getElementById('login-password').value,
                    role: 'guest'
                })
            });
            const json = await res.json();
            alert(json.message);
        });
    }

    // 4. Admin belépés (POST /api/login)
    const adminLoginForm = document.querySelector('#admin-login form');
    if (adminLoginForm) {
        adminLoginForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const res = await fetch(`${API_URL}/login`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    email: document.getElementById('admin-email').value,
                    password: document.getElementById('admin-password').value,
                    role: 'admin'
                })
            });
            const json = await res.json();
            alert(json.message);
            if (res.ok) {
                document.getElementById('dashboard').scrollIntoView({ behavior: 'smooth' });
            }
        });
    }

    // 5. Admin vezérlőpult betöltése
    if (document.querySelector('.admin-panel table tbody')) {
        loadAdminDashboard();
    }

    // 6. Új étel felvétele (POST /api/etelek)
    const addFoodForm = document.querySelector('.admin-menu-form');
    if (addFoodForm) {
        addFoodForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const payload = {
                food_name: document.getElementById('food-name').value,
                category: document.getElementById('food-category').value,
                price: document.getElementById('food-price').value,
                description: document.getElementById('food-description').value,
                available: addFoodForm.querySelector('input[name="available"]').checked
            };

            const res = await fetch(`${API_URL}/etelek`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });
            const json = await res.json();
            alert(json.message);
            if (res.ok) {
                addFoodForm.reset();
                loadAdminDashboard();
            }
        });
    }
});

async function loadMenu(container) {
    try {
        const res = await fetch(`${API_URL}/etelek`);
        const json = await res.json();
        const etelek = json.data || [];

        const katMap = { eloetel: 'Előétel', foetel: 'Főétel', desszert: 'Desszert', ital: 'Ital' };
        const iconMap = { eloetel: '🥗', foetel: '🍝', desszert: '🍰', ital: '🍸' };

        container.innerHTML = '';
        etelek.forEach(etel => {
            const card = document.createElement('article');
            card.className = 'menu-card';
            card.innerHTML = `
                <div class="food-icon">${iconMap[etel.kategoria] || '🍽️'}</div>
                <div class="menu-info">
                    <span class="category">${katMap[etel.kategoria] || etel.kategoria}</span>
                    <h3>${etel.nev}</h3>
                    <p>${etel.leiras || ''}</p>
                    <strong>${Number(etel.ar).toLocaleString('hu-HU')} Ft</strong>
                </div>
            `;
            container.appendChild(card);
        });
    } catch (e) {
        console.error('Hiba az étlap betöltésekor:', e);
    }
}

async function loadAdminDashboard() {
    try {
        // GET /api/statisztikak
        const statsRes = await fetch(`${API_URL}/statisztikak`);
        const statsJson = await statsRes.json();
        const stats = statsJson.data;

        const cards = document.querySelectorAll('.stat-card strong');
        if (cards.length >= 3 && stats) {
            cards[0].textContent = stats.maiFoglalasok;
            cards[1].textContent = stats.szabadAsztalok;
            cards[2].textContent = stats.etlaponEtelek;
        }

        // GET /api/foglalasok
        const fogRes = await fetch(`${API_URL}/foglalasok`);
        const fogJson = await fogRes.json();
        const foglalasok = fogJson.data || [];

        const tbody = document.querySelector('.admin-panel table tbody');
        tbody.innerHTML = '';

        foglalasok.forEach(f => {
            const confirmed = f.allapot === 'Visszaigazolva';
            const statusClass = confirmed ? 'status-confirmed' : 'status-pending';
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>${f.vendeg_nev}</td>
                <td>${f.datum.replaceAll('-', '.')}.</td>
                <td>${f.idopont}</td>
                <td>${f.vendegek_szama}</td>
                <td><span class="status ${statusClass}">${f.allapot}</span></td>
                <td>
                    ${confirmed
                        ? `<button type="button" class="small-btn" onclick="alert('E-mail: ${f.email}\nAsztal: ${f.asztal_tipus}\nMegjegyzés: ${f.megjegyzes || '-'}')">Részletek</button>`
                        : `<button type="button" class="small-btn" onclick="approveBooking(${f.id})">Jóváhagyás</button>`
                    }
                </td>
            `;
            tbody.appendChild(tr);
        });
    } catch (e) {
        console.error('Hiba az admin adatok betöltésekor:', e);
    }
}

// PATCH /api/foglalasok/{id}
window.approveBooking = async function(id) {
    const res = await fetch(`${API_URL}/foglalasok/${id}`, {
        method: 'PATCH',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ allapot: 'Visszaigazolva' })
    });
    if (res.ok) loadAdminDashboard();
};
