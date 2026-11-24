<template>
    <div class="min-h-screen bg-slate-100 text-slate-900">
        <header class="border-b-4 border-teal-500 bg-slate-900 text-white">
            <div class="mx-auto flex max-w-7xl flex-wrap items-center justify-between gap-5 px-5 py-5 sm:px-8">
                <div>
                    <p class="text-xl font-bold">Crane Design Studio</p>
                    <p class="text-sm text-slate-300">Parametric Overhead Crane Drawing Generator</p>
                </div>
                <nav aria-label="Main" class="flex items-center gap-5 text-sm">
                    <a href="/" aria-current="page" class="rounded bg-slate-700 px-3 py-2 font-semibold">New Design</a>
                    <button disabled title="Saved designs will be available in a later phase" class="text-slate-300">
                        Saved Designs <span class="text-xs">(unavailable)</span>
                    </button>
                </nav>
            </div>
        </header>
        <main class="mx-auto max-w-7xl px-5 py-7 sm:px-8"><slot /></main>
    </div>
</template>
