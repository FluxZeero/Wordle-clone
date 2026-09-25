document.addEventListener("DOMContentLoaded", function() {
    // console.log("Game loaded!"); // debug

    // stato del gioco - variabili globali
    let gameState = {
        gameId: null,
        wordLength: 5,
        maxAttempts: 6,
        timer: null,
        currentRow: 0,
        currentCol: 0,
        currentGuess: '',
        gameOver: false,
        won: false,
        timerInterval: null,
        timeLeft: 0,
        solution: ''
    };

    // elementi DOM
    const grid = document.getElementById('game-grid');
    const keyboard = document.getElementById('keyboard');
    const message = document.getElementById('message');
    const timerContainer = document.getElementById('timer-container');
    const timerDisplay = document.getElementById('timer');
    const modal = document.getElementById('game-over-modal');
    const modalTitle = document.getElementById('modal-title');
    const modalMessage = document.getElementById('modal-message');
    const playAgainBtn = document.getElementById('play-again');
    const HomeBtn = document.getElementById('home-btn');
    const modalSolution = document.getElementById('modal-solution');

    // prende i parametri dall'URL
    function getUrlParams() {
        const params = new URLSearchParams(window.location.search);
        return {
            mode: params.get('mode') || 'daily',
            len: parseInt(params.get('len')) || 5,
            tries: parseInt(params.get('tries')) || 6,
            timer: params.get('timer') ? parseInt(params.get('timer')) : null
        };
    }

    // inizializza la griglia di gioco
    function initGrid(rows, cols) {
        grid.innerHTML = '';
        grid.style.setProperty('--cols', cols);

        // creo le righe e le celle
        for (let i = 0; i < rows; i++) {
            const row = document.createElement('div');
            row.className = 'grid-row';
            row.id = 'row-' + i;

            for (let j = 0; j < cols; j++) {
                const cell = document.createElement('div');
                cell.className = 'grid-cell';
                cell.id = 'cell-' + i + '-' + j;
                row.appendChild(cell);
            }

            grid.appendChild(row);
        }
    }

    // funzione per ottenere una cella tramite id
    function getCell(row, col) {
        return document.getElementById('cell-' + row + '-' + col);
    }

    // mostra un messaggio all'utente
    function showMessage(text, type, duration) {
        // type può essere: info, error, success
        if (duration === undefined) duration = 2000; // default 2 secondi

        message.textContent = text;
        message.className = 'message-' + type;
        message.classList.remove('hidden');

        if (duration > 0) {
            setTimeout(function() {
                message.className = 'message-hidden';
            }, duration);
        }
    }

    // inizializza il timer
    function initTimer(seconds) {
        if (!seconds) return;

        gameState.timeLeft = seconds;
        timerContainer.classList.remove('hidden');
        updateTimerDisplay();

        // avvio il countdown
        gameState.timerInterval = setInterval(function() {
            gameState.timeLeft--;
            updateTimerDisplay();

            // tempo scaduto!
            if (gameState.timeLeft <= 0) {
                clearInterval(gameState.timerInterval);
                gameState.currentGuess = 'LAIN';
                submitGuess().then(() => {
                    endGame(false, 'Tempo scaduto!');
                })
            }
        }, 1000);
    }

    // aggiorna il display del timer
    function updateTimerDisplay() {
        const min = Math.floor(gameState.timeLeft / 60);
        const sec = gameState.timeLeft % 60;
        // formatto con gli zeri davanti
        timerDisplay.textContent = min.toString().padStart(2, '0') + ':' + sec.toString().padStart(2, '0');

        // se sta per scadere diventa rosso
        if (gameState.timeLeft <= 10) {
            timerDisplay.classList.add('timer-warning');
        }
    }

    // carica i dati del gioco dal server
    async function loadGame() {
        const params = getUrlParams();
        // console.log("Params:", params); // debug

        try {
            const url = new URL('api/get_game.php', window.location.origin + window.location.pathname.replace('game.php', ''));
            url.searchParams.set('mode', params.mode);
            url.searchParams.set('len', params.len);
            url.searchParams.set('tries', params.tries);
            if (params.timer) {
                url.searchParams.set('timer', params.timer);
            }

            const response = await fetch(url);
            const data = await response.json();
            // console.log("Game data:", data); // debug

            if (data.error) {
                if (data.error === 'login_required') {
                    showMessage('Devi effettuare il login per giocare', 'error', 0);
                    setTimeout(function() {
                        window.location.href = 'login.php';
                    }, 2000);
                    return;
                }
                showMessage(data.error, 'error', 0);
                return;
            }

            // salvo lo stato del gioco
            gameState.gameId = data.game_id;
            gameState.wordLength = data.word_length;
            gameState.maxAttempts = data.max_attempts;
            gameState.timer = data.timer;
            gameState.won = data.won;
            gameState.gameOver = data.status === 'completed';

            // creo la griglia
            initGrid(gameState.maxAttempts, gameState.wordLength);

            // se ci sono tentativi precedenti li mostro
            if (data.previous_guesses && data.previous_guesses.length > 0) {
                for (let i = 0; i < data.previous_guesses.length; i++) {
                    const guess = data.previous_guesses[i];
                    fillRow(gameState.currentRow, guess.word, guess.result);
                    updateKeyboard(guess.word, guess.result);
                    gameState.currentRow++;
                }
            }

            // gestisco se la partita è già finita
            if (gameState.gameOver) {
                if (gameState.won) {
                    showMessage('Hai gia vinto questa partita!', 'success', 0);
                } else {
                    showMessage('Hai gia perso questa partita', 'error', 0);
                }
                disableInput();
                return;
            }

            // avvio il timer se c'è
            if (data.timer && data.status === 'new') {
                initTimer(data.timer);
            }

        } catch (error) {
            console.error('Errore nel caricamento:', error);
            showMessage('Errore di connessione', 'error', 0);
        }
    }

    // riempe una riga con un tentativo
    function fillRow(rowIndex, word, result) {
        const letters = word.split('');

        for (let i = 0; i < letters.length; i++) {
            const cell = getCell(rowIndex, i);
            if (cell) {
                cell.textContent = letters[i];
                cell.classList.add('filled');
                cell.classList.add(result[i]);
            }
        }
    }

    // aggiorna i colori della tastiera virtuale
    function updateKeyboard(word, result) {
        const letters = word.split('');

        for (let i = 0; i < letters.length; i++) {
            const letter = letters[i];
            const key = document.getElementById('key-' + letter);
            if (!key) continue;

            // prendo lo stato corrente dall'attributo della classe
            const isGreen = key.classList.contains('green');
            const isYellow = key.classList.contains('yellow');
            const newState = result[i];

            // priorità: verde > giallo > grigio
            if (newState === 'green') {
                key.classList.remove('yellow', 'gray');
                key.classList.add('green');
            } else if (newState === 'yellow' && !isGreen) {
                key.classList.remove('gray');
                key.classList.add('yellow');
            } else if (newState === 'gray' && !isGreen && !isYellow) {
                key.classList.add('gray');
            }
        }
    }

    // aggiunge una lettera
    function addLetter(lettera) {
        if (gameState.gameOver) return;
        if (gameState.currentGuess.length >= gameState.wordLength) return;

        gameState.currentGuess += lettera;

        const cell = getCell(gameState.currentRow, gameState.currentCol);

        if (cell) {
            cell.textContent = lettera;
            cell.classList.add('filled');
            gameState.currentCol++;
        }
    }

    // rimuove l'ultima lettera
    function removeLetter() {
        if (gameState.gameOver) return;
        if (gameState.currentGuess.length === 0) return;

        gameState.currentGuess = gameState.currentGuess.slice(0, -1);
        gameState.currentCol--;

        const cell = getCell(gameState.currentRow, gameState.currentCol);

        if (cell) {
            cell.textContent = '';
            cell.classList.remove('filled');
        }
    }

    // invia il tentativo al server
    async function submitGuess() {
        if (gameState.gameOver) return;
        // console.log("Submitting:", gameState.currentGuess); // debug

        try {
            const response = await fetch('api/submit_guess.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    game_id: gameState.gameId,
                    guess: gameState.currentGuess
                })
            });

            const data = await response.json();
            // console.log("Response:", data); // debug

            if (data.error) {
                showMessage(data.error, 'error', 3000);
                return;
            }

            if (!data.valid) {
                showMessage(data.error || 'Parola non valida', 'error', 3000);
                return;
            }

            // se è timeout, salvo la soluzione e basta (no animazioni)
            if (data.timeout) {
                gameState.solution = data.solution;
                return;
            }

            // animo le celle con i colori
            await animateResult(gameState.currentRow, gameState.currentGuess, data.result);
            updateKeyboard(gameState.currentGuess, data.result);

            if (data.won) {
                endGame(true);
            } else if (data.game_over && gameState.timer && gameState.timeLeft <= 0){
                // timeout: la soluzione viene salvata qui, endGame viene chiamato dal timer
                gameState.solution = data.solution;
                return;
            } else if (data.game_over) {
                gameState.solution = data.solution;
                endGame(false);
            } else {
                gameState.currentRow++;
                gameState.currentCol = 0;
                gameState.currentGuess = '';
            }

        } catch (error) {
            console.error('Errore:', error);
            showMessage('Errore di connessione', 'error');
        }
    }

    // anima il risultato di un tentativo
    async function animateResult(rowIndex, word, result) {
        for (let i = 0; i < result.length; i++) {
            const cell = getCell(rowIndex, i);

            // aspetto un po' tra ogni cella per l'effetto
            await new Promise(function(resolve) {
                setTimeout(resolve, 200);
            });

            cell.classList.add(result[i]);
        }

        // aspetto che finisca l'animazione
        await new Promise(function(resolve) {
            setTimeout(resolve, 300);
        });
    }

    // fine della partita
    function endGame(won, customMessage) {
        gameState.gameOver = true;
        gameState.won = won;

        // fermo il timer se c'è
        if (gameState.timerInterval) {
            clearInterval(gameState.timerInterval);
        }

        disableInput();

        const params = getUrlParams();

        // se è la parola del giorno mostra solo il mosaico
        if(params.mode === 'daily'){
            playAgainBtn.textContent = "Mostra il mosaico";
        }

        // mostro il modal dopo un po'
        setTimeout(function() {
            if (won) {
                modalTitle.textContent = 'Complimenti!';
                if (customMessage) {
                    modalMessage.textContent = customMessage;
                } else {
                    modalMessage.textContent = 'Hai indovinato in ' + (gameState.currentRow + 1) + ' tentativi!';
                }
            } else {
                modalTitle.textContent = 'Peccato!';
                modalMessage.textContent = customMessage || 'Non hai indovinato la parola.';
                modalSolution.textContent = 'la parola era: ' +  gameState.solution + ' ';
            }
            modal.classList.remove('hidden');
        }, 1000);
    }

    // disabilita la tastiera
    function disableInput() {
        const keys = keyboard.querySelectorAll('.key');
        for (let i = 0; i < keys.length; i++) {
            keys[i].disabled = true;
        }
    }

    // event listener per la tastiera virtuale
    keyboard.addEventListener('click', function(e) {
        const key = e.target.closest('.key');
        if (!key || key.disabled) return;

        // prendo la lettera dall'id (es: "key-A" -> "A")
        const keyId = key.id;
        const keyValue = keyId.split('-')[1];

        if (keyValue === 'ENTER') {
            submitGuess();
        } else if (keyValue === 'BACKSPACE') {
            removeLetter();
        } else {
            addLetter(keyValue);
        }
    });

    // event listener per la tastiera fisica
    document.addEventListener('keydown', function(e) {
        if (gameState.gameOver) return;
        if (e.key === 'Enter') {
            submitGuess();
        } else if (e.key === 'Backspace') {
            removeLetter();
        } else if (/^[a-zA-Z]$/.test(e.key)) {
            addLetter(e.key.toUpperCase());
        }
    });

    // bottone gioca ancora
    playAgainBtn.addEventListener('click', function() {
        const params = getUrlParams();
        if (params.mode === 'daily') {
            // parola del giorno - non è possibile giocare ancora
            modal.classList.add("hidden");
        } else {
            // custom mode - ricarico per nuova partita
            window.location.reload();
        }
    });

    HomeBtn.addEventListener('click', function() {
        window.location.href = 'index.php';
    })

    // avvio il gioco
    loadGame();
});
