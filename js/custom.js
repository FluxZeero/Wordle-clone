document.addEventListener('DOMContentLoaded', function() {

    const form = document.getElementById('custom-form');
    const attemptsSlider = document.getElementById('attempts');
    const attemptsValue = document.getElementById('attempts-value');
    const timerToggle = document.getElementById('timer-toggle');
    const timerOptions = document.getElementById('timer-options');
    const timerSlider = document.getElementById('timer');
    const timerValue = document.getElementById('timer-value');

    // aggiorna valore tentativi
    attemptsSlider.addEventListener('input', function() {
        attemptsValue.textContent = this.value;
    });

    // toggle timer
    timerToggle.addEventListener('change', function() {
        timerOptions.classList.toggle('hidden', !this.checked);
    });

    // aggiorna valore timer
    timerSlider.addEventListener('input', function() {
        const minutes = Math.floor(this.value / 60);
        const seconds = this.value % 60;
        timerValue.textContent = `${minutes}:${seconds.toString().padStart(2, '0')}`;
    });


    // submit form
    form.addEventListener('submit', function(e) {
        e.preventDefault();

        const wordLength = document.querySelector('input[name="word-length"]:checked').value;
        const attempts = attemptsSlider.value;
        const useTimer = timerToggle.checked;
        const timer = useTimer ? timerSlider.value : null;

        let url = `game.php?mode=custom&len=${wordLength}&tries=${attempts}`;
        if(timer) {
            url += `&timer=${timer}`;
        }

        window.location.href = url;
    });

});
