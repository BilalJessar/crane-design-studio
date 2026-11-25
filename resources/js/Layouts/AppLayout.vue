<script setup>
import { Link, usePage } from '@inertiajs/vue3';

const page = usePage();
const links = [{ href: '/', label: 'New Design' }, { href: '/designs', label: 'Saved Designs' }];
</script>

<template>
    <div class="min-h-screen bg-slate-100 text-slate-900">
        <header class="border-b-4 border-teal-500 bg-slate-900 text-white">
            <div class="mx-auto flex max-w-7xl flex-wrap items-center justify-between gap-5 px-5 py-5 sm:px-8">
                <div>
                    <p class="text-xl font-bold">Crane Design Studio</p>
                    <p class="text-sm text-slate-300">Parametric Overhead Crane Drawing Generator</p>
                </div>
                <nav aria-label="Main" class="flex items-center gap-5 text-sm">
                    <Link v-for="link in links" :key="link.href" :href="link.href"
                        :aria-current="page.url.split('?')[0] === link.href ? 'page' : undefined"
                        :class="{ 'bg-slate-700': page.url.startsWith('/designs') === (link.href === '/designs') }"
                        class="rounded px-3 py-2 font-semibold hover:bg-slate-700">{{ link.label }}</Link>
                </nav>
            </div>
        </header>
        <main class="mx-auto max-w-7xl px-5 py-7 sm:px-8"><slot /></main>
    </div>
</template>
