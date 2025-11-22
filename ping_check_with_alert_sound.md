# This is the source code of pultiple ping status check and alert sound php code 

```bash 

<!-- <!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ping Monitor</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <style>
        body { padding: 20px; }
        .ping-history { white-space: nowrap; }
        .ping-success { color: green; }
        .ping-fail { color: red; }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="mb-4">Ping Monitor</h1>
        <form id="addForm" class="mb-3">
            <div class="input-group">
                <input type="text" id="ipInput" class="form-control" placeholder="Enter IP (e.g., 192.168.1.1 or 8.8.8.8)" required>
                <button type="submit" class="btn btn-primary">Add IP</button>
            </div>
        </form>
        <div class="mb-4">
            <label for="intervalInput" class="form-label">Ping Interval (seconds):</label>
            <div class="input-group" style="max-width: 300px;">
                <input type="number" id="intervalInput" class="form-control" value="5" min="1">
                <button id="setIntervalBtn" class="btn btn-secondary">Set</button>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>IP</th>
                        <th>Current Status</th>
                        <th>Current Latency (ms)</th>
                        <th>Last 5 Pings</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="ipTable"></tbody>
            </table>
        </div>
    </div>

    <script>
        let ips = [];
        let intervalTime = 5000;
        let pingTimer;

        function startPinging() {
            if (pingTimer) clearInterval(pingTimer);
            pingTimer = setInterval(pingAll, intervalTime);
        }

        function setIntervalTime() {
            const input = document.getElementById('intervalInput');
            intervalTime = parseInt(input.value, 10) * 1000;
            if (isNaN(intervalTime) || intervalTime < 1000) {
                intervalTime = 1000;
                input.value = 1;
            }
            startPinging();
        }

        document.getElementById('setIntervalBtn').addEventListener('click', setIntervalTime);

        function addIP(event) {
            event.preventDefault();
            const ipInput = document.getElementById('ipInput');
            const ip = ipInput.value.trim();
            if (ip && !ips.some(item => item.ip === ip)) {
                ips.push({ ip, history: [] });
                renderTable();
                ipInput.value = '';
            }
        }

        document.getElementById('addForm').addEventListener('submit', addIP);

        window.removeIP = function(ip) {
            ips = ips.filter(item => item.ip !== ip);
            renderTable();
        };

        async function ping(ip) {
            const start = performance.now();
            const controller = new AbortController();
            const timeoutId = setTimeout(() => controller.abort(), 5000);
            try {
                await fetch(`http://${ip}/`, {
                    mode: 'no-cors',
                    cache: 'no-store',
                    signal: controller.signal
                });
                clearTimeout(timeoutId);
                const latency = performance.now() - start;
                return { success: true, latency: Math.round(latency) };
            } catch (e) {
                clearTimeout(timeoutId);
                return { success: false, latency: null };
            }
        }

        async function pingAll() {
            const promises = ips.map(async (item) => {
                const result = await ping(item.ip);
                item.history.unshift(result);
                if (item.history.length > 5) item.history.pop();
            });
            await Promise.all(promises);
            renderTable();
        }

        function renderTable() {
            const tbody = document.getElementById('ipTable');
            tbody.innerHTML = '';
            ips.forEach(item => {
                const row = document.createElement('tr');
                const latest = item.history[0] || null;
                const status = latest ? (latest.success ? '<span class="text-success">Up</span>' : '<span class="text-danger">Down</span>') : '-';
                const latency = latest ? (latest.success ? latest.latency : '-') : '-';
                const historyHtml = item.history.map(h => 
                    h.success ? `<span class="ping-success">✅ ${h.latency}ms</span>` : `<span class="ping-fail">❌</span>`
                ).join(' | ');
                row.innerHTML = `
                    <td>${item.ip}</td>
                    <td>${status}</td>
                    <td>${latency}</td>
                    <td class="ping-history">${historyHtml || '-'}</td>
                    <td><button class="btn btn-danger btn-sm" onclick="removeIP('${item.ip}')">Remove</button></td>
                `;
                tbody.appendChild(row);
            });
        }

        // Start initial pinging
        startPinging();
    </script>
