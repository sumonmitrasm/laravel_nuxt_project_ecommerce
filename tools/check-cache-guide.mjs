import fs from 'node:fs'
import path from 'node:path'
import os from 'node:os'
import { spawn, spawnSync } from 'node:child_process'
import { fileURLToPath, pathToFileURL } from 'node:url'
import assert from 'node:assert/strict'

const root = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '..')
const file = path.join(root, 'adminpanel/docs/CACHE_GUIDE_BN.html')
const html = fs.readFileSync(file, 'utf8')
const decode = value => value.replaceAll('&quot;', '"').replaceAll('&gt;', '>').replaceAll('&lt;', '<').replaceAll('&amp;', '&')
const ids = Array.from(html.matchAll(/\bid="([^"]+)"/g), match => match[1])
assert.equal(new Set(ids).size, ids.length, 'Duplicate HTML IDs')
for (const match of html.matchAll(/href="#([^"]+)"/g)) assert.ok(ids.includes(match[1]), `Broken anchor ${match[1]}`)
assert.ok(!html.includes('__VERIFICATION__'))
assert.equal((html.match(/class="source"/g) || []).length, 34)
for (const match of html.matchAll(/<details id="source-\d+" class="source">[\s\S]*?<summary>[\s\S]*?<code>([^<]+)<\/code><\/summary>[\s\S]*?<pre><code>([\s\S]*?)<\/code><\/pre>/g)) {
  assert.equal(decode(match[2]), fs.readFileSync(path.join(root, decode(match[1])), 'utf8'), 'Source snapshot differs: ' + match[1])
}
const blocks = Array.from(html.matchAll(/<pre><code>([\s\S]*?)<\/code><\/pre>/g), match => decode(match[1]))
let phpChecks = 0
for (const source of blocks) {
  if (!source.startsWith('<?php') && !source.startsWith('use App\\') && !source.startsWith('// AppServiceProvider.php')) continue
  const php = source.startsWith('<?php') ? source : '<?php\n' + source
  const result = spawnSync('php', ['-l'], { input: php, encoding: 'utf8' })
  assert.equal(result.status, 0, result.stdout + result.stderr)
  phpChecks++
}
assert.ok(blocks.some(source => source.includes('\\App\\Models\\Setting::observe')))
console.log(`HTML anchors/source blocks valid; ${phpChecks} embedded PHP files/examples passed syntax checks.`)

const chrome = process.env.CHROME_PATH || 'C:/Program Files/Google/Chrome/Application/chrome.exe'
const profile = fs.mkdtempSync(path.join(os.tmpdir(), 'cache-guide-browser-'))
const child = spawn(chrome, ['--headless', '--disable-gpu', '--no-first-run', '--no-default-browser-check', '--remote-debugging-port=0', '--user-data-dir=' + profile, 'about:blank'])
let socket
try {
  const portFile = path.join(profile, 'DevToolsActivePort')
  for (let count = 0; !fs.existsSync(portFile) && count < 100; count++) await new Promise(resolve => setTimeout(resolve, 200))
  const [port, endpoint] = fs.readFileSync(portFile, 'utf8').trim().split(/\r?\n/)
  socket = new WebSocket('ws://127.0.0.1:' + port + endpoint)
  await new Promise((resolve, reject) => { socket.onopen = resolve; socket.onerror = reject })
  let next = 0
  const pending = new Map()
  const errors = []
  socket.onmessage = event => {
    const message = JSON.parse(event.data)
    if (message.method === 'Runtime.exceptionThrown') errors.push(message.params.exceptionDetails.text)
    if (pending.has(message.id)) {
      const { resolve, reject, timer } = pending.get(message.id)
      clearTimeout(timer); pending.delete(message.id)
      if (message.error) reject(new Error(JSON.stringify(message.error)))
      else resolve(message.result)
    }
  }
  function call(method, params = {}, sessionId) {
    return new Promise((resolve, reject) => {
      const id = ++next
      const timer = setTimeout(() => { pending.delete(id); reject(new Error('CDP timeout: ' + method)) }, 15000)
      pending.set(id, { resolve, reject, timer })
      socket.send(JSON.stringify({ id, method, params, sessionId }))
    })
  }
  const { targetId } = await call('Target.createTarget', { url: 'about:blank' })
  const { sessionId } = await call('Target.attachToTarget', { targetId, flatten: true })
  const run = (method, params) => call(method, params, sessionId)
  await run('Runtime.enable')
  await run('Page.enable')
  await run('Page.navigate', { url: pathToFileURL(file).href })
  async function evaluate(expression) {
    const result = await run('Runtime.evaluate', { expression, returnByValue: true, awaitPromise: true })
    assert.ok(!result.exceptionDetails, JSON.stringify(result.exceptionDetails))
    return result.result.value
  }
  for (let count = 0; count < 50; count++) {
    if (await evaluate('document.readyState === "complete" && !!document.getElementById("search")')) break
    await new Promise(resolve => setTimeout(resolve, 100))
  }
  for (const [width, height] of [[1440, 1000], [390, 844]]) {
    await run('Emulation.setDeviceMetricsOverride', { width, height, deviceScaleFactor: 1, mobile: false })
    assert.ok(await evaluate('document.documentElement.scrollWidth <= innerWidth + 1'), `Horizontal page overflow at ${width}px`)
    await run('Page.captureScreenshot', { format: 'png' }).then(result => fs.writeFileSync(path.join(profile, `guide-${width}.png`), Buffer.from(result.data, 'base64')))
  }
  assert.equal(await evaluate(`document.getElementById('expand').click(); Array.from(document.querySelectorAll('details.source')).every(item => item.open)`), true)
  assert.equal(await evaluate(`document.getElementById('expand').click(); Array.from(document.querySelectorAll('details.source')).every(item => !item.open)`), true)
  assert.equal(await evaluate(`document.getElementById('search').value = 'NO_MATCH_12345'; document.getElementById('search').dispatchEvent(new Event('input')); document.querySelectorAll('main > section:not([hidden])').length`), 0)
  assert.equal(await evaluate(`document.getElementById('search').value = ''; document.getElementById('search').dispatchEvent(new Event('input')); document.querySelectorAll('main > section:not([hidden])').length`), 12)
  await evaluate(`location.hash = '#source-0'; window.dispatchEvent(new Event('hashchange'))`)
  assert.equal(await evaluate(`document.getElementById('source-0').open`), true)
  await evaluate(`document.querySelector('.copy').click(); new Promise(resolve => setTimeout(resolve, 200))`)
  assert.ok(await evaluate(`/Copied|Selected/.test(document.querySelector('.copy').textContent)`))
  assert.deepEqual(errors, [])
  console.log('Chrome: desktop/mobile overflow checks, search, expand/collapse, source anchors and copy/select fallback passed; no JavaScript exceptions.')
  console.log('Browser screenshots: ' + profile)
  await call('Browser.close')
} finally {
  if (socket) socket.close()
  child.kill()
}
