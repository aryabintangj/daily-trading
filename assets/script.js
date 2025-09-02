// Tampilkan notifikasi dari parameter URL (?msg=...)
document.addEventListener("DOMContentLoaded", function () {
    const urlParams = new URLSearchParams(window.location.search);
    const msg = urlParams.get("msg");

    if (msg) {
        let alertBox = document.createElement("div");
        alertBox.className = "alert alert-info alert-dismissible fade show mt-2";
        alertBox.innerHTML = `
            <strong>Info:</strong> ${msg}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        document.querySelector(".container").prepend(alertBox);

        // Auto close setelah 4 detik
        setTimeout(() => {
            let alert = bootstrap.Alert.getOrCreateInstance(alertBox);
            alert.close();
        }, 4000);
    }
});

// Konfirmasi hapus
function confirmDelete(url) {
    if (confirm("Yakin mau hapus trade ini?")) {
        window.location.href = url;
    }
}
