<!DOCTYPE html>
<html>
<head>
<title>Forum Moderne</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<?php
session_start();

/*
    Fonction isAdmin()
    -------------------
    Elle vérifie si l'utilisateur connecté est administrateur.
*/

function isAdmin() {

    // On vérifie si une session utilisateur existe
    if (isset($_SESSION['username'])) {

        // Si le nom est "admin", alors c'est un administrateur
        if ($_SESSION['username'] === "admin") {
            return true;  // oui, c'est un admin
        }
    }

    return false; // sinon ce n'est pas un admin
}
?>


<style>

/* ===== RESET ===== */
* { box-sizing: border-box; margin:0; padding:0; }

body {
    font-family: 'Segoe UI', sans-serif;
    background: linear-gradient(135deg,#667eea,#764ba2);
    padding: 40px 15px;
}

/* ===== CONTAINER ===== */
.container {
    max-width: 900px;
    margin: auto;
}

/* ===== CARD ===== */
.card {
    background: white;
    padding: 25px;
    border-radius: 16px;
    box-shadow: 0 15px 40px rgba(0,0,0,0.15);
    margin-bottom: 25px;
}

/* ===== TITRES ===== */
h1 {
    text-align:center;
    margin-bottom:20px;
}

h2 {
    margin-bottom:15px;
}

/* ===== INPUTS ===== */
input, textarea {
    width:100%;
    padding:12px;
    margin:8px 0;
    border-radius:10px;
    border:1px solid #ddd;
    font-size:15px;
}

textarea {
    min-height:100px;
}

/* ===== BOUTONS ===== */
button {
    padding:10px 18px;
    border:none;
    border-radius:10px;
    background:#667eea;
    color:white;
    cursor:pointer;
    font-weight:bold;
    transition:0.2s;
}

button:hover {
    background:#5a67d8;
    transform:scale(1.03);
}

/* ===== TOPICS ===== */
.topic {
    padding:15px;
    background:#f7f8fc;
    border-radius:12px;
    margin-bottom:10px;
    display:flex;
    justify-content:space-between;
    align-items:center;
}

/* ===== MESSAGE ===== */
.message {
    background:#f1f3f9;
    padding:12px;
    border-radius:12px;
    margin-top:8px;
}

/* ===== DELETE ===== */
.delete {
    color:#e53e3e;
    font-size:13px;
    text-decoration:none;
    margin-left:10px;
}

/* ===== HEADER USER ===== */
.user-bar {
    display:flex;
    justify-content:space-between;
    align-items:center;
    margin-bottom:20px;
}

.user-bar a {
    text-decoration:none;
    color:#667eea;
    font-weight:bold;
}

/* ===== RESPONSIVE ===== */
@media(max-width:600px){
    .topic {
        flex-direction:column;
        align-items:flex-start;
    }
}


</style>
</head>
<body>

<div class="container">

<div class="card">
<h1>🚀 Forum Moderne</h1>

<?php


// Si les sujets n'existent pas encore
if (!isset($_SESSION['topics'])) {
    $_SESSION['topics'] = [];
}

$topics = &$_SESSION['topics']; // référence
?>


<?php if(isset($_SESSION["user"])): ?>
    <div class="user-bar">
        <div>
            Connecté : <strong><?= $_SESSION["user"] ?></strong>
            <?php if(isAdmin()) echo " 👑 ADMIN"; ?>
        </div>
        <div>
            <a href="?logout=1">Déconnexion</a>
        </div>
    </div>
<?php endif; ?>

<?php if(!isset($_SESSION["user"])): ?>

<h2>Inscription</h2>
<form method="POST">
    <input name="username" placeholder="Nom">
    <input type="password" name="password" placeholder="Mot de passe">
    <button name="register">Créer</button>
</form>

<hr style="margin:20px 0;">

<h2>Connexion</h2>
<form method="POST">
    <input name="username" placeholder="Nom">
    <input type="password" name="password" placeholder="Mot de passe">
    <button name="login">Connexion</button>
</form>

<?php endif; ?>
</div>

<?php
/* ===== VOIR SUJET ===== */

if(isset($_GET["topic"])):

$topicId = $_GET["topic"];
echo "<div class='card'>";
echo "<a href='index.php'>⬅ Retour</a><br><br>";

$topics = file_exists($topicFile) ? file($topicFile, FILE_IGNORE_NEW_LINES) : [];

foreach($topics as $t) {
    list($id, $title, $author) = explode("|", $t);
    if($id == $topicId) {
        echo "<h2>$title</h2>";
        echo "<p>Créé par $author</p>";
        if(isAdmin())
            echo "<a class='delete' href='?deleteTopic=$id'>🗑 Supprimer sujet</a>";
        echo "<hr style='margin:15px 0;'>";
    }
}

$posts = file_exists($postFile) ? file($postFile, FILE_IGNORE_NEW_LINES) : [];

foreach($posts as $index => $p) {
    list($tid, $user, $message) = explode("|", $p);
    if($tid == $topicId) {
        echo "<div class='message'>
                <strong>$user</strong><br>$message";
        if(isAdmin())
            echo "<br><a class='delete' href='?deletePost=$index'>🗑 Supprimer</a>";
        echo "</div>";
    }
}

if(isset($_SESSION["user"])): ?>

<hr style="margin:20px 0;">
<form method="POST">
    <input type="hidden" name="topicId" value="<?= $topicId ?>">
    <textarea name="message" placeholder="Répondre"></textarea>
    <button name="reply">Envoyer</button>
</form>

<?php endif;

echo "</div>";

else:

echo "<div class='card'>";

/* ===== TITRE LISTE DES SUJETS ===== */
echo "<h2>📋 Liste des sujets</h2>";
echo "<hr style='margin:15px 0;'>";

/* ===== SI AUCUN SUJET ===== */
if(empty($topics)){
    echo "<p>Aucun sujet pour le moment.</p>";
}

/* ===== AFFICHAGE DES SUJETS ===== */
foreach($topics as $t) {
    list($id, $title, $author) = explode("|", $t);
    echo "<div class='topic'>
            <div>
                <a href='?topic=$id'><strong>$title</strong></a><br>
                <small>par $author</small>
            </div>";
    if(isAdmin())
        echo "<a class='delete' href='?deleteTopic=$id'>🗑</a>";
    echo "</div>";
}

/* ===== CREATION DE SUJET ===== */
if(isset($_SESSION["user"])): ?>

<hr style="margin:20px 0;">
<h3>Créer un sujet</h3>
<form method="POST">
    <input name="title" placeholder="Titre du sujet">
    <button name="createTopic">Créer</button>
</form>

<?php endif;

echo "</div>";

endif;




foreach($topics as $t) {
    list($id, $title, $author) = explode("|", $t);
    echo "<div class='topic'>
            <div>
                <a href='?topic=$id'><strong>$title</strong></a><br>
                <small>par $author</small>
            </div>";
    if(isAdmin())
        echo "<a class='delete' href='?deleteTopic=$id'>🗑</a>";
    echo "</div>";
}

if(isset($_SESSION["user"])): ?>

<hr style="margin:20px 0;">
<h3>Créer un sujet</h3>
<form method="POST">
    <input name="title" placeholder="Titre du sujet">
    <button name="createTopic">Créer</button>
</form>

<?php endif;

echo "</div>";


?>

</div>
</body>
</html>
