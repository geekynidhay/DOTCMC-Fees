const { Client, LocalAuth, MessageMedia } = require('whatsapp-web.js');
const qrcode = require('qrcode-terminal');
const axios = require('axios');

const os = require('os');

// Auto-detect environment based on Operating System
// Windows (win32) = Live Server | Mac (darwin) = Dev Server
let API_URL;
if (os.platform() === 'win32') {
    API_URL = "https://dotcmc.ct.ws/api_whatsapp.php";
    console.log("🖥️ Windows PC Detected -> Running on LIVE SERVER");
} else {
    API_URL = "https://dev-dotcmc.ct.ws/api_whatsapp.php";
    console.log("🍎 Mac Detected -> Running on DEV SERVER");
}
const SECRET_KEY = "dotcmc_whatsapp_secret_2026";

console.log("Starting DOT-CMC WhatsApp Bot...");

const client = new Client({
    authStrategy: new LocalAuth(),
    puppeteer: {
        headless: true,
        args: ['--no-sandbox', '--disable-setuid-sandbox']
    }
});

client.on('qr', (qr) => {
    console.log('\n--- SCAN THIS QR CODE WITH YOUR WHATSAPP ---\n');
    qrcode.generate(qr, { small: true });
});

let pollingInterval = null;
let isPolling = false;

client.on('ready', () => {
    console.log('✅ WhatsApp Bot is Ready and Linked!');
    
    // Start polling the server every 10 seconds (Only if not already started)
    if (!pollingInterval) {
        pollingInterval = setInterval(async () => {
            if (isPolling) return; // Skip if already processing
            isPolling = true;
            try {
                await checkPendingMessages();
            } finally {
                isPolling = false;
            }
        }, 10000);
        console.log('🔄 Checking for new receipts every 10 seconds...');
    }
});

client.on('auth_failure', msg => {
    console.error('❌ Authentication failure', msg);
});

async function apiFetch(url, method = 'GET', data = null) {
    let page;
    try {
        const browser = client.pupBrowser;
        if (!browser) throw new Error("Browser not ready");
        
        page = await browser.newPage();
        await page.setUserAgent("Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36");
        
        if (method === 'POST') {
            await page.goto(API_URL.replace('api_whatsapp.php', ''), { waitUntil: 'domcontentloaded' });
            // Wait for the anti-bot cookie to be set if it's a new session
            await new Promise(resolve => setTimeout(resolve, 3000));
            const result = await page.evaluate(async (fetchUrl, payload) => {
                const res = await fetch(fetchUrl, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });
                return await res.json();
            }, url, data);
            return result;
        } else {
            await page.goto(url, { waitUntil: 'domcontentloaded', timeout: 30000 });
            
            try {
                // Wait until the page text looks like our JSON (bypassing the anti-bot HTML page)
                await page.waitForFunction(() => {
                    return document.body && document.body.innerText.trim().startsWith('{');
                }, { timeout: 15000 });
            } catch (waitError) {
                // If it times out, let's see what the page actually says!
                const debugText = await page.evaluate(() => document.body.innerText.trim().substring(0, 100));
                console.error("⚠️ Timeout waiting for JSON. Page actually says:", debugText);
                throw waitError;
            }
            
            const content = await page.evaluate(() => document.body.innerText.trim());
            return JSON.parse(content);
        }
    } catch (error) {
        throw error;
    } finally {
        if (page) await page.close();
    }
}

async function checkPendingMessages() {
    try {
        const result = await apiFetch(`${API_URL}?key=${SECRET_KEY}&action=get_pending`);

        if (result && result.status === 'success' && result.data && result.data.length > 0) {
            console.log(`\n📬 Found ${result.data.length} pending message(s)`);
            const sentIds = [];

            for (let row of result.data) {
                // Ensure phone number starts with 91 and append @c.us
                let phone = row.phone.replace(/[^0-9]/g, '');
                if (phone.length === 10) {
                    phone = "91" + phone;
                }
                const chatId = phone + "@c.us";
                const messageText = row.message;

                try {
                    console.log(`Sending to ${phone}...`);
                    
                    if (row.media_url1) {
                        try {
                            const media1 = await MessageMedia.fromUrl(row.media_url1);
                            await client.sendMessage(chatId, media1);
                            console.log(`✅ Sent media 1 to ${phone}`);
                            await new Promise(resolve => setTimeout(resolve, 1500));
                        } catch (e) { console.error("Error sending media 1:", e.message); }
                    }
                    
                    if (row.media_url2) {
                        try {
                            const media2 = await MessageMedia.fromUrl(row.media_url2);
                            await client.sendMessage(chatId, media2);
                            console.log(`✅ Sent media 2 to ${phone}`);
                            await new Promise(resolve => setTimeout(resolve, 1500));
                        } catch (e) { console.error("Error sending media 2:", e.message); }
                    }

                    await client.sendMessage(chatId, messageText);
                    console.log(`✅ Sent text successfully to ${phone}`);
                    sentIds.push(row.id);
                    
                    await new Promise(resolve => setTimeout(resolve, 2000));
                } catch (sendError) {
                    console.error(`❌ Failed to send to ${phone}:`, sendError.message);
                }
            }

            if (sentIds.length > 0) {
                await markMessagesAsSent(sentIds);
            }
        }
    } catch (error) {
        console.error("⚠️ Error checking server:", error.message);
    }
}

async function markMessagesAsSent(ids) {
    try {
        const result = await apiFetch(`${API_URL}?key=${SECRET_KEY}&action=mark_sent`, 'POST', { ids: ids });
        if (result && result.status === 'success') {
            console.log(`✅ Marked ${ids.length} messages as sent on the server.`);
        } else {
            console.error(`❌ Failed to mark as sent:`, result ? result.message : 'Unknown error');
        }
    } catch (error) {
        console.error("⚠️ Error calling mark_sent API:", error.message);
    }
}

client.initialize();
