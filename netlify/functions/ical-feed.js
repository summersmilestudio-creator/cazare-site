// Netlify Function pentru generarea feed-ului iCal
const fs = require('fs');
const path = require('path');

exports.handler = async (event, context) => {
  try {
    // Citește rezervările din fișierul JSON
    const reservationsPath = path.join(__dirname, '..', '..', 'reservations.json');
    let bookings = [];

    try {
      const data = fs.readFileSync(reservationsPath, 'utf8');
      const reservations = JSON.parse(data);
      bookings = reservations.bookings || [];
    } catch (err) {
      console.log('Nu există rezervări încă');
    }

    // Generează iCal content
    const icalContent = generateICalendar(bookings);

    return {
      statusCode: 200,
      headers: {
        'Content-Type': 'text/calendar; charset=utf-8',
        'Content-Disposition': 'inline; filename="calendar.ics"',
        'Access-Control-Allow-Origin': '*'
      },
      body: icalContent
    };
  } catch (error) {
    return {
      statusCode: 500,
      body: JSON.stringify({ error: error.message })
    };
  }
};

function generateICalendar(bookings) {
  const now = new Date();
  const timestamp = now.toISOString().replace(/[-:]/g, '').split('.')[0] + 'Z';

  let ical = [
    'BEGIN:VCALENDAR',
    'VERSION:2.0',
    'PRODID:-//Cazare Premium//Booking System//RO',
    'CALSCALE:GREGORIAN',
    'METHOD:PUBLISH',
    'X-WR-CALNAME:Rezervari Cazare Premium',
    'X-WR-TIMEZONE:Europe/Bucharest',
    'X-WR-CALDESC:Rezervari din site-ul Cazare Premium'
  ];

  bookings.forEach((booking, index) => {
    const checkin = new Date(booking.checkin);
    const checkout = new Date(booking.checkout);

    // Format: YYYYMMDD
    const dtstart = formatDateForICal(checkin);
    const dtend = formatDateForICal(checkout);
    const uid = `booking-${booking.id || index}-${timestamp}@cazare-premium.ro`;

    ical.push('BEGIN:VEVENT');
    ical.push(`UID:${uid}`);
    ical.push(`DTSTART;VALUE=DATE:${dtstart}`);
    ical.push(`DTEND;VALUE=DATE:${dtend}`);
    ical.push(`DTSTAMP:${timestamp}`);
    ical.push(`SUMMARY:Rezervare: ${booking.nume || 'Client'} - ${booking.cazare}`);
    ical.push(`DESCRIPTION:Check-in: ${booking.checkin}\\nCheck-out: ${booking.checkout}\\nOaspeti: ${booking.persoane}\\nTelefon: ${booking.telefon}\\nEmail: ${booking.email}`);
    ical.push(`LOCATION:${booking.cazare}`);
    ical.push('STATUS:CONFIRMED');
    ical.push('TRANSP:OPAQUE');
    ical.push('END:VEVENT');
  });

  ical.push('END:VCALENDAR');

  return ical.join('\r\n');
}

function formatDateForICal(date) {
  const year = date.getFullYear();
  const month = String(date.getMonth() + 1).padStart(2, '0');
  const day = String(date.getDate()).padStart(2, '0');
  return `${year}${month}${day}`;
}
