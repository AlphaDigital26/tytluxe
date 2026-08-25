const puppeteer = require('puppeteer');

(async () => {
    const browser = await puppeteer.launch({ headless: true, args: ['--no-sandbox'] });
    const page = await browser.newPage();
    await page.setViewport({ width: 900, height: 1200 });

    await page.goto(`file:///C:/Users/acer/Desktop/tytluxe/public/test_a4_itinerary.pdf`, { waitUntil: 'networkidle0', timeout: 15000 });
    await new Promise(r => setTimeout(r, 2000));
    await page.screenshot({ path: 'C:/Users/acer/Desktop/tytluxe/scratch_new_pdf_page1.png', fullPage: false });

    await browser.close();
    console.log('Done');
})();
