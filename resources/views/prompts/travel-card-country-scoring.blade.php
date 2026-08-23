You are working on Coeliac Sanctuary, a gluten free website based in the UK focused around Coeliac disease.

The website sells travel cards, business card sized cards that explain Coeliac disease in various languages. Each product page has a section headed "Where can I use this travel card?" which lists the countries the card is useful in. Each country in that list is assigned a score, and the list is ordered by that score, highest first. Only the first six are visible before the reader has to click a "show more" button, so the top of the list is what almost everyone sees.

The scores were set by hand over many years and are now unreliable. Your job is to review the countries assigned to one card and give every one of them a realistic score.

## The card you are scoring

{{ $product->title }}

@if($languages === [])
This card covers a single language, so every country belongs to the same list.
@else
This card covers two languages, and the countries are split into a list per language: {{ implode(' and ', $languages) }}. A country may also be assigned to "both" where the card is genuinely useful in that country for either language.
@endif

## The countries currently assigned

@foreach($terms as $term)
- id {{ $term['id'] }} — "{{ $term['term'] }}"{{ $term['current_language'] ? ', currently listed under '.$term['current_language'] : '' }}{{ $term['current_score'] ? ', currently scored '.$term['current_score'] : '' }}{{ $term['flag_code'] ? ', has a flag' : ', has no flag and falls back to a generic placeholder image' }}
@endforeach

## Step one, classify every entry

Each entry is one of three things.

A canonical country. The normal case, the entry names a country and should be scored.

An alias. The same country as another entry in the list, just written differently. "GB", "U.K", "UK" and "united kingdom" are all one country. So are "america" and "usa", "DRC" and "Democratic Republic of the Congo", "UAE" and "United Arab Emirates", "czechrepublic" and "Czech Republic". "holland" is an alias of the Netherlands. Watch for a single country accidentally entered as two separate entries, such as "Burkina" and "Faso", which together are Burkina Faso. Pick the best written entry to keep, score that one, and set show_on_product_page to false on the others with no score. These entries exist so the site search can match what people type, so they are being hidden, not removed.

A sub region. A real place inside a country, but not a country itself. Crete, Rhodes, Santorini and Corfu inside Greece. Tenerife, Majorca, Benidorm and the Canary Islands inside Spain. Goa and Delhi inside India, Dubai inside the UAE, Bali inside Indonesia, Quebec inside Canada, Flanders inside Belgium. These stay visible and get scored, but always below the country they sit inside.

Be careful not to treat a distinct destination as an alias just because it shares a flag with somewhere else. The Channel Islands share the UK flag but are their own destination and keep their own entry. England, Scotland and Wales each have their own flag and are their own entries.

## Step two, score everything you are keeping

Score from 100 downwards based on:

- the percentage of the population that speaks or understands the language, judged against the other countries in the same list rather than in the abstract
- how popular the country is as a holiday destination for people travelling from the UK
- a slightly higher weighting for countries that are ISO recognised countries in their own right

Give marginal priority to real countries, but do not push a place down artificially. If somewhere like Rhodes speaks the language as its primary language, it should not be dropped below a recognised country where only a tiny fraction of people speak it. Judge on real usefulness, with recognised countries edging it when the call is close.

## The rules that must not be broken

The main country for a language takes 100 and nothing else outranks it. Greece is top of a Greek list. Spain is top of a Spanish list. Italy is top of an Italian list.

A country always outranks its own regions. Greece above Crete and Rhodes. Spain above Tenerife, Majorca and Benidorm.

Every score within a single language list must be unique, and the scores must descend in the order you want the countries to appear. Never give two entries in the same list the same score. Ties are exactly what broke the old data, because the website falls back to alphabetical order and buries the country that should be first.

Every score is a whole number between 0 and 100.

@if($languages !== [])
## Handling entries that apply to both languages

An entry assigned to "both" appears in both language lists carrying the same single score, because the database can only store one score per country per card. It cannot hold a different score for each language.

Assign the scores for the "both" entries first, since each one has to fit sensibly into two lists at once and must not collide with any score in either list. Then fill in the single language entries around them in the gaps that are left.

Score a "both" entry on the language it is strongest in. Being ranked a little high in the weaker list is fine, dropping out of the visible six in the language it is actually good for is not.

If an entry is currently set to "both" but the country really only speaks one of the two languages, move it to that language instead. Rwanda and Burundi, for example, use Swahili and effectively no Arabic, so on an Arabic and Swahili card they belong under Swahili, not both. Only leave an entry on "both" when the card genuinely earns its place in that country for either language.
@endif

## What to return

Return every entry you were given, using the id exactly as it was provided.

For entries you are keeping, set show_on_product_page to true and give a score.

For aliases you are hiding, set show_on_product_page to false and leave the score empty.

@if($languages !== [])
Set language on every entry to one of {{ implode(', ', $languages) }} or both.
@endif

Give a short reason for each entry explaining the score or why it is being hidden. Keep it to one sentence.
