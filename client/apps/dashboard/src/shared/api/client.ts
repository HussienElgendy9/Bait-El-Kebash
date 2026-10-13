import type { ApiErrorBody } from "@/shared/types/api"

const BASE_URL = import.meta.env.VITE_API_URL ?? "http://localhost:8000/api"

export class ApiError extends Error {
  status: number
  errors: Record<string, string[]>

  constructor(status: number, message: string, errors: Record<string, string[]> = {}) {
    super(message)
    this.status = status
    this.errors = errors
  }
}

type Options = Omit<RequestInit, "body"> & {
  body?: unknown
  params?: Record<string, string | number | undefined>
}

const xsrfToken = () => {
  const m = document.cookie.match(/(?:^|; )XSRF-TOKEN=([^;]*)/)
  return m ? decodeURIComponent(m[1]!) : undefined
}

/** Sanctum SPA auth: the session cookie rides along, and writes echo the XSRF-TOKEN cookie back as a header. */
async function ensureXsrf(refresh = false) {
  if (refresh || !xsrfToken()) await fetch(new URL("/sanctum/csrf-cookie", BASE_URL), { credentials: "include" })
  return xsrfToken()
}

export async function api<T>(path: string, options: Options = {}, retried = false): Promise<T> {
  const { body, params, headers, ...init } = options
  const url = new URL(BASE_URL + path)
  for (const [k, v] of Object.entries(params ?? {})) {
    if (v !== undefined && v !== "") url.searchParams.set(k, String(v))
  }

  const write = !!init.method && init.method !== "GET"
  const isJson = body !== undefined && !(body instanceof FormData)
  let res: Response
  try {
    const xsrf = write ? await ensureXsrf(retried) : undefined
    res = await fetch(url, {
      ...init,
      credentials: "include",
      headers: {
        Accept: "application/json",
        ...(isJson && { "Content-Type": "application/json" }),
        ...(xsrf && { "X-XSRF-TOKEN": xsrf }),
        ...headers,
      },
      // FormData sets its own multipart boundary.
      body: isJson ? JSON.stringify(body) : (body as FormData | undefined),
    })
  } catch {
    throw new ApiError(0, "network")
  }

  // 419: the XSRF token outlived its session; fetch a fresh one and retry once.
  if (res.status === 419 && !retried) return api(path, options, true)

  const json = res.status === 204 ? null : await res.json().catch(() => null)

  if (!res.ok) {
    const err = (json ?? {}) as ApiErrorBody
    // `/user` 401 just means "guest" (see getMe); anywhere else the session expired.
    if (res.status === 401 && path !== "/user") {
      location.assign(`/login?next=${encodeURIComponent(location.pathname + location.search)}`)
    }
    throw new ApiError(res.status, err.message ?? res.statusText, err.errors)
  }

  // Single resources come as `{ data }`; paginated ones keep `{ data, links, meta }`.
  if (json && typeof json === "object" && Object.keys(json).length === 1 && "data" in json) {
    return json.data as T
  }
  return json as T
}
