<?php
session_start();
require_once '../config.php';
require_once 'get_daily_word.php';

header('Content-Type: application/json');

// check connessione
if ($db_error) {
    echo json_encode(['error' => $db_error]);
    exit;
}

// prendo i parametri dalla url
$mode = $_GET['mode'] ?? 'daily'; // default daily
$lunghezza = intval($_GET['len'] ?? 5); // default 5
$maxTentativi = intval($_GET['tries'] ?? 6); // default 6
$timer = isset($_GET['timer']) ? intval($_GET['timer']) : null; // default no timer

// limiti per sicurezza
if ($lunghezza < 5) $lunghezza = 5;
if ($lunghezza > 7) $lunghezza = 7;
if ($maxTentativi < 1) $maxTentativi = 1;
if ($maxTentativi > 10) $maxTentativi = 10;

// la modalità giornaliera richiede login
if ($mode === 'daily') {
    if (!isset($_SESSION['logged']) || $_SESSION['logged'] !== true) {
        echo json_encode([
            'error' => 'login_required',
            'message' => 'Devi effettuare il login per giocare alla parola del giorno'
        ]);
        exit;
    }
}

$userId = $_SESSION['user_id'] ?? null;

if ($mode === 'daily') {
    // MODALITA PAROLA DEL GIORNO

    $dailyWord = getDailyWord($conn, 5);

    if (isset($dailyWord['error'])) {
        echo json_encode(['error' => $dailyWord['error']]);
        exit;
    }

    $parolaGiornoId = $dailyWord['id'];
    $parolaTarget = $dailyWord['parola'];

    // controllo se l'utente ha già giocato oggi
    $stmt = $conn->prepare("
        SELECT p.id, p.completata, p.vinta
        FROM partite p
        WHERE p.user_id = ? AND p.parola_giorno_id = ? AND p.modalita = 'giorno'
    ");
    $stmt->bind_param("ii", $userId, $parolaGiornoId);
    $stmt->execute();
    $result = $stmt->get_result();

    // funzionalità di recupero della partita -> non si può giocare più volte alla parola del giorno
    if ($partita = $result->fetch_assoc()) {
        // ha già una partita - recupero i tentativi
        $partitaId = $partita['id'];

        // query per prendere i tentativi precedenti
        $stmt = $conn->prepare("
            SELECT tentativo, risultato, numero_riga
            FROM tentativi
            WHERE partita_id = ?
            ORDER BY numero_riga ASC
        ");
        $stmt->bind_param("i", $partitaId);
        $stmt->execute();
        $tentativiResult = $stmt->get_result();

        $previousGuesses = [];
        while ($row = $tentativiResult->fetch_assoc()) {
            $previousGuesses[] = [
                'word' => $row['tentativo'],
                'result' => json_decode($row['risultato'])
            ];
        }

        // mando la risposta al frontend
        echo json_encode([
            'status' => $partita['completata'] ? 'completed' : 'resume',
            'game_id' => $partitaId,
            'word_length' => 5,
            'max_attempts' => 6,
            'timer' => null,
            'previous_guesses' => $previousGuesses,
            'won' => (bool)$partita['vinta'],
            'attempts_left' => 6 - count($previousGuesses)
        ]);
        exit;
    }

    // inizializzazione partita
    $stmt = $conn->prepare("
        INSERT INTO partite (user_id, parola_giorno_id, modalita, parola_target, lunghezza, max_tentativi)
        VALUES (?, ?, 'giorno', ?, 5, 6)
    ");
    $stmt->bind_param("iis", $userId, $parolaGiornoId, $parolaTarget);

    if (!$stmt->execute()) {
        echo json_encode(['error' => 'Errore nella creazione della partita']);
        exit;
    }

    echo json_encode([
        'status' => 'new',
        'game_id' => $conn->insert_id,
        'word_length' => 5,
        'max_attempts' => 6,
        'timer' => null,
        'previous_guesses' => [],
        'won' => false,
        'attempts_left' => 6
    ]);

} else {
    // MODALITA CUSTOM

    $jsonFile = '../../data/parole.json';
    if ($lunghezza == 6) {
        $jsonFile = '../../data/parole_6.json';
    } else if ($lunghezza == 7) {
        $jsonFile = '../../data/parole_7.json';
    }

    // controllo che il file esista
    if (!file_exists($jsonFile)) {
        echo json_encode(['error' => 'File parole non trovato per questa lunghezza']);
        exit;
    }

    // guardo se ci sono parole se non ci sono vuol dire che c'è stato errore
    $parole = json_decode(file_get_contents($jsonFile), true);
    if (!$parole || count($parole) == 0) {
        echo json_encode(['error' => 'Nessuna parola disponibile']);
        exit;
    }

    // scelta della parola
    $parolaTarget = $parole[array_rand($parole)];
    // echo $parolaTarget;  //debug

    if ($userId) {
        // save game
        $stmt = $conn->prepare("
            INSERT INTO partite (user_id, modalita, parola_target, lunghezza, max_tentativi, timer_secondi)
            VALUES (?, 'custom', ?, ?, ?, ?)
        ");
        $stmt->bind_param("isiii", $userId, $parolaTarget, $lunghezza, $maxTentativi, $timer);

        if (!$stmt->execute()) {
            echo json_encode(['error' => 'Errore nella creazione della partita']);
            exit;
        }

        $gameId = $conn->insert_id;
    } else {
        // utente non loggato -> sessione temporanea
        $gameId = 'temp_' . session_id() . '_' . time();
        $_SESSION['temp_game'] = [
            'parola' => $parolaTarget,
            'lunghezza' => $lunghezza,
            'max_tentativi' => $maxTentativi,
            'timer' => $timer,
            'start_time' => time(),
            'tentativi' => []
        ];
    }

    echo json_encode([
        'status' => 'new',
        'game_id' => $gameId,
        'word_length' => $lunghezza,
        'max_attempts' => $maxTentativi,
        'timer' => $timer,
        'previous_guesses' => [],
        'won' => false,
        'attempts_left' => $maxTentativi
    ]);
}
?>
