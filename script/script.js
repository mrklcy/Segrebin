// Sidebar functionality
const sidebar = document.getElementById('sidebar');
const toggleSidebar = document.getElementById('toggleSidebar');

toggleSidebar.addEventListener('click', () => {
    sidebar.classList.toggle('minimized');
});

// Circular Bin levels visualization
const binData = [
    { name: 'Bin A', level: 75 },
    { name: 'Bin B', level: 50 },
    { name: 'Bin C', level: 90 },
    { name: 'Bin D', level: 30 },
    { name: 'Bin E', level: 60 }
];

const circularBinLevelsContainer = document.getElementById('circularBinLevels');

function createCircularBin(bin) {
    const binDiv = document.createElement('div');
    binDiv.className = 'circular-bin';

    const svg = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
    svg.setAttribute('viewBox', '0 0 36 36');

    const circle = document.createElementNS('http://www.w3.org/2000/svg', 'circle');
    circle.setAttribute('cx', '18');
    circle.setAttribute('cy', '18');
    circle.setAttribute('r', '16');
    circle.setAttribute('fill', 'none');
    circle.setAttribute('stroke', '#e0e0e0');
    circle.setAttribute('stroke-width', '3');

    const path = document.createElementNS('http://www.w3.org/2000/svg', 'path');
    const circumference = 2 * Math.PI * 16;
    const offset = circumference - (bin.level / 100) * circumference;
    path.setAttribute('d', 'M18 2 A16 16 0 0 1 18 34 A16 16 0 0 1 18 2');
    path.setAttribute('fill', 'none');
    path.setAttribute('stroke', '#4CAF50');
    path.setAttribute('stroke-width', '3');
    path.setAttribute('stroke-dasharray', circumference);
    path.setAttribute('stroke-dashoffset', offset);
    path.setAttribute('transform', 'rotate(-90 18 18)');

    const text = document.createElementNS('http://www.w3.org/2000/svg', 'text');
    text.setAttribute('x', '18');
    text.setAttribute('y', '18');
    text.setAttribute('text-anchor', 'middle');
    text.setAttribute('dominant-baseline', 'central');
    text.setAttribute('font-size', '8');
    text.setAttribute('font-weight', 'bold');
    text.textContent = `${bin.level}%`;

    svg.appendChild(circle);
    svg.appendChild(path);
    svg.appendChild(text);

    const binName = document.createElement('div');
    binName.className = 'bin-name';
    binName.textContent = bin.name;

    binDiv.appendChild(svg);
    binDiv.appendChild(binName);

    return binDiv;
}

binData.forEach((bin) => {
    const circularBin = createCircularBin(bin);
    circularBinLevelsContainer.appendChild(circularBin);
});

// Waste collected over time chart
const ctx = document.getElementById('wasteChart').getContext('2d');
new Chart(ctx, {
    type: 'line',
    data: {
        labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
        datasets: [{
            label: 'Waste Collected (tons)',
            data: [300, 450, 400, 500, 550, 500],
            borderColor: '#4CAF50',
            backgroundColor: 'rgba(76, 175, 80, 0.1)',
            tension: 0.4,
            fill: true
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: false
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                grid: {
                    color: 'rgba(0, 0, 0, 0.1)'
                }
            },
            x: {
                grid: {
                    display: false
                }
            }
        }
    }
});

// Recent Bin Updates
const recentUpdates = [
    { bin: 'Bin A', action: 'Emptied', time: '2 hours ago' },
    { bin: 'Bin C', action: 'Filled 80%', time: '4 hours ago' },
    { bin: 'Bin B', action: 'Maintenance', time: '1 day ago' },
    { bin: 'Bin E', action: 'Filled 50%', time: '1 day ago' },
];

const recentUpdatesList = document.getElementById('recentUpdates');
const recentUpdatesHeader = `
    <tr>
        <th>Bin</th>
        <th>Action</th>
        <th>Time</th>
    </tr>
`;
recentUpdatesList.innerHTML = recentUpdatesHeader + recentUpdates.map(update => `
    <tr>
        <td>${update.bin}</td>
        <td>${update.action}</td>
        <td>${update.time}</td>
    </tr>
`).join('');

// Collection Logs
const collectionLogs = [
    { bin: 'Bin A', amount: '100 kg', time: 'Today, 10:00 AM' },
    { bin: 'Bin D', amount: '75 kg', time: 'Yesterday, 2:00 PM' },
    { bin: 'Bin C', amount: '120 kg', time: '2 days ago, 11:30 AM' },
    { bin: 'Bin B', amount: '90 kg', time: '3 days ago, 9:15 AM' },
];

const collectionLogsList = document.getElementById('collectionLogs');
const collectionLogsHeader = `
    <tr>
        <th>Bin</th>
        <th>Amount</th>
        <th>Time</th>
    </tr>
`;
collectionLogsList.innerHTML = collectionLogsHeader + collectionLogs.map(log => `
    <tr>
        <td>${log.bin}</td>
        <td>${log.amount}</td>
        <td>${log.time}</td>
    </tr>
`).join('');

// Map initialization
function initMap() {
    const mapCenter = { lat: 15.8144, lng: 120.8583 };
    const map = new google.maps.Map(document.getElementById('map'), {
        zoom: 13,
        center: mapCenter,
    });

    const binLocations = [
        { lat: 15.8144, lng: 120.8583, title: "Bin 1 - Central" },
        { lat: 15.8244, lng: 120.8600, title: "Bin 2 - East Zone" },
        { lat: 15.8044, lng: 120.8500, title: "Bin 3 - West Zone" },
        { lat: 15.7944, lng: 120.8700, title: "Bin 4 - North Zone" },
    ];

    binLocations.forEach(bin => {
        const marker = new google.maps.Marker({
            position: { lat: bin.lat, lng: bin.lng },
            map: map,
            title: bin.title
        });

        const infoWindow = new google.maps.InfoWindow({
            content: bin.title
        });

        marker.addListener('click', () => {
            infoWindow.open(map, marker);
        });
    });
}

// Call the initMap function when the window has finished loading
window.onload = initMap;