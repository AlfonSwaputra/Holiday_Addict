const puppeteer = require('puppeteer');
const fs = require('fs');
const path = require('path');

class InstagramScraper {
    constructor() {
        // Konfigurasi path log
        this.logFile = path.join(__dirname, 'instagram_scraper.log');
        
        // Konfigurasi batasan
        this.MAX_POSTS = 50;
        this.TIMEOUT = 30000; // 30 detik
    }

    // Metode logging yang komprehensif
    _log(message, type = 'info') {
        const timestamp = new Date().toISOString();
        const logMessage = `[${timestamp}] [${type.toUpperCase()}] ${message}\n`;
        
        try {
            fs.appendFileSync(this.logFile, logMessage);
            console.log(logMessage); // Logging ke console juga
        } catch (error) {
            console.error('Logging error:', error);
        }
    }

    // Validasi input hashtag
    _validateHashtag(hashtag) {
        if (!hashtag || typeof hashtag !== 'string') {
            throw new Error('Invalid hashtag');
        }
        return hashtag.replace('#', ''); // Hapus # jika ada
    }

    async scrapeInstagramPosts(hashtag) {
        try {
            // Validasi hashtag
            const cleanHashtag = this._validateHashtag(hashtag);
            this._log(`Memulai scraping untuk hashtag: ${cleanHashtag}`);

            // Inisialisasi browser
            const browser = await puppeteer.launch({
                headless: true,
                args: ['--no-sandbox', '--disable-setuid-sandbox']
            });

            const page = await browser.newPage();

            // Konfigurasi user agent untuk menghindari blokir
            await page.setUserAgent('Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36');

            // Navigasi ke halaman hashtag Instagram
            const url = `https://www.instagram.com/explore/tags/${cleanHashtag}/`;
            await page.goto(url, { 
                waitUntil: 'networkidle2', 
                timeout: this.TIMEOUT 
            });

            // Ekstraksi posts
            const posts = await page.evaluate((maxPosts) => {
                const postElements = document.querySelectorAll('article');
                const extractedPosts = [];

                for (let i = 0; i < Math.min(postElements.length, maxPosts); i++) {
                    const postElement = postElements[i];
                    
                    // Ekstraksi informasi post
                    const imageUrl = postElement.querySelector('img')?.src || '';
                    const caption = postElement.querySelector('div[role="button"] span')?.innerText || '';
                    
                    if (imageUrl && caption) {
                        extractedPosts.push({
                            imageUrl,
                            caption,
                            hashtag: window.location.pathname.split('/')[3],
                            timestamp: new Date().toISOString()
                        });
                    }
                }

                return extractedPosts;
            }, this.MAX_POSTS);

            // Tutup browser
            await browser.close();

            // Log hasil scraping
            this._log(`Berhasil mengekstrak ${posts.length} posts`);

            return posts;

        } catch (error) {
            // Tangani berbagai jenis error
            this._log(`Scraping Error: ${error.message}`, 'error');
            
            if (error.name === 'TimeoutError') {
                this._log('Timeout saat mengakses Instagram', 'error');
            }

            return [];
        }
    }

    // Metode untuk membaca log
    getScraperLogs(limit = 100) {
        try {
            const logs = fs.readFileSync(this.logFile, 'utf8');
            return logs.split('\n').slice(-limit);
        } catch (error) {
            this._log(`Error membaca file log: ${error.message}`, 'error');
            return [];
        }
    }
}

// Ekspor instance
module.exports = new InstagramScraper();

// Contoh penggunaan langsung
async function main() {
    const hashtags = ['wisatapekanbaru', 'pekanbarutravel', 'riautrip'];
    
    for (const hashtag of hashtags) {
        try {
            const posts = await new InstagramScraper().scrapeInstagramPosts(hashtag);
            console.log(`Posts for #${hashtag}:`, posts);
        } catch (error) {
            console.error(`Error scraping ${hashtag}:`, error);
        }
    }
}

// Jalankan main function jika file dieksekusi langsung
if (require.main === module) {
    main();
}
