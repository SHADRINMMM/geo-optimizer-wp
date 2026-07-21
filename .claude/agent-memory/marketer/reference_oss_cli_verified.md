---
name: reference-oss-cli-verified
description: "Проверенный статус open-source CLI causabi-geo на PyPI/GitHub — что живо и как звучит README, на 2026-07-15."
metadata:
  type: reference
---

Проверено 2026-07-15 (WebFetch, не просто WebSearch — search индекс не находил пакет вообще, но прямой fetch на pypi.org/github.com подтвердил):
- PyPI `causabi-geo` — версия 0.2.1 (2026-07-06), MIT, `pip install causabi-geo` работает.
- GitHub `SHADRINMMM/causabi-geo` — репозиторий существует, MIT license, README актуален.
- Команды из README: `geo-optimizer analyze <url>`, `geo-optimizer fix <url> [--output DIR] [--api-key GEMINI_KEY]`.
- README сам честно пишет: "The score measures crawlability and machine-readability. It does not guarantee citations — no tool can." (важно — совпадает с тоном честных dev-постов, не противоречит).
- README описывает 6-категорийный scoring (robots/schema/FAQ/content depth/brand-NAP/freshness) — это v1-фрейминг CLI, отдельно от SaaS-бэкенда v2 (scorer v2 задеплоен на прод, но CLI, судя по всему, ещё не апдейтили под v2 категории). Не паниковать при расхождении в постах про CLI — фокус на том, что реально в README сейчас.

**Why:** прошлые 3 поломки в dev-канале (CLI-команда не работала, PyPI-404, llms.txt-ложь) — правило теперь: ПЕРЕД каждым dev-постом с командой/ссылкой проверять WebFetch напрямую на pypi.org/github.com, не полагаться на WebSearch (не индексирует) и не полагаться на память "должно работать".

**How to apply:** перед любым новым dev-постом, где упоминается `pip install causabi-geo` или репозиторий — повторно проверить WebFetch (пакеты обновляются, версии меняются). Если структура команд в README изменится — синхронизировать с этим фактом, не постить по памяти.

См. [[project-dev-audience-n44-finding]].
