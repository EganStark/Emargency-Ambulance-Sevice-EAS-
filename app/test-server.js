const fs=require('node:fs');
const path=require('node:path');
const runtime=path.join(__dirname,'test-results','runtime');
if(fs.existsSync(runtime))fs.rmSync(runtime,{recursive:true,force:true});
fs.mkdirSync(runtime,{recursive:true});
process.env.PORT=process.env.TEST_PORT||'3100';
process.env.DB_PATH=path.join(runtime,'test.sqlite');
process.env.UPLOAD_PATH=path.join(runtime,'uploads');
require('./server');
