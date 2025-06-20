
const ctx = document.getElementById('uptimeChart').getContext('2d');
new Chart(ctx, {
    type: 'bar',
    data: {
        labels: labels,
        datasets: [{
            label: 'Erfolgreiche dynv6 Updates',
            data: data,
            backgroundColor: 'rgba(75, 192, 192, 0.7)',
            borderRadius: 4,
        }]
    },
    options: {
        responsive: true,
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    precision: 0,
                    stepSize: 1
                }
            }
        }
    }
});
