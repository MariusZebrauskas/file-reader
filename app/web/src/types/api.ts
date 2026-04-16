export type HealthResponse = { ok: boolean }

export type ParseSuccess = {
  ok: true
  format: string
  fileName: string
  columns: string[]
  rows: string[][]
}

export type ParseError = { ok: false; errors: string[] }

export type ParseResponse = ParseSuccess | ParseError
