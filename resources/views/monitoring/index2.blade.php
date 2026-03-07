<!DOCTYPE html>
<html lang="id" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Water Quality Enterprise</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .pagination-button {
            @apply px-3 py-1 rounded-lg bg-slate-700 text-slate-300 hover:bg-slate-600 transition disabled:opacity-50 disabled:cursor-not-allowed;
        }
        .pagination-info {
            @apply text-sm text-slate-400;
        }
        .card {
            @apply bg-gradient-to-br from-slate-800 to-slate-900 p-6 rounded-2xl shadow-lg border border-slate-700 hover:border-blue-500/50 transition-all duration-300;
        }
        .value-badge {
            @apply text-xs px-3 py-1 rounded-full font-medium;
        }
        .tooltip-icon {
            @apply w-8 h-8 rounded-full bg-slate-700/50 flex items-center justify-center text-slate-400 hover:text-blue-400 hover:bg-slate-700 transition-all cursor-help;
        }
        .sensor-value {
            @apply text-3xl font-bold text-white font-mono;
        }
        .sensor-unit {
            @apply text-xs text-slate-500 ml-1;
        }
        .bg-topography {
            /* Warna background dasar (sesuaikan dengan desain Hero Patterns-mu) */
            background-color: #0f172b; 
            
            /* Panggil file SVG yang sudah dipindah ke public */
            background-image: url("{{ asset('assets/img/topography.svg') }}");
            
            /* Pengaturan agar polanya rapi */
            background-attachment: fixed;
            background-repeat: repeat;
        }
    </style>
</head>
<body class="bg-topography text-slate-200 font-sans">

