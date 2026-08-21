import assert from 'node:assert/strict';
import { spawn } from 'node:child_process';
import { once } from 'node:events';
import { readFile } from 'node:fs/promises';

const origin = 'http://127.0.0.1:4173';
const server = spawn(process.execPath, ['scripts/serve-dist.mjs'], {
  stdio: ['ignore', 'pipe', 'pipe']
});
let stderr = '';
server.stderr.on('data', (chunk) => { stderr += String(chunk); });

async function stopServer() {
  if (server.exitCode !== null) return;
  server.kill('SIGTERM');
  await once(server, 'exit');
}

async function fetchCheck(path, expectedStatus, expectedType) {
  const response = await fetch(`${origin}${path}`);
  const body = await response.text();
  assert.equal(response.status, expectedStatus, `${path} returned ${response.status}`);
  assert.match(
    response.headers.get('content-type') ?? '',
    expectedType,
    `${path} returned the wrong content type`
  );
  assert.equal(response.headers.get('x-content-type-options'), 'nosniff');
  return body;
}

try {
  await Promise.race([
    once(server.stdout, 'data'),
    once(server, 'exit').then(([code]) => {
      throw new Error(`Preview server exited early with ${String(code)}: ${stderr}`);
    })
  ]);

  const builtIndex = await readFile('dist/index.html', 'utf8');
  const javascriptAsset = builtIndex.match(/src="\.\/(assets\/[^"]+\.js)"/)?.[1];
  assert.ok(javascriptAsset, 'Built index does not reference a JavaScript asset');

  for (const route of ['/ui/', '/ui/forbidden', '/ui/this-route-does-not-exist']) {
    const html = await fetchCheck(route, 200, /^text\/html\b/);
    assert.match(html, /<div id="root"><\/div>/, `${route} did not return the built SPA shell`);
  }
  await fetchCheck(`/ui/${javascriptAsset}`, 200, /^text\/javascript\b/);
  await fetchCheck('/ui/assets/missing.js', 404, /^text\/plain\b/);
  await fetchCheck('/', 404, /^text\/plain\b/);

  console.log('Production preview check passed: /ui assets and deep-link fallback are coherent.');
} finally {
  await stopServer();
}
