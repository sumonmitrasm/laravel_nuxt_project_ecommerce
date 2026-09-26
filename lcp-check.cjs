const WebSocket = require('./frontend/node_modules/ws')

async function main() {
  const url = process.argv[2] || 'https://shahinenterprise.com.bd/'
  const mode = process.argv[3] || 'mobile'
  if (!['mobile', 'desktop'].includes(mode)) throw new Error('Mode must be mobile or desktop')
  const target = await (await fetch('http://127.0.0.1:9222/json/new?about:blank', {
    method: 'PUT',
  })).json()
  const socket = new WebSocket(target.webSocketDebuggerUrl)
  let id = 0
  const pending = new Map()
  const traceEvents = []

  socket.on('message', raw => {
    const message = JSON.parse(raw)
    if (message.method === 'Tracing.dataCollected') traceEvents.push(...message.params.value)
    if (message.id && pending.has(message.id)) {
      pending.get(message.id)(message)
      pending.delete(message.id)
    }
  })

  const send = (method, params = {}) => new Promise((resolve, reject) => {
    const requestId = ++id
    const timer = setTimeout(() => { pending.delete(requestId); reject(new Error(`CDP timeout: ${method}`)) }, 20000)
    pending.set(requestId, message => {
      clearTimeout(timer)
      if (message.error) reject(new Error(`${method}: ${message.error.message}`))
      else resolve(message)
    })
    socket.send(JSON.stringify({ id: requestId, method, params }))
  })

  await new Promise((resolve, reject) => { socket.once('open', resolve); socket.once('error', reject) })
  try {
  await send('Page.enable')
  await send('Network.enable')
  await send('Network.setCacheDisabled', { cacheDisabled: true })
  await send('Emulation.setDeviceMetricsOverride', mode === 'mobile'
    ? { width: 412, height: 823, deviceScaleFactor: 1.75, mobile: true }
    : { width: 1350, height: 940, deviceScaleFactor: 1, mobile: false })
  if (mode === 'mobile') {
    await send('Emulation.setUserAgentOverride', { userAgent: 'Mozilla/5.0 (Linux; Android 11; Moto G Power) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Mobile Safari/537.36' })
    await send('Emulation.setCPUThrottlingRate', { rate: 4 })
  }
  await send('Page.bringToFront')
  await send('Page.addScriptToEvaluateOnNewDocument', { source: `window.__scrollEvents=[];document.addEventListener('scroll',e=>{window.__scrollEvents.push({time:performance.now(),target:e.target.className||e.target.nodeName,left:e.target.scrollLeft,top:e.target.scrollTop})},true)` })
  if (process.env.LCP_TEST_SCRIPT) await send('Page.addScriptToEvaluateOnNewDocument', { source: process.env.LCP_TEST_SCRIPT })
  if (process.env.LCP_TRACE) await send('Tracing.start', { categories: 'loading,devtools.timeline,disabled-by-default-lighthouse', transferMode: 'ReportEvents' })
  if (process.env.LCP_TEST_CSS) {
    const css = JSON.stringify(process.env.LCP_TEST_CSS)
    await send('Page.addScriptToEvaluateOnNewDocument', {
      source: `new MutationObserver((_,o)=>{if(document.head){const s=document.createElement('style');s.textContent=${css};document.head.append(s);o.disconnect()}}).observe(document,{childList:true,subtree:true})`,
    })
  }
  await send('Page.addScriptToEvaluateOnNewDocument', {
    source: `window.__lcp=[];new PerformanceObserver(list=>window.__lcp.push(...list.getEntries().map(e=>({startTime:e.startTime,size:e.size,url:e.url,element:e.element?.tagName})))).observe({type:'largest-contentful-paint',buffered:true});`,
  })
  await send('Page.navigate', { url })
  await new Promise(resolve => setTimeout(resolve, 12000))
  if (process.env.LCP_TRACE) {
    const done = new Promise(resolve => { const listener = raw => { if (JSON.parse(raw).method === 'Tracing.tracingComplete') { socket.off('message', listener); resolve() } }; socket.on('message', listener) })
    await send('Tracing.end')
    await done
    require('node:fs').writeFileSync(process.env.LCP_TRACE, JSON.stringify(traceEvents.filter(e => /[Cc]andidate|[Pp]aint|[Ll]argest/.test(e.name)), null, 2))
  }
  const result = await send('Runtime.evaluate', {
    expression: `JSON.stringify((()=>{const image=document.querySelector('#heroCarousel img');const heading=document.querySelector('#heroCarousel h1');return {lcp:window.__lcp,paint:performance.getEntriesByType('paint'),visibility:document.visibilityState,hero:!!document.querySelector('#heroCarousel'),image:image&&{src:image.currentSrc,complete:image.complete,naturalWidth:image.naturalWidth,naturalHeight:image.naturalHeight,rect:image.getBoundingClientRect().toJSON(),display:getComputedStyle(image).display,visibility:getComputedStyle(image).visibility,opacity:getComputedStyle(image).opacity},heading:heading&&{text:heading.textContent,rect:heading.getBoundingClientRect().toJSON(),display:getComputedStyle(heading).display,visibility:getComputedStyle(heading).visibility,opacity:getComputedStyle(heading).opacity}}})())`,
    returnByValue: true,
  })
  const diagnostics = await send('Runtime.evaluate', {
    expression: `JSON.stringify({viewport:{width:innerWidth,height:innerHeight,scrollX,scrollY},ancestors:(()=>{let e=document.querySelector('#heroCarousel h1'),a=[];while(e){const s=getComputedStyle(e);a.push({tag:e.tagName,class:e.className,opacity:s.opacity,overflow:s.overflow,transform:s.transform,rect:e.getBoundingClientRect().toJSON()});e=e.parentElement}return a})()})`,
    returnByValue: true,
  })
  if (result.result.exceptionDetails) throw new Error(JSON.stringify(result.result.exceptionDetails))
  const scrolling = await send('Runtime.evaluate', { expression: 'JSON.stringify(window.__scrollEvents)', returnByValue: true })
  const report = { mode, url, ...JSON.parse(result.result.result.value), scrollEvents: JSON.parse(scrolling.result.result.value), diagnostics: JSON.parse(diagnostics.result.result.value) }
  if (process.env.LCP_SCROLL_TEST) {
    await send('Runtime.evaluate', { expression: `document.querySelector('.deals-track')?.scrollBy({left:280,behavior:'smooth'})` })
    await new Promise(resolve => setTimeout(resolve, 1500))
    const scrollResult = await send('Runtime.evaluate', { expression: `document.querySelector('.deals-track')?.scrollLeft`, returnByValue: true })
    report.dealsScrollAfter = scrollResult.result.result.value
  }
  if (process.env.LCP_SUMMARY) delete report.diagnostics
  console.log(JSON.stringify(report, null, 2))
  } finally {
  socket.close()
  await fetch(`http://127.0.0.1:9222/json/close/${target.id}`)
  }
}

main().catch(error => {
  console.error(error)
  process.exit(1)
})