<div class="max-w-7xl mx-auto px-4 py-6">

    <!-- HEADER with Glassmorphism effect -->
    <div class="flex flex-col lg:flex-row justify-between gap-6 mb-8 bg-slate-800/50 backdrop-blur-sm p-6 rounded-2xl border border-slate-700">
        <div class="flex items-center gap-4">
            <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center shadow-lg">
                <i class="fa-solid fa-droplet text-white text-2xl"></i>
            </div>
            <div>
                <h1 class="text-3xl font-bold bg-gradient-to-r from-blue-400 to-purple-400 bg-clip-text text-transparent">
                    Water Quality Enterprise
                </h1>
                <p class="text-sm text-slate-400 mt-1 flex items-center gap-2">
                    <i class="fa-regular fa-clock"></i>
                    <span>Last Update:</span>
                    <span id="live-clock" class="font-semibold text-blue-400"></span>
                    <span class="text-xs bg-blue-500/20 px-2 py-0.5 rounded-full">WIB</span>
                </p>
            </div>
        </div>

        <div class="flex flex-wrap gap-3 items-center">
            <div class="flex items-center gap-2 bg-slate-700/50 p-2 rounded-xl">
                <i class="fa-regular fa-calendar text-slate-400"></i>
                <input type="date" id="startDate"
                    class="bg-transparent border-none text-sm text-slate-300 focus:outline-none w-32">
            </div>
            <div class="flex items-center gap-2 bg-slate-700/50 p-2 rounded-xl">
                <i class="fa-regular fa-calendar text-slate-400"></i>
                <input type="date" id="endDate"
                    class="bg-transparent border-none text-sm text-slate-300 focus:outline-none w-32">
            </div>
            <button onclick="filterLogs()"
                class="px-4 py-2 text-sm rounded-xl bg-blue-600 hover:bg-blue-700 text-white transition flex items-center gap-2">
                <i class="fa-solid fa-filter"></i>
                Filter
            </button>
            <button onclick="exportExcel()"
                class="px-4 py-2 text-sm rounded-xl bg-green-600 hover:bg-green-700 text-white transition flex items-center gap-2">
                <i class="fa-solid fa-file-excel"></i>
                Excel
            </button>
            <button onclick="exportPDF()"
                class="px-4 py-2 text-sm rounded-xl bg-red-600 hover:bg-red-700 text-white transition flex items-center gap-2">
                <i class="fa-solid fa-file-pdf"></i>
                PDF
            </button>
        </div>
    </div>

    <!-- CARDS with Icons and Detailed Info -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <!-- pH Card -->
        <div class="bg-gradient-to-br from-slate-800 to-slate-900 p-6 rounded-2xl shadow-lg border-2 border-purple-500/30 hover:border-purple-500 transition-all duration-300 group">
            <div class="flex items-start justify-between mb-4">
                <div class="flex items-center gap-3">
                    <div class="w-14 h-14 rounded-xl bg-gradient-to-br from-purple-500 to-purple-600 flex items-center justify-center group-hover:scale-110 transition-transform shadow-lg">
                        <i class="fa-solid fa-flask text-white text-xl"></i>
                    </div>
                    <div>
                        <p class="text-sm text-slate-400 flex items-center gap-1">
                            <i class="fa-solid fa-circle-info text-xs text-slate-500"></i>
                            pH Level
                        </p>
                        <div class="flex items-baseline">
                            <h3 id="ph-val" class="text-3xl font-bold text-white font-mono">--</h3>
                            <span class="text-xs text-slate-500 ml-1">pH</span>
                        </div>
                    </div>
                </div>
                <div class="relative">
                    <div class="w-8 h-8 rounded-full bg-slate-700/50 flex items-center justify-center text-slate-400 hover:text-purple-400 hover:bg-slate-700 transition-all cursor-help group/tooltip">
                        <i class="fa-solid fa-circle-info"></i>
                        <div class="absolute hidden group-hover/tooltip:block right-0 mt-2 w-72 text-xs bg-slate-900 text-white p-4 rounded-xl shadow-xl z-50 border-2 border-purple-500/30">
                            <div class="font-semibold text-purple-400 mb-3 flex items-center gap-2">
                                <i class="fa-solid fa-circle-info"></i>
                                Informasi pH Level
                            </div>
                            <ul class="space-y-2">
                                <li class="flex items-center gap-2">
                                    <span class="w-2 h-2 rounded-full bg-green-500"></span>
                                    <span><strong class="text-green-400">LAYAK:</strong> 6.5 - 8.5 (Ideal)</span>
                                </li>
                                <li class="flex items-center gap-2">
                                    <span class="w-2 h-2 rounded-full bg-yellow-500"></span>
                                    <span><strong class="text-yellow-400">WASPADA:</strong> 5.5 - 6.4 atau 8.6 - 9.0</span>
                                </li>
                                <li class="flex items-center gap-2">
                                    <span class="w-2 h-2 rounded-full bg-red-500"></span>
                                    <span><strong class="text-red-400">TIDAK LAYAK:</strong> < 5.5 atau > 9.0</span>
                                </li>
                            </ul>
                            <div class="mt-3 text-xs text-slate-500 border-t border-purple-500/30 pt-2">
                                <i class="fa-regular fa-bell mr-1"></i> Nilai ideal untuk air minum
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="flex items-center justify-between mt-2 pt-3 border-t-2 border-purple-500/30">
                <span id="ph-badge" class="inline-block px-3 py-1.5 text-xs font-semibold rounded-lg shadow-sm"></span>
                <span class="text-xs text-slate-500 flex items-center gap-1.5 bg-slate-800/50 px-2 py-1 rounded-md">
                    <i class="fa-regular fa-clock"></i>
                    Real-time
                </span>
            </div>
        </div>

        <!-- TDS Card -->
        <div class="bg-gradient-to-br from-slate-800 to-slate-900 p-6 rounded-2xl shadow-lg border-2 border-blue-500/30 hover:border-blue-500 transition-all duration-300 group">
            <div class="flex items-start justify-between mb-4">
                <div class="flex items-center gap-3">
                    <div class="w-14 h-14 rounded-xl bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center group-hover:scale-110 transition-transform shadow-lg">
                        <i class="fa-solid fa-water text-white text-xl"></i>
                    </div>
                    <div>
                        <p class="text-sm text-slate-400 flex items-center gap-1">
                            <i class="fa-solid fa-circle-info text-xs text-slate-500"></i>
                            TDS
                        </p>
                        <div class="flex items-baseline">
                            <h3 id="tds-val" class="text-3xl font-bold text-white font-mono">--</h3>
                            <span class="text-xs text-slate-500 ml-1">ppm</span>
                        </div>
                    </div>
                </div>
                <div class="relative">
                    <div class="w-8 h-8 rounded-full bg-slate-700/50 flex items-center justify-center text-slate-400 hover:text-blue-400 hover:bg-slate-700 transition-all cursor-help group/tooltip">
                        <i class="fa-solid fa-circle-info"></i>
                        <div class="absolute hidden group-hover/tooltip:block right-0 mt-2 w-72 text-xs bg-slate-900 text-white p-4 rounded-xl shadow-xl z-50 border-2 border-blue-500/30">
                            <div class="font-semibold text-blue-400 mb-3 flex items-center gap-2">
                                <i class="fa-solid fa-circle-info"></i>
                                Informasi TDS
                            </div>
                            <ul class="space-y-2">
                                <li class="flex items-center gap-2">
                                    <span class="w-2 h-2 rounded-full bg-green-500"></span>
                                    <span><strong class="text-green-400">LAYAK:</strong> < 300 ppm (Sangat Baik)</span>
                                </li>
                                <li class="flex items-center gap-2">
                                    <span class="w-2 h-2 rounded-full bg-yellow-500"></span>
                                    <span><strong class="text-yellow-400">WASPADA:</strong> 300 - 600 ppm</span>
                                </li>
                                <li class="flex items-center gap-2">
                                    <span class="w-2 h-2 rounded-full bg-red-500"></span>
                                    <span><strong class="text-red-400">TIDAK LAYAK:</strong> > 600 ppm</span>
                                </li>
                            </ul>
                            <div class="mt-3 text-xs text-slate-500 border-t border-blue-500/30 pt-2">
                                <i class="fa-regular fa-bell mr-1"></i> Total Dissolved Solids
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="flex items-center justify-between mt-2 pt-3 border-t-2 border-blue-500/30">
                <span id="tds-badge" class="inline-block px-3 py-1.5 text-xs font-semibold rounded-lg shadow-sm"></span>
                <span class="text-xs text-slate-500 flex items-center gap-1.5 bg-slate-800/50 px-2 py-1 rounded-md">
                    <i class="fa-regular fa-clock"></i>
                    Real-time
                </span>
            </div>
        </div>

        <!-- Turbidity Card -->
        <div class="bg-gradient-to-br from-slate-800 to-slate-900 p-6 rounded-2xl shadow-lg border-2 border-orange-500/30 hover:border-orange-500 transition-all duration-300 group">
            <div class="flex items-start justify-between mb-4">
                <div class="flex items-center gap-3">
                    <div class="w-14 h-14 rounded-xl bg-gradient-to-br from-orange-500 to-orange-600 flex items-center justify-center group-hover:scale-110 transition-transform shadow-lg">
                        <i class="fa-solid fa-cloud-rain text-white text-xl"></i>
                    </div>
                    <div>
                        <p class="text-sm text-slate-400 flex items-center gap-1">
                            <i class="fa-solid fa-circle-info text-xs text-slate-500"></i>
                            Turbidity
                        </p>
                        <div class="flex items-baseline">
                            <h3 id="turb-val" class="text-3xl font-bold text-white font-mono">--</h3>
                            <span class="text-xs text-slate-500 ml-1">NTU</span>
                        </div>
                    </div>
                </div>
                <div class="relative">
                    <div class="w-8 h-8 rounded-full bg-slate-700/50 flex items-center justify-center text-slate-400 hover:text-orange-400 hover:bg-slate-700 transition-all cursor-help group/tooltip">
                        <i class="fa-solid fa-circle-info"></i>
                        <div class="absolute hidden group-hover/tooltip:block right-0 mt-2 w-72 text-xs bg-slate-900 text-white p-4 rounded-xl shadow-xl z-50 border-2 border-orange-500/30">
                            <div class="font-semibold text-orange-400 mb-3 flex items-center gap-2">
                                <i class="fa-solid fa-circle-info"></i>
                                Informasi Turbidity
                            </div>
                            <ul class="space-y-2">
                                <li class="flex items-center gap-2">
                                    <span class="w-2 h-2 rounded-full bg-green-500"></span>
                                    <span><strong class="text-green-400">LAYAK:</strong> < 5 NTU (Jernih)</span>
                                </li>
                                <li class="flex items-center gap-2">
                                    <span class="w-2 h-2 rounded-full bg-yellow-500"></span>
                                    <span><strong class="text-yellow-400">WASPADA:</strong> 5 - 10 NTU (Sedikit Keruh)</span>
                                </li>
                                <li class="flex items-center gap-2">
                                    <span class="w-2 h-2 rounded-full bg-red-500"></span>
                                    <span><strong class="text-red-400">TIDAK LAYAK:</strong> > 10 NTU (Keruh)</span>
                                </li>
                            </ul>
                            <div class="mt-3 text-xs text-slate-500 border-t border-orange-500/30 pt-2">
                                <i class="fa-regular fa-bell mr-1"></i> Tingkat kekeruhan air
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="flex items-center justify-between mt-2 pt-3 border-t-2 border-orange-500/30">
                <span id="turb-badge" class="inline-block px-3 py-1.5 text-xs font-semibold rounded-lg shadow-sm"></span>
                <span class="text-xs text-slate-500 flex items-center gap-1.5 bg-slate-800/50 px-2 py-1 rounded-md">
                    <i class="fa-regular fa-clock"></i>
                    Real-time
                </span>
            </div>
        </div>
    </div>

    <!-- CHART with better styling -->
    <div class="bg-gradient-to-br from-slate-800 to-slate-900 p-6 rounded-2xl shadow-lg mb-8 border border-slate-700">
        <div class="flex items-center gap-3 mb-4">
            <div class="w-8 h-8 rounded-lg bg-blue-500/20 flex items-center justify-center">
                <i class="fa-solid fa-chart-line text-blue-400"></i>
            </div>
            <h2 class="text-lg font-semibold text-white">Grafik Pemantauan Kualitas Air</h2>
            <span class="text-xs bg-slate-700 px-2 py-1 rounded-full text-slate-400">10 data terakhir</span>
        </div>
        <div class="h-80">
            <canvas id="waterChart"></canvas>
        </div>
    </div>

    <!-- TABLE with better styling -->
    <div class="bg-gradient-to-br from-slate-800 to-slate-900 rounded-2xl shadow-lg border border-slate-700 overflow-hidden">
        <div class="p-4 border-b border-slate-700 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-purple-500/20 flex items-center justify-center">
                    <i class="fa-solid fa-table text-purple-400"></i>
                </div>
                <h2 class="text-lg font-semibold text-white">Riwayat Data</h2>
            </div>
            <span class="text-sm text-slate-400 flex items-center gap-2" id="totalEntries">
                <i class="fa-regular fa-hard-drive"></i>
                Total: 0 entri
            </span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-700/50 text-slate-300">
                    <tr>
                        <th class="px-4 py-3 text-left">
                            <i class="fa-regular fa-calendar mr-2"></i>Timestamp (WIB)
                        </th>
                        <th class="px-4 py-3 text-center">
                            <i class="fa-solid fa-flask mr-2"></i>pH
                        </th>
                        <th class="px-4 py-3 text-center">
                            <i class="fa-solid fa-water mr-2"></i>TDS (ppm)
                        </th>
                        <th class="px-4 py-3 text-center">
                            <i class="fa-solid fa-cloud-rain mr-2"></i>Turbidity (NTU)
                        </th>
                        <th class="px-4 py-3 text-center">
                            <i class="fa-solid fa-circle-check mr-2"></i>Status
                        </th>
                    </tr>
                </thead>
                <tbody id="logsTable" class="divide-y divide-slate-700"></tbody>
            </table>
        </div>
        
        <!-- Pagination Controls -->
        <div class="flex items-center justify-between px-4 py-3 bg-slate-700/30">
            <div class="pagination-info flex items-center gap-2">
                <i class="fa-regular fa-eye"></i>
                Showing <span id="pageStart">1</span> to <span id="pageEnd">10</span> of <span id="totalItems">0</span> entries
            </div>
            <div class="flex gap-2">
                <button id="prevPage" onclick="changePage(currentPage - 1)" class="pagination-button" disabled>
                    <i class="fa-solid fa-chevron-left mr-1"></i>Previous
                </button>
                <span class="px-3 py-1 text-slate-300 bg-slate-700 rounded-lg">
                    <span id="currentPageDisplay">1</span>/<span id="totalPages">1</span>
                </span>
                <button id="nextPage" onclick="changePage(currentPage + 1)" class="pagination-button" disabled>
                    Next<i class="fa-solid fa-chevron-right ml-1"></i>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    lucide.createIcons();

    // Global variables
    let chart;
    let logsData = [];
    let filteredLogs = [];
    let currentPage = 1;
    const itemsPerPage = 10;

    // Live clock update WIB
    function updateClock() {
        const now = new Date();
        const options = { 
            timeZone: 'Asia/Jakarta',
            weekday: 'long',
            year: 'numeric',
            month: 'long', 
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit',
            hour12: false
        };
        document.getElementById('live-clock').innerText = 
            now.toLocaleString('id-ID', options);
    }
    updateClock();
    setInterval(updateClock, 1000);

    // Status badge function dengan detail angka
    function getStatusBadge(value, type) {
        if (type === 'tds') {
            if (value < 300) return ['LAYAK', 'bg-green-600', 'Sangat Baik'];
            if (value < 600) return ['WASPADA', 'bg-yellow-500', 'Perlu Perhatian'];
            return ['TIDAK LAYAK', 'bg-red-600', 'Berbahaya'];
        }
        
        if (type === 'ph') {
            if (value >= 6.5 && value <= 8.5) return ['LAYAK', 'bg-green-600', 'Ideal'];
            if (value >= 5.5 && value <= 9.0) return ['WASPADA', 'bg-yellow-500', 'Terindikasi'];
            return ['TIDAK LAYAK', 'bg-red-600', 'Kritis'];
        }
        
        if (type === 'turb') {
            if (value < 5) return ['LAYAK', 'bg-green-600', 'Jernih'];
            if (value < 10) return ['WASPADA', 'bg-yellow-500', 'Sedikit Keruh'];
            return ['TIDAK LAYAK', 'bg-red-600', 'Keruh'];
        }
    }

    function setBadge(id, status) {
        const el = document.getElementById(id);
        if (el) {
            el.innerText = status[0];
            // Menghapus class bawaan dan menambahkan yang baru
            el.className = `inline-block px-3 py-1.5 text-xs font-semibold rounded-lg shadow-sm text-white ${status[1]}`;
        }
    }

    // Refresh current data
    async function refreshData() {
        try {
            const res = await fetch("{{ url('/monitoring/data') }}");
            const data = await res.json();

            if (data.current) {
                // Format dengan 2 desimal untuk pH dan Turbidity, 2 desimal untuk TDS juga
                const phValue = data.current.ph !== undefined ? parseFloat(data.current.ph).toFixed(2) : '0.00';
                const tdsValue = data.current.tds !== undefined ? parseFloat(data.current.tds).toFixed(2) : '0.00';
                const turbValue = data.current.turbidity !== undefined ? parseFloat(data.current.turbidity).toFixed(2) : '0.00';

                document.getElementById('ph-val').innerText = phValue;
                document.getElementById('tds-val').innerText = tdsValue;
                document.getElementById('turb-val').innerText = turbValue;

                setBadge('ph-badge', getStatusBadge(parseFloat(phValue), 'ph'));
                setBadge('tds-badge', getStatusBadge(parseFloat(tdsValue), 'tds'));
                setBadge('turb-badge', getStatusBadge(parseFloat(turbValue), 'turb'));
            }

            updateChart(data);
        } catch (error) {
            console.error('Error refreshing data:', error);
        }
    }

    // Update chart
    function updateChart(data) {
        const ctx = document.getElementById('waterChart');
        if (!ctx) return;

        const chartData = {
            labels: data.labels || [],
            datasets: [
                { 
                    label: 'pH', 
                    data: data.ph || [], 
                    borderColor: '#8b5cf6', 
                    backgroundColor: 'rgba(139, 92, 246, 0.1)',
                    tension: 0.4,
                    pointBackgroundColor: '#8b5cf6',
                    borderWidth: 2
                },
                { 
                    label: 'TDS (ppm)', 
                    data: data.tds || [], 
                    borderColor: '#3b82f6', 
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    tension: 0.4,
                    pointBackgroundColor: '#3b82f6',
                    borderWidth: 2,
                    yAxisID: 'y1'
                },
                { 
                    label: 'Turbidity (NTU)', 
                    data: data.turbidity || [], 
                    borderColor: '#f59e0b', 
                    backgroundColor: 'rgba(245, 158, 11, 0.1)',
                    tension: 0.4,
                    pointBackgroundColor: '#f59e0b',
                    borderWidth: 2
                }
            ]
        };

        if (!chart) {
            chart = new Chart(ctx, {
                type: 'line',
                data: chartData,
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        mode: 'index',
                        intersect: false,
                    },
                    plugins: {
                        legend: { 
                            labels: { color: '#e2e8f0', usePointStyle: true } 
                        },
                        tooltip: {
                            backgroundColor: '#1e293b',
                            titleColor: '#e2e8f0',
                            bodyColor: '#94a3b8',
                            borderColor: '#334155',
                            borderWidth: 1,
                            callbacks: {
                                label: function(context) {
                                    let label = context.dataset.label || '';
                                    let value = context.raw || 0;
                                    return `${label}: ${value.toFixed(2)}`;
                                }
                            }
                        }
                    },
                    scales: {
                        y: { 
                            type: 'linear',
                            display: true,
                            position: 'left',
                            grid: { color: '#334155' }, 
                            ticks: { color: '#94a3b8' },
                            title: {
                                display: true,
                                text: 'pH / Turbidity (NTU)',
                                color: '#94a3b8'
                            }
                        },
                        y1: {
                            type: 'linear',
                            display: true,
                            position: 'right',
                            grid: { drawOnChartArea: false },
                            ticks: { color: '#94a3b8' },
                            title: {
                                display: true,
                                text: 'TDS (ppm)',
                                color: '#94a3b8'
                            }
                        },
                        x: { 
                            grid: { color: '#334155' }, 
                            ticks: { color: '#94a3b8' } 
                        }
                    }
                }
            });
        } else {
            chart.data.labels = data.labels;
            chart.data.datasets[0].data = data.ph;
            chart.data.datasets[1].data = data.tds;
            chart.data.datasets[2].data = data.turbidity;
            chart.update();
        }
    }

    // Load logs
    async function loadLogs() {
        try {
            const res = await fetch("{{ url('/monitoring/logs') }}");
            const result = await res.json();
            
            logsData = (result.data || []).filter(log => {
                return log.timestamp && log.timestamp !== '-' && !log.timestamp.includes('1970');
            });
            
            filteredLogs = [...logsData];
            
            // **TAMBAHKAN INFORMASI LIMIT**
            if (result.limited) {
                document.getElementById('totalEntries').innerHTML = `<i class="fa-regular fa-hard-drive"></i> Total: ${logsData.length} entri terbaru <span class="text-xs bg-yellow-500/20 text-yellow-400 px-2 py-0.5 rounded-full">Limit ${result.max_logs}</span>`;
            } else {
                document.getElementById('totalEntries').innerHTML = `<i class="fa-regular fa-hard-drive"></i> Total: ${logsData.length} entri`;
            }
            
            currentPage = 1;
            updatePagination();
            renderTable();
        } catch (error) {
            console.error('Error loading logs:', error);
        }
    }

    // Render table with pagination
    function renderTable() {
        const start = (currentPage - 1) * itemsPerPage;
        const end = start + itemsPerPage;
        const pageData = filteredLogs.slice(start, end);

        let html = '';
        pageData.forEach(log => {
            const phStatus = getStatusBadge(log.ph, 'ph');
            const tdsStatus = getStatusBadge(log.tds, 'tds');
            const turbStatus = getStatusBadge(log.turbidity, 'turb');
            
            // Format dengan 2 desimal untuk semua
            const phDisplay = log.ph.toFixed(2);
            const tdsDisplay = log.tds.toFixed(2);
            const turbDisplay = log.turbidity.toFixed(2);
            
            const overallStatus = [phStatus[0], tdsStatus[0], turbStatus[0]].every(s => s === 'LAYAK') ? 
                '<span class="text-green-400"><i class="fa-solid fa-circle-check mr-1"></i>AMAN</span>' : 
                '<span class="text-yellow-400"><i class="fa-solid fa-triangle-exclamation mr-1"></i>CEK</span>';
            
            html += `
                <tr class="hover:bg-slate-700/30 transition">
                    <td class="px-4 py-3 text-left font-mono text-xs text-slate-300">
                        <i class="fa-regular fa-clock text-slate-500 mr-2"></i>${log.timestamp}
                    </td>
                    <td class="px-4 py-3 text-center">
                        <span class="font-mono font-medium">${phDisplay}</span>
                        <span class="ml-2 px-2 py-0.5 text-xs rounded-full text-white ${phStatus[1]}">${phStatus[0]}</span>
                    </td>
                    <td class="px-4 py-3 text-center">
                        <span class="font-mono font-medium">${tdsDisplay}</span>
                        <span class="ml-2 px-2 py-0.5 text-xs rounded-full text-white ${tdsStatus[1]}">${tdsStatus[0]}</span>
                    </td>
                    <td class="px-4 py-3 text-center">
                        <span class="font-mono font-medium">${turbDisplay}</span>
                        <span class="ml-2 px-2 py-0.5 text-xs rounded-full text-white ${turbStatus[1]}">${turbStatus[0]}</span>
                    </td>
                    <td class="px-4 py-3 text-center font-medium">
                        ${overallStatus}
                    </td>
                </tr>
            `;
        });

        document.getElementById('logsTable').innerHTML = html;
        updatePagination();
    }

    // Update pagination controls
    function updatePagination() {
        const total = filteredLogs.length;
        const totalPages = Math.ceil(total / itemsPerPage) || 1;
        const start = total === 0 ? 0 : (currentPage - 1) * itemsPerPage + 1;
        const end = Math.min(currentPage * itemsPerPage, total);

        document.getElementById('totalItems').innerText = total;
        document.getElementById('totalPages').innerText = totalPages;
        document.getElementById('currentPageDisplay').innerText = currentPage;
        document.getElementById('pageStart').innerText = total === 0 ? 0 : start;
        document.getElementById('pageEnd').innerText = end;

        document.getElementById('prevPage').disabled = currentPage <= 1;
        document.getElementById('nextPage').disabled = currentPage >= totalPages;
    }

    // Change page
    function changePage(page) {
        const totalPages = Math.ceil(filteredLogs.length / itemsPerPage) || 1;
        if (page >= 1 && page <= totalPages) {
            currentPage = page;
            renderTable();
        }
    }

    // Filter logs
    function filterLogs() {
        const startDate = document.getElementById('startDate').value;
        const endDate = document.getElementById('endDate').value;

        if (!startDate && !endDate) {
            filteredLogs = [...logsData];
        } else {
            filteredLogs = logsData.filter(log => {
                // Parse timestamp WIB (format: DD-MM-YYYY HH:MM:SS)
                const parts = log.timestamp.split(' ');
                if (parts.length < 2) return true;
                
                const dateParts = parts[0].split('-');
                if (dateParts.length !== 3) return true;
                
                // Konversi ke YYYY-MM-DD untuk perbandingan
                const logDate = `${dateParts[2]}-${dateParts[1]}-${dateParts[0]}`;
                
                if (startDate && endDate) {
                    return logDate >= startDate && logDate <= endDate;
                } else if (startDate) {
                    return logDate >= startDate;
                } else if (endDate) {
                    return logDate <= endDate;
                }
                return true;
            });
        }

        currentPage = 1;
        renderTable();
    }

    // Export to Excel
    function exportExcel() {
        if (filteredLogs.length === 0) {
            alert('Tidak ada data untuk diexport');
            return;
        }

        const exportData = filteredLogs.map(log => {
            const phStatus = getStatusBadge(log.ph, 'ph');
            const tdsStatus = getStatusBadge(log.tds, 'tds');
            const turbStatus = getStatusBadge(log.turbidity, 'turb');
            
            return {
                'Timestamp (WIB)': log.timestamp,
                'pH': log.ph.toFixed(2),
                'TDS (ppm)': log.tds.toFixed(2),
                'Turbidity (NTU)': log.turbidity.toFixed(2),
                'Status pH': phStatus[0],
                'Status TDS': tdsStatus[0],
                'Status Turbidity': turbStatus[0],
                'Keterangan pH': phStatus[2],
                'Keterangan TDS': tdsStatus[2],
                'Keterangan Turbidity': turbStatus[2]
            };
        });

        const ws = XLSX.utils.json_to_sheet(exportData);
        const wb = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(wb, ws, "Water Quality Logs");
        
        const colWidths = [
            { wch: 22 }, { wch: 10 }, { wch: 12 }, { wch: 15 },
            { wch: 12 }, { wch: 12 }, { wch: 15 }, { wch: 15 }, { wch: 15 }
        ];
        ws['!cols'] = colWidths;

        const now = new Date().toLocaleDateString('id-ID', { 
            timeZone: 'Asia/Jakarta',
            year: 'numeric',
            month: '2-digit',
            day: '2-digit'
        }).replace(/\//g, '-');
        
        const fileName = `water-quality-${now}.xlsx`;
        XLSX.writeFile(wb, fileName);
    }

    // Export to PDF
    function exportPDF() {
        if (filteredLogs.length === 0) {
            alert('Tidak ada data untuk diexport');
            return;
        }

        // Pastikan jsPDF tersedia
        if (typeof window.jspdf === 'undefined') {
            alert('Library PDF tidak tersedia');
            return;
        }

        const { jsPDF } = window.jspdf;
        const doc = new jsPDF({
            orientation: 'landscape',
            unit: 'mm',
            format: 'a4'
        });

        // Title
        doc.setFontSize(18);
        doc.setTextColor(0, 102, 204);
        doc.text('Laporan Kualitas Air', 14, 10);
        
        // Date
        doc.setFontSize(10);
        doc.setTextColor(100);
        const now = new Date().toLocaleDateString('id-ID', { 
            timeZone: 'Asia/Jakarta',
            weekday: 'long',
            year: 'numeric',
            month: 'long',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit'
        });
        doc.text(`Generated: ${now} WIB`, 14, 18);

        // Summary
        doc.setFontSize(9);
        doc.setTextColor(0);
        doc.text(`Total Data: ${filteredLogs.length} entri`, 14, 24);

        // Table headers
        doc.setFontSize(8);
        doc.setFillColor(230, 230, 230);
        
        const headers = ['Timestamp (WIB)', 'pH', 'TDS', 'Turbidity', 'Status'];
        let xPos = 14;
        const colWidths = [45, 20, 20, 25, 25];
        
        headers.forEach((header, index) => {
            doc.rect(xPos - 1, 26, colWidths[index], 8, 'F');
            doc.text(header, xPos, 32);
            xPos += colWidths[index];
        });

        // Table data
        let yPos = 38;
        const displayData = filteredLogs.slice(0, 20); // Max 20 entries untuk PDF
        
        displayData.forEach((log) => {
            if (yPos > 190) {
                doc.addPage();
                yPos = 20;
            }

            xPos = 14;
            
            // Timestamp
            doc.text(log.timestamp, xPos, yPos);
            xPos += colWidths[0];
            
            // pH
            doc.text(log.ph.toFixed(2), xPos, yPos);
            xPos += colWidths[1];
            
            // TDS
            doc.text(log.tds.toFixed(2), xPos, yPos);
            xPos += colWidths[2];
            
            // Turbidity
            doc.text(log.turbidity.toFixed(2), xPos, yPos);
            xPos += colWidths[3];
            
            // Status
            const phStatus = getStatusBadge(log.ph, 'ph')[0];
            const tdsStatus = getStatusBadge(log.tds, 'tds')[0];
            const turbStatus = getStatusBadge(log.turbidity, 'turb')[0];
            const overallStatus = [phStatus, tdsStatus, turbStatus].every(s => s === 'LAYAK') ? 'AMAN' : 'CEK';
            doc.text(overallStatus, xPos, yPos);

            yPos += 6;
        });

        // Footer
        doc.setFontSize(8);
        doc.setTextColor(150);
        if (filteredLogs.length > 20) {
            doc.text(`*Menampilkan 20 dari ${filteredLogs.length} entri`, 14, 280);
        }

        const now2 = new Date().toLocaleDateString('id-ID', { 
            timeZone: 'Asia/Jakarta',
            year: 'numeric',
            month: '2-digit',
            day: '2-digit'
        }).replace(/\//g, '-');
        
        const fileName = `water-quality-${now2}.pdf`;
        doc.save(fileName);
    }

    // Initial load
    refreshData();
    loadLogs();
    
    // Auto refresh every 5 seconds
    setInterval(refreshData, 5000);
</script>

</body>
</html>