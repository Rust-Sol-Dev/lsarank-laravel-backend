const puppeteer = require("puppeteer-extra");

let args = process.argv;
let mapUrl = args[2];
let filePath = args[3];

const getOutput = async (output) => {
    return output.toString('base64');
};

const launch = async (puppet, mapUrl, filePath) => {
    let browser = await puppet.launch({
        ignoreHTTPSErrors: true,
        headless: true,
        args:[
            '--start-maximized'
        ]
    });

    try {
        let mapSelector = "#map > div";
        let page = await browser.newPage();
        await page.setViewport({ width: 1280, height: 1230});
        // Inject the window object into workers
        await page.setJavaScriptEnabled(true);
        await page.goto(mapUrl);
        await page.waitForSelector(mapSelector);
        await page.waitForTimeout(2000);
        const element = await page.$(mapSelector);
        await element.screenshot({ path: filePath})
        await getOutput('Done')
        await browser.close();
    } catch (e) {
        console.log(e);
    }

    return true;
};

launch(puppeteer, mapUrl, filePath);
