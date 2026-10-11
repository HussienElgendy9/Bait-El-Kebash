/** Mirrors server/app/Http/Resources/UserResource.php (customer view) */
export type User = {
  id: number
  name: string
  email: string
  phone_number: string
  address: string
}

export type AuthResponse = { message: string; user: User; token: string }
