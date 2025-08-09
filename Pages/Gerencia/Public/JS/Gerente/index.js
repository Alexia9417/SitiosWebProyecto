document.addEventListener('DOMContentLoaded', () => {
    const currentTimeSpan = document.getElementById('current-time');

    function updateTime() {
        const now = new Date();
        const hours = String(now.getHours()).padStart(2, '0');
        const minutes = String(now.getMinutes()).padStart(2, '0');
        const seconds = String(now.getSeconds()).padStart(2, '0');
        currentTimeSpan.textContent = `${hours}:${minutes}:${seconds}`;
    }

    // Update time immediately and then every second
    updateTime();
    setInterval(updateTime, 1000);
});