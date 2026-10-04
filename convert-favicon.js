const fs = require('fs');
const path = require('path');
const Jimp = require('jimp');
const toIco = require('to-ico');

const inputPath = path.join(__dirname, 'public/assets/images/pili_logo.png');
const outputPath = path.join(__dirname, 'public/favicon.ico');

Jimp.read(inputPath)
  .then(image => {
    return new Promise((resolve, reject) => {
      image.resize(64, 64).getBuffer(Jimp.MIME_PNG, (err, buf) => {
        if (err) reject(err);
        else resolve(buf);
      });
    });
  })
  .then(pngBuffer => toIco([pngBuffer]))
  .then(buf => {
    fs.writeFileSync(outputPath, buf);
    console.log('Favicon converted successfully!');
  })
  .catch(error => {
    console.error('Error converting favicon:', error);
  });