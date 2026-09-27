const express = require('express');
const { DatabaseSync } = require('node:sqlite');
const crypto = require('node:crypto');
const path = require('node:path');
const fs = require('node:fs');
const multer = require('multer');

const app = express();
const dbPath = path.resolve(process.env.DB_PATH || path.join(__dirname, 'data', 'ambulance.sqlite'));
const uploadPath = path.resolve(process.env.UPLOAD_PATH || path.join(__dirname, 'data', 'private-uploads'));
fs.mkdirSync(path.dirname(dbPath), { recursive: true });
fs.mkdirSync(uploadPath, { recursive: true });
const db = new DatabaseSync(dbPath);
db.exec('PRAGMA journal_mode=WAL; PRAGMA foreign_keys=ON;');
db.exec(`
 CREATE TABLE IF NOT EXISTS users (id INTEGER PRIMARY KEY, name TEXT NOT NULL, email TEXT NOT NULL UNIQUE, phone TEXT NOT NULL, password_hash TEXT NOT NULL, role TEXT NOT NULL CHECK(role IN ('requester','driver','admin')), created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP);
 CREATE TABLE IF NOT EXISTS vehicles (id INTEGER PRIMARY KEY, driver_id INTEGER NOT NULL UNIQUE REFERENCES users(id), plate TEXT NOT NULL UNIQUE, kind TEXT NOT NULL, location TEXT NOT NULL, equipment TEXT NOT NULL DEFAULT '', approved INTEGER NOT NULL DEFAULT 0, available INTEGER NOT NULL DEFAULT 0, updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP);
 CREATE TABLE IF NOT EXISTS requests (id INTEGER PRIMARY KEY, requester_id INTEGER NOT NULL REFERENCES users(id), vehicle_id INTEGER REFERENCES vehicles(id), pickup TEXT NOT NULL, destination TEXT NOT NULL, notes TEXT NOT NULL DEFAULT '', phone TEXT NOT NULL, status TEXT NOT NULL DEFAULT 'requested', created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP, updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP);
 CREATE TABLE IF NOT EXISTS events (id INTEGER PRIMARY KEY, request_id INTEGER NOT NULL REFERENCES requests(id), actor_id INTEGER NOT NULL REFERENCES users(id), status TEXT NOT NULL, created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP);
 CREATE TABLE IF NOT EXISTS messages (id INTEGER PRIMARY KEY, name TEXT NOT NULL, email TEXT NOT NULL, message TEXT NOT NULL, created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP);
 CREATE TABLE IF NOT EXISTS sessions (token_hash TEXT PRIMARY KEY, user_id INTEGER NOT NULL REFERENCES users(id), expires_at INTEGER NOT NULL);
 CREATE TABLE IF NOT EXISTS locations (vehicle_id INTEGER PRIMARY KEY REFERENCES vehicles(id), latitude REAL NOT NULL, longitude REAL NOT NULL, updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP);
 CREATE TABLE IF NOT EXISTS notification_reads (user_id INTEGER NOT NULL REFERENCES users(id), event_id INTEGER NOT NULL REFERENCES events(id), PRIMARY KEY(user_id,event_id));
 CREATE TABLE IF NOT EXISTS vehicle_details (vehicle_id INTEGER PRIMARY KEY REFERENCES vehicles(id), service_area TEXT NOT NULL DEFAULT '', crew TEXT NOT NULL DEFAULT '', capacity INTEGER NOT NULL DEFAULT 1, description TEXT NOT NULL DEFAULT '');
 CREATE TABLE IF NOT EXISTS driver_documents (id INTEGER PRIMARY KEY, driver_id INTEGER NOT NULL REFERENCES users(id), vehicle_id INTEGER NOT NULL REFERENCES vehicles(id), kind TEXT NOT NULL CHECK(kind IN ('driver_license','vehicle_registration')), original_name TEXT NOT NULL, stored_name TEXT NOT NULL UNIQUE, mime_type TEXT NOT NULL, size INTEGER NOT NULL, uploaded_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP, UNIQUE(driver_id,kind));
 CREATE TABLE IF NOT EXISTS password_resets (token_hash TEXT PRIMARY KEY, user_id INTEGER NOT NULL REFERENCES users(id), expires_at INTEGER NOT NULL, created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP);
`);
for(const sql of [
  "ALTER TABLE requests ADD COLUMN cancellation_reason TEXT NOT NULL DEFAULT ''",
  "ALTER TABLE events ADD COLUMN note TEXT NOT NULL DEFAULT ''"
]){try{db.exec(sql)}catch(error){if(!String(error.message).includes('duplicate column name'))throw error}}
const hashPassword = password => { const salt = crypto.randomBytes(16).toString('hex'); return `${salt}:${crypto.scryptSync(password, salt, 64).toString('hex')}`; };
const checkPassword = (password, stored) => { const [salt, hash] = stored.split(':'); return !!hash && crypto.timingSafeEqual(crypto.scryptSync(password, salt, 64), Buffer.from(hash, 'hex')); };
const fail = (res, code, message) => res.status(code).json({ error: message });
const clean = (v, max = 255) => typeof v === 'string' ? v.trim().slice(0, max) : '';
const getCookie = req => Object.fromEntries((req.headers.cookie || '').split(';').map(x => x.trim().split('='))).session;
const tokenHash = value => crypto.createHash('sha256').update(value).digest('hex');
const upload = multer({ storage: multer.memoryStorage(), limits: { fileSize: 5 * 1024 * 1024, files: 1 } });
const validFile = file => {
  const b=file.buffer;
  if(file.mimetype==='application/pdf')return b.length>4&&b.subarray(0,5).toString()==='%PDF-';
  if(file.mimetype==='image/png')return b.length>=8&&b.subarray(0,8).equals(Buffer.from([137,80,78,71,13,10,26,10]));
  if(file.mimetype==='image/jpeg')return b.length>3&&b[0]===255&&b[1]===216&&b[2]===255;
  return false;
};
const loginAttempts = new Map();
function loginLimit(req,res,next){
  const key=`${req.path}:${req.ip || req.socket.remoteAddress}:${clean(req.body?.email || '',120).toLowerCase()}`;
  const now=Date.now(), item=loginAttempts.get(key);
  if(item && now<item.until && item.count>=15)return fail(res,429,'Too many attempts. Try again later');
  if(!item || now>=item.until)loginAttempts.set(key,{count:1,until:now+15*60*1000});
  else item.count++;
  req.loginLimitKey=key;
  next();
}

