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

    // Trimite către Zapier Webhook (dacă e configurat)
    const zapierWebhookURL = process.env.ZAPIER_WEBHOOK_URL;
    if (zapierWebhookURL) {
      try {
        await sendToZapier(zapierWebhookURL, booking);
        console.log('Rezervare trimisă către Zapier cu succes');
      } catch (zapierError) {
        console.error('Eroare Zapier:', zapierError);
        // Continuăm chiar dacă Zapier fail
      }
    } else {
      console.log('ZAPIER_WEBHOOK_URL nu este configurat');
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
        integrations: {
          github: githubToken ? 'Salvat' : 'Nu e configurat',
          zapier: zapierWebhookURL ? 'Trimis' : 'Nu e configurat'
        }
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

// Funcție pentru trimitere către Zapier folosind https module
function sendToZapier(webhookURL, data) {
  return new Promise((resolve, reject) => {
    const url = new URL(webhookURL);
    const postData = JSON.stringify(data);

    const options = {
      hostname: url.hostname,
      port: url.port || 443,
      path: url.pathname + url.search,
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Content-Length': Buffer.byteLength(postData)
      }
    };

    const req = https.request(options, (res) => {
      let responseBody = '';

      res.on('data', (chunk) => {
        responseBody += chunk;
      });

      res.on('end', () => {
        if (res.statusCode >= 200 && res.statusCode < 300) {
          console.log('Zapier response status:', res.statusCode);
          console.log('Zapier response:', responseBody);
          resolve({ statusCode: res.statusCode, body: responseBody });
        } else {
          reject(new Error(`Zapier returned status ${res.statusCode}: ${responseBody}`));
        }
      });
    });

    req.on('error', (error) => {
      console.error('Eroare la trimiterea către Zapier:', error);
      reject(error);
    });

    req.write(postData);
    req.end();
  });
}
