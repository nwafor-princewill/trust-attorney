# Deploying to Railway — step by step

This app is now set up to deploy on Railway with **zero code edits** — the
`Dockerfile` and `config.php` read the database credentials from Railway's
environment variables automatically.

## 0. Fix your Railway account first
Your account shows "Trial Ended." You'll need to add a payment method /
upgrade to a paid plan (Railway has a low-cost Hobby plan) before it will
let you deploy or spin up a database — do that first at
https://railway.com/workspace/plans, or spin up a fresh account if you'd
rather start clean.

## 1. Push this folder to GitHub
Railway deploys from a Git repo. Create a new repo (public or private,
either is fine) and push everything in this folder to it — including
`Dockerfile` and `entrypoint.sh`.

## 2. Create the Railway project
- Railway dashboard → **New Project** → **Deploy from GitHub repo** → pick
  the repo you just pushed.
- Railway will detect the `Dockerfile` and build from it automatically.

## 3. Add a MySQL database
- In the same project → **+ New** → **Database** → **Add MySQL**.
- That's it — Railway automatically injects `MYSQLHOST`, `MYSQLUSER`,
  `MYSQLPASSWORD`, `MYSQLDATABASE`, `MYSQLPORT` into your app service as
  environment variables. `config.php` already reads these, so you don't
  need to type in any DB credentials manually.

## 4. First boot = schema import (automatic)
On container start, `entrypoint.sh` waits for MySQL to accept connections
and imports `sql/schema.sql`. It's safe to redeploy — the schema uses
`CREATE TABLE IF NOT EXISTS`, so it won't wipe existing data.

## 5. Set a couple of optional env vars
On the app service → **Variables**, you can optionally add:
- `SITE_URL` → your real domain once you have one (e.g. `https://yourdomain.com`)
- `SUPPORT_EMAIL`, `SUPPORT_PHONE` → real contact details
These all have safe placeholder defaults if you skip them for now.

## 6. Change the demo admin password
`sql/schema.sql` seeds an admin login (`admin` / `Admin@123`) — **change
this before going live.** Easiest way: generate a new bcrypt hash locally
with `php -r "echo password_hash('yournewpassword', PASSWORD_BCRYPT);"`,
then run in Railway's MySQL query console:
```sql
UPDATE admins SET password_hash = 'PASTE_HASH_HERE' WHERE username = 'admin';
```

## 7. Test it
Railway gives you a free `*.up.railway.app` URL immediately after deploy —
open it and click through: homepage → sign up → submit an application →
`/admin/login.php` with the admin login to review it.

## 8. Connect your real domain
App service → **Settings** → **Networking** → **Custom Domain** → follow
Railway's instructions to point your domain's DNS at it (usually a CNAME).
Once that's live and confirmed, update the `SITE_URL` variable to match.
