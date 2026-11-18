# Leaky Blog

A working Symfony blog that **leaks state between requests when it runs in worker mode** (FrankenPHP), on purpose.
It is the [official Symfony Demo application](https://github.com/symfony/demo) (`v3.1.0`) with a dozen ordinary
features added: a "Recently viewed" sidebar, a tag cloud, breadcrumbs, a timezone picker, an audit log... Each one
hides a classic worker-mode bug that [Igor](https://github.com/igor-php/igor-php) reports.

It is the demonstration project of [igor-doctor](https://github.com/monitaurus/doctor), a Symfony command that fixes
Igor's findings with an AI agent and has Igor verify each fix. Run the blog, see the leaks in your browser, let the
doctor fix them, and see that the blog still works.

> The leaks are deliberate. Do not deploy this application.

## Run it

Only Docker is required:

```bash
make start
```

| URL | Runtime |
|---|---|
| <http://localhost:8080> | **Worker mode**: Symfony boots once, one PHP worker serves every request |
| <http://localhost:8081> | **Classic mode**: Symfony boots on every request, nothing leaks |

The badge in the bottom-right corner shows the mode and the PHP process id: in worker mode the PID stays the same from
one request to the next. Both services use the bundled SQLite database (`data/database.sqlite`). Users: `jane_admin` /
`kitten` (admin) and `john_user` / `kitten`.

`make restart` restarts the workers (needed after changing `src/`), `make stop` stops everything,
`make logs` follows the logs.

Without Docker, `composer install` then `symfony serve` (or `php -S localhost:8000 -t public`) serves the blog in
classic mode: everything works, nothing leaks.

## Leak tour

Open the worker URL in two browsers, or a normal and a private window, so you are two different readers. Do the
same on the classic URL: nothing leaks there.

| # | Feature | Try it | What leaks in worker mode | Igor rule |
|---|---|---|---|---|
| 1 | "Recently viewed" sidebar on a post | Reader A opens two posts. Reader B opens one. | B's sidebar lists A's posts. | `StateMutation` |
| 2 | A rejected comment is shown again with its text | A (`john_user`) comments `Write to me at john@example.com` (rejected: no `@`). B (`jane_admin`) opens any post. | B's comment form is prefilled with A's text. | `IncompleteReset` |
| 3 | Tag cloud on the blog index | Note the counts. As `jane_admin`, publish a post with a new tag. | The new tag never appears, the counts never change. | `StateMutation` (static property) |
| 4 | Reading time of each post | As `jane_admin`, double the length of a post you have already viewed. | Its reading time does not change. | `LocalStaticVariable` |
| 5 | "Show dates in" timezone picker (footer) | A picks `Asia/Tokyo`. B, who never picked one, reloads. | B sees dates in Tokyo time. | `ProcessStateMutation` |
| 6 | Search | Search for `lorem`. | Works in the browser, but the search reads `$_GET`: the test client does not fill it, so `testAjaxSearch` fails. | `SuperglobalsUsage` |
| 7 | Comment notifications sent after the response | Post three comments, then `make logs`. | The 2nd comment sends 2 notifications, the 3rd sends 3. | `DetectClosureStateLeak` |
| 8 | Admin audit log (`/admin/audit-log`) | As `jane_admin`, edit a post, then open the audit log. | Empty: it is written in `__destruct()`, which runs only when the worker stops. | `MagicMethodDestruct` |
| 9 | "Download as Markdown" on a post | Download a post, then reload the page. | The PID in the badge changed: `exit` killed the worker, and every in-memory state with it. | `ExecutionTerminator` |
| 10 | Breadcrumbs | Open a post, then search, then another post. | The trail keeps growing with every page anyone opened. | `MissingResetTag` |
| 11 | "Posts per page: 10 30" on the blog index | A clicks `30`. B opens the blog. | B gets 30 posts per page. | `DetectSingletonMutation` |
| 12 | Search index listener | (log only) A failed save is followed by a successful one. | The failed post is indexed with the next save. | `DoctrineFlushState` |
| 13 | RSS feed always has 10 items | (log only) An error while the feed is built. | The feed's page size sticks for every reader. | `UnsafeStateRestore` |

Leak 9 restarts the worker, which clears the others: do it last.

Symfony Demo itself brings three more findings (`ControllerSubscriber`, `SourceCodeExtension`, `AppExtension::getLocales()`).

Each leak was added in its own commit (`git log upstream-v3.1.0..`), so `git show <commit>` shows the feature and the
bug together.

## Audit with Igor

Igor is installed with Composer (`igor-php/igor-php`, dev) and its bundle is registered for `dev` and `test`:

```bash
composer install
vendor/bin/igor-php .              # or: make audit
```

## Fix with igor-doctor

From a clone of [igor-doctor](https://github.com/monitaurus/doctor):

```bash
bin/demo-app --allow-behaviour-change
```

It copies this repository to `var/demo-app`, runs the doctor on the copy and saves the changes as
`var/demo-app.diff`. To see the fixed blog, run `make start` in `var/demo-app` and take the leak tour again.

## Tests

```bash
bin/phpunit
```

On the leaky version, `testAjaxSearch` fails (leak 6). Everything else passes.

## License

MIT, like the Symfony Demo application: see [LICENSE](LICENSE). The original README is kept as
[README.symfony-demo.md](README.symfony-demo.md).
