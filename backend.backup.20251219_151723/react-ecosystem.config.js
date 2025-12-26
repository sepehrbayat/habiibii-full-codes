// PM2 Ecosystem Configuration for React App
// کپی این فایل به پروژه React و اجرا با: pm2 start ecosystem.config.js

module.exports = {
  apps: [{
    name: '6ammart-react',
    script: 'serve',
    args: '-s build -l 3000',
    instances: 1,
    exec_mode: 'fork',
    env: {
      NODE_ENV: 'production',
      PORT: 3000,
      REACT_APP_API_URL: 'http://188.245.192.118/api/v1',
      REACT_APP_API_BASE_URL: 'http://188.245.192.118',
      REACT_APP_WS_URL: 'ws://188.245.192.118:6001',
    },
    error_file: './logs/err.log',
    out_file: './logs/out.log',
    log_date_format: 'YYYY-MM-DD HH:mm:ss Z',
    merge_logs: true,
    autorestart: true,
    watch: false,
    max_memory_restart: '1G',
    min_uptime: '10s',
    max_restarts: 10,
  }]
};

