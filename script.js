// Funcție pentru schimbarea imaginii principale în galerie
function changeImage(mainImgId, newSrc) {
    const mainImg = document.getElementById(mainImgId);
    mainImg.src = newSrc;
}

// Configurare iCal URLs pentru proprietăți
const icalURLs = {
    'Studio New York - Oradea': 'https://api.mobile-calendar.com/v1/ical/0000c0707feda709a38615fd0772e3f8/basic.ics'
};

// Stocare globală a rezervărilor pentru fiecare proprietate
const propertyBookings = {};

// Funcție pentru parsarea iCal și extragerea datelor ocupate
async function fetchAvailability(propertyName) {
    const icalURL = icalURLs[propertyName];
    if (!icalURL) return { available: true, bookings: [] };

    try {
        // Folosim un proxy CORS pentru a accesa iCal-ul
        const proxyURL = 'https://api.allorigins.win/raw?url=';
        const response = await fetch(proxyURL + encodeURIComponent(icalURL));
        const icalData = await response.text();

        // Parsare simplă a datelor iCal
        const bookings = parseICalBookings(icalData);

        // Salvează rezervările global
        propertyBookings[propertyName] = bookings;

        const isAvailable = checkAvailability(bookings);

        console.log(`${propertyName} - Rezervări găsite:`, bookings.length);
        console.log('Disponibil:', isAvailable);

        return { available: isAvailable, bookings };
    } catch (error) {
        console.error('Eroare la încărcarea disponibilității:', error);
        return { available: true, bookings: [] };
    }
}

// Parsare simplă iCal pentru a extrage rezervările
function parseICalBookings(icalData) {
    const bookings = [];
    const events = icalData.split('BEGIN:VEVENT');

    events.forEach(event => {
        if (event.includes('DTSTART') && event.includes('DTEND')) {
            const dtStartMatch = event.match(/DTSTART[;:].*?(\d{8})/);
            const dtEndMatch = event.match(/DTEND[;:].*?(\d{8})/);

            if (dtStartMatch && dtEndMatch) {
                const startDate = parseICalDate(dtStartMatch[1]);
                const endDate = parseICalDate(dtEndMatch[1]);

                if (startDate && endDate) {
                    bookings.push({ start: startDate, end: endDate });
                }
            }
        }
    });

    return bookings;
}

// Convertire dată iCal (YYYYMMDD) în obiect Date
function parseICalDate(dateStr) {
    if (dateStr.length >= 8) {
        const year = parseInt(dateStr.substr(0, 4));
        const month = parseInt(dateStr.substr(4, 2)) - 1;
        const day = parseInt(dateStr.substr(6, 2));
        return new Date(year, month, day);
    }
    return null;
}

// Verificare disponibilitate (dacă ASTĂZI/MÂINE e disponibil)
function checkAvailability(bookings) {
    const today = new Date();
    today.setHours(0, 0, 0, 0);

    const tomorrow = new Date(today);
    tomorrow.setDate(tomorrow.getDate() + 1);

    for (const booking of bookings) {
        // Verifică dacă ASTĂZI sau MÂINE e ocupat
        if (booking.start <= tomorrow && booking.end >= today) {
            return false; // Ocupat ACUM
        }
    }

    return true; // Disponibil ACUM
}

// Verifică dacă o dată specifică e disponibilă
function isDateAvailable(date, bookings) {
    if (!bookings || bookings.length === 0) return true;

    const checkDate = new Date(date);
    checkDate.setHours(0, 0, 0, 0);

    for (const booking of bookings) {
        if (checkDate >= booking.start && checkDate < booking.end) {
            return false; // Data e ocupată
        }
    }

    return true; // Data e liberă
}

// Obține toate datele ocupate pentru o proprietate
function getOccupiedDates(propertyName) {
    const bookings = propertyBookings[propertyName] || [];
    const occupiedDates = [];

    bookings.forEach(booking => {
        let currentDate = new Date(booking.start);
        while (currentDate < booking.end) {
            occupiedDates.push(new Date(currentDate));
            currentDate.setDate(currentDate.getDate() + 1);
        }
    });

    return occupiedDates;
}

// Actualizare badge-uri de disponibilitate
async function updateAvailabilityBadges() {
    // Studio New York
    const studioAvailability = await fetchAvailability('Studio New York - Oradea');
    updateBadge('Studio New York - Oradea', studioAvailability.available);
}

// Actualizare badge individual
function updateBadge(propertyName, isAvailable) {
    // Găsește cardul proprietății
    const cards = document.querySelectorAll('.cazare-card');
    cards.forEach(card => {
        const title = card.querySelector('h3');
        if (title && title.textContent.includes(propertyName.split(' - ')[0])) {
            const badge = card.querySelector('.availability-badge');
            const dot = badge.querySelector('.status-dot');
            const text = badge.querySelector('span:last-child');

            if (isAvailable) {
                dot.classList.add('available');
                dot.classList.remove('occupied');
                badge.style.background = '#f0fdf4';
                badge.style.borderColor = '#86efac';
                badge.style.color = '#166534';
                text.textContent = 'Disponibil';
            } else {
                dot.classList.remove('available');
                dot.classList.add('occupied');
                badge.style.background = '#fef2f2';
                badge.style.borderColor = '#fca5a5';
                badge.style.color = '#991b1b';
                text.textContent = 'Ocupat (Vezi alte date)';
            }
        }
    });
}

