import { useCallback, useEffect, useState } from 'react'
import type { HealthResponse, ParseResponse } from './types/api'
import './App.css'

export default function App() {
  const [api, setApi] = useState<'loading' | 'ok' | 'err'>('loading')
  const [drag, setDrag] = useState(false)
  const [last, setLast] = useState<ParseResponse | null>(null)
  const [busy, setBusy] = useState(false)

  useEffect(() => {
    const ac = new AbortController()
    fetch('/api/health.php', { signal: ac.signal })
      .then((r) => r.json())
      .then((d: HealthResponse) => setApi(d.ok ? 'ok' : 'err'))
      .catch((e: unknown) => {
        if (e instanceof DOMException && e.name === 'AbortError') {
          return
        }
        setApi('err')
      })
    return () => ac.abort()
  }, [])

  const send = useCallback((file: File) => {
    setBusy(true)
    setLast(null)
    const fd = new FormData()
    fd.append('file', file)
    fetch('/api/parse.php', { method: 'POST', body: fd })
      .then((r) => r.json())
      .then((d: ParseResponse) => setLast(d))
      .catch(() => setLast({ ok: false, errors: ['Network error.'] }))
      .finally(() => setBusy(false))
  }, [])

  return (
    <main className="shell">
      <header className="hdr">
        <h1>File reader</h1>
        <p className="lede">CSV, XML, JSON → table (validated on the server)</p>
      </header>
      <section className="panel" aria-live="polite">
        <h2>Backend</h2>
        <p className={`tag tag--${api}`}>
          {api === 'loading' && 'Checking /api/health.php…'}
          {api === 'ok' && 'PHP API: OK'}
          {api === 'err' && 'PHP API: unreachable (run pnpm dev:all)'}
        </p>
      </section>
      <section className="panel">
        <h2>Upload a file</h2>
        <p className="hint">
          Only .csv, .xml, .json (max 2 MB). Errors appear below the form.
        </p>
        <div
          className={`drop${drag ? ' drop--on' : ''}`}
          onDragEnter={(e) => {
            e.preventDefault()
            setDrag(true)
          }}
          onDragLeave={() => setDrag(false)}
          onDragOver={(e) => e.preventDefault()}
          onDrop={(e) => {
            e.preventDefault()
            setDrag(false)
            const f = e.dataTransfer.files[0]
            if (f) {
              send(f)
            }
          }}
        >
          {busy ? 'Processing…' : 'Drop here or'}
          <label className="pick">
            browse
            <input
              type="file"
              className="sr"
              accept=".csv,.xml,.json,application/json,text/csv,text/xml,application/xml"
              disabled={busy}
              onChange={(e) => {
                const f = e.target.files?.[0]
                if (f) {
                  send(f)
                }
                e.target.value = ''
              }}
            />
          </label>
        </div>
        {last && (
          <div className="out" aria-live="polite">
            {last.ok === false && (
              <ul className="errs">
                {last.errors.map((msg, i) => (
                  <li key={i}>{msg}</li>
                ))}
              </ul>
            )}
            {last.ok === true && (
              <>
                <p className="meta">
                  <strong>{last.fileName}</strong> · <code>{last.format}</code>
                </p>
                <div className="table-wrap">
                  <table className="grid">
                    <thead>
                      <tr>
                        {last.columns.map((c) => (
                          <th key={c} scope="col">
                            {c}
                          </th>
                        ))}
                      </tr>
                    </thead>
                    <tbody>
                      {last.rows.map((row, i) => (
                        <tr key={i}>
                          {row.map((cell, j) => (
                            <td key={j}>{cell}</td>
                          ))}
                        </tr>
                      ))}
                    </tbody>
                  </table>
                </div>
              </>
            )}
          </div>
        )}
      </section>
    </main>
  )
}
