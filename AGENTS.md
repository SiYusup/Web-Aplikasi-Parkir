# AGENTS.md — aplikasi_pengelolaan_parkir_kendaraan

Vanilla PHP (no framework) + MySQL + Apache/Laragon. Only runtime deps: `dompdf/dompdf`; dev: `phpunit/phpunit 13`.

- Entry: `public/index.php` — all routes registered there. Docroot is `public/`; `.htaccess` rewrites to `index.php/$1`, router reads `$_SERVER['PATH_INFO']` (`app/App/Router.php`). Sets `Asia/Jakarta` timezone, `session_start()`.
- Layers: `app/Controller → app/Service → app/Repository → app/Domain|Model`. Shared: `app/App/Router.php`, `app/App/View.php`, `app/Config/Database.php`, `app/Middleware/`, `app/Exception/ValidationException.php`. Tables: `tb_users`, `tb_parkir` (soft-delete via `deleted_at IS NULL`).
- Routes are exact-match regex (`"#^$path$#"`), no params. New page = `Router::add()` in `index.php` + method on `*Controller` + view in `app/View/`.
- Views: plain PHP via `View::render('path/name', $model)` → `header.php + View/$view.php + footer.php`. Templates read `$model['title']`, `$model['user']`, `$model['error']`, `$model['old']`. No templating engine (Bootstrap 5 via CDN in `header.php`).
- DB: `config/database.php` — `test` and `prod` are identical (`mysql:host=localhost:3306;dbname=db_parkir`, `root`/`""`). `Database::getConnection($env = "test")` is a singleton — first call wins. Requires live MySQL; no migrations/seeds in repo.
- Test: `vendor/bin/phpunit tests/Config/DatabaseTest.php`, single test `vendor/bin/phpunit --filter testConnection tests/Config/DatabaseTest.php`. No root `phpunit.xml`, no composer scripts. Tests hit real `db_parkir`; `UserRepositoryTest::setUp` runs `DELETE FROM tb_users` — destructive, use local DB only.
- Auth: `SessionService` cookie `parkir_login = username` (1 day, path `/`), no session table. Guard routes with `MustLoginMiddleware` / `MustNotLoginMiddleware`. `View::redirect()` sets `View::$lastRedirect` and skips `exit()` only when `getenv("mode") == "test"`.
- Conventions: Services validate input and throw `ValidationException`; Controllers catch → re-render with `error`/`old` or `setFlash()` + `redirect`. Plat normalized `strtoupper(trim())`; fee logic in `ParkirService::tarif()/hitungBiaya()` (`roda_2`: 2000+1000/jam, `roda_4`: 5000+1000/jam, min 1 jam). PDF via `Dompdf` in `ParkirController::laporanPdf()`.
- Gotchas: duplicate `GET /laporan/pdf` route in `index.php`; `UserRepository::save()` only inserts `(username, password)` (drops `full_name`/`role`); `tests/Repository/UserRepositoryTest.php` uses stale namespace `UCrazy\KasirTokoSederhana`; `package.json` is leftover (`npm test` is a placeholder — ignore).
