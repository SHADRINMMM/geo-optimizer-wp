=== Causabi GEO Optimizer ===
Contributors: shadrinmmm
Tags: schema, seo, chatgpt, ai-search, ai-visibility
Requires at least: 5.8
Tested up to: 7.0.2
Stable tag: 1.2.2
Requires PHP: 8.1
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Check and fix your AI search visibility: schema markup, llms.txt, and robots.txt for ChatGPT, Gemini, Claude, and Google AI Overviews.

== Description ==

**Is your site invisible to ChatGPT and AI search? Find out, then fix it in minutes.** This plugin checks your site the way AI engines do, then adds what's missing. No coding.

When someone asks ChatGPT, Perplexity, or Google's AI a question your business could answer, the AI picks which sites to cite from machine-readable signals — Schema.org structured data, an llms.txt file, a robots.txt that doesn't block the AI crawlers. If those are missing, you get skipped, no matter how good your content is.

Causabi GEO Optimizer scans your site, scores it, and adds the markup AI engines look for — automatically.

**What it does:**

* Adds **smart Schema.org markup (structured data)** — the correct type per business (Restaurant, CafeOrCoffeeShop, BankOrCreditUnion, and 40+ more), not just a generic "Organization"
* Adds **FAQ Schema** — research shows FAQ markup can raise AI citation rate by up to **41%**
* Serves an **llms.txt** file — a plain-text summary of your site for AI agents that read it
* Checks your **robots.txt** for blocked AI crawlers (GPTBot, ClaudeBot, PerplexityBot, and more) and can add explicit Allow rules
* **Language-aware** — your FAQ and markup are generated in your site's own language
* Shows your **AI Readiness Score** (0–100) with a full breakdown by category
* Tells you exactly which issues are hurting your AI visibility, and why
* Auto-refreshes your markup every 7 days so it stays current
* **No coding required** — works out of the box

**How it works:**

1. You install the plugin and enter your free API key from causabi.com
2. We crawl your site and generate the right Schema.org markup
3. The markup is automatically added to every page on your site
4. You see your AI Readiness Score and a list of issues to fix

**AI search is part of SEO now.** ChatGPT, Gemini, Grok, and Google's AI Overview all read the same machine-readable signals — Schema.org, llms.txt, robots.txt — that this plugin manages. Think of it as the AI-visibility layer of your existing SEO, not a separate discipline.

**Free to use.** Get your API key at [causabi.com](https://causabi.com).

== Installation ==

1. Upload the plugin to the `/wp-content/plugins/` directory, or install it directly through the WordPress plugin screen.
2. Activate the plugin through the **Plugins** screen in WordPress.
3. Go to **Settings → GEO Optimizer**.
4. Get your free API key at [causabi.com](https://causabi.com) and paste it in.
5. Click **Save & Connect** — your site is analyzed automatically within 30 seconds.

== Frequently Asked Questions ==

= What is an AI Readiness Score? =

It's a score from 0 to 100 that measures how well ChatGPT, Gemini, Grok, Claude, and other AI search engines can find, understand, and cite your website. A higher score means more chances of appearing in AI-generated answers.

= Does this plugin slow down my site? =

No. The Schema.org markup is cached for 24 hours and served from WordPress Transients — it adds a single tiny `<script>` tag to your page's `<head>`. No extra page load time.

= What is Schema.org markup? =

Schema.org is a standard way to describe your website to search engines and AI. It's added as a hidden `<script type="application/ld+json">` tag in your page's `<head>`. It doesn't change how your site looks — only how AI and search engines understand it.

= Is this plugin free? What's paid? =

The plugin itself is 100% free — Schema.org markup, llms.txt, robots.txt checks, and the auto-refresh every 7 days all work on the free API key, no time limit. What's paid (on causabi.com, not required to use this plugin) is the cloud dashboard: AI-generated FAQ content tailored to your business, citation monitoring across ChatGPT/Gemini/Grok, and done-for-you fixes if you'd rather not touch settings yourself.

= How is this different from other Schema plugins? =

Most Schema plugins require you to manually fill in all your business details. GEO Optimizer automatically generates the right markup by analyzing your existing site content. We also focus specifically on AI search engines, not just Google.

= Will this affect my existing SEO? =

Only positively. Schema.org markup is recommended by Google and all major search engines. It doesn't interfere with any existing SEO plugins.

== External Services ==

This plugin connects to the **Causabi API** (`https://ai.causabi.com`) to analyze your website and generate AI-optimized Schema.org markup.

**What data is sent:** Your website URL and domain name.

**When it is sent:**
* When you first save your API key and the plugin performs its initial analysis
* When you click "Refresh Now" in the settings page
* Automatically every 7 days via WP-Cron to keep your markup current

No personal data of your site visitors is ever sent to Causabi.

This service is provided by Causabi. By using this plugin you agree to their [Terms of Service](https://causabi.com/terms) and [Privacy Policy](https://causabi.com/privacy).

== Screenshots ==

1. AI Readiness Score dashboard — see your score and breakdown by category
2. Score Breakdown — understand exactly what each category means for your visibility
3. Issues list — clear explanation of what to fix and why
4. Dashboard widget — your score visible on the WP Admin home screen
5. Onboarding — 3-step setup, no coding required

== Changelog ==

= 1.2.2 =
* Fixed: on first-ever save, the API key could be encrypted twice due to a WordPress core quirk (`update_option` re-running the sanitize callback), leaving it permanently unusable. The plugin now detects and repairs already-affected installs automatically — no action needed.
* Fixed: if the initial site scan failed (network issue or invalid key), the plugin used to silently show "Analyzing..." for up to an hour with no way to retry. It now shows a clear error message with a retry link right away.

= 1.2.1 =
* Added an optional in-dashboard reminder to leave a review (appears once, a week after setup, and only after your site has a score)

= 1.2.0 =
* Added llms.txt support — serves a plain-text summary of your site at /llms.txt, refreshed every 7 days along with your Schema.org markup
* Added a robots.txt check that warns if AI crawlers (GPTBot, ClaudeBot, PerplexityBot, and others) are blocked, with an option to add explicit Allow rules
* Both features can be toggled off in Settings → GEO Optimizer

= 1.1.2 =
* Renamed plugin to "Causabi GEO Optimizer" and updated slug to causabi-geo-optimizer (per WordPress.org plugin review)

= 1.1.0 =
* Smart schema.org type detection — 40+ business types (Restaurant, CafeOrCoffeeShop, BankOrCreditUnion, NewsMediaOrganization, etc.)
* Language-aware generation — Russian sites get Russian FAQ and markup, English sites get English
* AI citation monitoring added to Pro plan (ChatGPT, Gemini, Grok)
* Multi-source reviews: Google, Yandex, 2GIS, TripAdvisor
* Improved AI profile generation accuracy

= 1.0.0 =
* Initial release

== Upgrade Notice ==

= 1.1.0 =
Major quality improvement: smarter schema.org types and language-aware FAQ generation. Upgrade recommended.

= 1.0.0 =
Initial release.
