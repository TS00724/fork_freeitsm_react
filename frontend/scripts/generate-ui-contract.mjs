import { readFile, writeFile } from 'node:fs/promises';
import { fileURLToPath } from 'node:url';
import path from 'node:path';

const scriptDirectory = path.dirname(fileURLToPath(import.meta.url));
const frontendDirectory = path.resolve(scriptDirectory, '..');
const repositoryDirectory = path.resolve(frontendDirectory, '..');
const contractPath = path.join(repositoryDirectory, 'api', 'ui', 'v1', 'openapi.json');
const outputPath = path.join(frontendDirectory, 'src', 'api', 'generated', 'ui-contract.ts');
const checkOnly = process.argv.includes('--check');

const contract = JSON.parse(await readFile(contractPath, 'utf8'));
if (typeof contract.openapi !== 'string' || !contract.openapi.startsWith('3.')) {
  throw new Error('UI API contract must be OpenAPI 3.x');
}
if (!contract.info || typeof contract.info.version !== 'string') {
  throw new Error('UI API contract is missing info.version');
}
const schemas = contract.components?.schemas;
if (!schemas || typeof schemas !== 'object') {
  throw new Error('UI API contract is missing components.schemas');
}

function referenceName(reference) {
  const prefix = '#/components/schemas/';
  if (typeof reference !== 'string' || !reference.startsWith(prefix)) {
    throw new Error(`Unsupported schema reference: ${String(reference)}`);
  }
  return reference.slice(prefix.length);
}

function schemaType(schema) {
  if (!schema || typeof schema !== 'object') return 'unknown';
  if (schema.$ref) return referenceName(schema.$ref);
  if (Object.prototype.hasOwnProperty.call(schema, 'const')) {
    return JSON.stringify(schema.const);
  }
  if (Array.isArray(schema.enum)) {
    return schema.enum.map((value) => JSON.stringify(value)).join(' | ');
  }
  if (Array.isArray(schema.oneOf)) {
    return schema.oneOf.map(schemaType).join(' | ');
  }
  if (schema.type === 'array') return `ReadonlyArray<${schemaType(schema.items)}>`;
  if (schema.type === 'object') {
    if (schema.additionalProperties === true) return 'Record<string, unknown>';
    const properties = schema.properties ?? {};
    const required = new Set(schema.required ?? []);
    const fields = Object.entries(properties).map(([name, property]) =>
      `readonly ${JSON.stringify(name)}${required.has(name) ? '' : '?'}: ${schemaType(property)};`
    );
    return `{ ${fields.join(' ')} }`;
  }
  if (schema.type === 'integer' || schema.type === 'number') return 'number';
  if (schema.type === 'boolean') return 'boolean';
  if (schema.type === 'null') return 'null';
  if (schema.type === 'string') return 'string';
  return 'unknown';
}

const operations = [];
for (const [route, pathItem] of Object.entries(contract.paths ?? {})) {
  for (const [method, operation] of Object.entries(pathItem)) {
    if (!['get', 'post', 'put', 'patch', 'delete', 'head', 'options'].includes(method)) continue;
    if (!operation || typeof operation.operationId !== 'string') {
      throw new Error(`Missing operationId for ${method.toUpperCase()} ${route}`);
    }
    operations.push({ route, method: method.toUpperCase(), operationId: operation.operationId });
  }
}
operations.sort((left, right) => left.operationId.localeCompare(right.operationId));

const lines = [
  '/* eslint-disable */',
  '/**',
  ' * GENERATED FILE — DO NOT EDIT.',
  ' * Source: api/ui/v1/openapi.json',
  ' * Run: npm run contracts:generate',
  ' */',
  '',
  `export const UI_API_CONTRACT_VERSION = ${JSON.stringify(contract.info.version)} as const;`,
  `export const UI_API_OPERATIONS = ${JSON.stringify(operations, null, 2)} as const;`,
  'export type UiApiOperationId = typeof UI_API_OPERATIONS[number]["operationId"];',
  ''
];

for (const name of Object.keys(schemas).sort()) {
  const schema = schemas[name];
  if (schema.type === 'object' && schema.additionalProperties !== true && !schema.$ref) {
    const required = new Set(schema.required ?? []);
    lines.push(`export interface ${name} {`);
    for (const [propertyName, propertySchema] of Object.entries(schema.properties ?? {})) {
      lines.push(
        `  readonly ${JSON.stringify(propertyName)}${required.has(propertyName) ? '' : '?'}: ${schemaType(propertySchema)};`
      );
    }
    lines.push('}', '');
  } else {
    lines.push(`export type ${name} = ${schemaType(schema)};`, '');
  }
}

const generated = `${lines.join('\n').trimEnd()}\n`;
if (checkOnly) {
  const existing = await readFile(outputPath, 'utf8').catch(() => null);
  if (existing !== generated) {
    throw new Error('Generated UI API TypeScript contract is stale. Run npm run contracts:generate.');
  }
  console.log('UI API TypeScript contract is current.');
} else {
  await writeFile(outputPath, generated, 'utf8');
  console.log(`Generated ${path.relative(repositoryDirectory, outputPath)}.`);
}
