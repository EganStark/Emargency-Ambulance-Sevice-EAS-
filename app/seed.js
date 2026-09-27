const {DatabaseSync}=require('node:sqlite');
const crypto=require('node:crypto');
const path=require('node:path');
const db=new DatabaseSync(path.resolve(process.env.DB_PATH||path.join(__dirname,'data','ambulance.sqlite')));
const email=process.env.ADMIN_EMAIL, password=process.env.ADMIN_PASSWORD;
if(!email || !password || password.length<10){console.error('Set ADMIN_EMAIL and ADMIN_PASSWORD (at least 10 characters)');process.exit(1);}
const salt=crypto.randomBytes(16).toString('hex');
const hash=`${salt}:${crypto.scryptSync(password,salt,64).toString('hex')}`;
db.prepare("INSERT INTO users(name,email,phone,password_hash,role) VALUES(?,?,?,?, 'admin') ON CONFLICT(email) DO UPDATE SET password_hash=excluded.password_hash,role='admin'").run('Administrator',email.toLowerCase(),'0000000000',hash);
console.log(`Admin ready: ${email}`);
