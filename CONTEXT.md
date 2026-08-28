# Watchly — Domain Glossary

Short, agreed definitions of the nouns the codebase is built around. One or two lines each.
Not architecture docs — just "what does this word mean in this project".

## Films & collection

- **Film** — a movie imported from TMDB and cached locally (`Film` entity, keyed by `tmdbId`). Genres are synced alongside it.
- **Collection entry** — one user's relationship to one Film (`UserCollection`): a status of `WATCHLIST` or `WATCHED`, an optional 1–5 rating, and a favorite flag. At most one per (user, film).
- **List** — a user-curated, ordered set of Films (`MovieList`), `PUBLIC` or `PRIVATE`, owned by one user.
- **Review** — one user's free-text opinion on one Film. At most one per (user, film); only allowed once the film is `WATCHED`.

## Moderation

- **Reportable** — content a user can flag: currently a list **comment** (`ListComment`) or a **Review**. Exposes its author, so the "can't report your own content" rule works uniformly.
- **Report** — a user's flag on a Reportable, with an optional free-text reason. Deduplicated per (target, reporter); a user can't report the same content twice. Stored single-table (`report`) with a `discr` discriminator; `CommentReport` / `ReviewReport` are the per-target subclasses.
- **Filing a report** — `ReportService::file()`: validates the reason, rejects self-reports and duplicates, persists the right subclass.
- **Resolving a report** — an admin action (`ReportService::resolve()`): **keep** (dismiss the report, leave the content) or **delete** (remove the reported content; the DB cascade clears its reports).
- **Moderation queue** — the admin view of all open Reports (`GET /api/admin/reports`), one row per report regardless of target type.
