<?php
// funzione per ottenere la parola del giorno
require_once '../config.php';

function getDailyWord($conn, $lunghezza = 5) {
    // prendo la data di oggi
    $oggi = date('Y-m-d');

    // Controlla se esiste già la parola per oggi
    $stmt = $conn->prepare("SELECT id, parola FROM parola_giorno WHERE data = ?");
    $stmt->bind_param("s", $oggi);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        return [
            'id' => $row['id'],
            'parola' => $row['parola']
        ];
    }
    $jsonFile = '../../data/parole.json';

    if (!file_exists($jsonFile)) {
        return ['error' => 'File parole non trovato'];
    }

    $parole = json_decode(file_get_contents($jsonFile), true);

    $parolaScelta = $parole[array_rand($parole)];

    // Inserisci la nuova parola del giorno
    $stmt = $conn->prepare("INSERT INTO parola_giorno (parola, data) VALUES (?, ?)");
    $stmt->bind_param("ss", $parolaScelta, $oggi);

    if ($stmt->execute()) {
        return [
            'id' => $conn->insert_id,
            'parola' => $parolaScelta
        ];
    }

    return ['error' => 'Errore nel salvare la parola del giorno'];
}
?>
