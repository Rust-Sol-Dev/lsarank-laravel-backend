const fs = require('fs');
const URL = require('url').URL;
const URLParse = require('url').parse;
///////
const { FingerprintGenerator } = require('fingerprint-generator');
const { FingerprintInjector }  = require('fingerprint-injector');
const { HeaderGenerator } = require('header-generator');
/////////

const [, , ...args] = process.argv;

/**
 * There are two ways for Browsershot to communicate with puppeteer:
 * - By giving a options JSON dump as an argument
 * - Or by providing a temporary file with the options JSON dump,
 *   the path to this file is then given as an argument with the flag -f
 */
const request = args[0].startsWith('-f ')
    ? JSON.parse(fs.readFileSync(new URL(args[0].substring(3))))
    : JSON.parse(args[0]);

const requestsList = [];

const consoleMessages = [];

const failedRequests = [];

const getOutput = async (page, request) => {
    let output;

    if (request.action == 'requestsList') {
        output = JSON.stringify(requestsList);

        return output;
    }

    if (request.action == 'consoleMessages') {
        output = JSON.stringify(consoleMessages);

        return output;
    }

    if (request.action == 'failedRequests') {
        output = JSON.stringify(failedRequests);

        return output;
    }

    if (request.action == 'evaluate') {
        output = await page.evaluate(request.options.pageFunction);

        return output;
    }

    output = await page[request.action](request.options);

    return output.toString('base64');
};

