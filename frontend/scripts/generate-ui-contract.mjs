import { mkdir, readFile, writeFile } from 'node:fs/promises';
import { dirname, resolve } from 'node:path';
import { fileURLToPath } from 'node:url';

const scriptDirectory = dirname(fileURLToPath(import.meta.url));
const sourcePath = resolve(scriptDirectory, '../../api/ui/v1/openapi.json');
const outputPath = resolve(scriptDirectory, '../src/api/generated/ui-contract.ts');
const checkOnly = process.argv.includes('--check');

const quote = (value) => JSON.stringify(value);
function referenceName(reference) {
  const prefix = '#/components/schemas/';
  if (typeof reference !== 'string' || !reference.startsWith(prefix)) throw new Error(`Unsupported OpenAPI reference: ${reference}`);
  return reference.slice(prefix.length);
}
const propertyName = (name) => (/^[A-Za-z_$][A-Za-z0-9_$]*$/.test(name) ? name : quote(name));
const arrayType = (type) => (type.includes(' | ') ? `(${type})` : type);

function schemaType(schema) {
  if (!schema || typeof schema !== 'object') return 'unknown';
  if (schema.$ref) return referenceName(schema.$ref);
  if (Object.prototype.hasOwnProperty.call(schema, 'const')) return quote(schema.const);
  if (Array.isArray(schema.enum)) return schema.enum.map(quote).join(' | ');
  if (Array.isArray(schema.oneOf)) return schema.oneOf.map(schemaType).join(' | ');
  if (Array.isArray(schema.anyOf)) return schema.anyOf.map(schemaType).join(' | ');
  if (Array.isArray(schema.type)) return schema.type.map((type) => schemaType({ ...schema, type })).join(' | ');
  switch (schema.type) {
    case 'string': return 'string';
    case 'integer':
    case 'number': return 'number';
    case 'boolean': return 'boolean';
    case 'null': return 'null';
    case 'array': return `${arrayType(schemaType(schema.items))}[]`;
    case 'object': {
      const required = new Set(schema.required ?? []);
      const lines = Object.entries(schema.properties ?? {}).map(([name, property]) =>
        `readonly ${propertyName(name)}${required.has(name) ? '' : '?'}: ${schemaType(property)};`
      );
      if (schema.additionalProperties === true) lines.push('readonly [key: string]: unknown;');
      else if (schema.additionalProperties && typeof schema.additionalProperties === 'object') lines.push(`readonly [key: string]: ${schemaType(schema.additionalProperties)};`);
      return lines.length === 0 ? 'Record<string, never>' : `{ ${lines.join(' ')} }`;
    }
    default: return 'unknown';
  }
}

function renderSchema(name, schema) {
  if (schema?.type === 'object' && schema.properties && !schema.enum && !schema.oneOf && !schema.anyOf) {
    const required = new Set(schema.required ?? []);
    const lines = [`export interface ${name} {`];
    for (const [key, value] of Object.entries(schema.properties)) lines.push(`  readonly ${propertyName(key)}${required.has(key) ? '' : '?'}: ${schemaType(value)};`);
    if (schema.additionalProperties === true) lines.push('  readonly [key: string]: unknown;');
    else if (schema.additionalProperties && typeof schema.additionalProperties === 'object') lines.push(`  readonly [key: string]: ${schemaType(schema.additionalProperties)};`);
    lines.push('}');
    return lines.join('\n');
  }
  return `export type ${name} = ${schemaType(schema)};`;
}

const contract = JSON.parse(await readFile(sourcePath, 'utf8'));
if (contract.openapi !== '3.1.0') throw new Error(`Expected OpenAPI 3.1.0, received ${contract.openapi}`);
const schemas = contract.components?.schemas;
if (!schemas || typeof schemas !== 'object') throw new Error('OpenAPI components.schemas is required.');
const blocks = Object.entries(schemas).map(([name, schema]) => renderSchema(name, schema));
const output = [
  '/* eslint-disable */',
  '/**',
  ' * GENERATED FILE — DO NOT EDIT.',
  ' * Source: api/ui/v1/openapi.json',
  ' * Generator: frontend/scripts/generate-ui-contract.mjs',
  ' *',
  ' * These are transport DTOs/enums, not React domain or view models.',
  ' */',
  '',
  ...blocks.flatMap((block) => [block, '']),
].join('\n');

if (checkOnly) {
  let current = '';
  try { current = await readFile(outputPath, 'utf8'); } catch { throw new Error(`Generated contract is missing: ${outputPath}`); }
  if (current !== output) { console.error('Generated UI contract is stale. Run: npm run generate:ui-contract'); process.exitCode = 1; }
  else console.log('Generated UI contract matches api/ui/v1/openapi.json.');
} else {
  await mkdir(dirname(outputPath), { recursive: true });
  await writeFile(outputPath, output, 'utf8');
  console.log(`Generated ${outputPath}`);
}
