<?php
session_start();
require_once '../config.php';

header('Content-Type: application/json');

// controllo db
if ($db_error) {
    echo json_encode(['error' => $db_error]);
    exit;
}

// prendo dati json
$dati = json_decode(file_get_contents('php://input'), true);

if (!$dati) {
    echo json_encode(['error' => 'Richiesta non valida']);
    exit;
}

$idPartita = $dati['game_id'] ?? null;
$parola = strtoupper(trim($dati['guess'] ?? ''));

// LAIN = parola speciale per quando finisce il tempo
if ($parola === 'LAIN' && isset($_SESSION['temp_game'])) {
    echo json_encode([
        'valid' => true,
        'game_over' => true,
        'won' => false,
        'timeout' => true,
        'solution' => $_SESSION['temp_game']['parola']
    ]);
    exit;
}

if (!$idPartita || !$parola) {
    echo json_encode(['error' => 'Parametri mancanti']);
    exit;
}


// funzione che calcola i colori
function calcolaRisultato($tentativo, $target) {
    $lettere = mb_str_split($tentativo);
    $lettTarget = mb_str_split($target);
    $lung = count($lettTarget);

    // tutto grigio all'inizio
    $ris = array_fill(0, $lung, 'gray');
    $usatoTarget = array_fill(0, $lung, false);
    $usatoTent = array_fill(0, $lung, false);

    // prima giro - verde (posizione giusta)
    for ($i = 0; $i < $lung; $i++) {
        if ($lettere[$i] === $lettTarget[$i]) {
            $ris[$i] = 'green';
            $usatoTarget[$i] = true;
            $usatoTent[$i] = true;
        }
    }

    // secondo giro - giallo (lettera c'è ma posto sbagliato)
    for ($i = 0; $i < $lung; $i++) {
        if ($usatoTent[$i]) continue;

        for ($j = 0; $j < $lung; $j++) {
            if ($usatoTarget[$j]) continue;

            if ($lettere[$i] === $lettTarget[$j]) {
                $ris[$i] = 'yellow';
                $usatoTarget[$j] = true;
                break;
            }
        }
    }

    return $ris;
}

// controlla se la parola esiste
function parolaValida($word, $dim) {
    // file giusto in base alla lunghezza
    $file = '../../data/parole.json';
    if ($dim == 6) {
        $file = '../../data/parole_6.json';
    } else if ($dim == 7) {
        $file = '../../data/parole_7.json';
    }

    if (!file_exists($file)) {
        return false;
    }

    $listaParole = json_decode(file_get_contents($file), true);
    return in_array(strtoupper($word), $listaParole);
}

// PARTITA TEMP (no login)
if (strpos($idPartita, 'temp_') === 0) {
    if (!isset($_SESSION['temp_game'])) {
        echo json_encode(['error' => 'Partita non trovata']);
        exit;
    }
    $temp = $_SESSION['temp_game'];
    $parolaDaIndovinare = $temp['parola'];
    $lung = $temp['lunghezza'];
    $maxTent = $temp['max_tentativi'];
    $listaTent = $temp['tentativi'];

    // check lunghezza
    if (mb_strlen($parola) !== $lung) {
        echo json_encode([
            'valid' => false,
            'error' => "La parola deve essere di $lung lettere"
        ]);
        exit;
    }

    // check dizionario
    if (!parolaValida($parola, $lung)) {
        echo json_encode([
            'valid' => false,
            'error' => 'Parola non presente nel dizionario'
        ]);
        exit;
    }

    // calcolo colori
    $ris = calcolaRisultato($parola, $parolaDaIndovinare);
    $vittoria = ($parola === $parolaDaIndovinare);

    // salvo tentativo
    $listaTent[] = ['word' => $parola, 'result' => $ris];
    $_SESSION['temp_game']['tentativi'] = $listaTent;

    $finita = $vittoria || count($listaTent) >= $maxTent;

    // risposta
    $resp = [
        'valid' => true,
        'result' => $ris,
        'game_over' => $finita,
        'won' => $vittoria,
        'attempts_left' => $maxTent - count($listaTent)
    ];

    if ($finita) {
        $resp['solution'] = $parolaDaIndovinare;
    }

    echo json_encode($resp);
    exit;
}

// PARTITA DA DATABASE
$idPartita = intval($idPartita);

