#!/usr/bin/env node
// Minimal stdio MCP client for calling Laravel Boost tools from the CLI.
//
// Usage:
//   node scripts/mcp-call.cjs <tool-name> '[json-args]'
//
// Example:
//   node scripts/mcp-call.cjs database-connections
//   node scripts/mcp-call.cjs database-query '{"sql": "select count(*) as total from pegawais"}'
//   node scripts/mcp-call.cjs search-docs '{"queries": ["rate limiting"]}'
const { spawn } = require('child_process');

const tool = process.argv[2];
if (!tool) {
    console.error('Usage: node scripts/mcp-call.cjs <tool-name> \'[json-args]\'');
    process.exit(1);
}

let args = {};
if (process.argv[3]) {
    try {
        args = JSON.parse(process.argv[3]);
    } catch (e) {
        console.error('Invalid JSON arguments:', e.message);
        process.exit(1);
    }
}

const php = 'C:\\Users\\HP\\.config\\herd\\bin\\php84\\php.exe';
const proc = spawn(php, ['artisan', 'boost:mcp'], { cwd: process.cwd() });

let buf = '';
let nextId = 1;
let initialized = false;

function send(method, params = {}, id) {
    const msgId = id ?? nextId++;
    proc.stdin.write(JSON.stringify({ jsonrpc: '2.0', id: msgId, method, params }) + '\n');
    return msgId;
}

function onLine(line) {
    try {
        const msg = JSON.parse(line);
        if (!msg.id) return;

        if (msg.id === 1) {
            initialized = true;
            send('tools/call', { name: tool, arguments: args }, 2);
        } else if (msg.id === 2) {
            const result = msg.result ?? msg.error;
            console.log(JSON.stringify(result, null, 2));
            proc.kill();
            process.exit(msg.error ? 1 : 0);
        }
    } catch (e) {
        // ignore non-JSON output
    }
}

proc.stdout.on('data', (d) => {
    buf += d.toString();
    let idx;
    while ((idx = buf.indexOf('\n')) >= 0) {
        onLine(buf.slice(0, idx).trim());
        buf = buf.slice(idx + 1);
    }
});

proc.stderr.on('data', (d) => process.stderr.write(d));
proc.on('exit', (code) => {
    if (!initialized) {
        console.error('Boost MCP server exited before responding (code', code + ')');
        process.exit(1);
    }
});

send('initialize', {
    protocolVersion: '2024-11-05',
    capabilities: {},
    clientInfo: { name: 'freebuff-mcp-client', version: '1.0' },
}, 1);

setTimeout(() => {
    console.error('TIMEOUT: no response within 20s');
    proc.kill();
    process.exit(1);
}, 20000);
