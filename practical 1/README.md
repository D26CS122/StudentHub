# StudentHub

A modern, premium student portal — a single record for attendance, assignments, study material, timetable, notices, profile and admin, built as a static multi-page site (HTML/CSS/JS, no build step, no backend).

## Design concept — "The Ledger"

Every AI-generated portal reaches for the same three looks (cream + terracotta, near-black + acid green, or a broadsheet grid). StudentHub goes a different way: **a private-university letterhead brought to a screen** — deep ink-navy surfaces, a struck-gold seal used as the recurring progress motif (attendance rings, grade bars, completion), a serif of record (**Fraunces**) for headings, a quiet grotesk (**Inter**) for interface text, and a mono face (**IBM Plex Mono**) for anything numeric — percentages, roll numbers, dates — so data reads like an entry in a register rather than UI chrome.

**Signature element:** the gold progress ring. It appears at three scales — small in stat cards, large on the Attendance page, and as animated bar-fills in tables — so attendance, grades and completion all speak the same visual language.

**Motion:** page-load fades, scroll-triggered reveals (`IntersectionObserver`), rings and bars that draw in on load, a soft cross-fade between pages, and hover/press micro-interactions on buttons and rows. All motion respects `prefers-reduced-motion`.

## Structure

```
StudentHub/
│── index.html            Marketing landing page
│── login.html             Sign-in (student / administrator toggle, demo auth)
│── dashboard.html         Student overview: stat rings, today's timetable, notices
│── attendance.html        Attendance ring + subject-wise breakdown table
│── assignments.html       Filterable assignment cards (pending / submitted / graded)
│── study-material.html    Subject folders with resource lists + search field
│── timetable.html         Weekly grid timetable with a highlighted current period
│── notices.html           Filterable notice timeline
│── profile.html            Student profile & emergency contact form
│── contact.html            Contact form + department directory
│── admin.html               Admin dashboard: records table, notice publisher, approvals
│
├── css/style.css          Full design system (tokens, layout, components, animation)
├── js/script.js           Shared interactions (see below)
├── images/                (empty — add your own photography/illustrations here)
├── docs/                  (place sitemap, wireframes, requirements here)
└── README.md
```

## Shared interactions (`js/script.js`)

- **Reveal on scroll** — `.reveal` elements fade/slide in via `IntersectionObserver`.
- **Progress rings** — any `.ring-wrap[data-value="N"]` animates its stroke to N% on load.
- **Animated table bars** — `.bar-fill` elements grow to their target width on load.
- **Page transitions** — internal links cross-fade through a veil before navigating.
- **Mobile sidebar** — `.menu-toggle` opens/closes `.sidebar` under 860px width.
- **Tabs/filters** — any `.tabs[data-target="#id"]` filters `[data-status]` cards inside `#id`.
- **Demo forms** — the login and contact forms validate lightly and simulate submission; there is no backend.

## Running it

No build tools required. Open `index.html` directly in a browser, or serve the folder with any static server, e.g.:

```
npx serve StudentHub
```

## Notes

- This is a front-end prototype: forms, sign-in and "Publish notice" are visual demos only (no server, no database, no persistence).
- All names, IDs and figures are placeholders — replace them with real data from your institution's records system.
- To wire this up to a real backend, the most natural seams are: `login.html`'s `#login-form` (auth), `attendance.html`/`assignments.html`'s tables (data feed), and `admin.html`'s "Publish notice" form (notice creation).
