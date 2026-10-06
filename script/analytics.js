document.addEventListener("DOMContentLoaded", function () {
    fetchAnalyticsData();

    function fetchAnalyticsData() {
        const wasteType = document.getElementById("wasteTypeFilter").value;
        const dateRange = document.getElementById("dateRange").value;

        fetch(`fetch_analytics.php?waste_type=${wasteType}&date_range=${dateRange}`)
            .then(response => response.json())
            .then(data => {
                updateCharts(data);
            })
            .catch(error => console.error("Error fetching data:", error));
    }

    function updateCharts(data) {
        const wasteLabels = data.wasteData.map(item => item.waste_type);
        const wasteValues = data.wasteData.map(item => item.total_amount);
        updateChart(wasteDistributionChart, wasteLabels, wasteValues, "Waste Distribution");

        const monthLabels = data.monthlyData.map(item => item.month);
        const monthValues = data.monthlyData.map(item => item.total_amount);
        updateChart(monthlyTrendsChart, monthLabels, monthValues, "Monthly Collection Trends");
    }

    function updateChart(chart, labels, data, label) {
        chart.data.labels = labels;
        chart.data.datasets[0].data = data;
        chart.data.datasets[0].label = label;
        chart.update();
    }

    const wasteDistributionChart = new Chart(document.getElementById("wasteDistributionChart"), {
        type: "pie",
        data: {
            labels: [],
            datasets: [{
                data: [],
                backgroundColor: ["#ff6384", "#36a2eb", "#ffce56"], // Different colors for each waste type
            }],
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'top',
                },
                tooltip: {
                    callbacks: {
                        label: function(tooltipItem) {
                            return tooltipItem.label + ': ' + tooltipItem.raw + ' kg'; // Show total amount in tooltip
                        }
                    }
                }
            }
        }
    });

    const monthlyTrendsChart = new Chart(document.getElementById("monthlyTrendsChart"), {
        type: "line",
        data: {
            labels: [],
            datasets: [{
                data: [],
                backgroundColor: "#36a2eb",
                borderColor: "#36a2eb",
                fill: false,
                label: "Monthly Trends"
            }],
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'top',
                },
                tooltip: {
                    callbacks: {
                        label: function(tooltipItem) {
                            return tooltipItem.raw + ' kg'; // Show total amount in tooltip
                        }
                    }
                }
            },
            scales: {
                x: {
                    title: {
                        display: true,
                        text: 'Month'
                    }
                },
                y: {
                    title: {
                        display: true,
                        text: 'Amount (kg)'
                    },
                    beginAtZero: true
                }
            }
        }
    });
});
