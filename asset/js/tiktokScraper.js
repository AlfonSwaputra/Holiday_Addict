const { ApifyClient } = require('apify-client');
require('dotenv').config();

const client = new ApifyClient({
    token: process.env.APIFY_API_KEY
});

async function fetchTikTokVideos(hashtag) {
    console.log(`Mengambil video TikTok untuk hashtag: ${hashtag}`);
    try {
        const run = await client.actor("zuzka/tiktok-scraper").call({
            hashtag: hashtag,
            maxPostCount: 15,
            maxItems: 15,
            scrapeType: "hashtag",
            searchType: "hashtag"
        });
        
        const { defaultDatasetId } = run;
        const datasetItems = await client.dataset(defaultDatasetId).listItems();
        return datasetItems.items;
    } catch (error) {
        console.error('Error mengambil video TikTok:', error);
        return [];
    }
}

module.exports = fetchTikTokVideos;
