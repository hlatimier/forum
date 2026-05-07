/************************************************************
 * FORUM JDR SECURISE - SINGLE FILE
 * Node.js + Express + JWT + bcrypt
 ************************************************************/

const express = require("express");
const bcrypt = require("bcrypt");
const jwt = require("jsonwebtoken");
const fs = require("fs");

const app = express();
const SECRET = "CHANGE_ME_SUPER_SECRET";

app.use(express.json());

/* =========================
   BASE DE DONNEES
========================= */

const DB_FILE = "db.json";

function loadDB() {
  if (!fs.existsSync(DB_FILE)) return { users: [], posts: [] };
  return JSON.parse(fs.readFileSync(DB_FILE));
}

function saveDB(db) {
  fs.writeFileSync(DB_FILE, JSON.stringify(db, null, 2));
}

/* =========================
   AUTH
========================= */

// REGISTER
app.post("/register", async (req, res) => {
  const db = loadDB();
  const { username, password } = req.body;

  if (db.users.find(u => u.username === username)) {
    return res.status(400).json({ error: "taken" });
  }

  const hash = await bcrypt.hash(password, 10);
  db.users.push({ username, password: hash });
  saveDB(db);

  res.json({ ok: true });
});

// LOGIN
app.post("/login", async (req, res) => {
  const db = loadDB();
  const { username, password } = req.body;

  const user = db.users.find(u => u.username === username);
  if (!user) return res.status(400).json({ error: "invalid" });

  const ok = await bcrypt.compare(password, user.password);
  if (!ok) return res.status(400).json({ error: "invalid" });

  const token = jwt.sign({ username }, SECRET);
  res.json({ token });
});

// AUTH MIDDLEWARE
function auth(req, res, next) {
  const token = req.headers.authorization;
  if (!token) return res.status(401).json({ error: "no token" });

  try {
    req.user = jwt.verify(token, SECRET);
    next();
  } catch {
    res.status(401).json({ error: "bad token" });
  }
}

/* =========================
   POSTS API
========================= */

app.get("/posts", (req, res) => {
  res.json(loadDB().posts);
});

app.post("/posts", auth, (req, res) => {
  const db = loadDB();

  db.posts.unshift({
    id: Date.now(),
    title: req.body.title,
    content: req.body.content,
    author: req.user.username,
    date: Date.now(),
    replies: []
  });

  saveDB(db);
  res.json({ ok: true });
});

/* =========================
   FRONTEND (HTML UNIQUE)
========================= */

app.get("/", (req, res) => {
  res.send(`
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Forum JDR Secure</title>
</head>
<body style="font-family:Arial;background:#1e1e2f;color:white;padding:20px">

<h1>🎲 Forum JDR sécurisé</h1>

<div>
<input id="u" placeholder="username">
<input id="p" type="password" placeholder="password">
<button onclick="register()">Register</button>
<button onclick="login()">Login</button>
</div>

<hr>

<h3>Nouveau sujet</h3>
<input id="t" placeholder="title"><br>
<textarea id="c"></textarea><br>
<button onclick="addPost()">Poster</button>

<hr>

<div id="posts"></div>

<script>
let token = null;

async function register(){
await fetch('/register',{
method:'POST',
headers:{'Content-Type':'application/json'},
body:JSON.stringify({username:u.value,password:p.value})
});
alert("OK");
}

async function login(){
let r = await fetch('/login',{
method:'POST',
headers:{'Content-Type':'application/json'},
body:JSON.stringify({username:u.value,password:p.value})
});
let d = await r.json();
token = d.token;
load();
}

async function addPost(){
await fetch('/posts',{
method:'POST',
headers:{
'Content-Type':'application/json',
'Authorization':token
},
body:JSON.stringify({title:t.value,content:c.value})
});
load();
}

async function load(){
let r = await fetch('/posts');
let posts = await r.json();

posts.sort((a,b)=>b.date-a.date);

document.getElementById('posts').innerHTML =
posts.map(p=>\`
<div style="margin:10px 0;padding:10px;background:#2c2c44">
<b>\${p.title}</b><br>
\${p.content}<br>
<small>\${p.author}</small>
</div>\`).join('');
}

load();
</script>

</body>
</html>
  `);
});

/* =========================
   START SERVER
========================= */

app.listen(3000, () => {
  console.log("🚀 Forum running on http://localhost:3000");
});