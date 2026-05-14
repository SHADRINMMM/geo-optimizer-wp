=== GEO Optimizer by Causabi ===
Contributors: causabi
Tags: schema, seo, ai, structured-data, ai-search
Requires at least: 5.8
Tested up to: 6.8
Stable tag: 1.1.0
Requires PHP: 8.1
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Make your website visible to ChatGPT, Perplexity, Gemini, and other AI search engines — automatically.

== Description ==

**73% of websites are invisible to AI search engines.** This plugin fixes that in minutes — no coding required.

When someone asks ChatGPT or Perplexity a question, the AI picks websites to cite from. If your site is missing the right structured data, it gets skipped — even if your content is great.

GEO Optimizer analyzes your site and automatically injects the markup that AI engines look for:

**What it does:**

* ✅ Adds **smart Schema.org markup** — correct type per business (Restaurant, CafeOrCoffeeShop, BankOrCreditUnion, and 40+ more), not just generic "Organization"
* ✅ Adds **FAQ Schema** — research shows this boosts citation rate by up to **41%** in Perplexity
* ✅ **Language-aware AI analysis** — RU sites get Russian FAQ and markup, EN sites get English
* ✅ Shows your **AI Readiness Score** (0–100) with a full breakdown
* ✅ Identifies exactly which issues are hurting your AI visibility
* ✅ Auto-refreshes your markup every 7 days to stay current
* ✅ **No coding required** — works out of the box

**How it works:**

1. You install the plugin and enter your free API key from causabi.com
2. We crawl your site and generate the right Schema.org markup
3. The markup is automatically added to every page on your site
4. You see your AI Readiness Score and a list of issues to fix

**What is GEO?**

GEO (Generative Engine Optimization) is the practice of optimizing your website so that AI search engines like ChatGPT, Perplexity, and Google's AI Overview cite your content in their answers. It's like SEO, but for the AI era.

**Free to use.** Get your API key at [causabi.com](https://causabi.com).

== Installation ==

1. Upload the plugin to the `/wp-content/plugins/` directory, or install it directly through the WordPress plugin screen.
2. Activate the plugin through the **Plugins** screen in WordPress.
3. Go to **Settings → GEO Optimizer**.
4. Get your free API key at [causabi.com](https://causabi.com) and paste it in.
5. Click **Save & Connect** — your site is analyzed automatically within 30 seconds.

== Frequently Asked Questions ==

= What is an AI Readiness Score? =

It's a score from 0 to 100 that measures how well ChatGPT, Perplexity, Gemini, and other AI search engines can find, understand, and cite your website. A higher score means more chances of appearing in AI-generated answers.

= Does this plugin slow down my site? =

No. The Schema.org markup is cached for 24 hours and served from WordPress Transients — it adds a single tiny `<script>` tag to your page's `<head>`. No extra page load time.

= What is Schema.org markup? =

Schema.org is a standard way to describe your website to search engines and AI. It's added as a hidden `<script type="application/ld+json">` tag in your page's `<head>`. It doesn't change how your site looks — only how AI and search engines understand it.

= Is this plugin free? =

Yes — the plugin and basic API key are free. Advanced features like FAQ generation via AI and citation monitoring are available on paid plans at causabi.com.

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
