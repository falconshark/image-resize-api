/* This script is for testing upload function without broswer. */
const fs = require('fs');
const fetch = require('node-fetch');
const settingFile = fs.readFileSync('./setting.json');
const setting = JSON.parse(settingFile);
fetch(setting.serverUrl, {
  body:JSON.stringify({
    width: 500,
    height: 300,
    imageUrl: setting.imageUrl,
  }),
  headers: {
    'content-type': 'application/json',
  },
  method: 'POST',
})
.then(response => response.json())
.then((result) =>{
  console.log(result);
});