// Funcție pentru scroll lin la secțiuni
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
            target.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }
    });
});

// Funcție pentru selectarea cazării din carduri
function selecteazaCazare(tipCazare, pret) {
    const selectCazare = document.getElementById('cazare');
    selectCazare.value = tipCazare;

    // Scroll la secțiunea de rezervare
    document.getElementById('rezervare').scrollIntoView({
        behavior: 'smooth',
        block: 'start'
    });

    // Calculează totalul dacă sunt date check-in și check-out
    calculeazaTotal();
}

// Calculare automată a totalului
function calculeazaTotal() {
    const selectCazare = document.getElementById('cazare');
    const checkinInput = document.getElementById('checkin');
    const checkoutInput = document.getElementById('checkout');
    const totalInput = document.getElementById('total');

    const selectedOption = selectCazare.options[selectCazare.selectedIndex];
    const pret = selectedOption.getAttribute('data-pret');

    if (pret && checkinInput.value && checkoutInput.value) {
        const checkin = new Date(checkinInput.value);
        const checkout = new Date(checkoutInput.value);

        // Calculează diferența în zile
        const diferentaZile = Math.ceil((checkout - checkin) / (1000 * 60 * 60 * 24));

        if (diferentaZile > 0) {
            const total = diferentaZile * parseInt(pret);
            totalInput.value = total + ' Lei (' + diferentaZile + ' nopți)';
        } else {
            totalInput.value = '0 Lei';
        }
    } else {
        totalInput.value = '0 Lei';
    }
}

// Event listeners pentru calcularea automată a totalului
document.getElementById('cazare').addEventListener('change', function() {
    calculeazaTotal();
    updateDatePickerForProperty();
});
document.getElementById('checkin').addEventListener('change', function() {
    calculeazaTotal();
    validateSelectedDates();
});
document.getElementById('checkout').addEventListener('change', function() {
    calculeazaTotal();
    validateSelectedDates();
});

// Actualizează date picker când se selectează o proprietate
function updateDatePickerForProperty() {
    const selectedProperty = document.getElementById('cazare').value;
    if (!selectedProperty) return;

    const checkinInput = document.getElementById('checkin');
    const checkoutInput = document.getElementById('checkout');

    // Resetează datele selectate
    checkinInput.value = '';
    checkoutInput.value = '';

    // Actualizează mesajul de ajutor
    showAvailabilityInfo(selectedProperty);
}

// Afișează informații despre disponibilitate
function showAvailabilityInfo(propertyName) {
    const bookings = propertyBookings[propertyName] || [];

    if (bookings.length > 0) {
        let message = `📅 Date ocupate pentru ${propertyName.split(' - ')[0]}:\n\n`;
        bookings.forEach((booking, index) => {
            const startStr = formatDate(booking.start);
            const endStr = formatDate(booking.end);
            message += `${index + 1}. ${startStr} - ${endStr}\n`;
        });

        console.log(message);

        // Adaugă mesaj vizual sub formular
        const existingMessage = document.getElementById('occupiedDatesMessage');
        if (existingMessage) {
            existingMessage.remove();
        }

        const messageDiv = document.createElement('div');
        messageDiv.id = 'occupiedDatesMessage';
        messageDiv.className = 'occupied-dates-info';
        messageDiv.innerHTML = `
            <h4>📅 Date Ocupate:</h4>
            <ul>
                ${bookings.map(b => `
                    <li>
                        <span class="date-range">${formatDate(b.start)} - ${formatDate(b.end)}</span>
                    </li>
                `).join('')}
            </ul>
            <p class="info-text">💡 Alege alte date pentru a face rezervarea.</p>
        `;

        const formRezervare = document.getElementById('formRezervare');
        formRezervare.insertBefore(messageDiv, formRezervare.firstChild);
    }
}

// Formatare dată pentru afișare
function formatDate(date) {
    const day = date.getDate().toString().padStart(2, '0');
    const month = (date.getMonth() + 1).toString().padStart(2, '0');
    const year = date.getFullYear();
    return `${day}.${month}.${year}`;
}

