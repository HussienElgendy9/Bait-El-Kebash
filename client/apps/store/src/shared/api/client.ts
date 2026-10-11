import type { ApiErrorBody } from "@/shared/types/api"

const BASE_URL = import.meta.env.VITE_API_URL ?? "http://localhost:8000/api"
export const TOKEN_KEY = "auth_token"

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

export async function api<T>(path: string, { body, params, headers, ...init }: Options = {}): Promise<T> {
  const url = new URL(BASE_URL + path)
  for (const [k, v] of Object.entries(params ?? {})) {
    if (v !== undefined && v !== "") url.searchParams.set(k, String(v))
  }

  const token = localStorage.getItem(TOKEN_KEY)
  let res: Response
  try {
    res = await fetch(url, {
      ...init,
      headers: {
        Accept: "application/json",
        ...(body !== undefined && { "Content-Type": "application/json" }),
        ...(token && { Authorization: `Bearer ${token}` }),
        ...headers,
      },
      body: body !== undefined ? JSON.stringify(body) : undefined,
    })
  } catch {
    throw new ApiError(0, "network")
  }

  const json = res.status === 204 ? null : await res.json().catch(() => null)

  if (!res.ok) {
    const err = (json ?? {}) as ApiErrorBody
    if (res.status === 401 && token) {
      localStorage.removeItem(TOKEN_KEY)
      const next = encodeURIComponent(location.pathname + location.search)
      location.assign(`/login?next=${next}&expired=1`)
    }
    throw new ApiError(res.status, err.message ?? res.statusText, err.errors)
  }

  // Single resources come as `{ data }`; paginated ones keep `{ data, links, meta }`.
  if (json && typeof json === "object" && Object.keys(json).length === 1 && "data" in json) {
    return json.data as T
  }
  return json as T
}
