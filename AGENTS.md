# Repository guidelines

This is a small defensive Laravel API case study. Keep changes focused, reviewable, and consistent with the existing Laravel structure.

## Security invariants

- Ticket listing and object access must remain scoped to the authenticated owner.
- Clients must not control `user_id` or `status` through mass assignment.
- Authentication failures must not reveal whether an account exists.
- API responses must not expose secrets, ownership internals, stack traces, or raw exception details.
- Never commit `.env`, access tokens, private keys, production data, or real customer information.

## Validation

Run before committing:

```bash
vendor/bin/pint --test
php artisan test
docker compose config --quiet
```

For changes to migrations, Docker, authentication, or database behavior, also exercise the PostgreSQL stack with `docker compose up --build`.
