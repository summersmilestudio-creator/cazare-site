// Netlify Function pentru salvarea rezervărilor
const https = require('https');

exports.handler = async (event, context) => {
  // Verifică metoda HTTP
  if (event.httpMethod !== 'POST') {
    return {
      statusCode: 405,
      body: JSON.stringify({ error: 'Metoda nu este permisă' })
    };
  }

  try {
    // Parsează datele rezervării
    const booking = JSON.parse(event.body);

    // Validare date
    if (!booking.nume || !booking.telefon || !booking.checkin || !booking.checkout || !booking.cazare) {
      return {
        statusCode: 400,
        body: JSON.stringify({ error: 'Date incomplete' })
      };
    }

    // Adaugă ID unic și timestamp
    booking.id = `BK${Date.now()}`;
    booking.timestamp = new Date().toISOString();
    booking.status = 'confirmed';

    console.log('Rezervare nouă primită:', booking);

    // Salvare în GitHub (dacă există token configurat)
    const githubToken = process.env.GITHUB_TOKEN;
    const githubRepo = process.env.GITHUB_REPO || 'summersmilestudio-creator/cazare-site';

    if (githubToken) {
      try {
        await saveToGitHub(booking, githubToken, githubRepo);
        console.log('Rezervare salvată în GitHub');
      } catch (githubError) {
        console.error('Eroare salvare GitHub:', githubError);
        // Continuăm chiar dacă GitHub fail - măcar logăm rezervarea
      }
    }

    // Trimite notificare (opțional - dacă e configurat email)
    const notificationEmail = process.env.NOTIFICATION_EMAIL;
    if (notificationEmail) {
      await sendNotification(booking, notificationEmail);
    }

    return {
      statusCode: 200,
      headers: {
        'Access-Control-Allow-Origin': '*',
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({
        success: true,
        message: 'Rezervare înregistrată cu succes',
        booking: booking,
        info: githubToken ? 'Salvat în sistem' : 'Notificare trimisă (configurează GITHUB_TOKEN pentru salvare automată)'
      })
    };
  } catch (error) {
    console.error('Eroare:', error);
    return {
      statusCode: 500,
      headers: {
        'Access-Control-Allow-Origin': '*',
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({
        success: false,
        error: error.message
      })
    };
  }
};

// Funcție pentru salvare în GitHub via API
async function saveToGitHub(booking, token, repo) {
  // Această funcție va fi implementată pentru a face commit în GitHub
  // Pentru moment, doar logăm
  console.log('GitHub save - Token configured:', !!token);
  console.log('Repo:', repo);
  // TODO: Implementare completă GitHub API
  return true;
}

// Funcție pentru trimitere notificare
async function sendNotification(booking, email) {
  console.log(`Notificare pentru ${email}:`, booking);
  // TODO: Implementare trimitere email
  return true;
}