// prendo dati partita
$query = $conn->prepare("
    SELECT id, user_id, parola_target, lunghezza, max_tentativi, completata, modalita
    FROM partite
    WHERE id = ?
");
$query->bind_param("i", $idPartita);
$query->execute();
$risultato = $query->get_result();

// se il result set è vuoto
if (!$partita = $risultato->fetch_assoc()) {
    echo json_encode(['error' => 'Partita non trovata']);
    exit;
}

// check utente
$idUtente = $_SESSION['user_id'] ?? null;
if ($partita['user_id'] != $idUtente) {
    echo json_encode(['error' => 'Non autorizzato']);
    exit;
}

// già finita?
if ($partita['completata']) {
    echo json_encode(['error' => 'Partita già completata']);
    exit;
}

$parolaDaIndovinare = $partita['parola_target'];
$lung = $partita['lunghezza'];
$maxTent = $partita['max_tentativi'];

// LAIN per timeout
if ($parola === 'LAIN') {
    echo json_encode([
        'valid' => true,
        'game_over' => true,
        'won' => false,
        'timeout' => true,
        'solution' => $parolaDaIndovinare
    ]);
    exit;
}

// check lunghezza
if (mb_strlen($parola) !== $lung) {
    echo json_encode([
        'valid' => false,
        'error' => "La parola deve essere di $lung lettere"
    ]);
    exit;
}

// check dizionario
if (!parolaValida($parola, $lung)) {
    echo json_encode([
        'valid' => false,
        'error' => 'Parola non presente nel dizionario'
    ]);
    exit;
}

// quanti tentativi fatti
$query = $conn->prepare("SELECT COUNT(*) as tot FROM tentativi WHERE partita_id = ?");
$query->bind_param("i", $idPartita);
$query->execute();
$conteggio = $query->get_result()->fetch_assoc();
$numTent = $conteggio['tot'];

if ($numTent >= $maxTent) {
    echo json_encode(['error' => 'Hai esaurito i tentativi']);
    exit;
}

// calcolo colori
$ris = calcolaRisultato($parola, $parolaDaIndovinare);
$risJson = json_encode($ris);
$vittoria = ($parola === $parolaDaIndovinare);
$numRiga = $numTent + 1;

// salvo nel db
$query = $conn->prepare("
    INSERT INTO tentativi (partita_id, tentativo, risultato, numero_riga)
    VALUES (?, ?, ?, ?)
");
$query->bind_param("issi", $idPartita, $parola, $risJson, $numRiga);

if (!$query->execute()) {
    echo json_encode(['error' => 'Errore nel salvare il tentativo']);
    exit;
}

// partita finita?
$finita = $vittoria || ($numTent + 1 >= $maxTent);

// aggiorno stato se finita
if ($finita) {
    $query = $conn->prepare("UPDATE partite SET completata = TRUE, vinta = ? WHERE id = ?");
    $query->bind_param("ii", $vittoria, $idPartita);
    $query->execute();

    // aggiorno streak solo per modalità giorno
    if ($partita['modalita'] === 'giorno') {
        if ($vittoria) {
            // controllo se l'utente ha vinto anche la parola di ieri
            $stmtprec = $conn->prepare("
                SELECT p.vinta
                FROM partite p
                JOIN parola_giorno pg ON p.parola_giorno_id = pg.id
                WHERE p.user_id = ?
                  AND pg.data = DATE_SUB(CURDATE(), INTERVAL 1 DAY)
                  AND p.modalita = 'giorno'
                  AND p.completata = 1
                LIMIT 1
            ");
            $stmtprec->bind_param("i", $idUtente);
            $stmtprec->execute();
            $resPrec = $stmtprec->get_result();
            $prec = $resPrec->fetch_assoc();

            if ($prec && $prec['vinta'] == 1) {
                // ha vinto anche ieri -> incremento streak
                $stmtStreak = $conn->prepare("
                    UPDATE users
                    SET streak_curr = streak_curr + 1,
                        streak_max = GREATEST(streak_max, streak_curr + 1)
                    WHERE id = ?
                ");
            } else {
                // non ha vinto ieri o non ha giocato -> streak riparte da 1
                $stmtStreak = $conn->prepare("
                    UPDATE users
                    SET streak_curr = 1,
                        streak_max = GREATEST(streak_max, 1)
                    WHERE id = ?
                ");
            }
            $stmtStreak->bind_param("i", $idUtente);
            $stmtStreak->execute();
        } else {
    
            $stmtStreak = $conn->prepare("UPDATE users SET streak_curr = 0 WHERE id = ?");
            $stmtStreak->bind_param("i", $idUtente);
            $stmtStreak->execute();
        }
    }
}

// mando risposta
if(!$finita) {
    echo json_encode([
        'valid' => true,
        'result' => $ris,
        'game_over' => $finita,
        'won' => $vittoria,
        'attempts_left' => $maxTent - $numRiga,
        'solution' => ''
    ]);
} else {
    echo json_encode([
        'valid' => true,
        'result' => $ris,
        'game_over' => $finita,
        'won' => $vittoria,
        'attempts_left' => $maxTent - $numRiga,
        'solution' => $parolaDaIndovinare
    ]);
}

?>
