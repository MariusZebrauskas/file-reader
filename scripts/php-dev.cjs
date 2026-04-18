const { spawn } = require('child_process');
const os = require('os');
const path = require('path');

const root = path.join(__dirname, '..');
const port = '8888';

function lanIpv4() {
  for (const addrs of Object.values(os.networkInterfaces())) {
    if (!addrs) {
      continue;
    }
    for (const a of addrs) {
      if ((a.family === 'IPv4' || a.family === 4) && !a.internal) {
        return a.address;
      }
    }
  }
  return '';
}

const ip = lanIpv4();
console.log('Dev (this machine): http://127.0.0.1:' + port);
if (ip) {
  console.log('Dev (phone/LAN): http://' + ip + ':' + port);
} else {
  console.log('Dev (phone/LAN): (no LAN IPv4 found — use this host IP):' + port);
}

const child = spawn(
  'php',
  [
    '-d',
    'xdebug.mode=debug',
    '-d',
    'xdebug.start_with_request=yes',
    '-S',
    '0.0.0.0:' + port,
    '-t',
    '.',
    'router.php',
  ],
  { cwd: root, stdio: 'inherit', shell: false, env: process.env },
);
child.on('error', (e) => {
  console.error(e);
  process.exit(1);
});
child.on('exit', (code) => {
  process.exit(code == null ? 0 : code);
});