</body>
</html> -->
<!-- <!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ping Monitor</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <style>
        body { padding: 20px; }
        .ping-history { white-space: nowrap; }
        .ping-success { color: green; }
        .ping-fail { color: red; }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="mb-4">Ping Monitor</h1>
        <form id="addForm" class="mb-3">
            <div class="input-group">
                <input type="text" id="ipInput" class="form-control" placeholder="Enter IP (e.g., 192.168.1.1 or 8.8.8.8)" required>
                <button type="submit" class="btn btn-primary">Add IP</button>
            </div>
        </form>
        <div class="mb-4">
            <label for="intervalInput" class="form-label">Ping Interval (seconds):</label>
            <div class="input-group" style="max-width: 300px;">
                <input type="number" id="intervalInput" class="form-control" value="5" min="1">
                <button id="setIntervalBtn" class="btn btn-secondary">Set</button>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>IP</th>
                        <th>Current Status</th>
                        <th>Current Latency (ms)</th>
                        <th>Last 5 Pings</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="ipTable"></tbody>
            </table>
        </div>
    </div>

    <script>
        let ips = [];
        let intervalTime = 5000;
        let pingTimer;

        function startPinging() {
            if (pingTimer) clearInterval(pingTimer);
            pingTimer = setInterval(pingAll, intervalTime);
        }

        function setIntervalTime() {
            const input = document.getElementById('intervalInput');
            intervalTime = parseInt(input.value, 10) * 1000;
            if (isNaN(intervalTime) || intervalTime < 1000) {
                intervalTime = 1000;
                input.value = 1;
            }
            startPinging();
        }

        document.getElementById('setIntervalBtn').addEventListener('click', setIntervalTime);

        function addIP(event) {
            event.preventDefault();
            const ipInput = document.getElementById('ipInput');
            const ip = ipInput.value.trim();
            if (ip && !ips.some(item => item.ip === ip)) {
                ips.push({ ip, history: [] });
                renderTable();
                ipInput.value = '';
            }
        }

        document.getElementById('addForm').addEventListener('submit', addIP);

        window.removeIP = function(ip) {
            ips = ips.filter(item => item.ip !== ip);
            renderTable();
        };

        async function ping(ip) {
            const start = performance.now();
            const controller = new AbortController();
            const timeoutId = setTimeout(() => controller.abort(), 5000);
            try {
                await fetch(`http://${ip}/`, {
                    mode: 'no-cors',
                    cache: 'no-store',
                    signal: controller.signal
                });
                clearTimeout(timeoutId);
                const latency = performance.now() - start;
                return { success: true, latency: Math.round(latency) };
            } catch (e) {
                clearTimeout(timeoutId);
                return { success: false, latency: null };
            }
        }

        async function pingAll() {
            const promises = ips.map(async (item) => {
                const result = await ping(item.ip);
                item.history.unshift(result);
                if (item.history.length > 5) item.history.pop();
            });
            await Promise.all(promises);
            renderTable();
        }

        function renderTable() {
            const tbody = document.getElementById('ipTable');
            tbody.innerHTML = '';
            ips.forEach(item => {
                const row = document.createElement('tr');
                const latest = item.history[0] || null;
                const status = latest ? (latest.success ? '<span class="text-success">Up</span>' : '<span class="text-danger">Down</span>') : '-';
                const latency = latest ? (latest.success ? latest.latency : '-') : '-';
                const historyHtml = item.history.map(h => 
                    h.success ? `<span class="ping-success">✅ ${h.latency}ms</span>` : `<span class="ping-fail">❌</span>`
                ).join(' | ');
                row.innerHTML = `
                    <td>${item.ip}</td>
                    <td>${status}</td>
                    <td>${latency}</td>
                    <td class="ping-history">${historyHtml || '-'}</td>
                    <td><button class="btn btn-danger btn-sm" onclick="removeIP('${item.ip}')">Remove</button></td>
                `;
                tbody.appendChild(row);
            });
        }

        // Start initial pinging
        startPinging();
    </script>
