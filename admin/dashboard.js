document.addEventListener("DOMContentLoaded", function () {
    if (typeof Chart === "undefined" || typeof trendLabels === "undefined") return;

    const wine = "#5c1a2e";
    const bark = "#3a2620";
    const paletteFill = ["#5c1a2e", "#b5654a", "#c8a15c", "#8a7b74", "#3a2620", "#e6b8ab"];

    /* ---------- Revenue trend line chart ---------- */
    const revenueCanvas = document.getElementById("revenueChart");
    if (revenueCanvas) {
        new Chart(revenueCanvas, {
            type: "line",
            data: {
                labels: trendLabels,
                datasets: [{
                    label: "Revenue (Rs.)",
                    data: trendValues,
                    borderColor: wine,
                    backgroundColor: "rgba(92,26,46,0.08)",
                    fill: true,
                    tension: 0.35,
                    pointBackgroundColor: wine,
                    pointRadius: 4,
                    borderWidth: 2.5
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, grid: { color: "#ece3d8" }, ticks: { color: bark, font: { family: "Jost" } } },
                    x: { grid: { display: false }, ticks: { color: bark, font: { family: "Jost" } } }
                }
            }
        });
    }

    /* ---------- Category split donut chart ---------- */
    const categoryCanvas = document.getElementById("categoryChart");
    if (categoryCanvas) {
        new Chart(categoryCanvas, {
            type: "doughnut",
            data: {
                labels: catLabels,
                datasets: [{
                    data: catValues,
                    backgroundColor: paletteFill,
                    borderColor: "#faf6ef",
                    borderWidth: 3
                }]
            },
            options: {
                responsive: true,
                cutout: "62%",
                plugins: {
                    legend: {
                        position: "bottom",
                        labels: { color: bark, font: { family: "Jost", size: 11 }, boxWidth: 10, padding: 14 }
                    }
                }
            }
        });
    }
});