// Validează datele selectate
function validateSelectedDates() {
    const selectedProperty = document.getElementById('cazare').value;
    if (!selectedProperty) return;

    const checkinInput = document.getElementById('checkin');
    const checkoutInput = document.getElementById('checkout');

    if (!checkinInput.value || !checkoutInput.value) return;

    const checkinDate = new Date(checkinInput.value);
    const checkoutDate = new Date(checkoutInput.value);
    const bookings = propertyBookings[selectedProperty] || [];

    // Verifică dacă intervalul selectat se suprapune cu rezervări
    let hasConflict = false;
    let conflictDetails = '';

    for (const booking of bookings) {
        // Verifică dacă există suprapunere
        if (checkinDate < booking.end && checkoutDate > booking.start) {
            hasConflict = true;
            conflictDetails = `${formatDate(booking.start)} - ${formatDate(booking.end)}`;
            break;
        }
    }

    // Afișează mesaj dacă există conflict
    const existingWarning = document.getElementById('dateConflictWarning');
    if (existingWarning) {
        existingWarning.remove();
    }

    if (hasConflict) {
        const warningDiv = document.createElement('div');
        warningDiv.id = 'dateConflictWarning';
        warningDiv.className = 'date-conflict-warning';
        warningDiv.innerHTML = `
            ⚠️ <strong>Atenție!</strong> Datele selectate se suprapun cu o rezervare existentă (${conflictDetails}).
            <br>Te rugăm să alegi alte date.
        `;

        checkoutInput.parentElement.appendChild(warningDiv);

        // Marchează input-urile cu roșu
        checkinInput.style.borderColor = '#ef4444';
        checkoutInput.style.borderColor = '#ef4444';
    } else {
        // Marchează input-urile cu verde dacă sunt OK
        checkinInput.style.borderColor = '#22c55e';
        checkoutInput.style.borderColor = '#22c55e';
    }
}

// Setare dată minimă pentru check-in (astăzi)
const astazi = new Date().toISOString().split('T')[0];
document.getElementById('checkin').setAttribute('min', astazi);

// Event listener pentru check-in (actualizează check-out minim)
document.getElementById('checkin').addEventListener('change', function() {
    const checkinData = this.value;
    document.getElementById('checkout').setAttribute('min', checkinData);
});

// Funcție pentru salvarea rezervării pe server
async function saveBookingToServer(bookingData) {
    try {
        const response = await fetch('/.netlify/functions/save-booking', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(bookingData)
        });

        const result = await response.json();
        return result;
    } catch (error) {
        console.error('Eroare la salvarea rezervării:', error);
        return { success: false, error: error.message };
    }
}

// Gestionare trimitere formular
document.getElementById('formRezervare').addEventListener('submit', function(e) {
    e.preventDefault();

    // Obține datele din formular
    const formData = {
        nume: document.getElementById('nume').value,
        email: document.getElementById('email').value,
        telefon: document.getElementById('telefon').value,
        cazare: document.getElementById('cazare').value,
        checkin: document.getElementById('checkin').value,
        checkout: document.getElementById('checkout').value,
        persoane: document.getElementById('persoane').value,
        total: document.getElementById('total').value,
        observatii: document.getElementById('observatii').value
    };

    // Validare
    if (!formData.cazare) {
        alert('Vă rugăm să selectați tipul de cazare!');
        return;
    }

    const checkin = new Date(formData.checkin);
    const checkout = new Date(formData.checkout);

    if (checkout <= checkin) {
        alert('Data de check-out trebuie să fie după data de check-in!');
        return;
    }

    // Trimite rezervarea către Netlify Function pentru salvare
    saveBookingToServer(formData).then(response => {
        if (response.success) {
            console.log('Rezervare salvată cu succes:', formData);

            // Afișează mesajul de confirmare
            document.getElementById('formRezervare').style.display = 'none';
            document.getElementById('mesajConfirmare').style.display = 'block';

            // Scroll la mesajul de confirmare
            document.getElementById('mesajConfirmare').scrollIntoView({
                behavior: 'smooth',
                block: 'center'
            });
        } else {
            alert('Eroare la salvarea rezervării. Vă rugăm să încercați din nou.');
        }
    }).catch(error => {
        console.error('Eroare:', error);
        alert('Eroare la trimiterea rezervării. Vă rugăm să ne contactați telefonic.');
    });
});

// Funcție pentru închiderea mesajului de confirmare și resetarea formularului
function inchideMesaj() {
    document.getElementById('mesajConfirmare').style.display = 'none';
    document.getElementById('formRezervare').style.display = 'block';
    document.getElementById('formRezervare').reset();
    document.getElementById('total').value = '0 Lei';
}

// Animație la scroll pentru cardurile de cazare
const observerOptions = {
    threshold: 0.1,
    rootMargin: '0px 0px -50px 0px'
};

const observer = new IntersectionObserver(function(entries) {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            entry.target.style.animation = 'fadeInUp 0.6s ease-out';
            observer.unobserve(entry.target);
        }
    });
}, observerOptions);

// Observă toate cardurile de cazare
document.addEventListener('DOMContentLoaded', function() {
    const cazareCards = document.querySelectorAll('.cazare-card');
    cazareCards.forEach(card => {
        observer.observe(card);
    });

    // Actualizează disponibilitatea din iCal
    updateAvailabilityBadges();
});