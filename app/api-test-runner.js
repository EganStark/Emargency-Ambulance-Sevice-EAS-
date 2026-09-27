const {spawn,spawnSync}=require('node:child_process');
const fs=require('node:fs');
const path=require('node:path');

const target=process.argv[2];
if(!['smoke-test.js','workflow-test.js'].includes(target)){console.error('Unknown API test');process.exit(2)}
const testName=path.basename(target,'.js'),port=testName==='smoke-test'?'3101':'3102';
const runtime=path.join(__dirname,'test-results',`api-${testName}`);
if(fs.existsSync(runtime))fs.rmSync(runtime,{recursive:true,force:true});
fs.mkdirSync(runtime,{recursive:true});
const env={...process.env,PORT:port,DB_PATH:path.join(runtime,'test.sqlite'),UPLOAD_PATH:path.join(runtime,'uploads'),ADMIN_EMAIL:'admin@rapidcare.test',ADMIN_PASSWORD:'local-demo-password-123'};
const server=spawn(process.execPath,['server.js'],{cwd:__dirname,env,stdio:['ignore','pipe','inherit']});
server.stdout.pipe(process.stdout);
async function waitForServer(){for(let i=0;i<50;i++){try{const response=await fetch(`http://127.0.0.1:${port}/api/me`);if(response.ok)return}catch{}await new Promise(resolve=>setTimeout(resolve,100))}throw Error('Test server did not start')}
(async()=>{try{await waitForServer();const seed=spawnSync(process.execPath,['seed.js'],{cwd:__dirname,env,stdio:'inherit'});if(seed.status)process.exitCode=seed.status;else{const result=spawnSync(process.execPath,[target],{cwd:__dirname,env:{...env,TEST_URL:`http://127.0.0.1:${port}`},stdio:'inherit'});process.exitCode=result.status??1}}catch(error){console.error(error);process.exitCode=1}finally{server.kill()}})();
