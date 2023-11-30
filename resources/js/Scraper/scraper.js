import puppeteer from 'puppeteer';
import extractHostingId from './utils/extractHostingId.js';

async function scrapeImages(url) {
    const browser = await puppeteer.launch({ headless: "new" });
    const page = await browser.newPage();
    const hostingId = extractHostingId(url);

    await page.goto(url + '?modal=PHOTO_TOUR_SCROLLABLE', { waitUntil: 'networkidle2' });

    // Get the html content of this element: <div role="dialog" aria-label="Fotorundgang" aria-modal="true" class="dvbgto d1rvbcd7 dir dir-ltr">
    const html = await page.$eval('div[role="dialog"][aria-label="Fotorundgang"]', e => e.innerHTML);

    // find all images where the path includes Hosting-{{hostingId}}
    // const imageUrls = await page.$$eval(
    //     'img',
    //     (imgs, id) => imgs
    //         .filter(img => img.src.includes('Hosting-' + id))
    //         .map(img => img.src),
    //     hostingId
    // );

    await browser.close();
    return html;
}

// URL der Airbnb-Unterkunft
scrapeImages('https://www.airbnb.de/rooms/1022870940231653444')
    .then(urls => console.log(urls));
