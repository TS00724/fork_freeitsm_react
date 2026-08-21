import { createServer } from 'node:http';
import { readFile, stat } from 'node:fs/promises';
import { dirname, extname, resolve, sep } from 'node:path';
import { fileURLToPath } from 'node:url';

const host = '127.0.0.1';
const port = 4173;
const mountPath = '/ui/';
const distDirectory = resolve(dirname(fileURLToPath(import.meta.url)), '..', 'dist');
const indexPath = resolve(distDirectory, 'index.html');
const mimeTypes = new Map([
  ['.css', 'text/css; charset=utf-8'],
  ['.html', 'text/html; charset=utf-8'],
  ['.ico', 'image/x-icon'],
  ['.js', 'text/javascript; charset=utf-8'],
  ['.json', 'application/json; charset=utf-8'],
  ['.png', 'image/png'],
  ['.svg', 'image/svg+xml'],
  ['.woff', 'font/woff'],
  ['.woff2', 'font/woff2']
]);

function send(response, status, contentType, body, headOnly = false) {
  response.writeHead(status, {
    'Content-Type': contentType,
    'Content-Length': Buffer.byteLength(body),
    'X-Content-Type-Options': 'nosniff'
  });
  response.end(headOnly ? undefined : body);
}

function safeAssetPath(pathname) {
  let relativePath;
  try {
    relativePath = decodeURIComponent(pathname.slice(mountPath.length));
  } catch {
    return null;
  }
  if (
    !relativePath ||
    relativePath.includes('\\') ||
    relativePath.split('/').some((segment) => segment === '.' || segment === '..')
  ) {
    return null;
  }
  const candidate = resolve(distDirectory, relativePath);
  const distPrefix = `${distDirectory}${sep}`;
  return candidate.startsWith(distPrefix) ? candidate : null;
}

const server = createServer(async (request, response) => {
  const method = request.method ?? 'GET';
  const headOnly = method === 'HEAD';
  if (method !== 'GET' && !headOnly) {
    send(response, 405, 'text/plain; charset=utf-8', 'Method not allowed');
    return;
  }

  const url = new URL(request.url ?? '/', `http://${host}:${port}`);
  if (!url.pathname.startsWith(mountPath)) {
    send(response, 404, 'text/plain; charset=utf-8', 'Not found', headOnly);
    return;
  }

  const assetPath = safeAssetPath(url.pathname);
  if (assetPath) {
    try {
      const metadata = await stat(assetPath);
      if (metadata.isFile()) {
        const body = await readFile(assetPath);
        send(
          response,
          200,
          mimeTypes.get(extname(assetPath)) ?? 'application/octet-stream',
          body,
          headOnly
        );
        return;
      }
    } catch (error) {
      if (!(error instanceof Error) || !('code' in error) || error.code !== 'ENOENT') throw error;
    }
  }

  if (url.pathname.startsWith(`${mountPath}assets/`)) {
    send(response, 404, 'text/plain; charset=utf-8', 'Asset not found', headOnly);
    return;
  }

  const index = await readFile(indexPath);
  send(response, 200, 'text/html; charset=utf-8', index, headOnly);
});

server.listen(port, host, () => {
  console.log(`Serving frontend/dist at http://${host}:${port}${mountPath}`);
});

function closeServer() {
  server.close((error) => {
    if (error) throw error;
    process.exit(0);
  });
}

process.on('SIGINT', closeServer);
process.on('SIGTERM', closeServer);
