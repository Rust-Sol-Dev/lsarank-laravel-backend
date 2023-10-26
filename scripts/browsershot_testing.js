const puppeteer = require("puppeteer-extra");
const puppeteerAdblock = require("puppeteer-extra-plugin-adblocker");
const puppeteerStealth = require("puppeteer-extra-plugin-stealth");
const axios = require("axios");

const config = {
    headers:{
        'authority': 'www.google.com',
        'accept': 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9',
        'accept-language': 'en-US,en;q=0.9',
        'referer': 'https://www.google.com/',
        'sec-ch-ua': '"Not_A Brand";v="99", "Google Chrome";v="109", "Chromium";v="109"',
        'sec-ch-ua-platform': 'Linux',
        'sec-ch-ua-mobile': '?0',
        'user-agent': 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36',
    }
};

axios.get('https://www.google.com/localservices/prolist?g2lbs=ALIxsqrCY-bqChlN5YveRZ-KaN51hQCx6OJfwKZXszYqmS06kWUkKqpgaVtPMJgf9i9N5jicjqfR_pQFAadjYuZ4TEgIa-mHhQ%3D%3D&hl=en-US&gl=us&ssta=1&src=1&gsas=1&scp=CiV4Y2F0OnNlcnZpY2VfYXJlYV9idXNpbmVzc19odmFjOmVuLVVTEiEaEgmXmemvXvfAhxGiUapq5iWFVSILS2Fuc2FzIENpdHkqBEh2YWM%3D&slp=IhBodmFjX21haW50ZW5hbmNlMhYKFBoSChBodmFjX21haW50ZW5hbmNlOq8BQ2hNSTllZmJzdDZjX1FJVk1JT0RCeDJZTEFwWUVod0lCQkFCR2d3SXBiNzUtZ01Rd0lLWjV3a2dzNmZTRWpqWms2a0pFaHNJQkJBQ0dnc0lvX3FUZlJDS3Y4U3NDU0RfeHQwYk9MX2o3ZzBTSEFnRUVBTWFEQWpxanFXSUFSRDV2SS11Q1NDZ3piY2ZPTkRtMnc4WXdKXzR2TzhRR1BxUzVfTFRFQmlOM09TWXNoQVoQSFZBQyBtYWludGVuYW5jZQ%3D%3D&q=hvac+near+kansas+city+mo&sa=X&ved=2ahUKEwiBz9Ky3pz9AhWpi_0HHQrxAJYQl5UCegQICRAj', config)
    .then(({ data }) => console.log(data));

// const script = `./browser_testing.js`;
// const browser = require(script);
//
// puppeteer.use(puppeteerAdblock({blockTrackers: true}));
// puppeteer.use(puppeteerStealth());
// browser.callChrome(puppeteer);