app.disable('x-powered-by');
app.use(express.json({ limit: '20kb' }));
app.use((req, res, next) => {
  res.set('X-Content-Type-Options', 'nosniff');
  res.set('Referrer-Policy', 'strict-origin-when-cross-origin');
  const token = getCookie(req);
  const session = token && db.prepare('SELECT u.id,u.name,u.email,u.phone,u.role FROM sessions s JOIN users u ON u.id=s.user_id WHERE s.token_hash=? AND s.expires_at>?').get(tokenHash(token), Date.now());
  req.user = session || null;
  if (req.method !== 'GET' && req.method !== 'HEAD' && req.method !== 'OPTIONS') {
    const origin = req.get('origin');
    if (origin && origin !== `${req.protocol}://${req.get('host')}`) return fail(res, 403, 'Invalid origin');
  }
  next();
});
const auth = roles => (req, res, next) => !req.user ? fail(res, 401, 'Please sign in') : roles && !roles.includes(req.user.role) ? fail(res, 403, 'Not allowed') : next();
const expose = u => ({ id: u.id, name: u.name, email: u.email, phone: u.phone, role: u.role });
function signIn(res, user) {
  const token = crypto.randomBytes(32).toString('hex');
  db.prepare('INSERT INTO sessions(token_hash,user_id,expires_at) VALUES(?,?,?)').run(tokenHash(token), user.id, Date.now() + 7 * 86400000);
  res.cookie('session', token, { httpOnly: true, sameSite: 'lax', secure: process.env.NODE_ENV === 'production', maxAge: 7 * 86400000, path: '/' });
  res.json({ user: expose(user) });
}
app.post('/api/register', loginLimit, (req, res) => {
  const name = clean(req.body.name), email = clean(req.body.email).toLowerCase(), phone = clean(req.body.phone), password = req.body.password, role = req.body.role === 'driver' ? 'driver' : 'requester';
  if (name.length < 2 || !/^\S+@\S+\.\S+$/.test(email) || phone.length < 7 || typeof password !== 'string' || password.length < 10) return fail(res, 400, 'Enter a name, valid email and phone, and a password of at least 10 characters');
  try { const id = db.prepare('INSERT INTO users(name,email,phone,password_hash,role) VALUES(?,?,?,?,?)').run(name,email,phone,hashPassword(password),role).lastInsertRowid; signIn(res, db.prepare('SELECT * FROM users WHERE id=?').get(id)); }
  catch (e) { if (e.code === 'ERR_SQLITE_ERROR') return fail(res, 409, 'Email already registered'); throw e; }
});
app.post('/api/login', loginLimit, (req, res) => {
  const user = db.prepare('SELECT * FROM users WHERE email=?').get(clean(req.body.email).toLowerCase());
  if (!user || !checkPassword(req.body.password || '', user.password_hash)) return fail(res, 401, 'Invalid email or password');
  loginAttempts.delete(req.loginLimitKey);
  signIn(res, user);
});
app.post('/api/logout', (req, res) => { const token = getCookie(req); if (token) db.prepare('DELETE FROM sessions WHERE token_hash=?').run(tokenHash(token)); res.clearCookie('session'); res.json({ ok: true }); });
app.post('/api/password/forgot',loginLimit,(req,res)=>{
  const email=clean(req.body.email,254).toLowerCase();
  const generic={message:'If an account exists for that email, password reset instructions have been created.'};
  if(!/^\S+@\S+\.\S+$/.test(email))return res.json(generic);
  db.prepare('DELETE FROM password_resets WHERE expires_at<=?').run(Date.now());
  const account=db.prepare('SELECT id FROM users WHERE email=?').get(email);
  if(!account)return res.json(generic);
  const token=crypto.randomBytes(32).toString('hex');
  db.prepare('DELETE FROM password_resets WHERE user_id=?').run(account.id);
  db.prepare('INSERT INTO password_resets(token_hash,user_id,expires_at) VALUES(?,?,?)').run(tokenHash(token),account.id,Date.now()+30*60*1000);
  if(process.env.NODE_ENV!=='production')generic.resetUrl=`${req.protocol}://${req.get('host')}/#reset?token=${token}`;
  res.json(generic);
});
app.post('/api/password/reset',(req,res)=>{
  const token=clean(req.body.token,128),password=req.body.password;
  if(typeof password!=='string'||password.length<10)return fail(res,400,'Password must have at least 10 characters');
  const hashed=tokenHash(token),reset=db.prepare('SELECT * FROM password_resets WHERE token_hash=? AND expires_at>?').get(hashed,Date.now());
  if(!reset)return fail(res,400,'This reset link is invalid or has expired');
  try{
    db.exec('BEGIN IMMEDIATE');
    db.prepare('UPDATE users SET password_hash=? WHERE id=?').run(hashPassword(password),reset.user_id);
    db.prepare('DELETE FROM sessions WHERE user_id=?').run(reset.user_id);
    db.prepare('DELETE FROM password_resets WHERE user_id=?').run(reset.user_id);
    db.exec('COMMIT');res.clearCookie('session');res.json({ok:true});
  }catch(error){db.exec('ROLLBACK');throw error}
});
app.get('/api/me', (req, res) => res.json({ user: req.user }));
app.patch('/api/me', auth(), (req, res) => {
  const name = clean(req.body.name), phone = clean(req.body.phone);
  if (name.length < 2 || phone.length < 7) return fail(res, 400, 'Name or phone is too short');
  db.prepare('UPDATE users SET name=?,phone=? WHERE id=?').run(name, phone, req.user.id);
  res.json({ user: expose(db.prepare('SELECT * FROM users WHERE id=?').get(req.user.id)) });
});
app.post('/api/me/password', auth(), (req,res) => {
  const current=req.body.currentPassword,newPassword=req.body.newPassword;
  const row=db.prepare('SELECT password_hash FROM users WHERE id=?').get(req.user.id);
  if(typeof current!=='string'||!checkPassword(current,row.password_hash))return fail(res,403,'Current password is incorrect');
  if(typeof newPassword!=='string'||newPassword.length<10)return fail(res,400,'New password must have at least 10 characters');
  db.prepare('UPDATE users SET password_hash=? WHERE id=?').run(hashPassword(newPassword),req.user.id);
  db.prepare('DELETE FROM sessions WHERE user_id=?').run(req.user.id);
  res.clearCookie('session');
  res.json({ok:true});
});
app.get('/api/vehicles', (req, res) => {
  const q = `%${clean(req.query.q || '', 80)}%`;
  const kind=clean(req.query.kind || '',50);
  res.json({ vehicles: db.prepare("SELECT v.id,v.plate,v.kind,v.location,v.equipment,v.updated_at,u.name AS driver,d.service_area,d.crew,d.capacity FROM vehicles v JOIN users u ON u.id=v.driver_id LEFT JOIN vehicle_details d ON d.vehicle_id=v.id WHERE v.approved=1 AND v.available=1 AND (?='' OR v.kind=?) AND (v.location LIKE ? OR v.kind LIKE ? OR v.equipment LIKE ? OR d.service_area LIKE ?) ORDER BY v.updated_at DESC").all(kind,kind,q,q,q,q) });
});
app.get('/api/vehicles/:id', (req,res)=>{
  const vehicle=db.prepare('SELECT v.id,v.plate,v.kind,v.location,v.equipment,v.available,v.updated_at,u.name AS driver,d.service_area,d.crew,d.capacity,d.description FROM vehicles v JOIN users u ON u.id=v.driver_id LEFT JOIN vehicle_details d ON d.vehicle_id=v.id WHERE v.id=? AND v.approved=1').get(req.params.id);
  if(!vehicle)return fail(res,404,'Ambulance not found');
  res.json({vehicle});
});
app.get('/api/vehicle', auth(['driver']), (req, res) => res.json({ vehicle: db.prepare('SELECT v.*,d.service_area,d.crew,d.capacity,d.description FROM vehicles v LEFT JOIN vehicle_details d ON d.vehicle_id=v.id WHERE v.driver_id=?').get(req.user.id) || null }));
app.get('/api/vehicle/documents',auth(['driver']),(req,res)=>res.json({documents:db.prepare('SELECT id,kind,original_name,mime_type,size,uploaded_at FROM driver_documents WHERE driver_id=? ORDER BY kind').all(req.user.id)}));
app.post('/api/vehicle/documents',auth(['driver']),upload.single('document'),(req,res)=>{
  const kind=clean(req.body.kind,40),vehicle=db.prepare('SELECT * FROM vehicles WHERE driver_id=?').get(req.user.id);
  if(!vehicle)return fail(res,400,'Register a vehicle before uploading documents');
  if(!['driver_license','vehicle_registration'].includes(kind))return fail(res,400,'Invalid document type');
  if(!req.file||!validFile(req.file))return fail(res,400,'Upload a valid PDF, PNG, or JPEG file up to 5 MB');
  if(db.prepare("SELECT 1 FROM requests WHERE vehicle_id=? AND status IN ('assigned','accepted','en_route','arrived','onboard')").get(vehicle.id))return fail(res,409,'Finish the active trip before changing documents');
  const extension={'application/pdf':'.pdf','image/png':'.png','image/jpeg':'.jpg'}[req.file.mimetype];
  const storedName=`${crypto.randomUUID()}${extension}`,destination=path.join(uploadPath,storedName);
  const previous=db.prepare('SELECT stored_name FROM driver_documents WHERE driver_id=? AND kind=?').get(req.user.id,kind);
  fs.writeFileSync(destination,req.file.buffer,{flag:'wx'});
  try{
    db.prepare('INSERT INTO driver_documents(driver_id,vehicle_id,kind,original_name,stored_name,mime_type,size) VALUES(?,?,?,?,?,?,?) ON CONFLICT(driver_id,kind) DO UPDATE SET vehicle_id=excluded.vehicle_id,original_name=excluded.original_name,stored_name=excluded.stored_name,mime_type=excluded.mime_type,size=excluded.size,uploaded_at=CURRENT_TIMESTAMP').run(req.user.id,vehicle.id,kind,clean(path.basename(req.file.originalname),120),storedName,req.file.mimetype,req.file.size);
    db.prepare('UPDATE vehicles SET approved=0,available=0,updated_at=CURRENT_TIMESTAMP WHERE id=?').run(vehicle.id);
    if(previous){const old=path.join(uploadPath,previous.stored_name);if(old.startsWith(uploadPath)&&fs.existsSync(old))fs.unlinkSync(old)}
    res.status(201).json({ok:true});
  }catch(error){if(fs.existsSync(destination))fs.unlinkSync(destination);throw error}
});
app.get('/api/documents/:id',auth(),(req,res)=>{
  const document=db.prepare('SELECT d.* FROM driver_documents d WHERE d.id=?').get(req.params.id);
  if(!document||(req.user.role!=='admin'&&document.driver_id!==req.user.id))return fail(res,404,'Document not found');
  const file=path.join(uploadPath,document.stored_name);
  if(!file.startsWith(uploadPath)||!fs.existsSync(file))return fail(res,404,'Document file missing');
  res.type(document.mime_type);res.set('Content-Disposition',`attachment; filename="${document.original_name.replace(/["\\]/g,'_')}"`);
  const stream=fs.createReadStream(file);
  stream.on('error',error=>{if(!res.headersSent)fail(res,404,'Document file missing');else res.destroy(error)});
  stream.pipe(res);
});
app.put('/api/vehicle', auth(['driver']), (req, res) => {
  const plate=clean(req.body.plate,30).toUpperCase(), kind=clean(req.body.kind,50), location=clean(req.body.location,100), equipment=clean(req.body.equipment,300);
  const serviceArea=clean(req.body.serviceArea,150),crew=clean(req.body.crew,100),description=clean(req.body.description,500),capacity=Number(req.body.capacity);
  if (!plate || !['Basic','Advanced','Patient transport'].includes(kind) || !location || !serviceArea || !Number.isInteger(capacity) || capacity<1 || capacity>8) return fail(res,400,'Plate, type, location, service area, and capacity (1–8) are required');
  try {
    db.exec('BEGIN IMMEDIATE');
    const existing=db.prepare('SELECT id FROM vehicles WHERE driver_id=?').get(req.user.id);
    if (existing && db.prepare("SELECT 1 FROM requests WHERE vehicle_id=? AND status IN ('assigned','accepted','en_route','arrived','onboard')").get(existing.id)){db.exec('ROLLBACK');return fail(res,409,'Finish the active trip before editing this vehicle');}
    let id;
    if (existing){id=existing.id;db.prepare('UPDATE vehicles SET plate=?,kind=?,location=?,equipment=?,approved=0,available=0,updated_at=CURRENT_TIMESTAMP WHERE id=?').run(plate,kind,location,equipment,id);}
    else id=db.prepare('INSERT INTO vehicles(driver_id,plate,kind,location,equipment) VALUES(?,?,?,?,?)').run(req.user.id,plate,kind,location,equipment).lastInsertRowid;
    db.prepare('INSERT INTO vehicle_details(vehicle_id,service_area,crew,capacity,description) VALUES(?,?,?,?,?) ON CONFLICT(vehicle_id) DO UPDATE SET service_area=excluded.service_area,crew=excluded.crew,capacity=excluded.capacity,description=excluded.description').run(id,serviceArea,crew,capacity,description);
    db.exec('COMMIT');res.json({ vehicle: db.prepare('SELECT v.*,d.service_area,d.crew,d.capacity,d.description FROM vehicles v JOIN vehicle_details d ON d.vehicle_id=v.id WHERE v.id=?').get(id) });
  } catch(e) {db.exec('ROLLBACK');if(e.code==='ERR_SQLITE_ERROR') return fail(res,409,'Vehicle plate already registered'); throw e; }
});
app.patch('/api/vehicle/availability', auth(['driver']), (req,res) => {
  const vehicle=db.prepare('SELECT * FROM vehicles WHERE driver_id=?').get(req.user.id);
  if(!vehicle || !vehicle.approved) return fail(res,403,'Vehicle approval required');
  if(req.body.available && db.prepare("SELECT 1 FROM requests WHERE vehicle_id=? AND status IN ('assigned','accepted','en_route','arrived','onboard')").get(vehicle.id)) return fail(res,409,'Finish the active trip first');
  db.prepare('UPDATE vehicles SET available=?,location=?,updated_at=CURRENT_TIMESTAMP WHERE id=?').run(req.body.available ? 1:0,clean(req.body.location || vehicle.location),vehicle.id);
  res.json({ok:true});
});
app.post('/api/requests', auth(['requester']), (req,res) => {
  const pickup=clean(req.body.pickup),destination=clean(req.body.destination),notes=clean(req.body.notes,500),phone=clean(req.body.phone || req.user.phone);
  if(pickup.length<3 || destination.length<3 || phone.length<7) return fail(res,400,'Pickup, destination, and phone are required');
  const id=db.prepare('INSERT INTO requests(requester_id,pickup,destination,notes,phone) VALUES(?,?,?,?,?)').run(req.user.id,pickup,destination,notes,phone).lastInsertRowid;
  db.prepare('INSERT INTO events(request_id,actor_id,status) VALUES(?,?,?)').run(id,req.user.id,'requested');
  res.status(201).json({id});
});
app.get('/api/requests', auth(), (req,res) => {
  let sql="SELECT r.*,u.name AS requester_name,v.plate,v.kind,d.name AS driver_name FROM requests r JOIN users u ON u.id=r.requester_id LEFT JOIN vehicles v ON v.id=r.vehicle_id LEFT JOIN users d ON d.id=v.driver_id";
  const params=[];
  if(req.user.role==='requester'){sql+=' WHERE r.requester_id=?';params.push(req.user.id);}
  if(req.user.role==='driver'){sql+=' WHERE v.driver_id=?';params.push(req.user.id);}
  res.json({requests:db.prepare(sql+' ORDER BY r.created_at DESC,r.id DESC').all(...params)});
});
app.get('/api/requests/:id/events', auth(), (req,res) => {
  const row=db.prepare('SELECT r.*,v.driver_id FROM requests r LEFT JOIN vehicles v ON v.id=r.vehicle_id WHERE r.id=?').get(req.params.id);
  if(!row || (req.user.role!=='admin' && row.requester_id!==req.user.id && row.driver_id!==req.user.id)) return fail(res,404,'Request not found');
  res.json({events:db.prepare('SELECT e.status,e.note,e.created_at,u.name AS actor FROM events e JOIN users u ON u.id=e.actor_id WHERE e.request_id=? ORDER BY e.id').all(row.id)});
});
app.get('/api/notifications',auth(),(req,res)=>{
  let where,params=[];
  if(req.user.role==='admin')where='e.actor_id<>?';
  else if(req.user.role==='requester')where='r.requester_id=? AND e.actor_id<>?';
  else where='v.driver_id=? AND e.actor_id<>?';
  params=req.user.role==='admin'?[req.user.id]:[req.user.id,req.user.id];
  const notifications=db.prepare(`SELECT e.id,e.request_id,e.status,e.created_at,u.name AS actor,CASE WHEN nr.event_id IS NULL THEN 0 ELSE 1 END AS is_read FROM events e JOIN requests r ON r.id=e.request_id LEFT JOIN vehicles v ON v.id=r.vehicle_id JOIN users u ON u.id=e.actor_id LEFT JOIN notification_reads nr ON nr.event_id=e.id AND nr.user_id=? WHERE ${where} ORDER BY e.id DESC LIMIT 50`).all(req.user.id,...params);
  res.json({notifications,unread:notifications.filter(n=>!n.is_read).length});
});
app.post('/api/notifications/read',auth(),(req,res)=>{
  const ids=Array.isArray(req.body.ids)?req.body.ids.filter(Number.isInteger).slice(0,50):[];
  const visible=new Set(db.prepare("SELECT e.id FROM events e JOIN requests r ON r.id=e.request_id LEFT JOIN vehicles v ON v.id=r.vehicle_id WHERE (?='admin' OR (?='requester' AND r.requester_id=?) OR (?='driver' AND v.driver_id=?))").all(req.user.role,req.user.role,req.user.id,req.user.role,req.user.id).map(x=>x.id));
  const insert=db.prepare('INSERT OR IGNORE INTO notification_reads(user_id,event_id) VALUES(?,?)');
  for(const id of ids)if(visible.has(id))insert.run(req.user.id,id);
  res.json({ok:true});
});
app.put('/api/requests/:id/location', auth(['driver']), (req,res) => {
  const row=db.prepare('SELECT r.status,v.id AS vehicle_id,v.driver_id FROM requests r JOIN vehicles v ON v.id=r.vehicle_id WHERE r.id=?').get(req.params.id);
  if(!row || row.driver_id!==req.user.id || !['accepted','en_route','arrived','onboard'].includes(row.status)) return fail(res,403,'No active assigned trip');
  const latitude=Number(req.body.latitude),longitude=Number(req.body.longitude);
  if(!Number.isFinite(latitude)||!Number.isFinite(longitude)||Math.abs(latitude)>90||Math.abs(longitude)>180)return fail(res,400,'Invalid coordinates');
  db.prepare('INSERT INTO locations(vehicle_id,latitude,longitude) VALUES(?,?,?) ON CONFLICT(vehicle_id) DO UPDATE SET latitude=excluded.latitude,longitude=excluded.longitude,updated_at=CURRENT_TIMESTAMP').run(row.vehicle_id,latitude,longitude);
  res.json({ok:true});
});
app.get('/api/requests/:id/location', auth(), (req,res) => {
  const row=db.prepare('SELECT r.*,v.driver_id FROM requests r LEFT JOIN vehicles v ON v.id=r.vehicle_id WHERE r.id=?').get(req.params.id);
  if(!row || (req.user.role!=='admin'&&row.requester_id!==req.user.id&&row.driver_id!==req.user.id))return fail(res,404,'Request not found');
  if(!['accepted','en_route','arrived','onboard'].includes(row.status))return res.json({location:null});
  res.json({location:db.prepare('SELECT latitude,longitude,updated_at FROM locations WHERE vehicle_id=?').get(row.vehicle_id)||null});
});
function transition(req,res,status,vehicleId,reason) {
  const id=Number(req.params.id);
  try {
    db.exec('BEGIN IMMEDIATE');
    const row=db.prepare('SELECT r.*,v.driver_id FROM requests r LEFT JOIN vehicles v ON v.id=r.vehicle_id WHERE r.id=?').get(id);
    if(!row) {db.exec('ROLLBACK');return fail(res,404,'Request not found');}
    const allowed={requested:['assigned','cancelled'],assigned:['accepted','declined','cancelled'],accepted:['en_route','declined','cancelled'],en_route:['arrived','cancelled'],arrived:['onboard','cancelled'],onboard:['completed']};
    if(!(allowed[row.status]||[]).includes(status)){db.exec('ROLLBACK');return fail(res,409,'Invalid status change');}
    if(status==='assigned') {
      if(req.user.role!=='admin'){db.exec('ROLLBACK');return fail(res,403,'Admin required');}
      const vehicle=db.prepare('SELECT * FROM vehicles WHERE id=? AND approved=1 AND available=1').get(vehicleId);
      if(!vehicle){db.exec('ROLLBACK');return fail(res,409,'Vehicle unavailable');}
      db.prepare('UPDATE vehicles SET available=0,updated_at=CURRENT_TIMESTAMP WHERE id=?').run(vehicleId);
      db.prepare('DELETE FROM locations WHERE vehicle_id=?').run(vehicleId);
      db.prepare('UPDATE requests SET vehicle_id=? WHERE id=?').run(vehicleId,id);
    } else if(status==='cancelled') {
      reason=clean(reason,300);
      if(req.user.role!=='admin' && !(req.user.role==='requester' && row.requester_id===req.user.id)){db.exec('ROLLBACK');return fail(res,403,'Not allowed');}
      if(reason.length<3){db.exec('ROLLBACK');return fail(res,400,'A cancellation reason is required');}
      if(row.vehicle_id) { db.prepare('UPDATE vehicles SET available=1,updated_at=CURRENT_TIMESTAMP WHERE id=?').run(row.vehicle_id); db.prepare('DELETE FROM locations WHERE vehicle_id=?').run(row.vehicle_id); }
      db.prepare('UPDATE requests SET cancellation_reason=? WHERE id=?').run(reason,id);
    } else if(status==='declined') {
      reason=clean(reason,300);
      if(req.user.role!=='driver'||row.driver_id!==req.user.id){db.exec('ROLLBACK');return fail(res,403,'Only the assigned driver can decline');}
      if(reason.length<3){db.exec('ROLLBACK');return fail(res,400,'A decline reason is required');}
      db.prepare('UPDATE vehicles SET available=1,updated_at=CURRENT_TIMESTAMP WHERE id=?').run(row.vehicle_id);
      db.prepare('DELETE FROM locations WHERE vehicle_id=?').run(row.vehicle_id);
      db.prepare("UPDATE requests SET vehicle_id=NULL,status='requested',updated_at=CURRENT_TIMESTAMP WHERE id=?").run(id);
      db.prepare('INSERT INTO events(request_id,actor_id,status,note) VALUES(?,?,?,?)').run(id,req.user.id,'declined',reason);
      db.exec('COMMIT');return res.json({ok:true});
    } else if(req.user.role!=='admin' && !(req.user.role==='driver' && row.driver_id===req.user.id)){db.exec('ROLLBACK');return fail(res,403,'Not assigned to this trip');}
    if(status==='completed') {db.prepare('UPDATE vehicles SET available=1,updated_at=CURRENT_TIMESTAMP WHERE id=?').run(row.vehicle_id);db.prepare('DELETE FROM locations WHERE vehicle_id=?').run(row.vehicle_id);}
    db.prepare('UPDATE requests SET status=?,updated_at=CURRENT_TIMESTAMP WHERE id=?').run(status,id);
    db.prepare('INSERT INTO events(request_id,actor_id,status,note) VALUES(?,?,?,?)').run(id,req.user.id,status,reason||'');
    db.exec('COMMIT');res.json({ok:true});
  } catch(e){db.exec('ROLLBACK');throw e;}
}
app.patch('/api/requests/:id/status', auth(), (req,res)=>transition(req,res,clean(req.body.status,30),Number(req.body.vehicle_id),req.body.reason));
app.post('/api/requests/:id/reassign',auth(['admin']),(req,res)=>{
  const id=Number(req.params.id),vehicleId=Number(req.body.vehicle_id),reason=clean(req.body.reason,300);
  if(reason.length<3)return fail(res,400,'A reassignment reason is required');
  try{
    db.exec('BEGIN IMMEDIATE');
    const request=db.prepare("SELECT * FROM requests WHERE id=? AND status IN ('assigned','accepted','en_route','arrived')").get(id);
    if(!request){db.exec('ROLLBACK');return fail(res,409,'This request cannot be reassigned');}
    const vehicle=db.prepare('SELECT * FROM vehicles WHERE id=? AND approved=1 AND available=1').get(vehicleId);
    if(!vehicle){db.exec('ROLLBACK');return fail(res,409,'Replacement vehicle is unavailable');}
    if(request.vehicle_id){db.prepare('UPDATE vehicles SET available=1,updated_at=CURRENT_TIMESTAMP WHERE id=?').run(request.vehicle_id);db.prepare('DELETE FROM locations WHERE vehicle_id=?').run(request.vehicle_id);}
    db.prepare('UPDATE vehicles SET available=0,updated_at=CURRENT_TIMESTAMP WHERE id=?').run(vehicleId);
    db.prepare("UPDATE requests SET vehicle_id=?,status='assigned',updated_at=CURRENT_TIMESTAMP WHERE id=?").run(vehicleId,id);
    db.prepare("INSERT INTO events(request_id,actor_id,status,note) VALUES(?,?, 'reassigned',?)").run(id,req.user.id,reason);
    db.exec('COMMIT');res.json({ok:true});
  }catch(error){db.exec('ROLLBACK');throw error}
});
app.get('/api/admin/vehicles',auth(['admin']),(req,res)=>res.json({vehicles:db.prepare("SELECT v.*,u.name AS driver,u.phone,(SELECT COUNT(*) FROM driver_documents dd WHERE dd.driver_id=v.driver_id) AS document_count FROM vehicles v JOIN users u ON u.id=v.driver_id ORDER BY v.id DESC").all()}));
app.get('/api/admin/vehicles/:id/documents',auth(['admin']),(req,res)=>res.json({documents:db.prepare('SELECT d.id,d.kind,d.original_name,d.mime_type,d.size,d.uploaded_at FROM driver_documents d WHERE d.vehicle_id=? ORDER BY d.kind').all(req.params.id)}));
app.patch('/api/admin/vehicles/:id',auth(['admin']),(req,res)=>{
  const vehicle=db.prepare('SELECT id FROM vehicles WHERE id=?').get(req.params.id);
  if(!vehicle)return fail(res,404,'Vehicle not found');
  if(db.prepare("SELECT 1 FROM requests WHERE vehicle_id=? AND status IN ('assigned','accepted','en_route','arrived','onboard')").get(vehicle.id))return fail(res,409,'Finish or cancel the active trip first');
  if(req.body.approved&&db.prepare('SELECT COUNT(*) AS count FROM driver_documents WHERE vehicle_id=?').get(vehicle.id).count<2)return fail(res,409,'Driver licence and vehicle registration documents are required');
  db.prepare('UPDATE vehicles SET approved=?,available=0,updated_at=CURRENT_TIMESTAMP WHERE id=?').run(req.body.approved?1:0,vehicle.id);
  res.json({ok:true});
});
app.post('/api/contact',(req,res)=>{const name=clean(req.body.name),email=clean(req.body.email),message=clean(req.body.message,1000);if(!name||!/^\S+@\S+\.\S+$/.test(email)||message.length<10)return fail(res,400,'Complete all fields');db.prepare('INSERT INTO messages(name,email,message) VALUES(?,?,?)').run(name,email,message);res.status(201).json({ok:true});});
app.get('/api/admin/messages',auth(['admin']),(req,res)=>res.json({messages:db.prepare('SELECT * FROM messages ORDER BY id DESC').all()}));
app.use(express.static(path.join(__dirname,'public')));
app.use((req,res)=>res.sendFile(path.join(__dirname,'public','index.html')));
app.use((err,req,res,next)=>{if(err instanceof multer.MulterError)return fail(res,400,err.code==='LIMIT_FILE_SIZE'?'Document must be 5 MB or smaller':'Invalid document upload');console.error(err);fail(res,500,'Server error');});
const port=Number(process.env.PORT||3000);
app.listen(port,()=>console.log(`Ambulance app running at http://localhost:${port}`));
