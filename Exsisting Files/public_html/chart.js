(() => {
  const chartEl = document.getElementById('chart');
  if (!chartEl || typeof echarts === 'undefined') return;

  const card = document.querySelector('.chart-card');
  const title = card?.querySelector('h2');
  const sub = card?.querySelector('.section-title p');
  const legend = card?.querySelector('.legend');

  const seriesDefinitions = [
    { id: 'temperature', label: 'Temperature', unit: '°C', color: '#6fa8ff', axis: 'temp', source: { file: 'tempdata', key: 'temp' } },
    { id: 'feelslike', label: 'Feels like', unit: '°C', color: '#ff9f68', axis: 'temp', source: { file: 'tempdata', key: 'feelslike' } },
    { id: 'dewpoint', label: 'Dew point', unit: '°C', color: '#54d6cf', axis: 'temp', source: { file: 'tempdata', key: 'dew' } },
    { id: 'humidity', label: 'Humidity', unit: '%', color: '#ffd166', axis: 'humidity', source: { file: 'humdata', key: 'hum' } },
    { id: 'pressure', label: 'Pressure', unit: 'hPa', color: '#86d7ff', axis: 'pressure', source: { file: 'pressdata', key: 'press' } },
    { id: 'rainfall', label: 'Rainfall', unit: 'mm', color: '#35b8c3', axis: 'rain', source: { file: 'raindata', key: 'rfall' } },
    { id: 'rainrate', label: 'Rain rate', unit: 'mm/h', color: '#8ee3a3', axis: 'rain', source: { file: 'raindata', key: 'rrate' } },
    { id: 'windspeed', label: 'Wind speed', unit: 'mph', color: '#7aa6ff', axis: 'wind', source: { file: 'winddata', key: 'wspeed' } },
    { id: 'gust', label: 'Gust', unit: 'mph', color: '#ff7b72', axis: 'wind', source: { file: 'winddata', key: 'wgust' } },
    { id: 'winddirection', label: 'Wind direction', unit: '°', color: '#c29cff', axis: 'wind', source: { file: 'wdirdata', key: 'bearing' } },
    { id: 'solar', label: 'Solar radiation', unit: 'W/m²', color: '#ffe59e', axis: 'solar', source: { file: 'solardata', key: 'SolarRad' } },
    { id: 'uv', label: 'UV', unit: '', color: '#ff9f68', axis: 'solar', source: { file: 'solardata', key: 'UV' } }
  ];

  const presets = {
    temp: ['temperature', 'feelslike', 'dewpoint'],
    rain: ['rainfall', 'rainrate'],
    wind: ['windspeed', 'gust', 'winddirection'],
    pressure: ['pressure', 'humidity']
  };

  const rangeOptions = {
    '6h': 6 * 60 * 60 * 1000,
    '12h': 12 * 60 * 60 * 1000,
    '24h': 24 * 60 * 60 * 1000,
    '48h': 48 * 60 * 60 * 1000,
    '7d': 7 * 24 * 60 * 60 * 1000,
    '30d': 30 * 24 * 60 * 60 * 1000,
    all: null
  };

  const state = { preset: 'temp', range: '48h', visible: {}, payloads: {}, loading: true };
  let chart = null;

  const createInitialVisibility = () => {
    const visible = {};
    seriesDefinitions.forEach((series) => {
      visible[series.id] = presets[state.preset].includes(series.id);
    });
    return visible;
  };

  const formatNumber = (value) => {
    const n = Number(value);
    if (!Number.isFinite(n)) return '--';
    const decimals = Math.abs(n) >= 100 || Math.abs(n) <= 10 ? 0 : 1;
    return n.toFixed(decimals);
  };

  const toCompass = (value) => {
    const directions = ['N', 'NE', 'E', 'SE', 'S', 'SW', 'W', 'NW'];
    const index = Math.round(((Number(value) % 360) + 360) % 360 / 45) % 8;
    return directions[index];
  };

  const formatValue = (series, value) => {
    if (value === null || value === undefined || value === '') return `--`;
    const n = Number(value);
    if (!Number.isFinite(n)) return `${value}`;
    switch (series.id) {
      case 'humidity':
        return `${formatNumber(n)}%`;
      case 'pressure':
        return `${formatNumber(n)} hPa`;
      case 'rainfall':
      case 'rainrate':
        return `${formatNumber(n)} ${series.unit}`;
      case 'windspeed':
      case 'gust':
        return `${formatNumber(n)} ${series.unit}`;
      case 'winddirection':
        return `${Math.round(n)}° ${toCompass(n)}`;
      case 'solar':
        return `${formatNumber(n)} W/m²`;
      case 'uv':
        return `${formatNumber(n)}`;
      default:
        return `${formatNumber(n)}${series.unit}`;
    }
  };

  const toLocalStamp = (ts) => new Date(ts).toLocaleString('en-GB', {
    year: 'numeric', month: 'short', day: '2-digit', hour: '2-digit', minute: '2-digit', second: '2-digit'
  });

  const getSeriesData = (series) => {
    const payload = state.payloads[series.source.file];
    if (!payload || !Array.isArray(payload[series.source.key])) return [];
    return payload[series.source.key]
      .map(([ts, value]) => [Number(ts), Number(value)])
      .filter(([ts, value]) => Number.isFinite(ts) && Number.isFinite(value));
  };

  const filterByRange = (points) => {
    const window = rangeOptions[state.range];
    if (!window) return points;
    const cutoff = Date.now() - window;
    return points.filter(([ts]) => ts >= cutoff);
  };

  const buildSeries = () => seriesDefinitions
    .filter((series) => state.visible[series.id])
    .map((series) => ({
      id: series.id,
      name: series.label,
      color: series.color,
      axis: series.axis,
      unit: series.unit,
      data: filterByRange(getSeriesData(series))
    }));

  const renderLegend = () => {
    if (!legend) return;
    legend.innerHTML = seriesDefinitions.map((series) => {
      const isVisible = !!state.visible[series.id];
      return `<button class="legend-item ${isVisible ? 'active' : ''}" data-series="${series.id}"><span class="legend-swatch" style="background:${series.color}"></span>${series.label}</button>`;
    }).join('');

    legend.querySelectorAll('.legend-item').forEach((button) => {
      const seriesId = button.dataset.series;
      button.addEventListener('click', () => {
        const next = !state.visible[seriesId];
        state.visible[seriesId] = next;
        if (!next && !Object.values(state.visible).some(Boolean)) {
          state.visible[seriesId] = true;
        }
        render();
      });
      button.addEventListener('dblclick', () => {
        const nextVisible = {};
        seriesDefinitions.forEach((series) => {
          nextVisible[series.id] = series.id === seriesId;
        });
        state.visible = nextVisible;
        render();
      });
    });
  };

  const render = () => {
    if (!state.payloads || Object.keys(state.payloads).length === 0) return;
    if (title) title.textContent = 'Weather monitoring';
    if (sub) sub.textContent = state.range === '48h' ? 'Last 48 hours' : state.range === 'all' ? 'All available data' : `Last ${state.range}`;
    renderLegend();

    const series = buildSeries();
    if (!chartEl) return;
    if (chart) chart.dispose();

    chart = echarts.init(chartEl, null, { renderer: 'canvas', useDirtyRect: true });

    const yAxes = [
      { type: 'value', id: 'temp', position: 'left', name: 'Temperature', nameTextStyle: { color: '#98b3c1' }, axisLine: { lineStyle: { color: '#244256' } }, axisLabel: { color: '#98b3c1' }, splitLine: { lineStyle: { color: '#1b3850' } } },
      { type: 'value', id: 'humidity', position: 'right', name: 'Humidity', nameTextStyle: { color: '#98b3c1' }, axisLine: { lineStyle: { color: '#244256' } }, axisLabel: { color: '#98b3c1' }, splitLine: { lineStyle: { color: '#1b3850' } } },
      { type: 'value', id: 'pressure', position: 'right', name: 'Pressure', nameTextStyle: { color: '#98b3c1' }, axisLine: { lineStyle: { color: '#244256' } }, axisLabel: { color: '#98b3c1' }, splitLine: { lineStyle: { color: '#1b3850' } } },
      { type: 'value', id: 'rain', position: 'right', name: 'Rain', nameTextStyle: { color: '#98b3c1' }, axisLine: { lineStyle: { color: '#244256' } }, axisLabel: { color: '#98b3c1' }, splitLine: { lineStyle: { color: '#1b3850' } } },
      { type: 'value', id: 'wind', position: 'right', name: 'Wind', nameTextStyle: { color: '#98b3c1' }, axisLine: { lineStyle: { color: '#244256' } }, axisLabel: { color: '#98b3c1' }, splitLine: { lineStyle: { color: '#1b3850' } } },
      { type: 'value', id: 'solar', position: 'right', name: 'Solar / UV', nameTextStyle: { color: '#98b3c1' }, axisLine: { lineStyle: { color: '#244256' } }, axisLabel: { color: '#98b3c1' }, splitLine: { lineStyle: { color: '#1b3850' } } }
    ];

    const axisIndex = (axisId) => yAxes.findIndex((axis) => axis.id === axisId);

    const eSeries = series.map((item) => ({
      name: item.name,
      type: 'line',
      showSymbol: false,
      smooth: false,
      connectNulls: false,
      large: true,
      largeThreshold: 2000,
      lineStyle: { width: 2.1, color: item.color },
      itemStyle: { color: item.color },
      yAxisIndex: axisIndex(item.axis),
      tooltip: {
        valueFormatter: (value) => formatValue(seriesDefinitions.find((entry) => entry.id === item.id), value)
      },
      data: item.data.map(([ts, value]) => [ts, value])
    }));

    chart.setOption({
      animation: false,
      tooltip: {
        trigger: 'axis',
        confine: true,
        axisPointer: { type: 'cross', snap: true, label: { backgroundColor: '#0c1d2b' } },
        formatter: (params) => {
          if (!params || !params.length) return '';
          const stamp = toLocalStamp(params[0].value[0]);
          const rows = params
            .filter((item) => item.value && item.value[1] !== null && item.value[1] !== undefined)
            .map((item) => `
              <div style="display:flex;justify-content:space-between;gap:12px;align-items:center;color:#f4fbff">
                <span style="display:flex;align-items:center;gap:8px;color:#f4fbff"><span style="color:${item.color}">■</span>${item.seriesName}</span>
                <b style="color:#ffffff;font-weight:700">${formatValue(seriesDefinitions.find((entry) => entry.id === item.seriesId || entry.label === item.seriesName), item.value[1])}</b>
              </div>
            `)
            .join('');
          return `<div style="font-size:12px;color:#f4fbff">${stamp}</div>${rows}`;
        }
      },
      grid: { left: 24, right: 20, top: 42, bottom: 46 },
      xAxis: {
        type: 'time',
        boundaryGap: false,
        axisLine: { lineStyle: { color: '#244256' } },
        axisLabel: {
          color: '#98b3c1',
          formatter: (value) => {
            const d = new Date(value);
            return d.toLocaleDateString('en-GB', { day: '2-digit', month: 'short' });
          }
        },
        splitLine: { lineStyle: { color: '#1b3850' } }
      },
      yAxis: yAxes,
      dataZoom: [
        { type: 'inside', start: 0, end: 100, throttle: 50, zoomLock: false },
        {
          type: 'slider',
          show: true,
          height: 18,
          bottom: 0,
          backgroundColor: '#071522',
          fillerColor: '#1c5363',
          borderColor: '#244256',
          showDetail: true,
          showDataShadow: true,
          moveHandleSize: 7,
          handleStyle: { borderColor: '#54d6cf' },
          textStyle: { color: '#98b3c1' }
        }
      ],
      toolbox: {
        show: true,
        feature: {
          dataZoom: { show: true, yAxisIndex: 'none', title: { zoom: 'Zoom', back: 'Back' } },
          restore: { show: true, title: 'Restore' },
          saveAsImage: { show: true, title: 'Save as PNG' }
        },
        iconStyle: { borderColor: '#6fa8ff', color: '#eff8fb' }
      },
      series: eSeries
    });
  };

  const loadData = async () => {
    try {
      const [tempdata, raindata, winddata, pressdata, humdata, solardata, wdirdata] = await Promise.all([
        fetch('cutils/tempdata.json?' + Date.now()).then((response) => response.json()),
        fetch('cutils/raindata.json?' + Date.now()).then((response) => response.json()),
        fetch('cutils/winddata.json?' + Date.now()).then((response) => response.json()),
        fetch('cutils/pressdata.json?' + Date.now()).then((response) => response.json()),
        fetch('cutils/humdata.json?' + Date.now()).then((response) => response.json()),
        fetch('cutils/solardata.json?' + Date.now()).then((response) => response.json()),
        fetch('cutils/wdirdata.json?' + Date.now()).then((response) => response.json())
      ]);

      state.payloads = { tempdata, raindata, winddata, pressdata, humdata, solardata, wdirdata };
      if (!state.visible || Object.keys(state.visible).length === 0) {
        state.visible = createInitialVisibility();
      }
      render();
    } catch (error) {
      console.warn(error);
    }
  };

  document.querySelectorAll('#chartMetrics .chart-tab').forEach((button) => {
    button.addEventListener('click', () => {
      state.preset = button.dataset.metric;
      state.visible = createInitialVisibility();
      document.querySelectorAll('#chartMetrics .chart-tab').forEach((tab) => tab.classList.toggle('active', tab === button));
      render();
    });
  });

  document.querySelectorAll('#chartRange .chart-tab').forEach((button) => {
    button.addEventListener('click', () => {
      state.range = button.dataset.range;
      document.querySelectorAll('#chartRange .chart-tab').forEach((tab) => tab.classList.toggle('active', tab === button));
      render();
    });
  });

  window.addEventListener('resize', () => {
    if (chart) chart.resize();
  });

  state.visible = createInitialVisibility();
  loadData();
})();
