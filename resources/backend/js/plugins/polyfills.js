import polyfill from 'dynamic-polyfill'
polyfill({
  fills: [
    'Promise',
    'Object.assign',
    'Object.values',
    'Array.prototype.find',
    'Array.prototype.findIndex',
    'Array.prototype.includes',
    'String.prototype.includes',
    'String.prototype.startsWith',
    'String.prototype.endsWith'
  ],
  options: ['gated'],
  minify: false,
  rum: false
})
