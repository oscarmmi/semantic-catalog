<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

interface CatalogResult {
    sku: string;
    name: string;
    category: string;
    description: string;
    tags: string[];
    price: number;
    score: number;
    matched_terms: string[];
}

interface SearchResponse {
    results: CatalogResult[];
    mode: string;
    latency_ms: number;
}

const props = defineProps<{
    productCount: number;
    categories: string[];
    examples: string[];
}>();

const query = ref('');
const results = ref<CatalogResult[] | null>(null);
const mode = ref('TF-IDF');
const latency = ref(0);
const loading = ref(false);
const error = ref('');
const searchedQuery = ref('');
const resultLabel = computed(() => {
    if (results.value === null) return 'Ready when you are';
    return `${results.value.length} ${results.value.length === 1 ? 'match' : 'matches'}`;
});

async function search(value = query.value) {
    query.value = value;
    const normalizedQuery = value.trim();

    if (!normalizedQuery) {
        error.value = 'Enter a phrase to search the catalog.';
        results.value = null;
        return;
    }

    loading.value = true;
    error.value = '';
    searchedQuery.value = normalizedQuery;

    try {
        const response = await fetch('/api/search', {
            method: 'POST',
            headers: {
                Accept: 'application/json',
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ q: normalizedQuery }),
        });
        const payload = (await response.json()) as SearchResponse & {
            errors?: { q?: string[] };
            message?: string;
        };

        if (!response.ok) {
            throw new Error(
                payload.errors?.q?.[0] ??
                    payload.message ??
                    'Search could not be completed. Please try again.',
            );
        }

        results.value = payload.results;
        mode.value = payload.mode;
        latency.value = payload.latency_ms;
    } catch (exception) {
        error.value =
            exception instanceof Error
                ? exception.message
                : 'Search could not be completed. Please try again.';
        results.value = [];
    } finally {
        loading.value = false;
    }
}
</script>

