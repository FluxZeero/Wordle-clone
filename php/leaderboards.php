<?php
session_start();
require_once 'config.php';

// funzione per trasformare il mosaico in quadratini colorati
function mostraMosaico($mosaico) {
    $righe = explode("|", $mosaico);
    $html = "";
    foreach ($righe as $riga) {
        $colori = json_decode($riga);
        $count = 0;
        foreach ($colori as $colore) {
            $count ++;
            if ($colore == "green") {
                $html .= '<span class="quadrato verde"></span>';
            } else if ($colore == "yellow") {
                $html .= '<span class="quadrato giallo"></span>';
            } else {
                $html .= '<span class="quadrato grigio"></span>';
            }
        }
        $html .= "<br>";
    }
    return $html;
}

// statistiche di oggi

$today_stats_query = "SELECT 
                    u.username,
                    COUNT(t.id) AS num_tentativi,
                    GROUP_CONCAT(t.risultato ORDER BY t.numero_riga SEPARATOR '|') AS mosaico
                FROM partite p
                JOIN users u ON p.user_id = u.id
                JOIN parola_giorno pg ON p.parola_giorno_id = pg.id
                JOIN tentativi t ON t.partita_id = p.id
                WHERE pg.data = CURDATE()
                AND p.modalita = 'giorno'
                AND p.vinta = 1
                GROUP BY p.id, u.username
                ORDER BY num_tentativi ASC";
$result = mysqli_query($conn, $today_stats_query);
$today_stats = mysqli_fetch_all($result, MYSQLI_ASSOC);

// statistiche globali streak

$global_stats_query = "SELECT 
                    u.username,
                    COUNT(DISTINCT p.id) AS parole_indovinate,
                    COUNT(t.id) AS tentativi_totali,
                    u.streak_curr,
                    u.streak_max
                FROM users u
                JOIN partite p ON p.user_id = u.id
                AND p.modalita = 'giorno'
                AND p.vinta = 1
                JOIN tentativi t ON t.partita_id = p.id
                GROUP BY u.id, u.username, u.streak_curr, u.streak_max
                ORDER BY u.streak_curr DESC";
$result = mysqli_query($conn, $global_stats_query);
$global_stats = mysqli_fetch_all($result, MYSQLI_ASSOC);

?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ZORZLE - classifiche</title>
    <link rel="stylesheet" href="../css/header.css">
    <link rel="stylesheet" href="../css/footer.css">
    <link rel="stylesheet" href="../css/leaderboards.css">
    <link rel="stylesheet" href="../css/font.css">
</head>
<body>
    <header>
        <div class="left">
            <a href="index.php" id="home" class="logo">
                <img src="../res/img/main_logo.png" alt="Logo worzle" class="logo">
            </a>
        </div>
        <div class="center">
            <div>
                <a href="game.php" id="game"><h3>Parola di oggi</h3></a>
            </div>
            <div>
                <a href="leaderboards.php" id="classifiche"><h3>Classifiche</h3></a>
            </div>
            <div>
                <a href="custom.php" id="custom"><h3>Custom mode</h3></a>
            </div>
        </div>
        <div class="right">
            <?php
                if(isset($_SESSION["logged"]) && $_SESSION["logged"] == true){
                    $username = $_SESSION["username"];
                    echo '<p>Ciao ' . htmlspecialchars($username) . '</p>';
                    echo '<button id="logout" class="logout" onclick="window.location.href=\'logout.php\'">Logout</button>';
                } else {
                    echo '<button id="login" class="login" onclick="window.location.href=\'login.php\'">Login</button>';
                    echo '<button id="signup" class="signup" onclick="window.location.href=\'signup.php\'">Sign up</button>';
                }
            ?>
        </div>
    </header>

    <div class = "main-container">


        <!-- classifica per la parola del giorno -->
            <div class = "leaderboard-section">
                <div class = "section-title">
                    <h1>Classifica di oggi</h1>
                    <img src = "../res/img/trophy.png" alt = "trophy-icon">
                </div>
                <div class = "leaderboard-container">
                    <!--
                    <h1>qui ci sono le classifiche della parola di oggi</h1>
                    -->
                    <table class = "leaderboard-table">
                        <thead>
                            <tr class = "first-row">
                                <th>Posizione</th>
                                <th>Nickname</th>
                                <th>Numero Tentativi</th>
                                <th>Mosaico</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php $pos = 1; foreach ($today_stats as $riga): ?>
                            <tr class = "leaderboard-row">
                                <td><?= $pos++ ?></td>
                                <td><?= htmlspecialchars($riga['username']) ?></td>
                                <td><?= $riga['num_tentativi'] ?></td>
                                <td><?= mostraMosaico($riga['mosaico']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- classifica per la streak corrente -->
            <div class = "leaderboard-section">
                <div class = "section-title">
                    <h1>Le migliori streak</h1>
                    <img src="../res/img/fire-3.png" alt="fire-icon">
                </div>
            <div class = "leaderboard-container">
                    <!-- 
                    <h1>qui ci sono le classifiche delle megliori streak</h1>
                    -->
                    <table class = "leaderboard-table">
                        <thead>
                            <tr class = "first-row">
                                <th>Posizione</th>
                                <th>Nickname</th>
                                <th>Numero tentativi</th>
                                <th>Parole indovinate</th>
                                <th>Streak attuale</th>
                                <th>Streak massima</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php $pos = 1; foreach ($global_stats as $riga): ?>
                            <tr class = "leaderboard-row">
                                <td><?= $pos++ ?></td>
                                <td><?= htmlspecialchars($riga['username']) ?></td>
                                <td><?= $riga['tentativi_totali'] ?></td>
                                <td><?= $riga['parole_indovinate'] ?></td>
                                <td><?= $riga['streak_curr'] ?></td>
                                <td><?= $riga['streak_max'] ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>


    </div>

    <footer>
        <p>Progetto realizzato da Matteo Spallazzi per l'esame di Progettazione Web, licenza per l'utilizzo delle icone concessa da <a href="https://www.flaticon.com/">flaticon.com</a></p>
    </footer>
</body>
</html>