// ===============================
// Challenge Progress Animation
// ===============================
document.addEventListener("DOMContentLoaded", function () {
    const progressBars = document.querySelectorAll(".challenge-progress .progress-bar");

    progressBars.forEach(bar => {
        let target = parseInt(bar.getAttribute("data-progress")) || 0; // Ambil nilai progress dari atribut
        let width = 0;

        let interval = setInterval(() => {
            if (width >= target) {
                clearInterval(interval);
            } else {
                width++;
                bar.style.width = width + "%";
                bar.textContent = width + "%";
            }
        }, 15); // kecepatan animasi
    });
});

// ===============================
// Tandai Challenge Selesai
// ===============================
function markChallengeComplete(id) {
    const challengeCard = document.querySelector(`#challenge-${id}`);
    if (challengeCard) {
        let status = challengeCard.querySelector(".challenge-status");
        if (status) {
            status.textContent = "✅ Completed";
            status.classList.remove("progressing");
            status.classList.add("success");
        }
    }
}