<template>
    <Head title="Semantic Catalog — Northwind Gadgets">
        <meta
            name="description"
            content="Find the right gadget with natural-language search, transparent TF-IDF scores, and optional AI reranking."
        />
    </Head>

    <main class="min-h-screen overflow-hidden">
        <header class="border-b border-[#e8e5df]">
            <div
                class="mx-auto flex max-w-7xl items-center justify-between px-5 py-5 sm:px-8"
            >
                <a
                    href="/"
                    class="flex items-center gap-3"
                    aria-label="Northwind home"
                >
                    <span
                        class="flex size-10 items-center justify-center rounded-xl bg-[#152b27] text-[#d8f36a]"
                    >
                        <svg
                            viewBox="0 0 24 24"
                            fill="none"
                            class="size-6"
                            aria-hidden="true"
                        >
                            <path
                                d="M4 6.5h16M4 12h16M4 17.5h10"
                                stroke="currentColor"
                                stroke-width="2"
                                stroke-linecap="round"
                            />
                            <circle
                                cx="18"
                                cy="17.5"
                                r="2"
                                fill="currentColor"
                            />
                        </svg>
                    </span>
                    <span>
                        <span
                            class="block text-[15px] font-semibold tracking-[-0.04em] text-[#182a26]"
                            >northwind</span
                        >
                        <span
                            class="block text-[10px] font-medium tracking-[0.17em] text-[#8a918c] uppercase"
                            >Gadgets & everyday tech</span
                        >
                    </span>
                </a>

                <div class="flex items-center gap-3">
                    <span class="hidden text-xs text-[#7e8781] sm:inline"
                        >Search demo</span
                    >
                    <span
                        class="flex items-center gap-2 rounded-full border border-[#dbe8c0] bg-[#f1f7e5] px-3 py-1.5 text-xs font-medium text-[#49612a]"
                    >
                        <span class="size-1.5 rounded-full bg-[#80a93e]"></span>
                        AI ready · no key needed
                    </span>
                </div>
            </div>
        </header>

        <section
            class="mx-auto max-w-7xl px-5 pt-12 pb-10 sm:px-8 sm:pt-16 lg:pt-20"
        >
            <div class="grid gap-10 lg:grid-cols-[1fr_300px] lg:items-end">
                <div>
                    <div
                        class="mb-5 flex items-center gap-2 text-xs font-semibold tracking-[0.17em] text-[#718071] uppercase"
                    >
                        <span class="size-2 rounded-full bg-[#d2ed59]"></span>
                        A smarter way to shop
                    </div>
                    <h1
                        class="max-w-3xl text-4xl leading-[1.04] font-medium tracking-[-0.06em] text-[#182a26] sm:text-6xl"
                    >
                        Search like a person.<br />
                        <span class="text-[#768a3e]">Find it like a pro.</span>
                    </h1>
                    <p
                        class="mt-5 max-w-xl text-base leading-7 text-[#66736c] sm:text-lg"
                    >
                        Describe what you need in your own words. Northwind’s
                        semantic catalog connects the dots and shows you why
                        each item fits.
                    </p>
                </div>

                <div class="grid grid-cols-2 gap-3 lg:pb-1">
                    <div
                        class="rounded-2xl border border-[#e6e3dc] bg-white/70 p-4"
                    >
                        <div
                            class="text-2xl font-semibold tracking-[-0.05em] text-[#20332c]"
                        >
                            {{ productCount }}
                        </div>
                        <div class="mt-1 text-xs text-[#818a83]">
                            demo products
                        </div>
                    </div>
                    <div
                        class="rounded-2xl border border-[#e6e3dc] bg-white/70 p-4"
                    >
                        <div
                            class="text-2xl font-semibold tracking-[-0.05em] text-[#20332c]"
                        >
                            {{ categories.length }}
                        </div>
                        <div class="mt-1 text-xs text-[#818a83]">
                            product families
                        </div>
                    </div>
                </div>
            </div>

            <form
                class="search-panel mt-10 rounded-[26px] p-3 sm:mt-12 sm:p-4"
                @submit.prevent="search()"
            >
                <label for="catalog-query" class="sr-only"
                    >Search the catalog</label
                >
                <div class="flex flex-col gap-3 sm:flex-row">
                    <div
                        class="flex min-w-0 flex-1 items-center gap-3 px-3 sm:px-4"
                    >
                        <svg
                            viewBox="0 0 24 24"
                            fill="none"
                            class="size-5 shrink-0 text-[#93a28f]"
                            aria-hidden="true"
                        >
                            <circle
                                cx="10.8"
                                cy="10.8"
                                r="6.8"
                                stroke="currentColor"
                                stroke-width="1.8"
                            />
                            <path
                                d="m16 16 4.2 4.2"
                                stroke="currentColor"
                                stroke-width="1.8"
                                stroke-linecap="round"
                            />
                        </svg>
                        <input
                            id="catalog-query"
                            v-model="query"
                            type="search"
                            maxlength="200"
                            placeholder="Try “quiet fan for a studio”"
                            class="h-14 min-w-0 flex-1 border-0 bg-transparent text-base text-[#20332c] outline-none placeholder:text-[#9ba49d] focus:ring-0 sm:text-lg"
                        />
                        <kbd
                            class="hidden rounded-md border border-[#e5e7e1] bg-white px-2 py-1 text-[10px] font-medium text-[#929b94] lg:inline"
                            >ENTER ↵</kbd
                        >
                    </div>
                    <button
                        type="submit"
                        :disabled="loading"
                        class="flex h-14 items-center justify-center gap-2 rounded-[17px] bg-[#18352e] px-7 text-sm font-semibold text-white transition hover:bg-[#24483d] disabled:cursor-wait disabled:opacity-70"
                    >
                        <span>{{
                            loading ? 'Finding…' : 'Find products'
                        }}</span>
                        <svg
                            viewBox="0 0 20 20"
                            fill="none"
                            class="size-4"
                            aria-hidden="true"
                        >
                            <path
                                d="M3.5 10h12m-5-5 5 5-5 5"
                                stroke="currentColor"
                                stroke-width="1.7"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            />
                        </svg>
                    </button>
                </div>
            </form>

            <div class="mt-4 flex flex-col gap-3 sm:flex-row sm:items-center">
                <span class="shrink-0 text-xs font-medium text-[#929b94]"
                    >Try a search</span
                >
                <div class="flex flex-wrap gap-2">
                    <button
                        v-for="example in examples"
                        :key="example"
                        type="button"
                        class="query-chip"
                        @click="search(example)"
                    >
                        {{ example }}
                        <span aria-hidden="true">↗</span>
                    </button>
                </div>
            </div>
        </section>

        <section class="mx-auto max-w-7xl px-5 pb-20 sm:px-8">
            <div
                class="mb-5 flex flex-wrap items-end justify-between gap-3 border-b border-[#e4e2db] pb-4"
            >
                <div>
                    <div
                        class="text-[11px] font-semibold tracking-[0.16em] text-[#919b91] uppercase"
                    >
                        The catalog
                    </div>
                    <h2
                        class="mt-1 text-xl font-semibold tracking-[-0.04em] text-[#20332c]"
                    >
                        {{
                            results === null
                                ? 'Made for the way you think'
                                : `Results for “${searchedQuery}”`
                        }}
                    </h2>
                </div>
                <div class="flex items-center gap-3 text-xs text-[#7e8981]">
                    <span>{{ resultLabel }}</span>
                    <span
                        v-if="results !== null"
                        class="size-1 rounded-full bg-[#b8c0b8]"
                    ></span>
                    <span v-if="results !== null"
                        >{{ mode }} · {{ latency }}ms</span
                    >
                </div>
            </div>

            <div
                v-if="error"
                role="alert"
                class="mb-5 rounded-xl border border-[#efc6b9] bg-[#fff4ef] px-4 py-3 text-sm text-[#9a4f39]"
            >
                {{ error }}
            </div>

            <div v-if="results === null" class="grid gap-4 sm:grid-cols-3">
                <button
                    v-for="(example, index) in examples"
                    :key="example"
                    type="button"
                    class="intro-card group text-left"
                    @click="search(example)"
                >
                    <div class="flex items-center justify-between">
                        <span
                            class="text-[11px] font-semibold tracking-[0.12em] text-[#93a28f] uppercase"
                            >Search 0{{ index + 1 }}</span
                        >
                        <span
                            class="text-[#9aa695] transition group-hover:translate-x-1 group-hover:-translate-y-1"
                            >↗</span
                        >
                    </div>
                    <p
                        class="mt-7 text-lg font-medium tracking-[-0.03em] text-[#263a32]"
                    >
                        “{{ example }}”
                    </p>
                    <p class="mt-2 text-sm leading-6 text-[#7c8880]">
                        {{
                            index === 0
                                ? 'Find low-noise cooling for focus and recording.'
                                : index === 1
                                  ? 'Discover thoughtful picks at an easy price.'
                                  : 'Surface flexible, organized cable essentials.'
                        }}
                    </p>
                </button>
            </div>

            <div
                v-else-if="results.length"
                class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4"
            >
                <article
                    v-for="(item, index) in results"
                    :key="item.sku"
                    class="result-card"
                >
                    <div class="flex items-center justify-between">
                        <span class="category-label">{{ item.category }}</span>
                        <span class="text-[11px] font-medium text-[#9aa39d]">{{
                            String(index + 1).padStart(2, '0')
                        }}</span>
                    </div>
                    <div
                        class="product-mark"
                        :class="`product-mark-${item.category.toLowerCase()}`"
                        aria-hidden="true"
                    >
                        <svg
                            v-if="item.category === 'Fans'"
                            viewBox="0 0 80 80"
                            fill="none"
                            class="size-16"
                        >
                            <circle
                                cx="40"
                                cy="32"
                                r="22"
                                stroke="currentColor"
                                stroke-width="2"
                            />
                            <circle cx="40" cy="32" r="4" fill="currentColor" />
                            <path
                                d="M40 28c-1-10 2-15 7-14 8 2 5 11-3 18M44 32c10-1 15 2 14 7-2 8-11 5-18-3m-4 0c1 10-2 15-7 14-8-2-5-11 3-18M36 32c-10 1-15-2-14-7 2-8 11-5 18 3M35 56h10m-5-2v10m-12 3h24"
                                stroke="currentColor"
                                stroke-width="2"
                                stroke-linecap="round"
                            />
                        </svg>
                        <svg
                            v-else-if="item.category === 'Cables'"
                            viewBox="0 0 80 80"
                            fill="none"
                            class="size-16"
                        >
                            <path
                                d="M28 22v15a12 12 0 0 0 24 0v-5a9 9 0 0 1 18 0v8"
                                stroke="currentColor"
                                stroke-width="3"
                                stroke-linecap="round"
                            />
                            <rect
                                x="21"
                                y="14"
                                width="14"
                                height="10"
                                rx="3"
                                stroke="currentColor"
                                stroke-width="2"
                            />
                            <path
                                d="M22 14v-4m5 4V7m5 7v-4M66 40v8m-6 0h12m-9-8h6"
                                stroke="currentColor"
                                stroke-width="2"
                                stroke-linecap="round"
                            />
                            <circle cx="40" cy="49" r="4" fill="currentColor" />
                        </svg>
                        <svg
                            v-else-if="item.category === 'Lamps'"
                            viewBox="0 0 80 80"
                            fill="none"
                            class="size-16"
                        >
                            <path
                                d="M40 13 22 42h36L40 13Z"
                                stroke="currentColor"
                                stroke-width="2"
                                stroke-linejoin="round"
                            />
                            <path
                                d="M40 42v19m-12 0h24m-19-8h14M15 29l-5-3m55 3 5-3M39 7V3m-25 52 5-2m47 2-5-2"
                                stroke="currentColor"
                                stroke-width="2"
                                stroke-linecap="round"
                            />
                        </svg>
                        <svg
                            v-else
                            viewBox="0 0 80 80"
                            fill="none"
                            class="size-16"
                        >
                            <path
                                d="M24 28v-5a16 16 0 0 1 32 0v5m-38 1h44l-4 37H22l-4-37Z"
                                stroke="currentColor"
                                stroke-width="2"
                                stroke-linejoin="round"
                            />
                            <path
                                d="M31 31v9m18-9v9M30 52h20"
                                stroke="currentColor"
                                stroke-width="2"
                                stroke-linecap="round"
                            />
                        </svg>
                    </div>
                    <div class="mt-4 flex items-start justify-between gap-3">
                        <div>
                            <p
                                class="text-[10px] font-medium tracking-[0.11em] text-[#a0a8a1] uppercase"
                            >
                                {{ item.sku }}
                            </p>
                            <h3
                                class="mt-1 text-[15px] leading-5 font-semibold tracking-[-0.03em] text-[#20332c]"
                            >
                                {{ item.name }}
                            </h3>
                        </div>
                        <span
                            class="shrink-0 pt-0.5 text-sm font-semibold text-[#294138]"
                            >${{ item.price.toFixed(2) }}</span
                        >
                    </div>
                    <p
                        class="mt-3 min-h-[60px] text-xs leading-5 text-[#77827a]"
                    >
                        {{ item.description }}
                    </p>
                    <div class="mt-4 border-t border-[#eeede8] pt-3">
                        <div class="mb-2 flex items-center justify-between">
                            <span
                                class="text-[10px] font-semibold tracking-[0.1em] text-[#8c988d] uppercase"
                                >Why it matched</span
                            >
                            <span class="text-[10px] font-medium text-[#809151]"
                                >{{ Math.round(item.score * 100) }}%
                                similarity</span
                            >
                        </div>
                        <div class="flex min-h-6 flex-wrap gap-1.5">
                            <span
                                v-for="term in item.matched_terms"
                                :key="term"
                                class="term-chip"
                                >{{ term }}</span
                            >
                            <span
                                v-if="!item.matched_terms.length"
                                class="text-[10px] text-[#9ba49c]"
                                >Related catalog match</span
                            >
                        </div>
                    </div>
                </article>
            </div>

            <div
                v-else
                class="rounded-2xl border border-dashed border-[#d9ddd5] bg-white/45 px-6 py-14 text-center"
            >
                <div
                    class="mx-auto flex size-12 items-center justify-center rounded-2xl bg-[#e9eee1] text-[#708247]"
                >
                    <svg
                        viewBox="0 0 24 24"
                        fill="none"
                        class="size-6"
                        aria-hidden="true"
                    >
                        <circle
                            cx="10.8"
                            cy="10.8"
                            r="6.8"
                            stroke="currentColor"
                            stroke-width="1.8"
                        />
                        <path
                            d="m16 16 4.2 4.2"
                            stroke="currentColor"
                            stroke-width="1.8"
                            stroke-linecap="round"
                        />
                    </svg>
                </div>
                <h3
                    class="mt-4 text-lg font-semibold tracking-[-0.03em] text-[#20332c]"
                >
                    No close matches yet
                </h3>
                <p class="mt-2 text-sm text-[#7c8880]">
                    Try a broader phrase or one of the example searches above.
                </p>
            </div>

            <footer
                class="mt-10 flex flex-col gap-2 border-t border-[#e4e2db] pt-4 text-xs text-[#929b94] sm:flex-row sm:items-center sm:justify-between"
            >
                <span>Northwind Gadgets · fictional demo catalog</span>
                <span
                    >Pure PHP TF-IDF · optional Gemini rerank · transparent
                    matches</span
                >
            </footer>
        </section>
    </main>
</template>
