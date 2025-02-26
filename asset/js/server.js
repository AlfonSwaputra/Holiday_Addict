const express = require('express');
const fetchInstagramPosts = require('./instagramScraper');
const fetchTikTokVideos = require('./tiktokScraper'); // Pastikan ini benar
require('dotenv').config();

const app = express();
const PORT = process.env.PORT || 5000;

app.get('/instagram/:hashtag', async (req, res) => {
    try {
        const data = await fetchInstagramPosts(req.params.hashtag);
        res.json(data);
    } catch (error) {
        res.status(500).json({ error: error.message });
    }
});

app.get('/tiktok/:hashtag', async (req, res) => {
    try {
        const data = await fetchTikTokVideos(req.params.hashtag); // Pastikan ini memanggil fungsi yang benar
        res.json(data);
    } catch (error) {
        res.status(500).json({ error: error.message });
    }
});

app.listen(PORT, () => {
    console.log(`Server berjalan di http://localhost:${PORT}`);
});
