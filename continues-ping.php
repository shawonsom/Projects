<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Continuous Multi-Ping Tool</title>
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background: #f0f2f5;
      color: #333;
      padding: 20px;
    }
    .container {
      max-width: 900px;
      margin: 0 auto;
      background: white;
      border-radius: 12px;
      box-shadow: 0 4px 20px rgba(0,0,0,0.1);
      overflow: hidden;
    }
    header {
      background: #1d4ed8;
      color: white;
      padding: 20px;
      text-align: center;
    }
    header h1 { font-size: 1.8rem; }

    .content { padding: 25px; }

    label { display: block; margin-bottom: 8px; font-weight: 600; }
    textarea {
      width: 100%;
      height: 120px;
      padding: 12px;
      border: 1px solid #ddd;
      border-radius: 8px;
      font-family: monospace;
      font-size: 14px;
      resize: vertical;
    }

    .controls {
      display: flex;
      gap: 10px;
      margin-top: 15px;
      flex-wrap: wrap;
    }
    button {
      padding: 12px 20px;
      border: none;
      border-radius: 8px;
      font-size: 15px;
      cursor: pointer;
      transition: all 0.3s;
      font-weight: 600;
    }
    #startBtn {
      background: #16a34a;
      color: white;
    }
    #startBtn:hover:not(:disabled) { background: #15803d; }
    #stopBtn {
      background: #dc2626;
      color: white;
      display: none;
    }
    #stopBtn:hover { background: #b91c1c; }
    button:disabled {
      background: #9ca3af;
      cursor: not-allowed;
      opacity: 0.7;
    }

    .interval-control {
      display: flex;
      align-items: center;
      gap: 8px;
      font-size: 14px;
    }
    .interval-control input {
      width: 60px;
      padding: 6px;
      border: 1px solid #ddd;
      border-radius: 6px;
      text-align: center;
    }

    .results {
      margin-top: 20px;
      border: 1px solid #e5e7eb;
      border-radius: 8px;
      overflow: hidden;
      display: none;
    }
    table {
      width: 100%;
      border-collapse: collapse;
    }
    th {
      background: #f8f9fa;
      padding: 12px;
      text-align: left;
      font-weight: 600;
      border-bottom: 2px solid #e5e7eb;
    }
    td {
      padding: 10px 12px;
      border-bottom: 1px solid #e5e7eb;
      font-family: monospace;
      font-size: 14px;
    }
    .host { font-weight: 500; }
    .status-live {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      font-weight: bold;
    }
    .status-up { color: #16a34a; }
    .status-down { color: #dc2626; }
    .pulse {
      width: 8px;
      height: 8px;
      background: currentColor;
      border-radius: 50%;
      animation: pulse 1.5s infinite;
    }
    @keyframes pulse {
      0%, 100% { opacity: 0.4; }
      50% { opacity: 1; }
    }
    .latency { color: #6366f1; }
    .latency-slow { color: #d97706; }
    .latency-bad { color: #dc2626; }

    .stats {
      margin-top: 15px;
      padding: 12px;
      background: #f8f9fa;
      border-radius: 8px;
      font-size: 0.9rem;
      color: #4b5563;
    }

    .note {
      font-size: 0.85rem;
      color: #6b7280;
      margin-top: 15px;
      font-style: italic;
    }
  </style>
</head>
<body>
  <div class="container">
    <header>
      <h1>Continuous Multi-Ping Monitor</h1>
    </header>
    <div class="content">
      <label for="domains">Enter Domains or IPs (one per line):</label>
      <textarea id="domains" placeholder="example.com&#10;google.com&#10;8.8.8.8&#10;github.com"></textarea>

      <div class="controls">
        <button id="startBtn">Start Ping</button>
        <button id="stopBtn">Stop Pinging</button>
        <div class="interval-control">
          <label>Interval:</label>
          <input type="number" id="interval" value="2" min="1" max="60" /> sec
        </div>
      </div>

      <div id="results" class="results">
        <table>
          <thead>
            <tr>
              <th>Host</th>
              <th>Status</th>
              <th>Latency</th>
              <th>Last Checked</th>
            </tr>
          </thead>
          <tbody id="resultsBody"></tbody>
        </table>
        <div class="stats" id="stats">Monitoring <span id="count">0</span> hosts...</div>
      </div>

      <p class="note">
        ℹ️ Uses HTTP/HTTPS HEAD requests. Continuous mode updates every N seconds. Real ICMP requires backend.
      </p>
    </div>
  </div>

  <script>
    const domainsInput = document.getElementById('domains');
    const startBtn = document.getElementById('startBtn');
    const stopBtn = document.getElementById('stopBtn');
    const intervalInput = document.getElementById('interval');
    const resultsDiv = document.getElementById('results');
    const resultsBody = document.getElementById('resultsBody');
    const statsCount = document.getElementById('count');
    const statsDiv = document.getElementById('stats');

    let hosts = [];
    let intervalId = null;
    let isRunning = false;

    startBtn.addEventListener('click', startPinging);
    stopBtn.addEventListener('click', stopPinging);

    function startPinging() {
      const raw = domainsInput.value.trim();
      if (!raw) {
        alert('Please enter at least one domain or IP');
        return;
      }

      hosts = raw.split('\n').map(h => h.trim()).filter(h => h);
      if (hosts.length === 0) return;

      // Initialize rows
      resultsBody.innerHTML = '';
      hosts.forEach(host => {
        const row = document.createElement('tr');
        row.id = `row-${btoa(host).replace(/=/g, '')}`;
        row.innerHTML = `
          <td class="host">${escapeHtml(host)}</td>
          <td class="status-live status-down"><span class="pulse"></span> Checking...</td>
          <td class="latency">-</td>
          <td class="time">-</td>
        `;
        resultsBody.appendChild(row);
      });

      resultsDiv.style.display = 'block';
      statsCount.textContent = hosts.length;

      startBtn.style.display = 'none';
      stopBtn.style.display = 'inline-block';
      isRunning = true;

      pingAllHosts(); // First ping
      const interval = Math.max(1000, parseInt(intervalInput.value) * 1000);
      intervalId = setInterval(pingAllHosts, interval);
    }

    function stopPinging() {
      if (intervalId) clearInterval(intervalId);
      isRunning = false;
      startBtn.style.display = 'inline-block';
      stopBtn.style.display = 'none';
      statsDiv.textContent = 'Monitoring stopped.';
    }

    async function pingAllHosts() {
      if (!isRunning) return;

      const now = new Date();
      const timeStr = now.toLocaleTimeString();

      for (const host of hosts) {
        const row = document.getElementById(`row-${btoa(host).replace(/=/g, '')}`);
        if (!row) continue;

        const result = await pingHost(host);
        const statusCell = row.cells[1];
        const latencyCell = row.cells[2];
        const timeCell = row.cells[3];

        // Update status
        if (result.success) {
          statusCell.innerHTML = `<span class="pulse"></span> UP`;
          statusCell.className = 'status-live status-up';

          const latency = result.time;
          latencyCell.textContent = latency + ' ms';
          latencyCell.className = 'latency ' + 
            (latency > 300 ? 'latency-bad' : latency > 150 ? 'latency-slow' : '');
        } else {
          statusCell.innerHTML = `DOWN`;
          statusCell.className = 'status-live status-down';
          latencyCell.textContent = result.error;
          latencyCell.className = '';
        }

        timeCell.textContent = timeStr;
      }
    }

    async function pingHost(host) {
      const protocols = ['https://', 'http://'];
      let bestTime = Infinity;
      let success = false;
      let errorMsg = 'Timeout';

      const start = performance.now();

      for (const protocol of protocols) {
        const url = protocol + host;
        try {
          const controller = new AbortController();
          const timeoutId = setTimeout(() => controller.abort(), 4000);

          await fetch(url, {
            method: 'HEAD',
            mode: 'no-cors',
            signal: controller.signal
          });

          clearTimeout(timeoutId);
          const end = performance.now();
          const time = Math.round(end - start);

          success = true;
          if (time < bestTime) bestTime = time;

        } catch (err) {
          if (err.name === 'AbortError') {
            errorMsg = 'Timeout';
          }
        }
      }

      return {
        success,
        time: success ? (bestTime === Infinity ? Math.round(performance.now() - start) : bestTime) : null,
        error: errorMsg
      };
    }

    function escapeHtml(text) {
      const div = document.createElement('div');
      div.textContent = text;
      return div.innerHTML;
    }

    // Allow Enter key in textarea
    domainsInput.addEventListener('keydown', e => {
      if (e.key === 'Enter' && e.ctrlKey) {
        e.preventDefault();
        if (!isRunning) startPinging();
      }
    });
  </script>
</body>
</html>
