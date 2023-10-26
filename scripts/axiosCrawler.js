const axios = require("axios");
const { HttpProxyAgent, HttpsProxyAgent } = require('hpagent');
const { HeaderGenerator } = require('header-generator');

let args = process.argv;
let url = args[2];
let ip = args[3];
let port = args[4];
let username = args[5];
let password = args[6];

let proxyUrl = "http://" + username + ":" + password + "@" + ip + ":" + port;
let headerGenerator = new HeaderGenerator({
    browsers: [
        {name: "chrome", minVersion: 101},
        {name: "firefox", minVersion: 101},
    ],
    devices: [
        "desktop"
    ],
    operatingSystems: [
        "linux", "windows"
    ],
    locales: ["en-US", "en"]
});

let correctHeaders = {};

let referArray = [
    "https://www.bing.com/",
    "https://www.blogger.com/",
    "https://wordpress.org/",
    "https://istockphoto.com/",
    "https://cnn.com/",
];

while (true) {
    let headers = headerGenerator.getHeaders();

    if (headers['sec-ch-ua-platform']) {
        if (headers['sec-ch-ua'] && headers['sec-ch-ua-mobile']) {
            correctHeaders = headers;
            break;
        }
    } else {
        correctHeaders = headers;
        break;
    }
}

const agent = new HttpsProxyAgent({
    keepAlive: true,
    keepAliveMsecs: 7000,
    timeout: 7000,
    maxSockets: 256,
    maxFreeSockets: 256,
    proxy: proxyUrl
});

let refer = referArray[Math.floor(Math.random()*referArray.length)];

const config = {
    headers:{
        "accept": correctHeaders["accept"],
        "accept-language": correctHeaders["accept-language"],
        "accept-encoding": correctHeaders["accept-encoding"],
        "referer": refer,
        "sec-ch-ua": correctHeaders["sec-ch-ua"],
        "sec-ch-ua-mobile": correctHeaders["sec-ch-ua-mobile"],
        "sec-ch-ua-platform": correctHeaders["sec-ch-ua-platform"],
        "sec-fetch-dest": correctHeaders["sec-fetch-dest"],
        "sec-fetch-mode": correctHeaders["sec-fetch-mode"],
        "sec-fetch-site": correctHeaders["sec-fetch-site"],
        "sec-fetch-user": correctHeaders["sec-fetch-user"],
        "upgrade-insecure-requests": correctHeaders["upgrade-insecure-requests"],
        "user-agent": correctHeaders["user-agent"],
    },
    httpsAgent: agent,
    timeout: 7000,
    proxy: false
    // proxy: {
    //     protocol: 'http',
    //     host: ip,
    //     port: port,
    //     auth: {
    //         username: username,
    //         password: password
    //     }
    // }
};

axios.get(url, config)
    .then(({ data }) => console.log(data));
