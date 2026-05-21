# WordPress.org Plugin Developer FAQ — Key Notes for Submission

Source: https://developer.wordpress.org/plugins/wordpress-org/plugin-developer-faq/
Fetched: 2026-04-19

---

## Submission Process

| Item | Detail |
|------|--------|
| Upload URL | wordpress.org/plugins/developers/add/ |
| Max ZIP size | 10 MB (remove test folders, node_modules, vendor) |
| Review time | Up to **14 business days** after initial review |
| Fixes reply time | Up to **10 business days** |
| Inactivity timeout | Rejected after **3 months** of no progress |
| One at a time | Only **1 plugin** per account in queue at a time |
| Wrong account? | Reply to email — they transfer ownership, don't resubmit |

## Slug Rules

- Slug = Plugin Name header, lowercased + hyphenated
- Cannot start with someone else's trademark
- Cannot use some terms at all (trademark owners requested removal)
- Cannot contain "wordpress" or "plugin" (except extreme cases)
- Only English letters and Arabic numbers in slug
- **Slug cannot be changed after approval — ever**
- Display name CAN be changed (update both readme.txt and main plugin file)

## SVN Repository Layout

```
trunk/          ← production code (readme.txt + main plugin file at root, NOT in subdirectory)
tags/2.0.0/     ← released versions (numbers and periods only, e.g. 2.8.4)
assets/         ← icons, banners, screenshots
```

- Keep as few old tags as possible (last 1–2)
- No SVN externals (won't be included in ZIP)
- No compressed files (zip inside zip)
- Minified JS OK if non-minified version is also included or linked
- `Stable Tag` in readme.txt controls what version users get — never use "trunk"

## Plugin Page / readme.txt

- Page updates every few minutes; allow up to **6 hours** for changes
- `Tested Up To` = latest WP version you actually tested against (never exceed current RC)
- Only first **5 tags** display on wp.org (max 12 in readme)
- Changelog: keep current major + one version back; older → changelog.txt
- Videos: paste YouTube/Vimeo URL on its own line (embedding must be allowed)
- Banners/icons go in `assets/` folder with specific file names

## Assets Naming (for reference)

| File | Size |
|------|------|
| icon-128x128.png | 128×128 |
| icon-256x256.png | 256×256 |
| banner-772x250.png | 772×250 |
| banner-1544x500.png | 1544×500 |
| screenshot-1.png, screenshot-2.png … | any size |

## Going Live

- Plugin goes live **as soon as you push to SVN** — don't push until ready
- Search indexing takes **6–14 days** after first SVN commit
- Better ranking: good readme, fast support responses, good reviews

## Common Rejection Reasons

- Plugin does "nothing" or is illegal/unethical
- Framework/library-only plugin with no user-facing functionality
- 100% copy of another plugin
- Missing readme.txt for a service-based plugin
- Not tested with WP_DEBUG
- "Phoning home" without disclosure

## Mistakes to Avoid

- Don't resubmit to "redo" a plugin — update the existing one
- Don't submit multiple plugins simultaneously
- Don't create multiple accounts to get around the 1-plugin-at-a-time limit
- Don't expect deadline exceptions — submit early
- Don't use official brand logos (e.g. Google, Stripe) in your banner/icon

## Support & Closed Plugins

- Subscribe to forum notifications at: `https://wordpress.org/support/plugin/YOURPLUGIN`
- Closing a plugin is **permanent** (except by emailing plugins@wordpress.org immediately)
- Plugins with >10,000 users require email to close or transfer
- After 60 days closed, closure reason becomes public
- Security issues → email plugins@wordpress.org
- Ownership transfer via Advanced tab Danger Zone

## Contact

- General / review questions: plugins@wordpress.org (≤7 business days response)