</body>
</html> -->


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ping Monitor + Sound Alert</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { padding: 20px; background: #f8f9fa; }
        .ping-success { color: green; font-weight: bold; }
        .ping-fail { color: red; font-weight: bold; }
        .alert-critical { background-color: #fee; border-left: 5px solid red; animation: pulse 2s infinite; }
        @keyframes pulse { 0% { opacity: 1; } 50% { opacity: 0.7; } 100% { opacity: 1; } }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="mb-4">Ping Monitor with Critical Alert Sound</h1>

        <form id="addForm" class="mb-3">
            <div class="input-group">
                <input type="text" id="ipInput" class="form-control" placeholder="Enter IP (e.g. 8.8.8.8 or 192.168.1.1)" required>
                <button type="submit" class="btn btn-success">Add IP</button>
            </div>
        </form>

        <div class="row mb-4">
            <div class="col-md-4">
                <label for="intervalInput" class="form-label">Ping Interval (seconds):</label>
                <div class="input-group" style="max-width: 300px;">
                    <input type="number" id="intervalInput" class="form-control" value="5" min="1">
                    <button id="setIntervalBtn" class="btn btn-outline-secondary">Set</button>
                </div>
            </div>
            <div class="col-md-4 d-flex align-items-end">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="soundEnabled" checked>
                    <label class="form-check-label" for="soundEnabled">Enable Sound Alerts</label>
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-striped table-hover align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>IP Address</th>
                        <th>Status</th>
                        <th>Latency (ms)</th>
                        <th>Last 5 Pings</th>
                        <th>Consecutive Down</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="ipTable"></tbody>
            </table>
        </div>
    </div>

    <!-- Alert Sound (short beep) -->
    <audio id="alertSound" preload="auto">
        <source src="https://file-examples.com/storage/fe4fdcf47a692181a9d3301/2017/11/file_example_WAV_1MG.wav" type="audio/mpeg">
        <source src="https://cdn.freesound.org/previews/834/834302_18150901-lq.mp3" type="audio/mpeg">
        Your browser does not support audio.
    </audio>

    <script>
        let ips = [];
        let intervalTime = 5000;
        let pingTimer;
        const alertSound = document.getElementById('alertSound');
        let alertedIps = new Set(); // Track which IPs have already triggered alarm

        function playAlert() {
            if (document.getElementById('soundEnabled').checked) {
                alertSound.currentTime = 0;
                alertSound.play().catch(() => console.log("Sound blocked (user interaction needed"));
            }
        }

        function startPinging() {
            if (pingTimer) clearInterval(pingTimer);
            pingTimer = setInterval(pingAll, intervalTime);
            // Also run once immediately
            setTimeout(pingAll, 500);
        }

        document.getElementById('setIntervalBtn').addEventListener('click', () => {
            const val = parseInt(document.getElementById('intervalInput').value);
            intervalTime = (isNaN(val) || val < 1) ? 5000 : val * 1000;
            startPinging();
        });

        document.getElementById('addForm').addEventListener('submit', (e) => {
            e.preventDefault();
            const ip = document.getElementById('ipInput').value.trim();
            const ipRegex = /^((\d{1,3}\.){3}\d{1,3}|[\w.-]+)$/;
            if (ip && ipRegex.test(ip) && !ips.some(i => i.ip === ip)) {
                ips.push({ ip, history: [], consecutiveDown: 0 });
                renderTable();
                document.getElementById('ipInput').value = '';
            }
        });

        window.removeIP = function(ip) {
            ips = ips.filter(item => item.ip !== ip);
            alertedIps.delete(ip);
            renderTable();
        };

        async function ping(ip) {
            const start = performance.now();
            const controller = new AbortController();
            const timeoutId = setTimeout(() => controller.abort(), 4000);

            try {
                // Try HTTP (port 80) – works for public IPs with web servers
                await fetch(`http://${ip}/`, {
                    method: 'HEAD',
                    mode: 'no-cors',
                    cache: 'no-cache',
                    signal: controller.signal
                });
                clearTimeout(timeoutId);
                const latency = Math.round(performance.now() - start);
                return { success: true, latency };
            } catch (err) {
                clearTimeout(timeoutId);
                // Fallback: try HTTPS for IPs that have web servers on 443
                try {
                    await fetch(`https://${ip}/`, { mode: 'no-cors', signal: controller.signal });
                    return { success: true, latency: 0 };
                } catch {
                    return { success: false, latency: null };
                }
            }
        }

        async function pingAll() {
            let hasNewCritical = false;

            await Promise.all(ips.map(async (item) => {
                const result = await ping(item.ip);

                item.history.unshift(result);
                if (item.history.length > 5) item.history.pop();

                if (result.success) {
                    item.consecutiveDown = 0;
                    alertedIps.delete(item.ip); // reset alarm
                } else {
                    item.consecutiveDown++;
                    if (item.consecutiveDown === 5 && !alertedIps.has(item.ip)) {
                        alertedIps.add(item.ip);
                        hasNewCritical = true;
                    }
                }
            }));

            if (hasNewCritical) {
                playAlert();
            }

            renderTable();
        }

        function renderTable() {
            const tbody = document.getElementById('ipTable');
            tbody.innerHTML = '';

            ips.forEach(item => {
                const row = document.createElement('tr');
                if (item.consecutiveDown >= 5) {
                    row.classList.add('alert-critical');
                }

                const latest = item.history[0];
                const status = latest
                    ? (latest.success
                        ? '<span class="ping-success">UP</span>'
                        : '<span class="ping-fail">DOWN</span>')
                    : '-';

                const latency = latest && latest.success ? latest.latency + ' ms' : '-';

                const historyHtml = item.history.map((h, i) =>
                    h.success
                        ? `<span class="ping-success">UP ${h.latency}ms</span>`
                        : `<span class="ping-fail">DOWN</span>`
                ).join(' | ') || '-';

                row.innerHTML = `
                    <td><strong>${item.ip}</strong></td>
                    <td>${status}</td>
                    <td>${latency}</td>
                    <td>${historyHtml}</td>
                    <td><span class="badge ${item.consecutiveDown >= 5 ? 'bg-danger' : 'bg-warning'}">
                        ${item.consecutiveDown}
                    </span></td>
                    <td><button class="btn btn-danger btn-sm" onclick="removeIP('${item.ip}')">Remove</button></td>
                `;
                tbody.appendChild(row);
            });
        }

        // Start monitoring
        startPinging();
    </script>
</body>
</html>
```
