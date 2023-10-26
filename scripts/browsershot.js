const puppeteer = require("puppeteer-extra");
const puppeteerAdblock = require("puppeteer-extra-plugin-adblocker");
const puppeteerStealth = require("puppeteer-extra-plugin-stealth");

const script = `./browser.js`;
const browser = require(script);

puppeteer.use(puppeteerAdblock({blockTrackers: true}));
puppeteer.use(puppeteerStealth());
browser.callChrome(puppeteer);