const callChrome = async pup => {
    let browser;
    let page;
    let output;
    let remoteInstance;
    const puppet = (pup || require('puppeteer'));

    try {
        if (request.options.remoteInstanceUrl || request.options.browserWSEndpoint ) {
            // default options
            let options = {
                ignoreHTTPSErrors: request.options.ignoreHttpsErrors
            };

            // choose only one method to connect to the browser instance
            if ( request.options.remoteInstanceUrl ) {
                options.browserURL = request.options.remoteInstanceUrl;
            } else if ( request.options.browserWSEndpoint ) {
                options.browserWSEndpoint = request.options.browserWSEndpoint;
            }

            try {
                browser = await puppet.connect( options );

                remoteInstance = true;
            } catch (exception) { /** does nothing. fallbacks to launching a chromium instance */}
        }


        const fingerprintGenerator = new FingerprintGenerator({
            browsers: [
                {name: "chrome", minVersion: 101},
            ],
            devices: ['desktop'],
            operatingSystems: ['linux'],
            locales: ["en-US", "en"]
        });

        let fingerPrintObject = {};

        while (true) {
            let { fingerprint, headers } = fingerprintGenerator.getFingerprint();

            if (headers['sec-ch-ua']) {
                fingerPrintObject['fingerprint'] = fingerprint;
                fingerPrintObject['headers'] = headers;

                break;
            }
        }

        if (!fingerPrintObject.headers['sec-ch-ua-mobile']) {
            fingerPrintObject.headers['sec-ch-ua-mobile'] = '?0';
        }

        if (!fingerPrintObject.headers['sec-ch-ua-platform']) {
            fingerPrintObject.headers['sec-ch-ua-platform'] = '"Linux"';
        }

        if (!browser) {
            let userAgent = fingerPrintObject.fingerprint.navigator.userAgent;

            let uaFormatted = `--user-agent=` + userAgent;

            request.options.args.push(uaFormatted);
            request.options.viewport.height = fingerPrintObject.fingerprint.screen.height;
            request.options.viewport.width = fingerPrintObject.fingerprint.screen.width;

            browser = await puppet.launch({
                ignoreHTTPSErrors: request.options.ignoreHttpsErrors,
                headless: true,
                executablePath: request.options.executablePath,
                args: request.options.args || [],
                pipe: request.options.pipe || false,
                env: {
                    ...(request.options.env || {}),
                    ...process.env
                },
            });
        }

        page = await browser.newPage();

        const fingerprintInjector = new FingerprintInjector();
        await fingerprintInjector.attachFingerprintToPuppeteer(page, fingerPrintObject);

        await page.evaluateOnNewDocument(() => {
            //store the existing descriptor
            const elementDescriptor = Object.getOwnPropertyDescriptor(
                HTMLElement.prototype,
                "offsetHeight"
            );

            // redefine the property with a patched descriptor
            Object.defineProperty(HTMLDivElement.prototype, "offsetHeight", {
                ...elementDescriptor,
                get: function() {
                    if (this.id === "modernizr") {
                        return 1;
                    }
                    // @ts-ignore
                    return elementDescriptor.get.apply(this);
                },
            });

            Object.defineProperty(window, 'RTCPeerConnection', {
                get: () => {
                    return {};
                },
            });
            Object.defineProperty(window, 'RTCDataChannel', {
                get: () => {
                    return {};
                },
            });
        });

        if (request.options && request.options.disableJavascript) {
            await page.setJavaScriptEnabled(false);
        }

        await page.setRequestInterception(true);

        const contentUrl = request.options.contentUrl;
        const parsedContentUrl = contentUrl ? contentUrl.replace(/\/$/, "") : undefined;
        let pageContent;

        if (contentUrl) {
            pageContent = fs.readFileSync(request.url.replace('file://', ''));
            request.url = contentUrl;
        }

        page.on('console',  message => consoleMessages.push({
            type: message.type(),
            message: message.text(),
            location: message.location()
        }));

        page.on('response', function (response) {
            if (response.status() >= 200 && response.status() <= 399) {
                return;
            }

            failedRequests.push({
                status: response.status(),
                url: response.url(),
            });
        })

        page.on('request', interceptedRequest => {
            var headers = interceptedRequest.headers();

            requestsList.push({
                url: interceptedRequest.url(),
            });

            if (request.options && request.options.disableImages) {
                if (interceptedRequest.resourceType() === 'image') {
                    interceptedRequest.abort();
                    return;
                }
            }

            if (request.options && request.options.blockDomains) {
                const hostname = URLParse(interceptedRequest.url()).hostname;
                if (request.options.blockDomains.includes(hostname)) {
                    interceptedRequest.abort();
                    return;
                }
            }

            if (request.options && request.options.blockUrls) {
                for (const element of request.options.blockUrls) {
                    if (interceptedRequest.url().indexOf(element) >= 0) {
                        interceptedRequest.abort();
                        return;
                    }
                }
            }

            if (request.options && request.options.extraNavigationHTTPHeaders) {
                // Do nothing in case of non-navigation requests.
                if (interceptedRequest.isNavigationRequest()) {
                    headers = Object.assign({}, headers, request.options.extraNavigationHTTPHeaders);
                }
            }

            if (pageContent) {
                const interceptedUrl = interceptedRequest.url().replace(/\/$/, "");

                // if content url matches the intercepted request url, will return the content fetched from the local file system
                if (interceptedUrl === parsedContentUrl) {
                    interceptedRequest.respond({
                        headers,
                        body: pageContent,
                    });
                    return;
                }
            }

            if (request.postParams) {
                const postParamsArray = request.postParams;
                const queryString = Object.keys(postParamsArray)
                    .map(key => `${key}=${postParamsArray[key]}`)
                    .join('&');
                interceptedRequest.continue({
                    method: "POST",
                    postData: queryString,
                    headers: {
                        ...interceptedRequest.headers(),
                        "Content-Type": "application/x-www-form-urlencoded"
                    }
                });
                return;
            }

            interceptedRequest.continue({ headers });
        });

        if (request.options && request.options.dismissDialogs) {
            page.on('dialog', async dialog => {
                await dialog.dismiss();
            });
        }

        if (request.options && request.options.userAgent) {
            await page.setUserAgent(request.options.userAgent);
        }

        if (request.options && request.options.device) {
            const devices = puppet.devices;
            const device = devices[request.options.device];
            await page.emulate(device);
        }

        if (request.options && request.options.emulateMedia) {
            await page.emulateMediaType(request.options.emulateMedia);
        }

        if (request.options && request.options.viewport) {
            await page.setViewport(request.options.viewport);
        }

        if (request.options && request.options.extraHTTPHeaders) {
            await page.setExtraHTTPHeaders(request.options.extraHTTPHeaders);
        }

        if (request.options && request.options.authentication) {
            await page.authenticate(request.options.authentication);
        }

        if (request.options && request.options.cookies) {
            await page.setCookie(...request.options.cookies);
        }

        if (request.options && request.options.timeout) {
            await page.setDefaultNavigationTimeout(request.options.timeout);
        }

        const requestOptions = {};

        if (request.options && request.options.networkIdleTimeout) {
            requestOptions.waitUntil = 'networkidle';
            requestOptions.networkIdleTimeout = request.options.networkIdleTimeout;
        } else if (request.options && request.options.waitUntil) {
            requestOptions.waitUntil = request.options.waitUntil;
        }

        await page.setExtraHTTPHeaders({
            'accept': fingerPrintObject.headers['accept'],
            'accept-language': fingerPrintObject.headers['accept-language'],
            'cache-control': 'no-cache',
            'pragma': 'no-cache',
            'sec-ch-ua': fingerPrintObject.headers['sec-ch-ua'],
            'sec-ch-ua-mobile': fingerPrintObject.headers['sec-ch-ua-mobile'],
            'sec-ch-ua-platform': fingerPrintObject.headers['sec-ch-ua-platform'],
            'sec-fetch-dest': fingerPrintObject.headers['sec-fetch-dest'],
            'sec-fetch-mode': fingerPrintObject.headers['sec-fetch-mode'],
            'sec-fetch-site': fingerPrintObject.headers['sec-fetch-site'],
            'sec-fetch-user': fingerPrintObject.headers['sec-fetch-user'],
            'upgrade-insecure-requests': fingerPrintObject.headers['upgrade-insecure-requests'],
            'user-agent': fingerPrintObject.headers['user-agent']
        });

        const response = await page.goto(request.url, {"waitUntil" : "networkidle2"});

        if (request.options.preventUnsuccessfulResponse) {
            const status = response.status()

            if (status >= 400 && status < 600) {
                throw {type: "UnsuccessfulResponse", status};
            }
        }

        if (request.options && request.options.disableImages) {
            await page.evaluate(() => {
                let images = document.getElementsByTagName('img');
                while (images.length > 0) {
                    images[0].parentNode.removeChild(images[0]);
                }
            });
        }

        if (request.options && request.options.types) {
            for (let i = 0, len = request.options.types.length; i < len; i++) {
                let typeOptions = request.options.types[i];
                await page.type(typeOptions.selector, typeOptions.text, {
                    'delay': typeOptions.delay,
                });
            }
        }

        if (request.options && request.options.selects) {
            for (let i = 0, len = request.options.selects.length; i < len; i++) {
                let selectOptions = request.options.selects[i];
                await page.select(selectOptions.selector, selectOptions.value);
            }
        }

        if (request.options && request.options.clicks) {
            for (let i = 0, len = request.options.clicks.length; i < len; i++) {
                let clickOptions = request.options.clicks[i];
                await page.click(clickOptions.selector, {
                    'button': clickOptions.button,
                    'clickCount': clickOptions.clickCount,
                    'delay': clickOptions.delay,
                });
            }
        }

        if (request.options && request.options.addStyleTag) {
            await page.addStyleTag(JSON.parse(request.options.addStyleTag));
        }

        if (request.options && request.options.addScriptTag) {
            await page.addScriptTag(JSON.parse(request.options.addScriptTag));
        }

        if (request.options.delay) {
            await page.waitForTimeout(request.options.delay);
        }

        if (request.options.initialPageNumber) {
            await page.evaluate((initialPageNumber) => {
                window.pageStart = initialPageNumber;

                const style = document.createElement('style');
                style.type = 'text/css';
                style.innerHTML = '.empty-page { page-break-after: always; visibility: hidden; }';
                document.getElementsByTagName('head')[0].appendChild(style);

                const emptyPages = Array.from({length: window.pageStart}).map(() => {
                    const emptyPage = document.createElement('div');
                    emptyPage.className = "empty-page";
                    emptyPage.textContent = "empty";
                    return emptyPage;
                });
                document.body.prepend(...emptyPages);
            }, request.options.initialPageNumber);
        }

        if (request.options.selector) {
            var element;
            const index = request.options.selectorIndex || 0;
            if(index){
                element = await page.$$(request.options.selector);
                if(!element.length || typeof element[index] === 'undefined'){
                    element = null;
                }else{
                    element = element[index];
                }
            }else{
                element = await page.$(request.options.selector);
            }
            if (element === null) {
                throw {type: 'ElementNotFound'};
            }

            request.options.clip = await element.boundingBox();
        }

        if (request.options.function) {
            let functionOptions = {
                polling: request.options.functionPolling,
                timeout: request.options.functionTimeout || request.options.timeout
            };
            await page.waitForFunction(request.options.function, functionOptions);
        }

        const delay = ms => new Promise(resolve => setTimeout(resolve, ms))
        await delay(4000) /// waiting 1 second.

        try {
            await page.screenshot({                      // Screenshot the website using defined options
                path: "/var/www/html/lsacrawler/lsacrawler/scripts/tests/captcha2.png",                   // Save the screenshot in current directory
                fullPage: true                              // take a fullpage screenshot
            });
        } catch (e) {
            console.log('Error');
            console.log(e);
        }



        await page.close();                           // Close the website

        await browser.close();

        // output = await getOutput(page, request);
        //
        // if (!request.options.path) {
        //     console.log(output);
        // }
        //
        // if (remoteInstance && page) {
        //     await page.close();
        // }
        //
        // await remoteInstance ? browser.disconnect() : browser.close();
    } catch (exception) {
        console.error(exception);
        if (browser) {

            if (remoteInstance && page) {
                await page.close();
            }

            await remoteInstance ? browser.disconnect() : browser.close();
        }

        if (exception.type === 'UnsuccessfulResponse') {
            console.error(exception.status)

            process.exit(3);
        }

        console.error(exception);

        if (exception.type === 'ElementNotFound') {
            process.exit(2);
        }

        process.exit(1);
    }
};

if (require.main === module) {
    callChrome();
}

exports.callChrome = callChrome;
